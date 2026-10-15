=== Duplicate Payment Gateways ===
Contributors: BrianHenryIE
Tags: woocommerce, payment gateway, duplicate, payments
Requires at least: 6.5
Tested up to: 7.1
Stable tag: 1.6.0
Requires PHP: 7.4
WC tested up to: 11.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Create additional instances of WooCommerce payment gateways, each with its own settings.

== Description ==

Duplicate a WooCommerce payment gateway to get another instance of it with its own settings, title and description, e.g. to report phone orders separately.

* WooCommerce / Duplicate Gateways lists every gateway, with buttons to duplicate those that can be duplicated, and to delete duplicates.
* Each duplicate has an admin title and admin description, shown to shop managers, and a "Hide on checkout" option for duplicates used only for reporting or manual orders.
* Duplicates of gateways without payment fields are available in the block checkout and the shortcode checkout.
* plugins.php links to each plugin's gateways and their duplicates.

Not every gateway can be duplicated: gateways whose constructors set their own id (including WooCommerce's bank transfer, check and cash on delivery) or require arguments (e.g. WooCommerce Payments) cannot.

== Changelog ==

= 1.6.0 =

* Add WooCommerce / Duplicate Gateways page, since WooCommerce 9.x removed the payment gateways table.
* Support duplicates in the block checkout.
* Re-enable the admin title, admin description and "hide on checkout" settings.
* Fixes for PHP 8.2+, recent WooCommerce, and security hardening.

See CHANGELOG.md for details.
