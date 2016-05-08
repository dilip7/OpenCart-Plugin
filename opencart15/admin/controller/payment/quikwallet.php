<?php

class ControllerPaymentQuikWallet extends Controller
{
  private $error = array();

  public function index()
  {
    $this->language->load('payment/quikwallet');

    $this->document->setTitle($this->language->get('heading_title'));

    $this->load->model('setting/setting');

    if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
      $this->model_setting_setting->editSetting('quikwallet', $this->request->post);

      $this->db->query('DELETE FROM '.DB_PREFIX."url_alias WHERE query = 'callback=1' AND keyword = 'payment-callback'");
      $this->db->query('INSERT INTO '.DB_PREFIX."url_alias SET query = 'callback=1', keyword = 'payment-callback'");

      $this->session->data['success'] = $this->language->get('text_success');

      $this->redirect($this->url->link('extension/payment', 'token='.$this->session->data['token'], 'SSL'));
    }

    $this->data['heading_title'] = $this->language->get('heading_title');

    $this->data['text_enabled'] = $this->language->get('text_enabled');
    $this->data['text_disabled'] = $this->language->get('text_disabled');

    $this->data['entry_key_id'] = $this->language->get('entry_key_id');
    $this->data['entry_key_secret'] = $this->language->get('entry_key_secret');

    // dilip
    $this->data['entry_quikwallet_partnerid'] = $this->language->get('entry_quikwallet_partnerid');
    $this->data['entry_quikwallet_secret'] = $this->language->get('entry_quikwallet_secret');
    $this->data['entry_quikwallet_url'] = $this->language->get('entry_quikwallet_url');


    $this->data['entry_order_status'] = $this->language->get('entry_order_status');
    $this->data['entry_status'] = $this->language->get('entry_status');
    $this->data['entry_sort_order'] = $this->language->get('entry_sort_order');

    $this->data['button_save'] = $this->language->get('button_save');
    $this->data['button_cancel'] = $this->language->get('button_cancel');

    if (isset($this->error['warning'])) {
      $this->data['error_warning'] = $this->error['warning'];
    } else {
      $this->data['error_warning'] = '';
    }

    if (isset($this->error['quikwallet_partnerid'])) {
      $this->data['error_quikwallet_partnerid'] = $this->error['quikwallet_partnerid'];
    } else {
      $this->data['error_quikwallet_partnerid'] = '';
    }

    if (isset($this->error['quikwallet_secret'])) {
      $this->data['error_quikwallet_secret'] = $this->error['quikwallet_secret'];
    } else {
      $this->data['error_quikwallet_secret'] = '';
    }

    if (isset($this->error['quikwallet_url'])) {
      $this->data['error_quikwallet_url'] = $this->error['quikwallet_url'];
    } else {
      $this->data['error_quikwallet_url'] = '';
    }

    $this->data['breadcrumbs'] = array();

    $this->data['breadcrumbs'][] = array(
      'text' => $this->language->get('text_home'),
      'href' => $this->url->link('common/home', 'token='.$this->session->data['token'], 'SSL'),
      'separator' => false,
    );

    $this->data['breadcrumbs'][] = array(
      'text' => $this->language->get('text_payment'),
      'href' => $this->url->link('extension/payment', 'token='.$this->session->data['token'], 'SSL'),
      'separator' => ' :: ',
    );

    $this->data['breadcrumbs'][] = array(
      'text' => $this->language->get('heading_title'),
      'href' => $this->url->link('payment/quikwallet', 'token='.$this->session->data['token'], 'SSL'),
      'separator' => ' :: ',
    );

    $this->data['action'] = $this->url->link('payment/quikwallet', 'token='.$this->session->data['token'], 'SSL');

    $this->data['cancel'] = $this->url->link('extension/payment', 'token='.$this->session->data['token'], 'SSL');

