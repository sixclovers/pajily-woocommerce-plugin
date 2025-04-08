# Pajily Payments for WooCommerce

A WooCommerce payment gateway that allows your customers to pay with cryptocurrency via Pajily Payments.

## Installation

### From WordPress.org

This plugin is available on the [WordPress.org plugin repository], and can be installed either directly from there or from the admin dashboard within your website.

#### Within your WordPress dashboard

1. Visit ‘Plugins > Add New’.
2. Search for Pajily Payments’.
3. Activate Pajily Payments from your Plugins page.

#### From WordPress.org plugin repository

1. Download Pajily Payments from <https://wordpress.org/plugins/pajily-payments/>.
2. Upload to your ‘/wp-content/plugins/’ directory, using your favorite method (ftp, sftp, scp, etc...).
3. Activate Pajily Payments from your Plugins page.

### From this repository

Clone this Github repository and create a zip file of the plugin with `zip -r9 pajily-payments.zip pajily-payments`.

Within your WordPress administration panel, go to Plugins > Add New and click the Upload Plugin button on the top of the page.

Alternatively, you can move the zip file into the `wp-content/plugins` folder of your website and unzip.

You will then need to go to your WordPress administration Plugins page, and activate the plugin.

## Configuring Pajily Payments

You will need to set up an account on the [Pajily Payments Production Dashboard] and optionally on the [Pajily Payments Sandbox Dashboard] for testing purposes.

Within the WordPress administration area, go to the WooCommerce > Settings > Payments page and you will see Pajily Payments in the table of payment gateways.

Clicking the Manage button on the right hand side will take you into the settings page, where you can configure the plugin for your store.

**Note: If you are running version of WooCommerce older than 3.4.x your Pajily Payments tab will be underneath the WooCommerce > Settings > Checkout tab**

## Settings

### Enable / Disable

Turn the Pajily Payments payment method on / off for visitors at checkout.

### Title

Title of the payment method displayed on the checkout page.

### Description

Description of the payment method displayed on the checkout page.

### Order Button

The text displayed of the payment button on the checkout page.

### Sandbox

Turn the toggle on / off to indicate if the API credentials should connect to the Sandbox or Production environment.

### Public Key / Private Key

Your Pajily Payments API key. Configured within the Pajily Dashboard `Configuration > Manage API Keys` page.

Used to communicate with Pajily Payments to initiate payment sessions and update payment status.

When creating an API key, select `Custom Authorization` and enable at minimum `Transaction APIs` with `Read+Write Access`.

### Payment Wall

Once an environment is selected and the API keys are provided, select a configured Payment Wall from the list.

A Payment Wall is configured within the Pajily Dashboard `Configuration > Manage Payment Walls` page.

A Payment Wall indicates what currencies you want to accept and the destination wallets that funds should be sent to.

### Debug log

Whether or not to store debug logs.

If this is checked, these are saved within your `wp-content/uploads/wc-logs/` folder in a .log file prefixed with `pajily_payments`.

## Prerequisites

To use this plugin with your WooCommerce store you will need:

* [WordPress] (tested up to 6.8)
* [WooCommerce] (tested up to 9.7.1)

## License

This project is licensed under GPLv3+.

## Changelog

### 1.1.4

* Changed: Validated with WP 6.8 and WC 9.7.1.

### 1.1.3

* Fixed: Added improvements based on publishing the plugin to WordPress.

### 1.1.2

* Fixed: Added improvements based on Plugin Check.

### 1.1.1

* Fixed: Improved handling of invalid API keys.

### 1.1.0

* New: Initial public release

[Pajily Payments Production Dashboard]: <https://dashboard.pajily.com/>
[Pajily Payments Sandbox Dashboard]: <https://payment-sandbox.pajily.com/>
[WooCommerce]: <https://woocommerce.com/>
[WordPress]: <https://wordpress.org/>
[WordPress.org plugin repository]: <https://wordpress.org/plugins/pajily-payments/>