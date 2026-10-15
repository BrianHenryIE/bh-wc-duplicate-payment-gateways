[![WooCommerce tested 11.2](https://img.shields.io/badge/WooCommerce-11.2%20tested-7f54b3.svg)](https://wordpress.org/plugins/woocommerce/) [![PHPCS WPCS](https://img.shields.io/badge/PHPCS-WordPress%20Coding%20Standards-8892BF.svg)](https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards) [![PHPStan ](https://img.shields.io/badge/PHPStan-Level%208-2a5ea7.svg)](https://github.com/szepeviktor/phpstan-wordpress)

# Duplicate Payment Gateways

Create additional instances of a WooCommerce payment gateway, each with its own settings, title and description – e.g. to take phone orders with a copy of a gateway so they are reported separately.

NB: not every gateway can be duplicated. Gateways whose constructors set their own id (including WooCommerce's bank transfer, check and cash on delivery) or require arguments (e.g. WooCommerce Payments) are listed as "Cannot be duplicated".

## Use

**WooCommerce / Duplicate Gateways** lists every gateway. Enter a new id and click "Duplicate":

![WooCommerce / Duplicate Gateways](./.wordpress-org/screenshots/duplicate-gateways-page.png)

Each duplicate is then configured like any other gateway, under WooCommerce / Settings / Payments, with three extra settings: an admin title and admin description shown to shop managers, and "Hide on checkout" for duplicates used only for reporting/manual orders:

![A duplicate's settings](./.wordpress-org/screenshots/duplicate-settings.png)

Until an admin title is set, a duplicate's id is appended to its title in WooCommerce / Settings / Payments, to tell it apart from the original:

![WooCommerce / Settings / Payments](./.wordpress-org/screenshots/payments-settings.png)

Duplicates of gateways without payment fields are available in the block checkout (and the shortcode checkout):

![Block checkout](./.wordpress-org/screenshots/block-checkout.png)

Plugins whose gateways have been duplicated get a "Gateways" link on plugins.php listing their gateways and duplicates:

![plugins.php](./.wordpress-org/screenshots/plugins-page-gateways-link.png)

### Filters

* `bh_wc_duplicate_payment_gateways_register_blocks_integration` `( bool $register, WC_Payment_Gateway $duplicate )` – whether to register the generic block checkout integration (title and description, no payment fields) for a duplicate. Default: when the gateway has no payment fields.

## Develop

Requires Docker, Node (see `.nvmrc`) and PHP 8.4 + Composer.

```bash
composer install
npm install
npx wp-env start
```

* Site: http://localhost:8890 – `admin` / `password`. Also `customer` and `shopmanager`, password `password`.
* MySQL: `127.0.0.1:33062`, `root` / `password`.

[`tests/_wp-env/initialize-internal.sh`](tests/_wp-env/initialize-internal.sh) configures the site on each start: WooCommerce pages, `/checkout-blocks/` and `/checkout-shortcode/` pages, a test product, the offline gateways enabled, and a demo duplicate.

### Development plugin

[`development-plugin`](development-plugin) is active in wp-env only (it is not in the plugin zip). It demonstrates which gateways can be duplicated:

| Gateway | Id | Duplicable? |
|---|---|---|
| [`Demo_Gateway`](development-plugin/gateways/class-demo-gateway.php) | `demo_gateway` | Yes: it only sets `$this->id` when it is not already set. It has its own block checkout integration, as a gateway plugin would. |
| [`Demo_Gateway_Hardcoded_Id`](development-plugin/gateways/class-demo-gateway-hardcoded-id.php) | `demo_hardcoded_id` | No: it always sets its id in its constructor. |
| [`Demo_Gateway_With_Constructor_Arguments`](development-plugin/gateways/class-demo-gateway-with-constructor-arguments.php) | `demo_constructor_args` | No: its constructor requires arguments. |

and adds REST endpoints for arranging E2E tests, `/wp-json/bh-wc-duplicate-payment-gateways-development/v1/duplicates` (see [`class-duplicates.php`](development-plugin/rest/class-duplicates.php)).

### Tests

```bash
composer test              # Codeception unit, wpunit and integration suites; wpunit/integration use the wp-env MySQL.
npm run test:e2e           # Playwright, against http://localhost:8890.
npm run test:e2e:report
```

Playwright tests arrange state with REST/WP-CLI and use the UI only for what is being tested. They run with one worker, since all duplicates are saved in one option.

### Lint

```bash
composer cs-fix            # phpcbf, phpcs (WPCS + plugin-check), phpstan level 8.
npm run lint:js
npm run typecheck
```

### Build

```bash
composer create-plugin-archive
```

## How it works

A gateway is duplicated by creating an anonymous subclass of it which sets the new id before calling the parent constructor. Since a class cannot extend a variable, each gateway class is first given a `class_alias()` (`Gateway_0` … `Gateway_30`), and the anonymous classes extend those. See [`Payment_Gateways::add_gateways()`](src/woocommerce/class-payment-gateways.php).
