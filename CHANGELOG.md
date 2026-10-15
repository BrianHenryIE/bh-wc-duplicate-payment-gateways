# Changelog

## 1.6.0

* Add WooCommerce / Duplicate Gateways page, since WooCommerce 9.x removed the payment gateways table the duplicate/delete buttons were in.
* Add "Gateways" link on every plugin on plugins.php that has duplicated gateways.
* Support duplicates in the block checkout: with the gateway's own integration (and payment fields) when it takes its name from the gateway it is constructed with, otherwise with their title and description for gateways without payment fields.
* Show a duplicate's id in its admin title until an admin title is set.
* Re-enable the admin title and admin description settings for duplicates.
* Remove "Hide on checkout": untick a duplicate's "Enable" to stop offering it. Duplicates that had it ticked are offered at checkout again until "Enable" is unticked.
* Fix: PHP 8.2+ "Use of "parent" in callables is deprecated".
* Fix: only the first duplicate was returned when WooCommerce reloaded its gateways.
* Fix: gateways which set their own id (e.g. WooCommerce's bank transfer, check, cash on delivery) could be "duplicated" via the API.
* Fix: fatal error duplicating gateways which declare `validate_fields(): bool` (e.g. Venmo Gateway Pro).
* Fix: duplicates of gateways from deactivated plugins could not be deleted, and caused warnings on plugins.php.
* Security: require `manage_woocommerce` to create or delete duplicates.
* Dev: wp-env, Playwright, development plugin with demo gateways.

## 1.5.4

* Restructure the project; allow duplicating gateways whose constructor parameters all have default values (1.5.3).

## 1.5.3 2022-07-26

* Allow duplicating gateways whose constructors parameters all have default values.

## 1.4.2 2021-09-28

* Increase max number of existing gateways that can be assessed for duplicating from 10 to 30.
* Increase max number of duplicate gateways from 10 to 30.

## 1.4.1 2021-09-28

* Cache creation of gateways.

## 1.4.0 2021-09-27

* Rename (more) POSTed variables to match parent gateway id, in case of hard-coded variables.

## 1.3.0 2021-09-23

* Test duplicating each gateway is possible before presenting the "duplicate" button.

## 1.2.0

* Added function to rename POSTed credit card fields – to change the id from the parent class to the duplicate class.
* Use parent admin title and description as placeholders in duplicate gateways' settings.

## 1.1.0

* Strip HTML from tooltiptext
* Move buttons to right-most column
