<?php

if( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

/**
 * WooCommerce Pajily Payments.
 *
 * @class   WC_Gateway_Pajily_Payments
 * @extends WC_Payment_Gateway
 * @version 1.0.0
 * @author  Pajily Payments
 */

include_once plugin_dir_path( __FILE__ ) . 'class-pajily-api.php';

use Pajily\API\PajilyAPI;

class WC_Gateway_Pajily_Payments extends WC_Payment_Gateway {

  /**
   * Constructor for the gateway.
   *
   * @access public
   * @return void
   */

  private $pajily_api;

  public function __construct() {
    $this->developServer       = 'https://dev.pajily.com';
    $this->productionDashboard = 'https://dashboard.pajily.com';
    $this->productionServer    = 'https://dashboard.pajily.com';
    $this->sandboxServer       = 'https://payment-sandbox.pajily.com';

    $this->id                  = 'pajily_payments';
    $this->has_fields          = false;
    $this->credit_fields       = false;
    $this->order_button_text   = __( 'Pay with Pajily Payments', 'pajily-payments' );
    $this->method_title        = __( 'Pajily Payments', 'pajily-payments' );
    $this->method_description  = __( 'Accept cryptocurrency payments with Pajily Payments.', 'pajily-payments' );
    $this->notify_url          = WC()->api_request_url( 'WC_Gateway_Pajily_Payments' );
    $this->supports            = array( 'products' );
		$this->timeout             = ( new WC_DateTime() )->sub( new DateInterval( 'P3D' ) );

    $this->enabled             = $this->get_option( 'enabled' );
    $this->title               = !$this->empty($this->get_option( 'title' )) ? $this->get_option( 'title' ) : __( 'Pajily Payments', 'pajily-payments' );
    $this->description         = !$this->empty($this->get_option( 'description' )) ? $this->get_option( 'description' ) : __( 'Clicking "Proceed to Pajily" will redirect you to Pajily to complete your purchase.', 'pajily-payments' );
    $this->next_button         = !$this->empty($this->get_option( 'next_button' )) ? $this->get_option( 'next_button' ) : __( 'Proceed to Pajily', 'pajily-payments' );
    $this->develop             = 'no';
    $this->sandbox             = $this->get_option( 'sandbox' );
    $this->public_key          = $this->get_option( 'public_key' );
    $this->private_key         = $this->get_option( 'private_key' );
    $this->debug               = $this->get_option( 'debug' );
    $this->api_endpoint        = $this->get_api_endpoint();

    if( $this->debug == 'yes' ) {
      if( class_exists( 'WC_Logger' ) ) {
        $this->log = new WC_Logger();
      }
      else {
        $this->log = $woocommerce->logger();
      }
    }

    $this->pajily_api = new PajilyAPI($this->api_endpoint, $this->public_key, $this->private_key);
    $this->init_form_fields();
    $this->init_settings();

    if( is_admin() ) {
      add_action( 'admin_notices', array( $this, 'checks' ) );
      add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
    }

    add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );
    add_action( 'woocommerce_api_' . $this->id, array( $this, 'webhook' ) );
  }

  private function empty($text) {
    return !trim($text ?? '');
  }

  private function get_api_endpoint() {
    return $this->develop == 'yes' ? $this->developServer : ($this->sandbox == 'yes' ? $this->sandboxServer : $this->productionServer);
  }

  public function webhook() {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");

    $order_id = $_GET['order_id'];
    $transaction_id = $_GET['transaction_id'];
    $status = $this->pajily_api->get_transaction_status( $transaction_id );
    $order = wc_get_order( $order_id );

    if ( $order ) {
      $this->update_order_status( $order, $status );
    }

    header( 'HTTP/1.1 200 OK' );
    die();
  }

	private function update_order_status( $order, $status ) {
		$prev_status = $order->get_meta( '_gateway_status' );
		if ( $status !== $prev_status ) {
			$order->update_meta_data( '_gateway_status', $status );

			if ( $status === 'EXPIRED' && $order->get_status() == 'pending' ) {
				$order->update_status( 'cancelled', __( 'The payment has expired.', 'pajily-payments' ) );
			} elseif ( $status === 'CANCELLED' ) {
				$order->update_status( 'cancelled', __( 'The payment was cancelled.', 'pajily-payments' ) );
			} elseif ( $status === 'DECLINED' ) {
				$order->update_status( 'cancelled', __( 'The payment was declined.', 'pajily-payments' ) );
			} elseif ( $status === 'PAID' ) {
				$order->update_status( 'processing', __( 'Payment was successfully processed.', 'pajily-payments' ) );
				$order->payment_complete();
			}
		}
	}

  /**
   * Admin Panel Options
   *
   * @access public
   * @return void
   */
  public function admin_options() {
    ?>
    <h3><?php _e( 'Pajily Payments', 'pajily-payments' ); ?></h3>

    <table class="form-table">
      <tr valign="top">
        <th scope="row" class="titledesc">
          <?php echo __( 'Dashboard', 'pajily-payments' ); ?>
        </th>
        <td class="forminp">
          <a href="<?php echo $this->productionDashboard ?>" target="_blank" class="button button-primary"><?php _e( 'Production Dashboard', 'pajily-payments' ); ?></a>
          <a href="<?php echo $this->sandboxServer ?>" target="_blank" class="button"><?php _e( 'Sandbox Dashboard', 'pajily-payments' ); ?></a>
        </td>
      </tr>
    
      <?php $this->generate_settings_html(); ?>
    </table>
    <?php
    echo "<script>window.develop = '{$this->develop}'; window.developServer = '{$this->developServer}'; window.productionServer = '{$this->productionServer}'; window.sandboxServer = '{$this->sandboxServer}';</script>";
    wp_enqueue_script('pajily-admin-js', plugin_dir_url(__FILE__) . '../assets/js/admin.js', array('jquery'), WC_Pajily_Payments::get_instance()->version, true);
  }

  /**
   * Configuration checks.
   *
   * @access public
   */
  public function checks() {
    if( $this->enabled == 'no' ) {
      return;
    }

    // PHP Version.
    if( version_compare( phpversion(), '5.3', '<' ) ) {
      echo '<div class="error"><p>' . sprintf( __( 'Pajily Payments Error: Pajily Payments requires PHP 5.3 and above. You are using version %s.', 'pajily-payments' ), phpversion() ) . '</p></div>';
    }

    // Check required fields.
    else if( !$this->public_key || !$this->private_key ) {
      echo '<div class="error"><p>' . __( 'Pajily Payments Error: Please enter your public and private API keys', 'pajily-payments' ) . '</p></div>';
    }
  }

  /**
   * Check if this gateway is enabled.
   *
   * @access public
   */
  public function is_available() {
    return $this->enabled == 'yes' && !$this->empty($this->public_key) && !$this->empty($this->private_key);
  }

  /**
   * Initialise Gateway Settings Form Fields
   *
   * @access public
   */
  public function init_form_fields() {
    $this->form_fields = array(
      'enabled' => array(
        'title'       => __( 'Enable/Disable', 'pajily-payments' ),
        'label'       => __( 'Enable Pajily Payments', 'pajily-payments' ),
        'type'        => 'checkbox',
        'description' => '',
        'default'     => 'no'
      ),
      'title' => array(
        'title'       => __( 'Title', 'pajily-payments' ),
        'type'        => 'text',
        'description' => __( 'This controls the title which the user sees during checkout.', 'pajily-payments' ),
        'default'     => __( 'Cryptocurrency', 'pajily-payments' ),
        'desc_tip'    => true
      ),
      'description' => array(
        'title'       => __( 'Description', 'pajily-payments' ),
        'type'        => 'text',
        'description' => __( 'This controls the description which the user sees during checkout.', 'pajily-payments' ),
        'default'     => 'Clicking "Proceed to Pajily" will redirect you to Pajily to complete your purchase.', 'pajily-payments',
        'desc_tip'    => true
      ),
      'next_button' => array(
        'title'       => __( 'Order Button', 'pajily-payments' ),
        'type'        => 'text',
        'description' => __( 'This controls the order button text which the user sees during checkout.', 'pajily-payments' ),
        'default'     => 'Proceed to Pajily',
        'desc_tip'    => true
      ),
      'sandbox' => array(
        'title'       => __( 'Sandbox', 'pajily-payments' ),
        'label'       => __( 'Enable Sandbox Mode', 'pajily-payments' ),
        'type'        => 'checkbox',
        'description' => __( 'Place the payment gateway into sandbox mode using sandbox API keys. Be sure to update the API keys below when toggling this setting.', 'pajily-payments' ),
        'default'     => 'yes',
        'desc_tip'    => true
      ),
      'public_key' => array(
        'title'       => __( 'Public Key', 'pajily-payments' ),
        'type'        => 'text',
        'description' => __( 'The production public key generated in the production dashboard; or the sandbox public key generated in the sandbox dashboard. Be sure to use the correct API key when toggling the sandbox setting above.', 'pajily-payments' ),
        'default'     => '',
        'desc_tip'    => true
      ),
      'private_key' => array(
        'title'       => __( 'Private Key', 'pajily-payments' ),
        'type'        => 'password',
        'description' => __( 'The production private key generated in the production dashboard; or the sandbox private key generated in the sandbox dashboard. Be sure to use the correct API key when toggling the sandbox setting above.', 'pajily-payments' ),
        'default'     => '',
        'desc_tip'    => true
      ),
      'payment_wall_id' => array(
        'title'       => __( 'Payment Wall', 'pajily-payments' ),
        'type'        => 'select',
        'description' => __( 'Enter the public and private API keys above to populate the payment wall list.', 'pajily-payments' ),
        'default'     => '',
        'desc_tip'    => true,
        'options'     => $this->pajily_api->get_payment_walls(),
      ),
      'debug' => array(
        'title'       => __( 'Debug Log', 'pajily-payments' ),
        'type'        => 'checkbox',
        'label'       => __( 'Enable logging', 'pajily-payments' ),
        'default'     => 'no',
        'description' => sprintf( __( 'Log Gateway name events inside <code>%s</code>', 'pajily-payments' ), wc_get_log_file_path( $this->id ) )
      ),
    );
  }

  /**
   * Outputs scripts used for the payment gateway.
   *
   * @access public
   */
  public function payment_scripts() {
    if( !is_checkout() || !$this->is_available() ) {
      return;
    }

    echo "<script>window.gatewayId = '{$this->id}'; window.methodTitle = '{$this->method_title}'; window.methodDescription = '{$this->method_description}'; window.paymentTitle = '{$this->title}'; window.paymentDescription = '{$this->description}'; window.paymentNextButton = '{$this->next_button}';</script>";
    wp_enqueue_script('pajily-js', plugin_dir_url(__FILE__) . '../assets/js/checkout.js', array('jquery'), WC_Pajily_Payments::get_instance()->version, true);
  }

  /**
   * Process the payment and return the result.
   *
   * @access public
   * @param  int $order_id
   * @return array
   */
  public function process_payment( $order_id ) {
    $order = wc_get_order( $order_id );
    $data = array(
      'beneficiary' => array(
        'currency' => get_woocommerce_currency()
      ),
      'cancelUrl' => wc_get_checkout_url(),
      'checkout' => array(
        'subtotalAmount' => $order->get_total()
      ),
      'flags' => array(
        'wooCommerce' => true
      ),
      'notifyUrl' => get_site_url() . '?wc-api=' . $this->id . '&order_id=' . $order_id . '&transaction_id=',
      'paymentWallId' => $this->get_option( 'payment_wall_id' ),
      'returnUrl' => $order->get_checkout_order_received_url()
    );

    $transaction = $this->pajily_api->create_transaction($data);

    if ($transaction != false && isset($transaction['transactionId'])) {
      $order->update_meta_data( '_gateway_transaction_id', $transaction['transactionId'] );
      $order->save();
      return array(
        'result' => 'success',
        'redirect' => $this->api_endpoint . '/payments/' . $transaction['transactionId'] . '/checkout/?type=redirect',
      );
    } else if ($transaction != false && isset($transaction['errors'])) {
      wc_add_notice( __( 'Payment error: ', 'pajily-payments' ) . $transaction['errors'][0]['message'], 'error' );
      return array( 'result' => 'failure', 'redirect' => null );
    } else {
      wc_add_notice( __( 'Payment error.', 'pajily-payments' ), 'error' );
      return array( 'result' => 'failure', 'redirect' => null );
    }
  }
}
