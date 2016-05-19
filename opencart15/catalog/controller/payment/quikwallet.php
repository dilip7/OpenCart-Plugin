<?php

class ControllerPaymentQuikWallet extends Controller
{
    protected function index()
    {
        $this->language->load('payment/quikwallet');

        //$this->log->debug("index called controller quikwallet");


        $this->data['button_confirm'] = $this->language->get('button_confirm');
        $this->data['text_title'] = $this->language->get('text_title');
        $this->data['version'] = $this->language->get('version');
        $this->data['text_wait'] = $this->language->get('text_wait');
        $this->data['text_attention'] = $this->language->get('text_attention');

        $this->load->model('checkout/order');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        // our code

        $productinfo = "Order ". $this->session->data['order_id'];
        $service_provider = 'quikwallet';

        $quikwallet_args = array(
            'amount' => $order_info['total'],
            'firstname' => $order_info['payment_firstname'],
            'quik_email' =>  $order_info['email'],
            'phone' => $order_info['telephone'],
            'productinfo' => $productinfo ,
            'lastname' => $order_info['payment_lastname'],
            'address1' => $order_info['payment_address_1'],
            'address2' => $order_info['payment_address_2'],
            'city' => $order_info['payment_city'],
            'state' => $order_info['payment_city'],
            'country' => $order_info['payment_country'],
            'zipcode' => $order_info['payment_postcode'],
            'order_id' => $this->session->data['order_id'],
            'service_provider' => $service_provider
        );


        $quikwallet_args_array = array();
        foreach ($quikwallet_args as $key => $value) {
            if (in_array($key, array(
                'quik_email',
                'phone'
            ))) {
                $quikwallet_args_array[] = "<input name='$key' value='$value'/>";
            } else {
                $quikwallet_args_array[] = "<input type='hidden' name='$key' value='$value'/>";
            }
        }

        $this->data['inputs_array'] = implode('', $quikwallet_args_array);
        $this->data['cancel_url']  =   '<a class="button cancel" href="'.$this->url->link('checkout/checkout').'">Cancel order and restore cart</a>!';

        $this->data['currency_code'] = $order_info['currency_code'];
        $this->data['total'] = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false) * 100;
        $this->data['merchant_order_id'] = $this->session->data['order_id'];
        $this->data['card_holder_name'] = $order_info['payment_firstname'].' '.$order_info['payment_lastname'];
        $this->data['email'] = $order_info['email'];
        $this->data['phone'] = $order_info['telephone'];
        $this->data['name'] = $this->config->get('config_name');
        $this->data['lang'] = $this->session->data['language'];
        $this->data['return_url'] = $this->url->link('payment/quikwallet/callback');


        if (file_exists(DIR_TEMPLATE.$this->config->get('config_template').'/template/payment/quikwallet.tpl')) {
            $this->template = $this->config->get('config_template').'/template/payment/quikwallet.tpl';
        } else {
            $this->template = 'default/template/payment/quikwallet.tpl';
        }

