<?php

class BigBuyOrder extends ObjectModel
{

    public $id_order;
    public $id_order_detail;
    public $sku;
    public $ref_darty;
    public $tracking_code;
    public $carrier_tracking;

    public $url_tracking;
    public $status;

    public $date_send;
    public $date_add;
    public $date_upd;


    public static $definition = array(
        'table' => 'bigbuy_orders',
        'primary' => 'id_order_detail',
        'fields' => array(
            'id_order' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'sku' => array('type' => self::TYPE_STRING, 'validate' => 'isReference', 'size' => 32, 'required' => true),
            'date_send' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'ref_darty' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'tracking_code' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'carrier_tracking' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'url_tracking' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        ),
    );


    /**
     * Récupère les commandes à envoyer
     */
    public static function getOrdersToSend()
    {
        // and o.valid = 1 and o.current_state in (2,3,71)
        $sql = 'SELECT aco.id_order, aco.id_order_detail, o.id_shop,aco.sku
               FROM ' . _DB_PREFIX_ . 'bigbuy_orders  aco
               INNER JOIN ' . _DB_PREFIX_ . 'orders  o on o.id_order = aco.id_order
              
               WHERE aco.date_send = \'0000-00-00 00:00:00\'
               ORDER BY aco.id_order, aco.id_order_detail
           ';


        $orders_to_send = [];
        $result = Db::getInstance()->ExecuteS($sql);
        var_export($result);
        $order = null;
        
        if (!empty($result)) {
            foreach ($result as $row) {

                $orderDetail = new OrderDetail(intval($row["id_order_detail"]));
                $product = new Product($orderDetail->product_id, false, null, $row['id_shop']);

                if (!isset($order->id) || $order->id != intval($row["id_order"])) {
                    $order = new Order(intval($row["id_order"]));
                    $delivery_address = new Address($order->id_address_delivery);
                    $orders_to_send[] = [
                        'order' => [
                            'internalReference' => $order->id,
                            'language' => 'fr', 
                            'paymentMethod' => $order->payment,
                            'carriers' => [
                                ['name'=> "dhl"]
                            ],
                            'shippingAddress' => [
                                'firstName' => $delivery_address->firstname,
                                'lastName' => $delivery_address->lastname,
                                'country' => Country::getIsoById($delivery_address->id_country),
                                'postcode' => $delivery_address->postcode,
                                'town' => $delivery_address->city,
                                'address' => $delivery_address->address1,
                                'phone' => str_replace('+33(0)', '0', str_replace(' ', '', $delivery_address->phone)),
                                'email'  => 'sophie@alienor-distribution.fr',
                                'vatNumber' => '',
                                'companyName' => $delivery_address->company,
                                'comment' => ''
                            ], 
                            'products' => [
                                [
                                    'reference' => $row["sku"],
                                    'quantity' => $orderDetail->product_quantity
                                ]
                            ]
                        ]
                    ];
                }else{
                    $orders_to_send[array_key_last($orders_to_send)]['order']['products'][] =  [
                        'reference' =>  $row["sku"],
                        'quantity' => $orderDetail->product_quantity
                    ];

                }
            }

        }


        return $orders_to_send;
    }


    public function sendOrder($order_to_send=null)
    {

        $data = '';

        // URL de l'API  
        $url = BigBuyTvs::API_BASE_URL.'/rest/order/create.json';
       

        $data = json_encode($order_to_send);
        

        // En-têtes de la requête
        $headers = [
            'Authorization: Bearer '. BigBuyTvs::API_KEY_PROD,
            'Content-Type: application/json'
        ];

        // Configuration de la requête
        $options = [
            'http' => [
                'header' => $headers,
                'method' => 'POST',
                'content' => $data,
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ]
        ];

        // Création du contexte de la requête
        $context = stream_context_create($options);

        // Envoi de la requête et récupération de la réponse
        $response = file_get_contents($url, false, $context);
        // Vérification de la réponse
        if ($response === false) {
            //return "Erreur lors de l'envoi de la requête API.";
            // Erreur lors de la requête
            echo "Erreur lors de l'envoi de la requête API.";

            return false;
             
        }else {
            $response_data = json_decode($response, true);

            if(array_key_exists('order_id', $response_data)){
                var_export( 'ajout de la ecommade id externe', $response_data['order_id']);
                return true;
            }else{
                return false;
            }
        }

       
    }

    public function sendAllOrders($orders_to_send = []){
        $send_orders_id = []; 
        foreach($orders_to_send as $order_to_send){
            if($this->sendOrder($order_to_send)){
                $send_orders_id[] =  $order_to_send['order']['internalReference'];
                var_export(json_encode($send_orders_id));
            }
            sleep(2);
        }

        return count($send_orders_id)>0;
    }


    /**
     * @param $order
     * @param $id_order_state
     */
    private static function _changeStatus($order, $id_order_state)
    {
        // pas de changement de statut si commande annulée
        if ($order->current_state == (int)Configuration::get('PS_OS_CANCELED')) {
            return null;
        }

        $context = Context::getContext();


        $history = new OrderHistory();
        $history->id_order = $order->id;
        $history->id_employee = (int)$context->employee->id;
        $use_existings_payment = !$order->hasInvoice();
        $history->changeIdOrderState($id_order_state, $order, $use_existings_payment);

        $templateVars = false;

        if ($id_order_state == Configuration::get('PS_OS_SHIPPING')) {
            $carrier = new Carrier($order->id_carrier, $order->id_lang);
            $templateVars = array('{followup}' => str_replace('@', $order->shipping_number, $carrier->url));
        }

        $history->addWithemail(true, $templateVars);
    }


    public static function saveSend($ref_order)
    {

        $order = new Order((int)$ref_order);

        $id_order_state = (int)Configuration::get('BIGBUY_STATUS_CREATE');
        if ($order->current_state != $id_order_state) {
            self::_changeStatus($order, $id_order_state);
        }

        return Db::getInstance()->update(
            BigBuyOrder::$definition['table'],
            array(
                "date_send" => date('c'),
                "date_upd" => date('c')),
            "id_order = " . (int)$ref_order
        );
    }

  
}