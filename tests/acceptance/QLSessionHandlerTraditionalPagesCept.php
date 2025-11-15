<?php
/**
 * Tests for QL_Session_Handler on traditional WordPress pages.
 *
 * Tests that when QL_Session_Handler is enabled for REST/AJAX requests,
 * it doesn't break traditional page loads that rely on cookies.
 *
 * @package Tests\WPGraphQL\WooCommerce
 */

$I = new AcceptanceTester( $scenario );

$I->wantTo( 'Verify QL_Session_Handler works with traditional WordPress pages when REST/AJAX modes are enabled' );

// Enable QL_Session_Handler for REST and AJAX requests.
$I->haveOptionInDatabase(
	'wp_graphql_woocommerce_settings',
	[
		'enable_ql_session_handler_on_rest' => 'on',
		'enable_ql_session_handler_on_ajax' => 'on',
	]
);

// Get product catalog which creates products.
$product_catalog = $I->getCatalog();

// Create a cart page using WooCommerce shortcode and set it as the cart page.
$cart_page_id = $I->haveACartShortcodePageInDatabase( 'cart-shortcode' );
$I->haveOptionInDatabase( 'woocommerce_cart_page_id', $cart_page_id );

// Add first product to cart via WooCommerce AJAX endpoint.
$I->wantTo( 'Add first product (t-shirt) to cart using WooCommerce AJAX' );
$I->sendAjaxPostRequest(
	'/?wc-ajax=add_to_cart',
	[
		'product_id' => $product_catalog['t-shirt'],
		'quantity'   => 1,
	]
);

// Navigate to cart page after first product.
$I->wantTo( 'Navigate to cart page and verify first product is in cart' );
$I->amOnPage( '/cart-shortcode/' );
$I->see( 'T-Shirt', '.woocommerce-cart-form' );

// Add second product to cart via AJAX.
$I->wantTo( 'Add second product (jeans) to cart using WooCommerce AJAX' );
$I->sendAjaxPostRequest(
	'/?wc-ajax=add_to_cart',
	[
		'product_id' => $product_catalog['jeans'],
		'quantity'   => 2,
	]
);

// Navigate to cart page after second product.
$I->wantTo( 'Navigate to cart page and verify both products are in cart' );
$I->amOnPage( '/cart-shortcode/' );

// The cart should be populated with both products (server-side rendered via shortcode).
// EXPECTED TO FAIL BEFORE FIX: Cart may lose session between requests.
// EXPECTED TO PASS AFTER FIX: Cart will show both "T-Shirt" and "Jeans" products.
$I->see( 'T-Shirt', '.woocommerce-cart-form' );
$I->see( 'Jeans', '.woocommerce-cart-form' );