        $this->render();
    }

    public function callback()
    {
        $request_params = array_merge($_GET, $_POST);

        $this->load->model('checkout/order');

        //$this->log->debug("callback called controller quikwallet ");

        //$this->log->debug($this->request->request);

        //$this->log->debug("config log " , $this->config->get("quikwallet_partnerid"));

        //if form is submit through quikwallet form on front end
        if (isset($request_params["quikwalletsubmit"])) {
            global $wpdb;

            $table = DB_PREFIX . 'quik_pay';

            // Getting data to build url
            $partnerid = $this->config->get("quikwallet_partnerid");

            // Url to call
            $url       = $this->config->get("quikwallet_url") . "/" .$partnerid . "/requestpayment";
            $secret    = $this->config->get("quikwallet_secret");
            //$partnerurl = $this->settings['partnerurl'];

            /*
             * force partnerurl to checkout url. Currently payment response only working on view
             * cart page
             */

            //$partnerurl = $woocommerce->cart->get_cart_url();

            //$partnerurl = site_url();

            $mobile  = $request_params["phone"];
            $amount  = $request_params["amount"];
            $name    = $request_params["firstname"];
            $email   = $request_params["quik_email"];
            $address = $request_params["address1"] . ", " . $request_params["address2"];
            $city    = $request_params["city"];
            $pincode = $request_params["zipcode"];
            $orderid = $request_params["order_id"];
            $date_c  = date('Y-m-d H:i');

            $this->session->data['order_id'] = $orderid;

            $partnerurl = $this->url->link('payment/quikwallet/callback');

          /*
          try {
              $order      = new WC_Order($_POST["order_id"]);
              $partnerurl = $order->get_checkout_order_received_url();
          }
          catch (Exception $e) {
              $msg = "Error";
          }
           */

            /*
             * Record order details
             *
             */

            $escape_email =  $this->db->escape($email);
            $escape_date = $this->db->escape($date_c);
            $escape_address = $this->db->escape($address);

            $sql = "REPLACE INTO `$table` (
                `order_no` ,
                `date_c` ,
                `name`,
                `email_id`,
                `address`,
                `city` ,
                `pincode` ,
                `mobile`,
                `amount` ,
                `q_id`,
                `hash`,
                `checksum`,
                `order_status`)
                VALUES(
                    '$orderid',
                    '$escape_date',
                    '$name',
                    '$escape_email',
                    '$escape_address',
                    '$city',
                    '$pincode',
                    '$mobile',
                    '$amount',
                    '','','','') ";

            //$this->log->debug("MYSQL QUERY" , $sql);

            $this->db->query($sql);

            $postFields = Array(
                "partnerid" => $partnerid, //fixed
                "secret" => $secret, //fixed
                //"outletid" => "39", //fixed - only for restaurant
                "redirecturl" => "$partnerurl" . "", //fixed
                "mobile" => $mobile, //client mobile no
                "billnumbers" => $orderid, //unique order no in the system
                "email" => $email, //unique order no in the system
                "amount" => $amount //amount for the transaction
            );


            //$this->log->debug("Post fields are " , $postFields);
            // AJAX call starts
            // Building post data
            $postFields = http_build_query($postFields);

            //$this->log->debug("POST url is  " , $url);

            //$this->log->debug(" POST postfields build query are " , $postFields);


            //cURL Request
            $ch = curl_init();

            //set the url, number of POST vars, POST data

            // defaults setting
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
            curl_setopt($ch,CURLOPT_HEADER,false);
            curl_setopt($ch,CURLOPT_ENCODING,'gzip,deflate');
            curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,15);
            curl_setopt($ch,CURLOPT_TIMEOUT,30);
            curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
            curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);

            // contextual info apart from defaults
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);

            $info = curl_getinfo($ch);

            //$this->log->debug("FULL CH IS ",$info);


            $this->response = curl_exec($ch);

            $info = curl_getinfo($ch);

            //$this->log->debug("FULL CH IS ",$info);

            if ($this->response === false) {
                $this->response = curl_error($ch);
            }

            // Fetching response
            $resp = $this->response;

            //$this->log->debug("Response was " , $resp);


            // Decode
            $r = json_decode($resp, true);



            if ($r['status'] == 'failed') {
                $message = $r['message'];

            } else if ($r['status'] == 'success') {
                $id     = $r['data']['id'];
                $hash   = $r['data']['hash'];
                $newurl = $r['data']['url'];


                //$this->log->debug("JSON RESPONSE --> ",$r['data']);

                $id2 = substr($id, 2);

                $escape_q_id =  $this->db->escape($id2);
                $escape_hash =  $this->db->escape($hash);

                // post API DB part

                $sql = "UPDATE `$table`  SET `q_id` = '$escape_q_id' , `hash` = '$escape_hash' WHERE
                    `order_no` = '$orderid' ";

                //$this->log->debug("MYSQL UPDATE QUERY" , $sql);

                $this->db->query($sql);

                //$this->response->redirect($newurl);

                header("Location: " . $newurl);


                // below redirecting to quikwallet payment gateway after updating q_id and hash
                // e.g $newurl = https://app.quikpay.in/#paymentrequest/6uP0SoY

                //header("Location: " . $newurl);
            } else {
                //print "Invalid Response";
            }

            // Exit Strategy
            exit();

        }

        /*
         * After redirection from payment gateway to our site update quik_pay
         */

        if (isset($_GET['status']) && isset($_GET['id']) && isset($_GET['checksum'])) {

            $table = DB_PREFIX . 'quik_pay';

            $status   = $_GET["status"];
            $id       = $_GET["id"];
            $checksum = $_GET["checksum"];
            $order_id = $this->session->data['order_id'];

            $partnerid = $this->config->get("quikwallet_partnerid");
            $secret    = $this->config->get("quikwallet_secret");

            $text = "status=$status&id=$id&billnumbers=$order_id";
            $hmac = hash_hmac('sha256', $text, $secret);

            $order_info = $this->model_checkout_order->getOrder($order_id);

            //$this->log->debug("logging order ", $order_info);


            if ($hmac == $checksum) {

                $escape_order_status =  $this->db->escape($status);
                $escape_checksum =  $this->db->escape($checksum);
                $escape_q_id = $this->db->escape($id);

                // post API DB part

                $sql = "UPDATE `$table`  SET `order_status` = '$escape_order_status' , `checksum` = '$escape_checksum' WHERE
                    `q_id` = '$escape_q_id' ";

                //$this->log->debug("MYSQL UPDATE QUERY" , $sql);

                $this->db->query($sql);

                $order_info = $this->model_checkout_order->getOrder($order_id);

                //$this->log->debug("logging order ", $order_info);

                if ($order_id != '') {
                    try {

                        if ($order_info['order_status_id'] !== 5) { // completed

                            //$this->log->debug("logging order  status here", $order_info['order_status_id']);

                            $status = strtolower($status);
                            if ($status == "paid") {

                                if ($order_info['order_status_id'] == 2) {  // Processing
                                    // pending
                                    $this->model_checkout_order->confirm($order_id, 2, 'Transaction Processing. QuikWallet ID: '.$id, true);
                                } else {
                                    // success
                                    $this->model_checkout_order->confirm($order_id, 15, 'Payment Successful. QuikWallet Payment Id:'.$id, true);

                                    echo '<html>'."\n";
                                    echo '<head>'."\n";
                                    echo '  <meta http-equiv="Refresh" content="0; url='.$this->url->link('checkout/success').'">'."\n";
                                    echo '</head>'."\n";
                                    echo '<body>'."\n";
                                    echo '  <p> Payment Successful. Please follow <a href="'.$this->url->link('checkout/success').'">link</a>!</p>'."\n";
                                    echo '</body>'."\n";
                                    echo '</html>'."\n";
                                    exit();

                                }
                            } else {
                                // failure
                                $this->model_checkout_order->confirm($order_id, 10, 'Transaction ERROR.<br/>QuikWallet ID: ' . $id, true);

                                echo '<html>'."\n";
                                echo '<head>'."\n";
                                echo '  <meta http-equiv="Refresh" content="0; url='.$this->url->link('checkout/checkout').'">'."\n";
                                echo '</head>'."\n";
                                echo '<body>'."\n";
                                echo '  <p>Please follow <a href="'.$this->url->link('checkout/checkout').'">link</a>!</p>'."\n";
                                echo '</body>'."\n";
                                echo '</html>'."\n";
                                exit();
                            }
                        }
                    }
                    catch (Exception $e) {
                        // $errorOccurred = true;
                        // failure
                        $this->model_checkout_order->confirm($order_id, 10, 'Transaction ERROR.<br/>QuikWallet ID: ' . $id, true);

                        echo '<html>'."\n";
                        echo '<head>'."\n";
                        echo '  <meta http-equiv="Refresh" content="0; url='.$this->url->link('checkout/checkout').'">'."\n";
                        echo '</head>'."\n";
                        echo '<body>'."\n";
                        echo '  <p>Please follow <a href="'.$this->url->link('checkout/checkout').'">link</a>!</p>'."\n";
                        echo '</body>'."\n";
                        echo '</html>'."\n";
                        exit();
                    }
                }

                //update the order table with id
                //send mail
            } else {
                //print "Invalid response, please try again <hr>\n";

                //wp_redirect(home_url());
              $this->model_checkout_order->confirm($order_id,10, 'Transaction ERROR.<br/>QuikWallet ID: ' . $id, true);

              echo '<html>'."\n";
              echo '<head>'."\n";
              echo '  <meta http-equiv="Refresh" content="0; url='.$this->url->link('checkout/checkout').'">'."\n";
              echo '</head>'."\n";
              echo '<body>'."\n";
              echo '  <p>Payment failed, Invalid Authentication. Please follow <a href="'.$this->url->link('checkout/checkout').'">link</a>!</p>'."\n";
              echo '</body>'."\n";
              echo '</html>'."\n";
              exit();


            }


            exit();
        }


    }



    private function is_serialized($value, &$result = null)
    {
        // Bit of a give away this one
        if (!is_string($value)) {
            return false;
        }
        if (empty($value)) {
            return false;
        }
        // Serialized false, return true. unserialize() returns false on an
        // invalid string or it could return false if the string is serialized
        // false, eliminate that possibility.
        if ($value === 'b:0;') {
            $result = false;

            return true;
        }

        $length = strlen($value);
        $end = '';

        switch ($value[0]) {
        case 's':
            if ($value[$length - 2] !== '"') {
                return false;
            }
        case 'b':
        case 'i':
        case 'd':
            // This looks odd but it is quicker than isset()ing
            $end .= ';';
        case 'a':
        case 'O':
            $end .= '}';

            if ($value[1] !== ':') {
                return false;
            }

            switch ($value[2]) {
            case 0:
            case 1:
            case 2:
            case 3:
            case 4:
            case 5:
            case 6:
            case 7:
            case 8:
            case 9:
                break;

            default:
                return false;
            }
            case 'N':
                $end .= ';';

                if ($value[$length - 1] !== $end[0]) {
                    return false;
                }
                break;

            default:
                return false;
        }

        if (($result = @unserialize($value)) === false) {
            $result = null;

            return false;
        }

        return true;
    }

}
