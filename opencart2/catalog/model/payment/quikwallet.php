<?php

class ModelPaymentQuikWallet extends Model
{
    public function getMethod($address, $total)
    {

        $this->language->load('payment/quikwallet');

        $method_data = array(
            'code' => 'quikwallet',
            'title' => $this->language->get('text_title'),
            'terms' => '',
            'sort_order' => $this->config->get('quikwallet_sort_order'),
        );

        return $method_data;
    }
}
