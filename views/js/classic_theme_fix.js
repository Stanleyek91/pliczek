/**
*  @author    Prestapro
*  @copyright Prestapro
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)*
*/

function productAttributeListClassicThemeFix(productMiniature) {
	var FLAG_MARGIN = 10;
	var $percent = productMiniature.find('.discount-percentage');
	let $onsale =  productMiniature.find('.on-sale');
	let $new = productMiniature.find('.new');

	if ($percent.length) {
		$new.css('top', $percent.height() * 2 + FLAG_MARGIN);
		$percent.css('top',-productMiniature.find('.thumbnail-container').height() + productMiniature.find('.product-description').height() + FLAG_MARGIN);
	}

	if ($onsale.length) {
		$percent.css('top', parseFloat($percent.css('top')) + $onsale.height() + FLAG_MARGIN);
		$new.css('top', ($percent.height() * 2 + $onsale.height()) + FLAG_MARGIN * 2);
	}
}
