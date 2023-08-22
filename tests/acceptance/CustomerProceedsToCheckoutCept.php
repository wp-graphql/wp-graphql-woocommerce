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


$I->wantTo( 'Set "client_session_id" and get nonced cart URL' );
$update_session_mutation = '
	mutation($input: UpdateSessionInput!) {
		updateSession(input: $input) {
			session {
				id
				key
				value
			}
			customer { cartUrl }
		}
	}
';
$success                 = $I->sendGraphQLRequest(
	$update_session_mutation,
	[
		'sessionData' => [
			[
				'key'   => 'client_session_id',
				'value' => 'test-client-session-id',
			],
			[
				'key'   => 'client_session_id_expiration',
				'value' => (string) ( time() + 3600 ),
			],
		],
	],
	$request_headers()
);

$I->assertArrayNotHasKey( 'errors', $success );
$I->assertArrayHasKey( 'data', $success );
$I->assertArrayHasKey( 'updateSession', $success['data'] );
$I->assertArrayHasKey( 'session', $success['data']['updateSession'] );
$session = $success['data']['updateSession']['session'];
$session = array_column( $session, 'value', 'key' );
$I->assertEquals( $session['client_session_id'], 'test-client-session-id' );
$I->assertArrayHasKey( 'customer', $success['data']['updateSession'] );
$I->assertArrayHasKey( 'cartUrl', $success['data']['updateSession']['customer'] );
$cart_url = $success['data']['updateSession']['customer']['cartUrl'];

$I->wantTo( 'Go cart page and confirm empty and session not seen' );
$I->amOnPage( '/cart' );
$I->seeElement('.wc-empty-cart-message');

$I->wantTo( 'Authenticate with cart url and confirm page redirect' );
$I->stopFollowingRedirects();
$I->amOnUrl( $cart_url );
$I->seeResponseCodeIs( 302 );
$I->followRedirect();
$I->seeInCurrentUrl( '/cart/' );
$I->startFollowingRedirects();

$I->wantTo( 'Confirm cart not empty and T-shirt in cart.' );
$I->see( 'T-Shirt' );
