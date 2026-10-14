#!/bin/bash

PLUGIN_SLUG=$1;
MODE=$2;
# Print the script name.
echo "Running " $(basename "$0") " for " $PLUGIN_SLUG;

# The scripts are mapped one level above the webroot so they are not web-servable.
SCRIPT_DIR="$(dirname "$0")"

# In CI the plugin is not mounted from the working directory; install the zip
# initialize-external.sh copied into the mapped setup directory.
if [ "$MODE" = "ci" ]; then
  PLUGIN_ZIP=$(ls -t "$SCRIPT_DIR/$PLUGIN_SLUG".*.zip 2>/dev/null | head -n 1)
  if [ -z "$PLUGIN_ZIP" ]; then
    echo "No $PLUGIN_SLUG zip found in $SCRIPT_DIR" >&2
    exit 1
  fi
  echo "Installing $PLUGIN_SLUG from $(basename "$PLUGIN_ZIP")"
  wp plugin install "$PLUGIN_ZIP" --force --activate
fi

if [ ! -d /var/www/html/wp-content/uploads ]; then
  echo "mkdir /var/www/html/wp-content/uploads"
  mkdir /var/www/html/wp-content/uploads
fi

chmod a+w /var/www/html/wp-content/uploads;

# Database for the Codeception wpunit/integration suites, which run on the host against this MySQL container.
# @see .env.testing
echo "Creating test database for Codeception..."
wp db query "CREATE DATABASE IF NOT EXISTS bh_wc_duplicate_payment_gateways_tests;"

echo "wp plugin activate --all"
wp plugin activate --all
wp plugin deactivate hello akismet 2>/dev/null || true

echo "Set up pretty permalinks for REST API."
wp rewrite structure /%year%/%monthnum%/%postname%/ --hard;

# Set up WooCommerce pages.
if ! wp option get woocommerce_shop_page_id 2>/dev/null | grep -q '^[1-9]'; then
  echo "Creating WooCommerce pages..."
  wp wc --user=1 tool run install_pages 2>/dev/null || true
fi

# Dedicated checkout pages – one per checkout style – so the shortcode and blocks
# E2E specs never mutate a shared page.
if ! wp post list --post_type=page --field=post_name 2>/dev/null | grep -qx "checkout-shortcode"; then
  echo "Creating /checkout-shortcode/ page..."
  wp post create --post_type=page --post_title="Checkout (Shortcode)" --post_name="checkout-shortcode" \
    --post_status=publish --post_content="$(cat "$SCRIPT_DIR/shortcode-checkout-post-content.txt")" 2>/dev/null || true
fi
if ! wp post list --post_type=page --field=post_name 2>/dev/null | grep -qx "checkout-blocks"; then
  echo "Creating /checkout-blocks/ page..."
  wp post create --post_type=page --post_title="Checkout (Blocks)" --post_name="checkout-blocks" \
    --post_status=publish --post_content="$(cat "$SCRIPT_DIR/blocks-checkout-post-content.txt")" 2>/dev/null || true
fi

# Enable the core offline gateways so there is something to duplicate.
echo "Enabling cheque, BACS, COD payment gateways..."
wp option set woocommerce_cheque_settings '{"enabled":"yes","title":"Check payments","description":"Please send a check to Store Name, Store Street, Store Town, Store State / County, Store Postcode."}' --format=json
wp option set woocommerce_bacs_settings '{"enabled":"yes","title":"Direct bank transfer","description":"Make your payment directly into our bank account."}' --format=json
wp option set woocommerce_cod_settings '{"enabled":"yes","title":"Cash on delivery","description":"Pay with cash upon delivery."}' --format=json

# Create a simple product for checkout testing.
if ! wp post list --post_type=product --field=post_title 2>/dev/null | grep -qx "Test Product"; then
  echo "Creating test product..."
  wp wc product create --user=1 --name="Test Product" --regular_price="19.99" --status=publish 2>/dev/null || true
fi

# Customer for checkout tests.
wp user get customer --field=ID >/dev/null 2>&1 || wp user create customer customer@example.com --role=customer --user_pass=password --display_name="Customer"

# Shop manager, who can manage WooCommerce settings, and so can duplicate gateways.
wp user get shopmanager --field=ID >/dev/null 2>&1 || wp user create shopmanager shopmanager@example.com --role=shop_manager --user_pass=password --display_name="Shop Manager"

# Disable "Welcome" guides.
wp user meta update 1 wp_persisted_preferences '{"core":{"isComplementaryAreaVisible":true},"core\/edit-post":{"welcomeGuide":false},"_modified":"2026-01-29T22:58:19.661Z"}' --format=json

wp option set woocommerce_coming_soon no
wp option set woocommerce_onboarding_profile '{"skipped":true}' --format=json
wp option set woocommerce_task_list_hidden yes
wp option set woocommerce_show_marketplace_suggestions no
wp option set woocommerce_allow_tracking no
