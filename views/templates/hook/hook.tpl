{*
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author     PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2019 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<div class="productattributelist {if isset($product_attributes_data.on_hover) && $product_attributes_data.on_hover}on-hover{/if}" data-id="{$id_product|intval}">
	{if isset($product_attributes_data.only_show) && $product_attributes_data.only_show}
		<div class="only-show">
			{foreach from=$groups key=id_attribute_group item=group}
				{if $group.attributes|@count}
					<div class="attribute_fieldset">
						{if ($group.group_type == 'color') && $product_attributes_data.show_color}
							{if isset($product_attributes_data.show_labels) && $product_attributes_data.show_labels}
								
							{/if}

							<div class="attribute_list">
								{foreach from=$group.attributes key=id_attribute item=group_attribute}
									{if isset($product_attributes_data.enable_links) && $product_attributes_data.enable_links}
										<a href="{$group.links[$id_attribute]|escape:'html':'UTF-8'}">
									{/if}

									<span id="{$id_attribute|intval}" data-img="{$group.images[$id_attribute]|escape:'html':'UTF-8'}" name="{$colors.$id_attribute.name|escape:'html':'UTF-8'}" class="color_pick" style="{if isset($colors.$id_attribute.img)}background-image:url({$colors.$id_attribute.img|escape:'html':'UTF-8'});background-size:contain{else}background:{$colors.$id_attribute.value|escape:'html':'UTF-8'}{/if};" title="{$colors.$id_attribute.name|escape:'html':'UTF-8'}">
									</span>

									{if isset($product_attributes_data.enable_links) && $product_attributes_data.enable_links}
										</a>
									{/if}
								{/foreach}
							</div>
						{else}
							{if isset($product_attributes_data.show_labels) && $product_attributes_data.show_labels}
								<label class="attribute_label">{$group.name|escape:'html':'UTF-8'}&nbsp;</label>
							{/if}

							<div class="attribute_list">
								{foreach from=$group.attributes key=id_attribute item=group_attribute}
									{if isset($product_attributes_data.enable_links) && $product_attributes_data.enable_links}
										<a href="{$group.links[$id_attribute]|escape:'html':'UTF-8'}">
									{/if}

									<span>{$group_attribute|escape:'html':'UTF-8'}{if isset($product_attributes_data.show_prices) && $product_attributes_data.show_prices} - {$group.prices[$id_attribute]|escape:'html':'UTF-8'}{/if}</span>

									{if isset($product_attributes_data.enable_links) && $product_attributes_data.enable_links}
										</a>
									{/if}
								{/foreach}
							</div>
						{/if}
						<!-- end attribute_list -->
					</div>
				{/if}
			{/foreach}
		</div> <!-- end attributes -->
	{else}
		<form class="productattributelist-form">
			{if $product_attributes_data.show_quantity}
				{if isset($product_attributes_data.show_labels) && $product_attributes_data.show_labels}
					<label for="quantity_wanted">{l s='Quantity' mod='productattributelist'}</label>
				{/if}

				<input class="quantity_wanted" type="text" value="{$quantity|escape:'html':'UTF-8'}" name="quantity_wanted" aria-label="{l s='Quantity' mod='productattributelist'}" />
			{/if}

			{if isset($groups)}
				<!-- attributes -->
				<div>
					{foreach from=$groups key=id_attribute_group item=group}
						{if $group.attributes|@count}
							<div class="attribute_fieldset">
								{if ($group.group_type == 'select') && $product_attributes_data.show_select}
									{if isset($product_attributes_data.show_labels) && $product_attributes_data.show_labels}
										<label class="attribute_label">{$group.name|escape:'html':'UTF-8'}&nbsp;</label>
									{/if}

									<div class="attribute_list">
										<select name="group[{$id_attribute_group|intval}]" class="group facet-dropdown" data-group="{$id_attribute_group|intval}">
											{foreach from=$group.attributes key=id_attribute item=group_attribute}
												<option value="{$id_attribute|intval}" {if $group.selected == $id_attribute} selected="selected"{/if} title="{$group_attribute|escape:'html':'UTF-8'}">{$group_attribute|escape:'html':'UTF-8'}{if isset($product_attributes_data.show_prices) && $product_attributes_data.show_prices} - {$group.prices[$id_attribute]|escape:'html':'UTF-8'}{/if}</option>
											{/foreach}
										</select>
									</div>
								{elseif ($group.group_type == 'color') && $product_attributes_data.show_color}
									{if isset($product_attributes_data.show_labels) && $product_attributes_data.show_labels}
										<label class="attribute_label">{$group.name|escape:'html':'UTF-8'}&nbsp;</label>
									{/if}

									<div class="attribute_list">
										<div class="group colors" data-group="{$id_attribute_group|intval}">
											{foreach from=$group.attributes key=id_attribute item=group_attribute}
											<span data-img="{$group.images[$id_attribute]|escape:'html':'UTF-8'}" id="{$id_attribute|intval}" name="{$colors.$id_attribute.name|escape:'html':'UTF-8'}" class="color_pick{if ($group.selected == $id_attribute)} selected{/if}" style="{if isset($colors.$id_attribute.img)}background-image:url({$colors.$id_attribute.img|escape:'html':'UTF-8'});background-size:contain{else}background:{$colors.$id_attribute.value|escape:'html':'UTF-8'}{/if};" title="{$colors.$id_attribute.name|escape:'html':'UTF-8'}">
											</span>
											{/foreach}
											<input name="group[{$id_attribute_group|intval}]" type="hidden" value="{$group.selected|intval}" {if ($group.selected == $id_attribute)} checked{/if} />
										</div>
									</div>
								{elseif ($group.group_type == 'radio') && $product_attributes_data.show_radio}
									{if isset($product_attributes_data.show_labels) && $product_attributes_data.show_labels}
										
									{/if}

									<div class="attribute_list">
										<form class="group" data-group="{$id_attribute_group|intval}">
											{foreach from=$group.attributes key=id_attribute item=group_attribute}
												{if isset($product_attributes_data.regular_radio) && $product_attributes_data.regular_radio == 'button'}
													<span class="regular-button btn btn-secondary{if ($group.selected == $id_attribute)} active{/if} pal-btn">
														<input name="group[{$id_attribute_group|intval}]" type="radio" class="attribute_radio" value="{$id_attribute|escape:'html':'UTF-8'}" {if ($group.selected == $id_attribute)} checked{/if} />
														<span>{$group_attribute|escape:'html':'UTF-8'}{if isset($product_attributes_data.show_prices) && $product_attributes_data.show_prices} - {$group.prices[$id_attribute]|escape:'html':'UTF-8'}{/if}</span>
													</span>
												{else}
													<span class="custom-radio{if ($group.selected == $id_attribute)} active{/if}">
														<input  name="group[{$id_attribute_group|intval}]" type="radio" class="attribute_radio" value="{$id_attribute|escape:'html':'UTF-8'}" {if ($group.selected == $id_attribute)} checked{/if} /><span></span>
													</span>

													<span>{$group_attribute|escape:'html':'UTF-8'}{if isset($product_attributes_data.show_prices) && $product_attributes_data.show_prices} - {$group.prices[$id_attribute]|escape:'html':'UTF-8'}{/if}</span>
												{/if}
											{/foreach}
										</form>
									</div>
								{/if}
								<!-- end attribute_list -->
							</div>
						{/if}
					{/foreach}
				</div> <!-- end attributes -->
			{/if}
		</form>

		<form type="post" action="{$urls.pages.cart|escape:'html':'UTF-8'}" class="buttons">
			<input type="hidden" name="token" value="{$static_token|escape:'html':'UTF-8'}">
			<input type="hidden" name="id_product" value="{$product.id_product|escape:'html':'UTF-8'}">
			<input type="hidden" name="id_product_attribute" value="{$product.id_product_attribute|escape:'html':'UTF-8'}" class="product_page_product_id">
			<input type="hidden" name="qty" value="{if isset($product.quantity_wanted) && $product.quantity_wanted}{$product.quantity_wanted|escape:'html':'UTF-8'}{else}{$product.minimal_quantity|escape:'html':'UTF-8'}{/if}">

			{if isset($product_attributes_data.show_add_to_cart) && $product_attributes_data.show_add_to_cart}
				<button data-button-action="add-to-cart"{if ($ps_order_out_of_stock == 0 || $ps_stock_management == 1) && (($product.quantity < $product.minimal_quantity) || (isset($product.quantity_wanted) && ($product.quantity < $product.quantity_wanted)))} disabled{/if} class="btn btn-primary pal-add-to-cart"><i class="material-icons shopping-cart"></i>{l s='Add to cart' d='Shop.Theme.Actions' mod='productattributelist'}</button>
			{/if}

			{if isset($product_attributes_data.show_more) && $product_attributes_data.show_more}
				<a href="{$product.url|escape:'html':'UTF-8'}" class="btn btn-tertiary-outline pal-btn">{l s='More' d='Shop.Theme.Actions' mod='productattributelist'}</a>
			{/if}
		</form>
	{/if}
</div>
{if isset($is_containers) && !empty($is_containers)}
	{foreach from=$is_containers key=id_attribute item=container}
		<div class="pal-is-images-{$id_attribute|intval}{if isset($container.active) && $container.active == 1} pal-is-active{/if}">
			{$container.html nofilter}{* Cannot be escaped *}
		</div>
	{/foreach}
{/if}
