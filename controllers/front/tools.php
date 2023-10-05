<?php
/**
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class ProductAttributeListToolsModuleFrontController extends ModuleFrontControllerCore
{
    public function initContent()
    {
        parent::initContent();
        $factory = new ProductPresenterFactory($this->context, new TaxConfiguration());
        $this->settings = $factory->getPresentationSettings();
        $this->presenter = $factory->getPresenter();
    }

    public function getCarouselSettings($id_carousel)
    {
        $shop_ids = Shop::getContextListShopID();
        $where = 'WHERE id_shop IN ('.implode(', ', array_map('intval', $shop_ids)).')';
        $where .= ' AND active = 1 AND id_carousel='.(int)$id_carousel;
        $json = Db::getInstance()->ExecuteS('
            SELECT settings
            FROM '._DB_PREFIX_.'easycarousels
            '.$where);
        $settings = Tools::jsonDecode($json[0]['settings'], true);
        return $settings['tpl'];
    }

    public function getProductInfos($id_product, $id_lang, $id_shop, $settings, $id_product_attribute)
    {
        $show_cat = $settings['product_cat'];
        $show_man = $settings['product_man'];
        $now = date('Y-m-d H:i:s');
        $nb_days_new = Configuration::get('PS_NB_DAYS_NEW_PRODUCT');

        $product_data = Db::getInstance()->executeS('
            SELECT p.*, product_shop.*, pl.*, image.id_image, il.legend,
            '.($show_cat ? 'cl.name AS cat_name, cl.link_rewrite as cat_link_rewrite, ' : '').'
            '.($show_man ? 'm.name AS man_name, ' : '').'
            DATEDIFF(\''.pSQL($now).'\', p.date_add) < '.(int)$nb_days_new.' AS new
            FROM '._DB_PREFIX_.'product p
            '.Shop::addSqlAssociation('product', 'p').'
            INNER JOIN '._DB_PREFIX_.'product_lang pl
                ON (pl.id_product = p.id_product
                AND pl.id_shop = '.(int)$id_shop.' AND pl.id_lang = '.(int)$id_lang.')
            '.($show_cat ? '
                LEFT JOIN '._DB_PREFIX_.'category_lang cl
                    ON (cl.id_category = product_shop.id_category_default
                    AND cl.id_shop = '.(int)$id_shop.' AND cl.id_lang = '.(int)$id_lang.')
            ' : '').'
            '.($show_man ? '
                LEFT JOIN '._DB_PREFIX_.'manufacturer m
                    ON m.id_manufacturer = p.id_manufacturer AND m.active = 1
            ' : '').'
            LEFT JOIN '._DB_PREFIX_.'image image
                ON (image.id_product = p.id_product AND image.cover = 1)
            LEFT JOIN '._DB_PREFIX_.'image_lang il
                ON (il.id_image = image.id_image AND il.id_lang = '.(int)$id_lang.')
            WHERE p.id_product = '.pSQL($id_product).'
        ')[0];

        $id = $id_product;
        $pd = $product_data;

        // out_of_stock and id_product_attribute are required to avoid extra queries in getProductProperties
        $pd['out_of_stock'] = StockAvailable::outOfStock($id, $id_shop);
        $pd['id_product_attribute'] = $pd['cache_default_attribute'];
        $pd = Product::getProductProperties($id_lang, $pd);


        $image_type = $settings['image_type'] != 'original' ? $settings['image_type'] : null;
        $link_rewrite = $pd['link_rewrite'];
        $pd['id_product_attribute'] = $id_product_attribute;

        $pd = $this->presenter->present($this->settings, $pd, $this->context->language);

        if (!$image_type) {
            $original_img_src = $this->context->link->getImageLink($pd['link_rewrite'], $pd['cover']['id_image']);
            $pd['cover']['bySize']['original']['url'] = $original_img_src;
        }

        $second_images = array();
        if (!empty($settings['second_image'])) {
            $second_images_data = Db::getInstance()->executeS('
                SELECT i.id_image
                FROM '._DB_PREFIX_.'image i
                '.Shop::addSqlAssociation('image', 'i').',
                '._DB_PREFIX_.'product_attribute_image ia
                '.Shop::addSqlAssociation('product_attribute_image', 'ia').'
                WHERE i.id_product = '.pSQL($id_product).'
                AND ia.id_product_attribute = '.pSQL($id_product_attribute).'
                AND ia.id_image = i.id_image
                AND i.id_image <>'.pSQL($pd['cover']['id_image']).'
            ');
            if (isset($second_images_data[0])) {
                $second_images = $second_images_data[0];
            }
        }

        if (!empty($second_images)) {
            $src  = $this->context->link->getImageLink($link_rewrite, $second_images['id_image'], $image_type);
            $pd['second_img_src'] = $src;
        }

        if ($show_man && !empty($pd['id_manufacturer'])) {
            $alias = Tools::str2url($pd['man_name']);
            $pd['man_url'] = $this->getItemUrl('manufacturer', $pd['id_manufacturer'], $alias);
            if ($show_man != 1) {
                $pd['man_img_src'] = $this->getImageUrl('manufacturer', $pd['id_manufacturer'], $show_man);
            }
        }

        if ($show_cat && !empty($pd['id_category_default'])) {
            $pd['cat_url'] = $this->getItemUrl('category', $pd['id_category_default'], $pd['cat_link_rewrite']);
        }

        return $pd;
    }

    public function getItemUrl($item_type, $id, $alias = null)
    {
        $url = '#';
        $method = 'get'.Tools::ucfirst($item_type).'Link';
        if (is_callable(array($this->context->link, $method))) {
            $url = $this->context->link->$method($id, $alias);
        }
        return $url;
    }


    public function displayAjaxMiniature()
    {
        $rawProduct = array();
        $id_product = Tools::getValue('id_product');
        $rawProduct['id_product'] = $id_product;
        $group = Tools::getValue('group');
        $id_product_attribute = null;

        if (!empty($group)) {
            $id_product_attribute = (int)Product::getIdProductAttributesByIdAttributes($id_product, $group, true);
        }

        $rawProduct['id_product_attribute'] = $id_product_attribute;
        $rawProduct['quantity_wanted'] = Tools::getValue('quantity_wanted');
        $product = (new ProductAssembler($this->context))->assembleProduct($rawProduct);
        $product_present = $this->presenter->present($this->settings, $product, $this->context->language);
        ob_end_clean();
        header('Content-Type: application/json');
        if (Tools::isSubmit('id_carousel')) {
            $id_carousel = Tools::getValue('id_carousel');
            $settings = $this->getCarouselSettings($id_carousel);
            $id_lang = $this->context->language->id;
            $id_shop = $this->context->shop->id;
            $product_info = $this->getProductInfos($id_product, $id_lang, $id_shop, $settings, $id_product_attribute);
            $product_info['quantity_wanted'] = Tools::getValue('quantity_wanted');
            $this->context->smarty->assign(array(
                'product' => $product_info,
                'settings' => $settings,
                'currency_iso_code' => $this->context->currency->iso_code,
                'cart_page_url' => $this->context->link->getPageLink('cart', $this->context->controller->ssl),
                'static_token' => Tools::getToken(false),
            ));
            $html = $this->module->fetch(_PS_MODULE_DIR_.'easycarousels/views/templates/hook/product-item-17.tpl');
            $this->ajaxDie(Tools::jsonEncode(array(
                'html' => $html,
            )));
        } else {
            $this->ajaxDie(Tools::jsonEncode(array(
                'html' => $this->render('catalog/_partials/miniatures/product', array('product' => $product_present)),
            )));
        }
    }
}
