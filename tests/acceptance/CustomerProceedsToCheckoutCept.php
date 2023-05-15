<?php
$I = new AcceptanceTester( $scenario );
// Create products
$product_catalog = $I->getCatalog();

// Flush permalinks.
$I->loginAsAdmin();
$I->amOnAdminPage('options-permalink.php');
$I->click('#submit');
$I->logOut();

// Make quick helper for managing the session token.
$request_headers = function () use ( $I, &$last_request_headers ) {
	$last_request_headers = [
		'woocommerce-session' => 'Session ' . $I->wantHTTPResponseHeaders( 'woocommerce-session' ),
	];

	return $last_request_headers;
};

// Begin test.
$I->wantTo( 'add items to the cart' );

/**
 * Add "T-Shirt" to cart and confirm response data.
 */
$add_to_cart_input = [
	'clientMutationId' => 'someId',
	'productId'        => $product_catalog['t-shirt'],
	'quantity'         => 3,
];

$success = $I->addToCart( $add_to_cart_input );

$I->assertArrayNotHasKey( 'errors', $success );
$I->assertArrayHasKey( 'data', $success );
$I->assertArrayHasKey( 'addToCart', $success['data'] );
$I->assertArrayHasKey( 'cartItem', $success['data']['addToCart'] );
$I->assertArrayHasKey( 'key', $success['data']['addToCart']['cartItem'] );
$shirt_key = $success['data']['addToCart']['cartItem']['key'];


$I->wantTo( 'Get Cart URL' );

$cart_query = 'query { customer { cartUrl } }';
$success    = $I->sendGraphQLRequest(
	$cart_query,
	null,
	$request_headers()
);

$I->assertArrayNotHasKey( 'errors', $success );
$I->assertArrayHasKey( 'data', $success );
$I->assertArrayHasKey( 'customer', $success['data'] );
$I->assertArrayHasKey( 'cartUrl', $success['data']['customer'] );
$cart_url = $success['data']['customer']['cartUrl'];

$I->wantTo( 'Go cart page and confirm empty and session not seen' );
$I->amOnPage( '/cart' );
$I->see( 'Your cart is currently empty.' );

$I->wantTo( 'Authenticate with cart url and confirm page redirect' );
$I->stopFollowingRedirects();
$I->amOnUrl( $cart_url );
$I->seeResponseCodeIs( 302 );
$I->followRedirect();
$I->seeInCurrentUrl( '/cart/' );
$I->startFollowingRedirects();

$I->wantTo( 'Confirm cart not empty and T-shirt in cart.' );
$I->see( 'T-Shirt' );