    if (isset($this->request->post['quikwallet_partnerid'])) {
      $this->data['quikwallet_partnerid'] = $this->request->post['quikwallet_partnerid'];
    } else {
      $this->data['quikwallet_partnerid'] = $this->config->get('quikwallet_partnerid');
    }

    if (isset($this->request->post['quikwallet_secret'])) {
      $this->data['quikwallet_secret'] = $this->request->post['quikwallet_secret'];
    } else {
      $this->data['quikwallet_secret'] = $this->config->get('quikwallet_secret');
    }

    if (isset($this->request->post['quikwallet_url'])) {
      $this->data['quikwallet_url'] = $this->request->post['quikwallet_url'];
    } else {
      $this->data['quikwallet_url'] = $this->config->get('quikwallet_url');
    }

    if (isset($this->request->post['quikwallet_order_status_id'])) {
      $this->data['quikwallet_order_status_id'] = $this->request->post['quikwallet_order_status_id'];
    } else {
      $this->data['quikwallet_order_status_id'] = $this->config->get('quikwallet_order_status_id');
    }

    $this->load->model('localisation/order_status');

    $this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

    if (isset($this->request->post['quikwallet_status'])) {
      $this->data['quikwallet_status'] = $this->request->post['quikwallet_status'];
    } else {
      $this->data['quikwallet_status'] = $this->config->get('quikwallet_status');
    }

    if (isset($this->request->post['quikwallet_sort_order'])) {
      $this->data['quikwallet_sort_order'] = $this->request->post['quikwallet_sort_order'];
    } else {
      $this->data['quikwallet_sort_order'] = $this->config->get('quikwallet_sort_order');
    }

    //$this->log->debug("logging data " ,$data);

    $this->template = 'payment/quikwallet.tpl';
    $this->children = array(
      'common/header',
      'common/footer',
    );
    //$this->log->debug("in index function of quikwallet admin controller");

    $this->response->setOutput($this->render());
  }

  protected function validate()
  {
    if (!$this->user->hasPermission('modify', 'payment/quikwallet')) {
      $this->error['warning'] = $this->language->get('error_permission');
    }

    if (!$this->request->post['quikwallet_partnerid']) {
      $this->error['quikwallet_partnerid'] = $this->language->get('error_quikwallet_partnerid');
    }

    if (!$this->request->post['quikwallet_secret']) {
      $this->error['quikwallet_secret'] = $this->language->get('error_quikwallet_secret');
    }

    if (!$this->request->post['quikwallet_url']) {
      $this->error['quikwallet_url'] = $this->language->get('error_quikwallet_url');
    }

    //$this->log->debug("in validate func",$this->request->post);


    if (!$this->error) {
      return true;
    } else {
      return false;
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







  public function install() {

    //$this->log->debug("in install function of quikwallet admin controller");


    $table = DB_PREFIX . 'quik_pay';

      /* this wont be needed mostly
      $charset_collate = '';

      if (!empty(DB_DATABSE->charset)) {
        $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
      }

      if (!empty(DB_DATABSE->collate)) {
        $charset_collate .= " COLLATE {$wpdb->collate}";
      }
       */

    $sql = "CREATE TABLE IF NOT EXISTS `$table` (
      `order_no` int(11) NOT NULL AUTO_INCREMENT,
      `date_c` datetime NOT NULL,
      `name` varchar(200) NOT NULL,
      `email_id` varchar(200) NOT NULL,
      `address` varchar(200) NOT NULL,
      `city` varchar(200) NOT NULL,
      `pincode` varchar(10) NOT NULL,
      `mobile` varchar(10) NOT NULL,
      `amount` int(11) NOT NULL,
      `q_id` varchar(100) NOT NULL,
      `hash` varchar(100) NOT NULL,
      `checksum` varchar(200) NOT NULL,
      `order_status` varchar(100) NOT NULL,
      PRIMARY KEY (`order_no`)
    ) ENGINE=MyISAM DEFAULT CHARACTER SET utf8  COLLATE utf8_general_ci ;";

    $this->db->query($sql);

  }


}
