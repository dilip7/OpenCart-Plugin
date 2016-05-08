<?php

class ControllerPaymentQuikWallet extends Controller
{
    public function index()
    {

        $this->log->debug("index called controller quikwallet");


        $data['button_confirm'] = $this->language->get('button_confirm');

        $this->load->model('checkout/order');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        // our code

        $productinfo = "Order ". $this->session->data['order_id'];
        $service_provider = 'quikwallet';

        $quikwallet_args = array(
          'amount' => $order_info['total'],
          'firstname' => $order_info['payment_firstname'],
          'email' =>  $order_info['email'],
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
            'email',
            'phone'
          ))) {
            $quikwallet_args_array[] = "<input name='$key' value='$value'/>";
          } else {
            $quikwallet_args_array[] = "<input type='hidden' name='$key' value='$value'/>";
          }
        }

        $data['inputs_array'] = implode('', $quikwallet_args_array);
        $data['cancel_url']  =   '<a class="button cancel" href="'.$this->url->link('checkout/failure').'">Cancel order and restore cart</a>!';

        $data['currency_code'] = $order_info['currency_code'];
        $data['total'] = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false) * 100;
        $data['merchant_order_id'] = $this->session->data['order_id'];
        $data['card_holder_name'] = $order_info['payment_firstname'].' '.$order_info['payment_lastname'];
        $data['email'] = $order_info['email'];
        $data['phone'] = $order_info['telephone'];
        $data['name'] = $this->config->get('config_name');
        $data['lang'] = $this->session->data['language'];
        $data['return_url'] = $this->url->link('payment/quikwallet/callback');


        if (file_exists(DIR_TEMPLATE.$this->config->get('config_template').'/template/payment/quikwallet.tpl')) {
            return $this->load->view($this->config->get('config_template').'/template/payment/quikwallet.tpl', $data);
        } else {
            return $this->load->view('payment/quikwallet', $data);
        }
    }

    public function callback()
    {
      $this->load->model('checkout/order');

      $this->log->debug("callback called controller quikwallet ");

      $this->log->debug($this->request->request);

      $this->log->debug("config log " , $this->config->get("quikwallet_partnerid"));

      //if form is submit through quikwallet form on front end
      if (isset($this->request->request["quikwalletsubmit"])) {
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

          $mobile  = $this->request->request["phone"];
          $amount  = $this->request->request["amount"];
          $name    = $this->request->request["firstname"];
          $email   = $this->request->request["email"];
          $address = $this->request->request["address1"] . ", " . $this->request->request["address2"];
          $city    = $this->request->request["city"];
          $pincode = $this->request->request["zipcode"];
          $orderid = $this->request->request["order_id"];
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

          $this->log->debug("MYSQL QUERY" , $sql);

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


          $this->log->debug("Post fields are " , $postFields);
          // AJAX call starts
          // Building post data
          $postFields = http_build_query($postFields);

          $this->log->debug("POST url is  " , $url);

          $this->log->debug(" POST postfields build query are " , $postFields);


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

          $this->log->debug("FULL CH IS ",$info);


          $this->response = curl_exec($ch);

          $info = curl_getinfo($ch);

          $this->log->debug("FULL CH IS ",$info);

          if ($this->response === false) {
            $this->response = curl_error($ch);
          }

          // Fetching response
          $resp = $this->response;

          $this->log->debug("Response was " , $resp);


          // Decode
          $r = json_decode($resp, true);



          if ($r['status'] == 'failed') {
              $message = $r['message'];

          } else if ($r['status'] == 'success') {
              $id     = $r['data']['id'];
              $hash   = $r['data']['hash'];
              $newurl = $r['data']['url'];


              $this->log->debug("JSON RESPONSE --> ",$r['data']);

              $id2 = substr($id, 2);

              $escape_q_id =  $this->db->escape($id2);
              $escape_hash =  $this->db->escape($hash);

              // post API DB part

              $sql = "UPDATE `$table`  SET `q_id` = '$escape_q_id' , `hash` = '$escape_hash' WHERE
                `order_no` = '$orderid' ";

              $this->log->debug("MYSQL UPDATE QUERY" , $sql);

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

          $this->log->debug("logging order ", $order_info);


          if ($hmac == $checksum) {

              $escape_order_status =  $this->db->escape($status);
              $escape_checksum =  $this->db->escape($checksum);
              $escape_q_id = $this->db->escape($id);

              // post API DB part

              $sql = "UPDATE `$table`  SET `order_status` = '$escape_order_status' , `checksum` = '$escape_checksum' WHERE
                `q_id` = '$escape_q_id' ";

              $this->log->debug("MYSQL UPDATE QUERY" , $sql);

              $this->db->query($sql);

              $order_info = $this->model_checkout_order->getOrder($order_id);

              $this->log->debug("logging order ", $order_info);

              if ($order_id != '') {
                  try {

                      if ($order_info['order_status_id'] !== 5) { // completed

                        $this->log->debug("logging order  status here", $order_info['order_status_id']);

                          $status = strtolower($status);
                          if ($status == "paid") {

                              if ($order_info['order_status_id'] == 2) {  // Processing
                                  // pending
                                  $this->model_checkout_order->addOrderHistory($order_id, $this->config->get('quikwallet_order_status_id'), 'Transaction Processing. QuikWallet ID: ' . $id);
                              } else {
                                  // success
                                  $this->model_checkout_order->addOrderHistory($order_id, $this->config->get('quikwallet_order_status_id'), 'QuikWallet payment successful.<br/>QuikWallet ID: ' . $id);


                                  echo '<html>'."\n";
                                  echo '<head>'."\n";
                                  echo '  <meta http-equiv="Refresh" content="0; url='.$this->url->link('checkout/success').'">'."\n";
                                  echo '</head>'."\n";
                                  echo '<body>'."\n";
                                  echo '  <p>Please follow <a href="'.$this->url->link('checkout/success').'">link</a>!</p>'."\n";
                                  echo '</body>'."\n";
                                  echo '</html>'."\n";
                                  exit();

                              }
                          } else {
                              // failure
                              $this->model_checkout_order->addOrderHistory($order_id, $this->config->get('quikwallet_order_status_id'), 'Transaction ERROR.<br/>QuikWallet ID: ' . $id);
                              echo '<html>'."\n";
                              echo '<head>'."\n";
                              echo '  <meta http-equiv="Refresh" content="0; url='.$this->url->link('checkout/failure').'">'."\n";
                              echo '</head>'."\n";
                              echo '<body>'."\n";
                              echo '  <p>Please follow <a href="'.$this->url->link('checkout/failure').'">link</a>!</p>'."\n";
                              echo '</body>'."\n";
                              echo '</html>'."\n";
                              exit();
                          }
                      }
                  }
                  catch (Exception $e) {
                      // $errorOccurred = true;
                      // failure
                      $this->model_checkout_order->addOrderHistory($order_id, $this->config->get('quikwallet_order_status_id'), 'Transaction ERROR.<br/>QuikWallet ID: ' . $id);
                      echo '<html>'."\n";
                      echo '<head>'."\n";
                      echo '  <meta http-equiv="Refresh" content="0; url='.$this->url->link('checkout/failure').'">'."\n";
                      echo '</head>'."\n";
                      echo '<body>'."\n";
                      echo '  <p>Please follow <a href="'.$this->url->link('checkout/failure').'">link</a>!</p>'."\n";
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
              exit();
          }


          exit();
      }


    }

}
