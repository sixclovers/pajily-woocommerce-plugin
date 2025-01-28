<?php

  namespace Pajily\API;

  class PajilyAPI {
    private $api_endpoint;
    private $logger;
    private $private_key;
    private $public_key;

    public function __construct($logger, $api_endpoint, $public_key, $private_key) {
      $this->api_endpoint = $api_endpoint;
      $this->logger = $logger;
      $this->private_key = $private_key;
      $this->public_key = $public_key;
    }

    private function api_request($endpoint, $method = 'GET', $body = null) {
      if ($this->public_key == null || $this->private_key == null) return false;

      $url = $this->api_endpoint . $endpoint;
      $authorization = base64_encode( "$this->public_key:$this->private_key" );
      $response = wp_remote_request( $url, array(
        'method' => $method,
        'timeout' => 20,
        'headers' => array( 'Authorization' => "Basic $authorization", 'Content-Type' => 'application/json' ),
        'body' => $body == null ? null : json_encode($body),
        )
      );

      if ( is_wp_error( $response ) ) {
        if ( $this->logger != null) {
          $errorResponse = $response->get_error_message();
          $this->logger->log( 'error', 'Request Error: ' . $errorResponse, array( 'source' => 'pajily-payments' ) );
          $this->logger->log( 'error', 'Method: '        . $method, array( 'source' => 'pajily-payments' ) );
          $this->logger->log( 'error', 'Endpoint: '      . $endpoint, array( 'source' => 'pajily-payments' ) );
          $this->logger->log( 'error', 'Body: '          . json_encode($body), array( 'source' => 'pajily-payments' ) );
        }
        return false;
      }

      return json_decode($response['body'], true);
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