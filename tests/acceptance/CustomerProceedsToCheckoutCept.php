<?php
$I = new AcceptanceTester( $scenario );
// Create products
$product_catalog = $I->getCatalog();

// Flush permalinks.
$I->loginAsAdmin();
$I->amOnAdminPage( 'options-permalink.php' );
$I->click( '#submit' );
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

$cart_query = '
	query {
		customer {
			cartUrl
			session {
				key
				value
			}
		}
	}
';
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

$I->wantTo( 'Start a new session with a custom "Client Session ID"' );
$update_session_mutation = '
	mutation($input: UpdateSessionInput!) {
		updateSession(input: $input) {
			session {
				id
				key
				value
			}
			customer { checkoutUrl }
		}
	}
';
$success                 = $I->sendGraphQLRequest(
	$update_session_mutation,
	[
		'sessionData' => [
			[
				'key'   => 'client_session_id',
				'value' => 'test-client-session-id-1',
			],
			[
				'key'   => 'client_session_id_expiration',
				'value' => '3600',
			],
		],
	]
);

$I->assertArrayNotHasKey( 'errors', $success );
$I->assertArrayHasKey( 'data', $success );
$I->assertArrayHasKey( 'updateSession', $success['data'] );
$I->assertArrayHasKey( 'session', $success['data']['updateSession'] );
$session = $success['data']['updateSession']['session'];
$session = array_column( $session, 'value', 'key' );
$I->assertEquals( $session['client_session_id'], 'test-client-session-id-1' );
$I->assertArrayHasKey( 'customer', $success['data']['updateSession'] );
$I->assertArrayHasKey( 'checkoutUrl', $success['data']['updateSession']['customer'] );
$checkout_url = $success['data']['updateSession']['customer']['checkoutUrl'];


$I->wantTo( 'add an item' );
$add_to_cart_input = [
	'clientMutationId' => 'someId',
	'productId'        => $product_catalog['belt'],
	'quantity'         => 3,
];

$success = $I->addToCart( $add_to_cart_input, $request_headers() );

$I->assertArrayNotHasKey( 'errors', $success );
$I->assertArrayHasKey( 'data', $success );
$I->assertArrayHasKey( 'addToCart', $success['data'] );
$I->assertArrayHasKey( 'cartItem', $success['data']['addToCart'] );
$I->assertArrayHasKey( 'key', $success['data']['addToCart']['cartItem'] );

$I->wantTo( 'Authenticate with checkout url and confirm page redirect' );
$I->stopFollowingRedirects();
$I->amOnUrl( $checkout_url );
$I->seeResponseCodeIs( 302 );
$I->followRedirect();
$I->seeInCurrentUrl( '/checkout/' );
$I->startFollowingRedirects();

$I->wantTo( 'Confirm checkout page has the belt in it.' );
$I->see( 'Belt' );

$I->wantTo( 'Start a new session with a expired "Client Session ID"' );
$success = $I->sendGraphQLRequest(
	$update_session_mutation,
	[
		'sessionData' => [
			[
				'key'   => 'client_session_id',
				'value' => 'test-client-session-id-1',
			],
			[
				'key'   => 'client_session_id_expiration',
				'value' => '1',
			],
		],
	]
);

$I->assertArrayNotHasKey( 'errors', $success );
$I->assertArrayHasKey( 'data', $success );
$I->assertArrayHasKey( 'updateSession', $success['data'] );
$I->assertArrayHasKey( 'session', $success['data']['updateSession'] );
$session = $success['data']['updateSession']['session'];
$session = array_column( $session, 'value', 'key' );
$I->assertEquals( $session['client_session_id'], 'test-client-session-id-1' );
$I->assertArrayHasKey( 'customer', $success['data']['updateSession'] );
$I->assertArrayHasKey( 'cartUrl', $success['data']['updateSession']['customer'] );
$invalid_cart_url = $success['data']['updateSession']['customer']['cartUrl'];

$I->wantTo( 'add an item to our new cart' );
$add_to_cart_input = [
	'clientMutationId' => 'someId',
	'productId'        => $product_catalog['jeans'],
	'quantity'         => 3,
];

$success = $I->addToCart( $add_to_cart_input, $request_headers() );

$I->assertArrayNotHasKey( 'errors', $success );
$I->assertArrayHasKey( 'data', $success );
$I->assertArrayHasKey( 'addToCart', $success['data'] );
$I->assertArrayHasKey( 'cartItem', $success['data']['addToCart'] );
$I->assertArrayHasKey( 'key', $success['data']['addToCart']['cartItem'] );

$I->wantTo( 'Go cart page and confirm empty and session not seen' );
$I->amOnPage( '/cart' );
$I->see( 'Your cart is currently empty.' );

$I->wantTo( 'Authenticate with cart url and confirm redirected to 404' );
$I->stopFollowingRedirects();
$I->amOnUrl( $invalid_cart_url );
$I->seeResponseCodeIs( 302 );
$I->followRedirect();
$I->seeResponseCodeIs( 404 );
