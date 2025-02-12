<?php

/*
 * Plugin Name:          Pajily Payments
 * Plugin URI:           https://github.com/sixclovers/pajily-woocommerce-plugin
 * Description:          Accept cryptocurrency payments with Pajily Payments.
 * Version:              1.1.3
 * Requires at least:    4.0
 * Requires PHP:         7.0
 * Author:               Pajily Payments
 * Author URI:           https://www.pajily.com/
 * License:              GPLv3+
 * License URI:          https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:          pajily-payments
 * WC requires at least: 4.0.0
 * WC tested up to:      9.6.1
 */

 /*
  Pajily Payments is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  any later version.

  Pajily Payments is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with Pajily Payments. If not, see https://www.gnu.org/licenses/gpl-3.0.html.
 */

if( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.

if( !class_exists( 'PajilyPayments' ) ) {

  /**
   * WooCommerce Pajily Payments main class.
   *
   * @class   Pajily_Payments
   * @version 1.0.0
   */
  final class PajilyPayments {

    /**
     * Instance of this class.
     *
     * @access protected
     * @access static
     * @var object
     */
    protected static $instance = null;

    /**
     * Slug
     *
     * @access public
     * @var    string
     */
     public $gateway_slug = 'pajily_payments';

    /**
     * Text Domain
     *
     * @access public
     * @var    string
     */
    public $text_domain = 'pajily-payments';

    /**
     * Pajily Payments
     *
     * @access public
     * @var    string
     */
     public $name = "Pajily Payments";

    /**
     * Gateway version.
     *
     * @access public
     * @var    string
     */
    public $version = '1.1.3';

    /**
     * The Gateway URL.
     *
     * @access public
     * @var    string
     */
     public $web_url = "https://www.pajily.com/";

    /**
     * The Gateway documentation URL.
     *
     * @access public
     * @var    string
     */
     public $doc_url = "https://github.com/sixclovers/pajily-woocommerce-plugin/";

    /**
     * Return an instance of this class.
     *
     * @return object A single instance of this class.
     */
    public static function get_instance() {
      // If the single instance hasn't been set, set it now.
      if( null == self::$instance ) {
        self::$instance = new self;
      }

      return self::$instance;
    }

    /**
     * Throw error on object clone
     *
     * The whole idea of the singleton design pattern is that there is a single
     * object therefore, we don't want the object to be cloned.
     *
     * @since  1.0.0
     * @access public
     * @return void
     */
     public function __clone() {
       // Cloning instances of the class is forbidden
       _doing_it_wrong( __FUNCTION__, esc_html__( 'Not Allowed', 'pajily-payments' ), esc_html($this->version) );
     }

    /**
     * Disable unserializing of the class
     *
     * @since  1.0.0
     * @access public
     * @return void
     */
     public function __wakeup() {
       // Unserializing instances of the class is forbidden
       _doing_it_wrong( __FUNCTION__, esc_html__( 'Not Allowed', 'pajily-payments' ), esc_html($this->version) );
     }

    /**
     * Initialize the plugin public actions.
     *
     * @access private
     */
    private function __construct() {
      // Hooks.
      add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'action_links' ) );
      add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );

      // Is WooCommerce activated?
      if( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
        add_action('admin_notices', array( $this, 'woocommerce_missing_notice' ) );
        return false;
      }
      else{
        // Check we have the minimum version of WooCommerce required before loading the gateway.
        if( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.2', '>=' ) ) {
          if( class_exists( 'WC_Payment_Gateway' ) ) {
            $this->includes();

            add_filter( 'woocommerce_payment_gateways', array( $this, 'add_gateway' ) );
            add_filter( 'woocommerce_currencies', array( $this, 'add_currency' ) );
            add_filter( 'woocommerce_currency_symbol', array( $this, 'add_currency_symbol' ), 10, 2 );
            add_filter( 'woocommerce_admin_order_data_after_order_details', array( $this, 'order_meta' ) );

            add_action( 'before_woocommerce_init', function() {
              if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
                \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
              }
            } );
          }
        }
        else {
          add_action( 'admin_notices', array( $this, 'upgrade_notice_safe' ) );
          return false;
        }
      }
    }

    /**
     * Plugin action links.
     *
     * @access public
     * @param  mixed $links
     * @return void
     */
     public function action_links( $links ) {
       if( current_user_can( 'manage_woocommerce' ) ) {
         $plugin_links = array(
           '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=' . $this->gateway_slug ) . '">' . __( 'Settings', 'pajily-payments' ) . '</a>',
         );
         return array_merge( $plugin_links, $links );
       }

       return $links;
     }

    /**
     * Plugin row meta links
     *
     * @access public
     * @param  array $input already defined meta links
     * @param  string $file plugin file path and name being processed
     * @return array $input
     */
     public function plugin_row_meta( $input, $file ) {
       if( plugin_basename( __FILE__ ) !== $file ) {
         return $input;
       }

       $links = array(
         //'<a href="' . esc_url( $this->doc_url ) . '">' . __( 'Documentation', 'pajily-payments' ) . '</a>',
       );

       $input = array_merge( $input, $links );

       return $input;
     }

    /**
     * Include files.
     *
     * @access private
     * @return void
     */
    private function includes() {
      include_once( 'includes/class-pajily-payments-gateway.php' );
    }

    /**
     * Add the gateway.
     *
     * @access public
     * @param  array $methods WooCommerce payment methods.
     * @return array WooCommerce Pajily Payments gateway.
     */
    public function add_gateway( $methods ) {
      $methods[] = 'PajilyPaymentsGateway';
      return $methods;
    }

    /**
     * Add the currency.
     *
     * @access public
     * @return array
     */
    public function add_currency( $currencies ) {
      $currencies['ALGO'] = 'ALGO';
      $currencies['AVAX'] = 'AVAX';
      $currencies['BEAM'] = 'BEAM';
      $currencies['BNB'] = 'BNB';
      $currencies['BTC'] = 'BTC';
      $currencies['BUSD'] = 'BUSD';
      $currencies['DAI'] = 'DAI';
      $currencies['ETH'] = 'ETH';
      $currencies['EUR'] = 'EUR';
      $currencies['EURC'] = 'EURC';
      $currencies['EURT'] = 'EURT';
      $currencies['GUSD'] = 'GUSD';
      $currencies['HBAR'] = 'HBAR';
      $currencies['LINK'] = 'LINK';
      $currencies['MATIC'] = 'MATIC';
      $currencies['MXN'] = 'MXN';
      $currencies['MXNT'] = 'MXNT';
      $currencies['NEAR'] = 'NEAR';
      $currencies['OKT'] = 'OKT';
      $currencies['PYUSD'] = 'PYUSD';
      $currencies['SOL'] = 'SOL';
      $currencies['SUI'] = 'SUI';
      $currencies['TRX'] = 'TRX';
      $currencies['TUSD'] = 'TUSD';
      $currencies['USD'] = 'USD';
      $currencies['USDC'] = 'USDC';
      $currencies['USDD'] = 'USDD';
      $currencies['USDT'] = 'USDT';
      $currencies['VIC'] = 'VIC';
      $currencies['XLM'] = 'XLM';
      return $currencies;
    }

    /**
     * Add the currency symbol.
     *
     * @access public
     * @return string
     */
    public function add_currency_symbol( $currency_symbol, $currency ) {
      return $currency_symbol;
    }

    /**
     * WooCommerce Fallback Notice.
     *
     * @access public
     * @return string
     */
    public function woocommerce_missing_notice() {
      /* translators: 1: plugin slug 2: admin URL */
      echo '<div class="error woocommerce-message wc-connect"><p>', esc_html( sprintf( __( 'Sorry, <strong>WooCommerce %1$s</strong> requires WooCommerce to be installed and activated first. Please install <a href="%2$s">WooCommerce</a> first.', 'pajily-payments' ), $this->name, admin_url('plugin-install.php?tab=search&type=term&s=WooCommerce' ) ) ), '</p></div>';
    }

    /**
     * WooCommerce Payment Gateway Upgrade Notice.
     *
     * @access public
     * @return string
     */
    public function upgrade_notice_safe() {
      /* translators: 1: plugin slug */
      echo '<div class="updated woocommerce-message wc-connect"><p>', esc_html( sprintf( __( 'WooCommerce %s depends on version 4.0 and up of WooCommerce for this gateway to work! Please upgrade before activating.', 'pajily-payments' ), $this->name ) ), '</p></div>';
    }

    /** Helper functions ******************************************************/

    /**
     * Get the plugin url.
     *
     * @access public
     * @return string
     */
    public function plugin_url() {
      return untrailingslashit( plugins_url( '/', __FILE__ ) );
    }

    /**
     * Get the plugin path.
     *
     * @access public
     * @return string
     */
    public function plugin_path() {
      return untrailingslashit( plugin_dir_path( __FILE__ ) );
    }

    public function order_meta( $order ) {
      if ($order->get_payment_method() == $this->gateway_slug) {
        echo '<br class="clear"/><h3>', esc_html( $this->name ), '</h3><div><p>Transaction Id ', esc_html( $order->get_meta('_gateway_transaction_id') ), '</p></div>';
      }
    }

  } // end if class

  add_action( 'plugins_loaded', array( 'PajilyPayments', 'get_instance' ), 0 );

} // end if class exists.

/**
 * Returns the main instance of PajilyPayments to prevent the need to use globals.
 *
 * @return WooCommerce Gateway Name
 */
function PajilyPayments() {
	return PajilyPayments::get_instance();
}
