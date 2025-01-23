<?php

  namespace Pajily\API;

  class PajilyAPI {
    private $api_endpoint;
    private $public_key;
    private $private_key;

    public function __construct($api_endpoint, $public_key, $private_key) {
        $this->api_endpoint = $api_endpoint;
        $this->public_key = $public_key;
        $this->private_key = $private_key;
    }

    private function api_request($endpoint, $method = 'GET', $body = null) {
        if ($this->public_key == null || $this->private_key == null) return false;

        $url = $this->api_endpoint . $endpoint;
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "$this->public_key:$this->private_key");
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20); 
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);

        if ($method === 'POST' && $body !== null) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        }

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            error_log('Request error: ' . curl_error($ch));
            return false;
        }

        curl_close($ch);

        return json_decode($response, true);
    }

    public function create_transaction($data) {
      return $this->api_request( '/v1/transactions/payment/', 'POST', $data );
    }

    public function get_payment_walls() {
      $data = $this->api_request( '/v1/transactions/payment-walls/' );

      if ($data == false || !isset($data['data']) || !is_array($data['data'])) {
        return array();
      }

      $options = array();
      foreach ($data['data'] as $wall) {
        $options[$wall['paymentWallId']] = $wall['name'];
      }
      return $options;
    }

    public function get_transaction_status($transaction_id) {
      $response = $this->api_request( '/v1/transactions/' . $transaction_id );

      if ($response == false) {
        return false;
      }

      return $response['status'];
    }
  }
?>