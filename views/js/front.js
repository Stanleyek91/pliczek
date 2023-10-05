/**
*  @author    Prestapro
*  @copyright Prestapro
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)*
*/

var productAttributeList = (function() {
	var typing_timer,
		typing_interval = 500,
		inputsMouseDown = false,
		quantityBlur = false,
		container,
		name,
		formData,
		id_product,
		carouselIdentifier,
		carouselId,
		productMiniature,
		className,
		count,
		c,
		prodAttrs,
		src,
		$img,
		quantity;

	function afterImageLoad() {
		container.closest(className).replaceWith(productMiniature);

		if (typeof($.fn.niceSelect) != 'undefined') {
			$(productMiniature).find('select').niceSelect();
		}

		productMiniature.addClass('hover-fix');
		setTimeout(function () {
			productMiniature.removeClass('hover-fix');
		}, 10);

		if (typeof (productAttributeListClassicThemeFix) !== 'undefined') {
			productAttributeListClassicThemeFix(productMiniature);
		}

		setSpin(productMiniature.find('.quantity_wanted'));

		if (name === 'quantity_wanted' && quantityBlur !== true) {
			productMiniature.find('.quantity_wanted').focus();
		} else {
			quantityBlur = false;
		}
	}

	function updateProductMiniature($el) {
		container = $el.closest('.productattributelist');
		container.find('.pal-add-to-cart').prop('disabled', true);
		name = $el.attr('name');
		formData = container.find('.productattributelist-form').serializeArray();
		id_product = container.attr('data-id');
		formData.push({name: 'id_product', value: id_product});

		if ($el.closest('.easycarousel').length) {
			carouselIdentifier = $el.closest('.easycarousel').attr('id');
			carouselId = carouselIdentifier.replace(/^.*_/, '');
			formData.push({name: 'id_carousel', value: carouselId});
		}

		$.post(product_attributes_ajax_display, formData, function(res) {
			productMiniature = $(res.html);
			className = '.product-miniature';
			count = 0;
			c = '';
			prodAttrs = productMiniature.find('.productattributelist');
			productMiniature.append(prodAttrs.removeClass('hidden'));
			src = productMiniature.find('img').first().attr('src');
			$img  = $('<img/>');

			$img.on('load', function() {
				afterImageLoad()
			});

			$img.on('error', function() {
				afterImageLoad();
			});

			$img[0].src = src;

			var isw_images = productMiniature.find('.pal-is-active .pal-is-container');

			if (isw_images.length > 0 && typeof imageSwitcher !== 'undefined' && typeof imageSwitcher === 'object') {
				isw_images.each(function() {
					$(this).after($(this).clone().removeClass('pal-is-container pal-swiper-container').addClass('is-container swiper-container'));
				});
				imageSwitcher.prepare(productMiniature.find('.thumbnail-container'), productMiniature.find('.swiper-container').first());
				productMiniature.find('.product-thumbnail').removeClass('product-thumbnail').addClass('pal-product-thumbnail');

				setTimeout(function() {
					imageSwitcher.update();
				}, 500);
			}
		}, 'json').error(function(err) {
			console.log(err.responseText);
		});
	}

	function setSpin(quantityInput) {
		quantityInput.each(function() {
			quantity = $(this);

			if (quantity.parent().hasClass('bootstrap-touchspin')) {
				return true;
			}

			quantity.TouchSpin({
				verticalbuttons: true,
				verticalupclass: 'material-icons touchspin-up',
				verticaldownclass: 'material-icons touchspin-down',
				buttondown_class: 'btn btn-touchspin js-touchspin',
				buttonup_class: 'btn btn-touchspin js-touchspin',
				min: parseInt(quantity.attr('min'), 10),
				max: 1000000
			});

			quantity.on('touchspin.on.stopspin', function(event) {
				$(this).parent().addClass('input-mask');
				updateProductMiniature($(this));
			});

			quantity.on('keyup paste', function() {
				input = $(this);
				clearTimeout(typing_timer);

				typing_timer = setTimeout(function() {
					updateProductMiniature(input);
				}, typing_interval);
			});
		});
	}

	function loadAttributes() {
		setSpin($('.productattributelist .quantity_wanted'));

		$('.productattributelist').each(function() {
			if (typeof($.fn.niceSelect) != 'undefined') {
				$(this).find('select').niceSelect();
			}

			$(this).closest('.product-miniature').append($(this));
		});
	}

	return {
		init: function() {
			if ($('.productattributelist').length === 0) {
				return false;
			}

			$(document).on('mousedown', '.productattributelist .regular-button, .productattributelist select, .productattributelist input:not(.quantity_wanted)', function() {
				inputsMouseDown = true;
			});

			loadAttributes();

			if (typeof(product_attributes_data.change_image_hover) != 'undefined' && product_attributes_data.change_image_hover) {
				$(document).on('mouseover', '.productattributelist .color_pick', function() {
					var img_src = $(this).attr('data-img'),
						product_miniature = $(this).closest('.product-miniature');

					if (typeof imageSwitcher !== 'undefined' && typeof imageSwitcher === 'object') {
						var id_attribute = $(this).attr('id'),
							isw_images = product_miniature.find('.pal-is-images-' + id_attribute + ' .pal-is-container');

						if (isw_images.length > 0) {
							product_miniature.find('.is-container, .product-thumbnail').first().after(isw_images.clone().removeClass('is-hidden'));
							product_miniature.find('.product-thumbnail').removeClass('product-thumbnail').addClass('pal-product-thumbnail').hide();
							product_miniature.find('.is-container').remove();
							product_miniature.find('.thumbnail-container > .pal-is-container').removeClass('pal-is-container pal-swiper-container').addClass('is-container swiper-container');
							imageSwitcher.prepare(product_miniature, product_miniature.find('.is-container'));
						} else {
							product_miniature.find('.pal-product-thumbnail').removeClass('pal-product-thumbnail').addClass('product-thumbnail').show();
							product_miniature.find('.is-container').remove();
						}
					}

					if (img_src != '') {
						product_miniature.find('.product-thumbnail img, .is-container .swiper-slide-active img').attr('src', img_src);
						product_miniature.find('.product-thumbnail').show();
					}
				});
			}

			if (typeof(product_attributes_data.only_show) == 'undefined' || !product_attributes_data.only_show) {
				$(document).on('click', '.productattributelist .regular-button', function(e) {
					$(this).closest('.productattributelist').find('.regular-button').removeClass('active');
					$(this).addClass('active');
					$(this).find('input')[0].checked = true;
					$(this).find('input').trigger('change');
				});

				$(document).on('click','.productattributelist .color_pick', function() {
					var id_attribute = $(this).attr('id');
					$(this).closest('.group').find('input').val(id_attribute).trigger('change');
				});

				$(document).on('change', '.productattributelist select, .productattributelist input:not(.quantity_wanted)', function() {
					updateProductMiniature($(this));
					inputsMouseDown = false;
				});
			}

			prestashop.on('updateProductList', function() {
				loadAttributes();
			});

			$(document).ajaxComplete(function(event, xhr, settings) {
				if (settings.url.indexOf('module=eis') !== -1
				|| settings.url.indexOf('module=prestasearch') !== -1) {
					setTimeout(function() {
						loadAttributes();
					}, 1500);
				}
			});
		}
	};
})();

$(function() {
	productAttributeList.init();
});
