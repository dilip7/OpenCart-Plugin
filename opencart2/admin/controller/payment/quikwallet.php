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

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('extension/payment', 'token='.$this->session->data['token'], 'SSL'));
        }

        $data['heading_title'] = $this->language->get('heading_title');

        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_all_zones'] = $this->language->get('text_all_zones');
        $data['text_yes'] = $this->language->get('text_yes');
        $data['text_no'] = $this->language->get('text_no');

        $data['entry_key_id'] = $this->language->get('entry_key_id');
        $data['entry_key_secret'] = $this->language->get('entry_key_secret');

        // dilip
        $data['entry_quikwallet_partnerid'] = $this->language->get('entry_quikwallet_partnerid');
        $data['entry_quikwallet_secret'] = $this->language->get('entry_quikwallet_secret');
        $data['entry_quikwallet_url'] = $this->language->get('entry_quikwallet_url');


        $data['entry_order_status'] = $this->language->get('entry_order_status');
        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_sort_order'] = $this->language->get('entry_sort_order');

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');

        $data['help_key_id'] = $this->language->get('help_key_id');
        $data['help_order_status'] = $this->language->get('help_order_status');

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['quikwallet_partnerid'])) {
            $data['error_quikwallet_partnerid'] = $this->error['quikwallet_partnerid'];
        } else {
            $data['error_quikwallet_partnerid'] = '';
        }

        if (isset($this->error['quikwallet_secret'])) {
            $data['error_quikwallet_secret'] = $this->error['quikwallet_secret'];
        } else {
            $data['error_quikwallet_secret'] = '';
        }

        if (isset($this->error['quikwallet_url'])) {
            $data['error_quikwallet_url'] = $this->error['quikwallet_url'];
        } else {
            $data['error_quikwallet_url'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', 'token='.$this->session->data['token'], 'SSL'),
            'separator' => false,
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_payment'),
            'href' => $this->url->link('extension/payment', 'token='.$this->session->data['token'], 'SSL'),
            'separator' => ' :: ',
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('payment/quikwallet', 'token='.$this->session->data['token'], 'SSL'),
            'separator' => ' :: ',
        );

        $data['action'] = $this->url->link('payment/quikwallet', 'token='.$this->session->data['token'], 'SSL');

        $data['cancel'] = $this->url->link('extension/payment', 'token='.$this->session->data['token'], 'SSL');

        if (isset($this->request->post['quikwallet_partnerid'])) {
            $data['quikwallet_partnerid'] = $this->request->post['quikwallet_partnerid'];
        } else {
            $data['quikwallet_partnerid'] = $this->config->get('quikwallet_partnerid');
        }

        if (isset($this->request->post['quikwallet_secret'])) {
            $data['quikwallet_secret'] = $this->request->post['quikwallet_secret'];
        } else {
            $data['quikwallet_secret'] = $this->config->get('quikwallet_secret');
        }

        if (isset($this->request->post['quikwallet_url'])) {
            $data['quikwallet_url'] = $this->request->post['quikwallet_url'];
        } else {
            $data['quikwallet_url'] = $this->config->get('quikwallet_url');
        }

        if (isset($this->request->post['quikwallet_order_status_id'])) {
            $data['quikwallet_order_status_id'] = $this->request->post['quikwallet_order_status_id'];
        } else {
            $data['quikwallet_order_status_id'] = $this->config->get('quikwallet_order_status_id');
        }

        $this->load->model('localisation/order_status');

        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        if (isset($this->request->post['quikwallet_status'])) {
            $data['quikwallet_status'] = $this->request->post['quikwallet_status'];
        } else {
            $data['quikwallet_status'] = $this->config->get('quikwallet_status');
        }

        if (isset($this->request->post['quikwallet_sort_order'])) {
            $data['quikwallet_sort_order'] = $this->request->post['quikwallet_sort_order'];
        } else {
            $data['quikwallet_sort_order'] = $this->config->get('quikwallet_sort_order');
        }

        //$this->log->debug("logging data " ,$data);

        $this->template = 'payment/quikwallet.tpl';
        $this->children = array(
            'common/header',
            'common/footer',
        );
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');


        //$this->log->debug("in index function of quikwallet admin controller");


        $this->response->setOutput($this->load->view('payment/quikwallet.tpl', $data));
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

    public function install() {

      $this->log->debug("in install function of quikwallet admin controller");


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
