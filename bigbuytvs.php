<?php
/**
 * 2007-2022 PrestaShop
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
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2022 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . "/models/BigBuyCatalog.php";
require_once dirname(__FILE__) . "/models/BigBuyOrder.php";



class BigBuyTvs extends Module
{
    const CLASS_LOG = "BigBuy";
    const CLASS_LOG_CRON = "BigBuy Cron";
    const SEND_TYPE_NORMAL = "Normal";
    const SEND_TYPE_RAPIDE = "Rapide";
    
    //production config
    // const API_KEY_PROD = "MGJlNzcwZWI4Njk2NDVjMTZjOTk3ODhlN2EzNGE1NDkyZmMzMjA1ZjZhYzJhZDQwZjZmMmFjNTczOWFiNTM3Mg";
    // const  API_BASE_URL = "https://api.bigbuy.eu";

    //sandbox config
    const API_KEY_PROD = "MThlMzlkOTBlNGRmNmY1MmRiODgxMjlmMmQ1MWQ0OWVhMzBhYTRlYzkzZGM5Mzg1ZjkzMzBmN2FlOGE4ZWU2MQ";
    const  API_BASE_URL = "https://api.sandbox.bigbuy.eu";

    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'bigbuytvs';
        $this->tab = 'market_place';
        $this->version = '1.0.0';
        $this->author = 'Tvs service';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;
        
        parent::__construct();

        $this->displayName = $this->l('Commandes Bigbuy');
        $this->description = $this->l('Gestion des commandes bigbuy et mise a jour des produits selectionnés');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    { 
        include(dirname(__FILE__) . '/sql/install.php');
        $this->addConfigFeature();
        return parent::install() &&
            $this->registerHook('actionValidateOrder') &&
            $this->registerHook('actionOrderStatusUpdate');
    }

    public function uninstall()
    {

        include(dirname(__FILE__) . '/sql/uninstall.php');

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitBigBuyModule')) == true) {

            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        return $this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitBigBuyModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        $order_states = OrderState::getOrderStates($this->context->language->id);

        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(

                    array(
                        'type' => 'select',
                        'label' => $this->l('ID Supplier'),
                        'name' => 'BIGBUY_ID_SUPPLIER',
                        'options' => array(
                            'query' => Supplier::getSuppliers(),
                            'id' => 'id_supplier',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Status pour commande envoyée'),
                        'name' => 'BIGBUY_STATUS_CREATE',
                        'options' => array(
                            'query' => $order_states,
                            'id' => 'id_order_state',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Email d\'envoi des commandes'),
                        'name' => 'BIGBUY_EMAIL_ORDERS',
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'BIGBUY_ID_SUPPLIER' => Configuration::get('BIGBUY_ID_SUPPLIER', null),
            'BIGBUY_STATUS_CREATE' => Configuration::get('BIGBUY_STATUS_CREATE', null),
            'BIGBUY_EMAIL_ORDERS' => Configuration::get('BIGBUY_EMAIL_ORDERS', null),

        );
    }

    

    public function hookActionOrderStatusUpdate($params)
    {
        if ($params['newOrderStatus']->id == _PS_OS_PAYMENT_) {
            return $this->hookActionValidateOrder(array('order' => new Order($params['id_order'])));
        }
    }

    public function hookUpdateOrderStatus($params)
    {
        return $this->hookActionOrderStatusUpdate($params);
    }

    public function hookActionValidateOrder($params)
    {
        $order = $params['order'];

        PrestaShopLogger::AddLog(sprintf("Declenchement validation commande BigBuy, OrderId : %d"
            , $order->id), 1, self::CLASS_LOG);

        $id_supplier = Configuration::get('BIGBUY_ID_SUPPLIER');

        // trace les lignes de commandes passées
        if ($id_supplier){   
            foreach ($this->getListByIdSupplier($order->id, $id_supplier) as $order_detail) {

                $bigBuyOrder = new BigBuyOrder();
                $bigBuyOrder->force_id = true;
                $bigBuyOrder->id_order = $order->id;
                $bigBuyOrder->sku = BigBuyTvs::removeRefPrefix($order_detail['product_reference']); 
                $bigBuyOrder->id = $order_detail['id_order_detail'];


                if (!$bigBuyOrder->add(true, true)) {
                    PrestaShopLogger::AddLog(sprintf("Impossible d'enregistrer la commande BigBuy, OrderId : %d | OrderDetail : %d | Sku : %s  "
                        , $order->id, $order_detail['id_order_detail'], $order_detail['product_supplier_reference']), 3, self::CLASS_LOG);
                }
            }
        }

    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }

        //$this->_sendUpdatedProductByEmail();
        //$this->cronSendOrder();

       $this->cronUpdatePriceAndQuantity();
    }


    /**
     * @param $action
     */
    public function cron($action, $argv)
    {

        try {
            $start = microtime(true);
            // check token
            PrestaShopLogger::AddLog(sprintf("Lancement CRON bigbuy avec action %s", $action), 1, self::CLASS_LOG_CRON);

            if (method_exists($this, "cron" . $action)) {
                if (sizeof($argv) > 1) {
                    $this->{"cron" . $action}($argv);

                } else {
                    $this->{"cron" . $action}();
                }
            } else
                PrestaShopLogger::AddLog(sprintf("Action %s inconnue", $action), 3, self::CLASS_LOG_CRON);

            $end = microtime(true);
            $time = $end - $start;
            PrestaShopLogger::AddLog(sprintf("Action %s exécuté en %d secondes", $action, $time), 1, self::CLASS_LOG_CRON);
        } catch (PrestaShopException $e) {
            PrestaShopLogger::AddLog($e->displayMessage(), 3, self::CLASS_LOG_CRON);
        }
    }


  

    /**
     * @throws Exception
     */
    public function cronUpdatePriceAndQuantity()
    {
        $bigBuyCatalog = new BigBuyCatalog();
       
        if ( $bigBuyCatalog->updatePriceAndQuantity()) {
            // $this->_sendUpdatedProductByEmail();
        }
        
    }
    /**
     * @throws Exception
     */
    public function cronSendOrder()
    {
      
        $bigBuyOrder = new BigBuyOrder();
        $orders_to_send = $bigBuyOrder->getOrdersToSend();
        
        $orders_to_send;

        if($bigBuyOrder->sendAllOrders($orders_to_send)){
       
            $this->_sendOrdersByEmail();

            foreach ($orders_to_send as $order) {
                BigBuyOrder::saveSend($order['order']["internalReference"]);

            }
        }
        
    }



    private function _sendUpdatedProductByEmail()
    {

        $id_lang = Context::getContext()->language->id;
      

        $send=Mail::Send(
        $id_lang,
        'update-product',
        Mail::l('BigBuy  - Informations test du ', $id_lang) . date("d/m/Y"),
        array("date_report" => date("d/m/Y")),
        explode(",", Configuration::get("BIGBUY_EMAIL_ORDERS")),
        null,
        null,
        null,
        null,
        null,
        realpath(dirname(__FILE__) . "/mails") . "/",
        true,
        null,
        null
        );
        var_export($send);
        if ($send )  echo "Email envoyé";
        else
            echo "Impossible d'envoyé l'email";
    }

    private function _sendOrdersByEmail()
    {

        $id_lang = Context::getContext()->language->id;


        $send = Mail::Send(
            $id_lang,
            'order-bigbuy',
            Mail::l('BigBuy  - Informations test du ', $id_lang) . date("d/m/Y"),
            array("date_report" => date("d/m/Y")),
            explode(",", Configuration::get("BIGBUY_EMAIL_ORDERS")),
            null,
            null,
            null,
            null,
            null,
            realpath(dirname(__FILE__) . "/mails") . "/",
            true,
            null,
            null
        );
        var_export($send);
        if ($send)
            echo "Email envoyé";
        else
            echo "Impossible d'envoyé l'email";
    }


    public function getListByIdSupplier($order_id, $id_supplier ){

        $results = Db::getInstance()->executeS('SELECT od.* FROM '._DB_PREFIX_.'order_detail od INNER JOIN '._DB_PREFIX_
            .'product p on p.id_product = od.product_id WHERE od.id_order = '
            .$order_id.' and p.id_supplier = '.$id_supplier);
        
        return is_array($results)? $results : [];
    }

    public static function removeRefPrefix($ref_with_prefix){
        return str_replace('BB-', '',$ref_with_prefix);
    }

    public static function addRefPrefix($ref){
        return 'BB-'.$ref;
    }

    public function addfeatured() {
        $feature = new Feature();
        $feature->name = array((int)Configuration::get('PS_LANG_DEFAULT') => 'Type d\'envoi');
        $feature->add();
    
        $feature_value_normal = new FeatureValue();
        $feature_value_normal->id_feature = $feature->id;
        $feature_value_normal->value = array((int)Configuration::get('PS_LANG_DEFAULT') => self::SEND_TYPE_NORMAL);
        $feature_value_normal->add();
    
        $feature_value_rapide = new FeatureValue();
        $feature_value_rapide->id_feature = $feature->id;
        $feature_value_rapide->value = array((int)Configuration::get('PS_LANG_DEFAULT') =>self::SEND_TYPE_RAPIDE);
        $feature_value_rapide->add();
    
        return $feature->id;
    }
    
    public function addConfigFeature() {
        if (!Configuration::get('ADD_FEATURE_KEY_BB', false)) {
            $featureId = $this->addfeatured();
            Configuration::updateValue('ADD_FEATURE_KEY_BB', $featureId);
        }
    
        return true;
    }

    public static function getSendType($day){
        return intval($day) > 1 ? BigBuyTvs::SEND_TYPE_NORMAL : BigBuyTvs::SEND_TYPE_RAPIDE;
    }
}
