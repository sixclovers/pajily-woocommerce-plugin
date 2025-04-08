=== Pajily Payments ===
Contributors: pajilypayments
Plugin URL: https://www.pajily.com/
Tags: woocommerce, payments, crypto, ethereum, bitcoin
Requires at least: 4.0
Requires PHP: 7.0
Tested up to: 6.8
Stable tag: 1.1.4
License: GPLv3 or later
Accept cryptocurrencies through Pajily Payments.

== Description ==

Accept cryptocurrencies through Pajily Payments such as USDC, USDT, and Ethereum on your WooCommerce store.

== Installation ==

= From your WordPress dashboard =

1. Visit ‘Plugins > Add New’.
2. Search for ‘Pajily Payments’.
3. Activate Pajily Payments from your Plugins page.

= From WordPress.org =

1. Download Pajily Payments from <https://wordpress.org/plugins/pajily-payments/>.
2. Upload to your ‘/wp-content/plugins/’ directory, using your favorite method (ftp, sftp, scp, etc...).
3. Activate Pajily Payments from your Plugins page.

= Once Activated =

1. Go to WooCommerce > Settings > Payments.
2. Configure the plugin for your store.

= Configuring Pajily Payments =

* You will need to set up an account on the [Pajily Payments Production Dashboard] and optionally on the [Pajily Payments Sandbox Dashboard] for testing purposes.
* Within the WordPress administration area, go to the WooCommerce > Settings > Payments page and you will see Pajily Payments in the table of payment gateways.
* Clicking the Manage button on the right hand side will take you into the settings page, where you can configure the plugin for your store.

**Note: If you are running version of WooCommerce older than 3.4.x your Pajily Payments tab will be underneath the WooCommerce > Settings > Checkout tab**

= Enable / Disable =

Turn the Pajily Payments payment method on / off for visitors at checkout.

= Title =

Title of the payment method displayed on the checkout page.

= Description =

Description of the payment method displayed on the checkout page.

= Order Button =

The text displayed of the payment button on the checkout page.

= Sandbox =

Turn the toggle on / off to indicate if the API credentials should connect to the Sandbox or Production environment.

= Public Key / Private Key =

Your Pajily Payments API key. Configured within the Pajily Dashboard `Configuration > Manage API Keys` page.

Used to communicate with Pajily Payments to initiate payment sessions and update payment status.

When creating an API key, select `Custom Authorization` and enable at minimum `Transaction APIs` with `Read+Write Access`.

= Payment Wall =

Once an environment is selected and the API keys are provided, select a configured Payment Wall from the list.

A Payment Wall is configured within the Pajily Dashboard `Configuration > Manage Payment Walls` page.

A Payment Wall indicates what currencies you want to accept and the destination wallets that funds should be sent to.

= Debug log =

Whether or not to store debug logs.

If this is checked, these are saved within your `wp-content/uploads/wc-logs/` folder in a .log file prefixed with `pajily_payments`.

== Frequently Asked Questions ==

= What cryptocurrencies does the plugin support?

The plugin supports all cryptocurrencies available at https://www.pajily.com/

= Prerequisites=

To use this plugin with your WooCommerce store you will need:

* WooCommerce plugin

== External services ==

This plugin connects to an API to securely create a payment session. The payment session is used to redirect the user to the payment wall experience.

It sends the checkout amount, checkout currency, and redirection URLs every time the user clicks on the Pay button.

This service is provided by "Pajily Payments": [Terms of Service], [Privacy Policy], [DPA].

== Changelog ==

= 1.1.4 =
* Validated with WP 6.8 and WC 9.7.1.

= 1.1.3 =
* Added improvements based on publishing the plugin to WordPress.

= 1.1.2 =
* Added improvements based on Plugin Check.

= 1.1.1 =
* Improved handling of invalid API keys.

= 1.1.0 =
* Initial public release

[Pajily Payments Production Dashboard]: <https://dashboard.pajily.com/>
[Pajily Payments Sandbox Dashboard]: <https://payment-sandbox.pajily.com/>
[Terms of Service]: <https://legal.pajily.com/terms-of-service>
[Privacy Policy]: <https://legal.pajily.com/privacy-policy>
[DPA]: <https://legal.pajily.com/data-processing-agreement>