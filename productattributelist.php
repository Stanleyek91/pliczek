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

class Productattributelist extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        if (!defined('_PS_VERSION_')) {
            exit;
        }

        $this->name = 'productattributelist';
        $this->tab = 'front_office_features';
        $this->version = '1.2.5';
        $this->author = 'Prestapro';
        $this->need_instance = 1;
        $this->bootstrap = true;
        $this->module_key = '7532a14cbb3f111503ed296bac87f258';

        parent::__construct();

        $this->displayName = $this->l('Product attribute list');
        $this->description = $this->l('Allows to select individual product attributes directly in product lists.');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->cNms = array(
          'only_show',
          'on_hover',
          'show_quantity',
          'show_labels',
          'show_color',
          'show_select',
          'show_prices',
          'show_radio',
          'regular_radio',
          'show_add_to_cart',
          'custom_select',
          'show_more',
          'enable_links',
          'change_image_hover',
        );
    }

    public function install()
    {
        Tools::clearCache();
        Configuration::updateValue('PRODUCTATTRIBUTELIST_DATA', serialize(array(
            'only_show'=>'0',
            'on_hover'=>'0',
            'show_quantity'=>'0',
            'show_labels'=>'1',
            'show_color'=>'1',
            'show_select'=>'1',
            'show_prices'=>'0',
            'show_radio'=>'1',
            'show_add_to_cart'=>'0',
            'show_more'=>'0',
            'custom_select'=>'0',
            'regular_radio'=>'button',
            'enable_links'=>'0',
            'change_image_hover'=>'0',
        )));

        return parent::install()
        && $this->registerHook('header')
        && $this->registerHook('displayProductPriceBlock')
        && $this->registerHook('backOfficeHeader');
    }

    public function uninstall()
    {
        Tools::clearCache();
        Configuration::deleteByName('PRODUCTATTRIBUTELIST_DATA');
        return parent::uninstall();
    }

    private function checkModule($name)
    {
        return Module::isInstalled($name) && Module::isEnabled($name);
    }

    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submitProductattributelistModule')) {
            $data = unserialize(Configuration::get('PRODUCTATTRIBUTELIST_DATA'));

            foreach ($this->cNms as $name) {
                $data[$name] = Tools::getValue($name);
            }

            if (Configuration::updateValue('PRODUCTATTRIBUTELIST_DATA', serialize($data))) {
                Tools::clearCache();
                $output.= $this->displayConfirmation($this->l("Configuration updated"));
            } else {
                $output.= $this->displayError($this->l('Error when try to update configuration'));
            }
        }

        $this->context->smarty->assign(array(
            'readme_link' => $this->_path.'readme_en.pdf',
        ));
        $output.= $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');
        return $output.$this->renderForm();
    }

    protected function renderForm()
    {
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitProductattributelistModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $fields_value = unserialize(Configuration::get('PRODUCTATTRIBUTELIST_DATA'));

        foreach ($this->cNms as $name) {
            if (!isset($fields_value[$name])) {
                $fields_value[$name] = 0;
            }
        }

        $helper->tpl_vars = array(
            'fields_value' => $fields_value,
        );
        return $helper->generateForm(array($this->getConfigForm()));
    }

    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Only show'),
                        'name' => 'only_show',
                        'desc' => $this->l('Only show attributes without click actions'),
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Enable links'),
                        'name' => 'enable_links',
                        'desc' => $this->l('Add link to product page with attribute. Work only if "Only show" enabled'),
                        'hint' => 'This work only if you enable "Only show"',
                        'is_bool' => true,
                        'disabled' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Show on hover'),
                        'name' => 'on_hover',
                        'desc' => $this->l('Show attributes when customer hover over product container'),
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Change image on hover over color'),
                        'name' => 'change_image_hover',
                        'desc' => $this->l('Show attributes when customer hover over product container'),
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Show quantity'),
                        'name' => 'show_quantity',
                        'desc' => $this->l('Show quantity input'),
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Show labels'),
                        'name' => 'show_labels',
                        'desc' => $this->l('Show labels near attributes'),
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Show colors'),
                        'name' => 'show_color',
                        'desc' => $this->l('Show attributes with type color'),
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Show selects'),
                        'name' => 'show_select',
                        'desc' => $this->l('Show attributes with type select'),
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Custom selects'),
                        'name' => 'custom_select',
                        'desc' => $this->l('Show select with custom styles'),
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Show radios'),
                        'name' => 'show_radio',
                        'desc' => $this->l('Show attributes with type radio'),
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Show prices'),
                        'name' => 'show_prices',
                        'desc' => $this->l('Show price for each attribute'),
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Add to cart'),
                        'name' => 'show_add_to_cart',
                        'desc' => $this->l('Show add to cart button'),
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('More button'),
                        'name' => 'show_more',
                        'desc' => $this->l('Show more button'),
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Radio button type'),
                        'name' => 'regular_radio',
                        'options' => array(
                            'query' => array(
                                array(
                                    'id_regular_radio' => 'radio',
                                    'regular_radio' => $this->l('Radio'),
                                ),
                                array(
                                    'id_regular_radio' => 'button',
                                    'regular_radio' => $this->l('Button'),
                                ),
                            ),
                            'id' => 'id_regular_radio',
                            'name' => 'regular_radio',
                        ),
                        'col' => 2,
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    public function hookDisplayProductPriceBlock($params)
    {
        if ($params['type'] == 'before_price' && isset($params['product']['id_product'])) {
            $id_product = $params['product']['id_product'];
            $quantity_wanted = isset($params['product']['quantity_wanted']) ? $params['product']['quantity_wanted'] : 1;
            $min_quantity = $params['product']['minimal_quantity'];
            $quantity = ($quantity_wanted && $quantity_wanted > $min_quantity) ? $quantity_wanted : $min_quantity;
            $id_product_attribute = $params['product']['id_product_attribute']; // need test if no combinations
            $data = unserialize(Configuration::get('PRODUCTATTRIBUTELIST_DATA'));
            $ps_order_out_of_stock = Configuration::get('PS_ORDER_OUT_OF_STOCK');
            $ps_stock_management = Configuration::get('PS_STOCK_MANAGEMENT');
            $this->context->smarty->assign(array(
                'product_attributes_data'=> $data,
                'id_product'=> $id_product,
                'quantity'=> $quantity,
                'product' => $params['product'],
                'ps_order_out_of_stock' => $ps_order_out_of_stock,
                'ps_stock_management' => $ps_stock_management,
            ));
            $this->assignAttributesGroups($id_product, $id_product_attribute);
            return $this->context->smarty->fetch($this->local_path.'views/templates/hook/hook.tpl');
        }
    }

    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('configure') == $this->name) {
            $this->context->controller->addJquery();
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    public function hookHeader()
    {
        $data = unserialize(Configuration::get('PRODUCTATTRIBUTELIST_DATA'));
        $theme_name = $this->context->shop->theme->getName();
        $params = array('ajax'=> 1, 'action' => 'miniature');
        $ajax_display = $this->context->link->getModuleLink($this->name, 'tools', $params);
        Media::addJsDef(array(
          'product_attributes_ajax_display' => $ajax_display,
          'product_attributes_data' => $data,
        ));
        $path_css = 'modules/'.$this->name.'/views/css/';
        $path_js = 'modules/'.$this->name.'/views/js/';

        if (isset($data['custom_select']) && $data['custom_select'] == '1') {
            $this->context->controller->registerStylesheet('modules-pal-select', $path_css.'nice-select.css');
            $this->context->controller->registerJavascript('modules-pal-select', $path_js.'jquery.nice-select.js');
        }

        $this->context->controller->registerStylesheet('modules-productattributelist', $path_css.'front.css');
        $this->context->controller->registerJavascript('modules-productattributelist', $path_js.'front.js');

        if ($theme_name == 'classic') {
            $this->context->controller->registerStylesheet('modules-pal-cfix', $path_css.'classic_theme_fix.css');
            $this->context->controller->registerJavascript('modules-pal-cfix', $path_js.'classic_theme_fix.js');
        }
    }

    private function getProductImageLink($product, $id_image, $image_home_name)
    {
        if ($product && $id_image) {
            if (ImageType::typeAlreadyExists($image_home_name)) {
                $link = $this->context->link->getImageLink(
                    $product->getFieldByLang('link_rewrite'),
                    $id_image,
                    $image_home_name
                );
            } else {
                $link = $this->context->link->getImageLink(
                    $product->getFieldByLang('link_rewrite'),
                    $id_image
                );
            }

            return $link;
        }
    }

    protected function assignAttributesGroups($id_product, $id_product_attribute)
    {
        $colors = array();
        $groups = array();
        $product = new Product($id_product, true);
        //$all_combinations = $product->getAttributeCombinations($this->context->language->id);
        //var_dump($product->getPrice(true, $all_combinations[2]['id_product_attribute']));
        $combination = $product->getAttributeCombinationsById($id_product_attribute, $this->context->language->id);
        $comb = array();

        foreach ($combination as $attr) {
            $comb[$attr['id_attribute_group']] = $attr['id_attribute'];
        }

        $attributes_groups = $product->getAttributesGroups($this->context->language->id);
        $image_switcher = false;
        $combination_images = $image_switcher_containers = array();

        if ($this->checkModule('imageswitcher')) {
            $image_switcher = Module::getInstanceByName('imageswitcher');
            $combination_images = $product->getCombinationImages($this->context->language->id);
            $active_container = false;
        }

        if (is_array($attributes_groups) && $attributes_groups) {
            foreach ($attributes_groups as $row) {
                $n = $row['id_attribute'];
                $texture = _PS_COL_IMG_DIR_.$row['id_attribute'].'.jpg';
                // Color management

                if (isset($row['is_color_group']) &&
                $row['is_color_group'] &&
                (isset($row['attribute_color']) &&
                $row['attribute_color']) ||
                (file_exists($texture))) {
                    $colors[$n]['value'] = $row['attribute_color'];
                    $colors[$n]['name'] = $row['attribute_name'];

                    if (!isset($colors[$n]['attributes_quantity'])) {
                        $colors[$n]['attributes_quantity'] = 0;
                    }

                    if (file_exists($texture)) {
                        $colors[$n]['img'] = _THEME_COL_DIR_.$row['id_attribute'].'.jpg';
                    }

                    $colors[$n]['attributes_quantity'] += (int)$row['quantity'];
                }

                $n_group = $row['id_attribute_group'];
				
				

               if (!isset($groups[$n_group])) {
    $groups[$n_group] = array(
        'group_name' => $row['group_name'],
        'name' => $row['public_group_name'],
        'group_type' => $row['group_type'],
        'default' => -1,
    );
}

$c = $comb;
$c[$n_group] = $n;

// Uzyskaj wszystkie kombinacje atrybutów dla danego produktu
$product = new Product($id_product);
$combinations = $product->getAttributeCombinations();

// Znajdź kombinację, która pasuje do wybranych atrybutów
foreach ($combinations as $combination) {
    $combinationAttributes = explode('-', $combination['id_product_attribute']);
    if (array_diff(array_values($c), $combinationAttributes)) {
        continue;
    }
    // Znaleziono kombinację, $combination['id_product_attribute'] to poszukiwane ID.
    $id_p_att = (int)$combination['id_product_attribute'];
    break;
}

if (!isset($groups[$n_group])) {
    $groups[$n_group] = array(
        'group_name' => $row['group_name'],
        'name' => $row['public_group_name'],
        'group_type' => $row['group_type'],
        'default' => -1,
    );
}

$c = $comb;
$c[$n_group] = $n;

$id_p_att = null; // Inicjalizacja zmiennej wartością domyślną

$product = new Product($id_product);
$combinations = $product->getAttributeCombinations();

foreach ($combinations as $combination) {
    $combinationAttributes = array($combination['id_attribute']); // Uzyskaj id_attribute z kombinacji
    if (array_diff(array_values($c), $combinationAttributes)) {
        continue;
    }
    $id_p_att = (int)$combination['id_product_attribute'];
    break;
}

if ($id_p_att !== null) { // Sprawdzenie, czy znaleziono kombinację
    $att_price = $product->getPrice(true, $id_p_att);
    $link = $this->context->link->getProductLink(
        $product->id,
        $product->getFieldByLang('link_rewrite'),
        $product->category,
        $product->ean13,
        $this->context->language->id,
        null,
        $id_p_att,
        false,
        false,
        true
    );
} else {
    // Obsługa sytuacji, gdy nie znaleziono kombinacji
    $att_price = $product->getPrice(); // Cena domyślna produktu
    $link = $this->context->link->getProductLink($product->id); // Link domyślny produktu
}

$image_home_name = ImageType::getFormattedName('home');
$img_link = '';




                if ($image_switcher !== false && is_array($combination_images)) {
                    $image_links = array();

                    foreach ($combination_images as $id_product_attribute => $images) {
                        if ($id_product_attribute == $id_p_att) {
                            if (count($images) > 1) {
                                foreach ($images as $image) {
                                    $image_links[] = $this->getProductImageLink(
                                        $product,
                                        $image['id_image'],
                                        $image_home_name
                                    );
                                }
                            } else {
                                $img_link = $this->getProductImageLink(
                                    $product,
                                    $images[0]['id_image'],
                                    $image_home_name
                                );
                            }
                        }
                    }

                    if (!empty($image_links)) {
                        $image_switcher_containers[$row['id_attribute']]['html'] =
                            $image_switcher->displayImageContainer(
                                $image_links,
                                'pal-is-container pal-swiper-container'
                            );

                        if ($row['id_attribute'] == $comb[$n_group] && $active_container == false) {
                            $image_switcher_containers[$row['id_attribute']]['active'] = 1;
                            $active_container = true;
                        }
                    }
                } 
				else {
    $productObj = new Product($id_product);
    $combinationImages = $productObj->getCombinationImages($this->context->language->id);
    if (isset($combinationImages[$id_p_att]) && is_array($combinationImages[$id_p_att]) && count($combinationImages[$id_p_att]) > 0) {
        $id_image = reset($combinationImages[$id_p_att]); // Pobranie pierwszego obrazu dla kombinacji
        $img_link = $this->getProductImageLink(
            $product,
            $id_image,
            $image_home_name
        );
    } else {
        // Ustaw wartość domyślną dla $img_link lub obsłuż błąd inaczej
        $img_link = ''; // Przykładowa wartość domyślna
    }
}

                $groups[$n_group]['prices'][$row['id_attribute']] = Tools::displayPrice($att_price);
                $groups[$n_group]['links'][$row['id_attribute']] = $link;
                $groups[$n_group]['images'][$row['id_attribute']] = $img_link;
                $groups[$n_group]['attributes'][$row['id_attribute']] = $row['attribute_name'];

                if ($row['default_on'] && $groups[$row['id_attribute_group']]['default'] == -1) {
                    $groups[$n_group]['default'] = (int)$row['id_attribute'];
                }

                $groups[$n_group]['selected'] = $comb[$n_group];

                if (!isset($groups[$n_group]['attributes_quantity'][$row['id_attribute']])) {
                    $groups[$n_group]['attributes_quantity'][$row['id_attribute']] = 0;
                }

                $groups[$n_group]['attributes_quantity'][$row['id_attribute']] += (int)$row['quantity'];
                // Call getPriceStatic in order to set $combination_specific_price
            }

            $current_selected_attributes = array();
            $count = 0;

            foreach ($groups as &$group) {
                $count++;

                if ($count > 1) {
                    //find attributes of current group, having a possible combination with current selected
                    $id_product_attributes_result = Db::getInstance()->executeS(
                        'SELECT pac.`id_product_attribute`
                        FROM `'._DB_PREFIX_.'product_attribute_combination` pac
                        INNER JOIN `'._DB_PREFIX_.'product_attribute` pa
                        ON pa.id_product_attribute = pac.id_product_attribute
                        WHERE id_product = '.(int)pSQL($product->id).' AND id_attribute
                        IN ('.implode(',', array_map('intval', $current_selected_attributes)).')
                        GROUP BY id_product_attribute
                        HAVING COUNT(id_product) = '.count($current_selected_attributes)
                    );

                    $id_product_attributes = array();

                    foreach ($id_product_attributes_result as $row) {
                        $id_product_attributes[] = $row['id_product_attribute'];
                    }

                    $id_attributes = array();

                    $id_attributes = Db::getInstance()->executeS(
                        'SELECT `id_attribute`
                        FROM `'._DB_PREFIX_.'product_attribute_combination` pac2
                            WHERE `id_product_attribute` IN
                                ('.implode(',', array_map('intval', $id_product_attributes)).')
                            AND id_attribute
                              NOT IN ('.implode(',', array_map('intval', $current_selected_attributes)).')'
                    );

                    foreach ($id_attributes as $k => $row) {
                        $id_attributes[$k] = (int)$row['id_attribute'];
                    }

                    foreach (array_keys($group['attributes']) as $key) {
                        if (!in_array((int)$key, $id_attributes)) {
                            unset($group['attributes'][$key]);
                            unset($group['attributes_quantity'][$key]);
                        }
                    }
                }

                $current_selected_attributes[] = $group['selected'];
            }

            // wash attributes list (if some attributes are unavailables and if allowed to wash it)
            if (!Product::isAvailableWhenOutOfStock($product->out_of_stock)
            && Configuration::get('PS_DISP_UNAVAILABLE_ATTR') == 0) {
                foreach ($groups as &$group) {
                    foreach ($group['attributes_quantity'] as $key => &$quantity) {
                        if ($quantity <= 0) {
                            unset($group['attributes'][$key]);
                        }
                    }
                }

                foreach ($colors as $key => $color) {
                    if ($color['attributes_quantity'] <= 0) {
                        unset($colors[$key]);
                    }
                }
            }
        }

        $this->context->smarty->assign(array(
            'groups' => $groups,
            'colors' => (count($colors)) ? $colors : false,
            'id_product' => $id_product,
            'is_containers' => $image_switcher_containers,
        ));
    }
}
