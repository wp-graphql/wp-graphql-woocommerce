<?php
$I = new AcceptanceTester( $scenario );
$I->setupStoreAndUsers();

// Create products
$product_catalog = $I->getCatalog();

// Begin Tests.
$I->wantTo( 'login' );
$login_input = [
	'clientMutationId' => 'someId',
	'username'         => 'jimbo1234',
	'password'         => 'password',
];

$success = $I->login( $login_input );

// Validate response.
$I->assertArrayNotHasKey( 'errors', $success );
$I->assertArrayHasKey( 'data', $success );
$I->assertArrayHasKey( 'login', $success['data'] );
$I->assertArrayHasKey( 'customer', $success['data']['login'] );
$I->assertArrayHasKey( 'authToken', $success['data']['login'] );
$I->assertArrayHasKey( 'refreshToken', $success['data']['login'] );
$I->assertArrayHasKey( 'sessionToken', $success['data']['login'] );

// Retrieve customer ID for later use.
$customer_id = $success['data']['login']['customer']['databaseId'];

// Retrieve JWT Authorization Token for later use.
$authToken = $success['data']['login']['authToken'];

// Retrieve session token. Add as "Session %s" in the woocommerce-session HTTP header to future requests
// so WooCommerce can identify the user session associated with actions made in the GraphQL requests.
// You can also retrieve the token from the "woocommerce-session" HTTP response header.
$initial_session_token = $success['data']['login']['sessionToken'];

$I->wantTo( 'Get current username' );
$query = '
    query {
        customer {
            databaseId
            username
        }
    }
';

$response = $I->sendGraphQLRequest(
	$query,
	null,
	[
		'Authorization'       => "Bearer {$authToken}",
		'woocommerce-session' => "Session {$initial_session_token}",
	]
);

$expected_results = [
	'data' => [
		'customer' => [
			'databaseId' => $customer_id,
			'username'   => 'jimbo1234',
		],
	],
];

$I->assertEquals( $expected_results, $response );

$I->wantTo( 'Put items in the cart' );

/**
 * Add "T-Shirt" to cart and confirm response data.
 */
$add_to_cart_input = [
	'clientMutationId' => 'someId',
	'productId'        => $product_catalog['t-shirt'],
	'quantity'         => 3,
];

$success = $I->addToCart(
	$add_to_cart_input,
	[
		'Authorization'       => "Bearer {$authToken}",
		'woocommerce-session' => "Session {$initial_session_token}",
	]
);

$I->assertArrayNotHasKey( 'errors', $success );
$I->assertArrayHasKey( 'data', $success );
$I->assertArrayHasKey( 'addToCart', $success['data'] );
$I->assertArrayHasKey( 'cartItem', $success['data']['addToCart'] );
$I->assertArrayHasKey( 'key', $success['data']['addToCart']['cartItem'] );
$shirt_key = $success['data']['addToCart']['cartItem']['key'];

$I->wantTo( 'Check the cart, should contain t-shirts.' );

$cart_query = '
	query {
		cart {
			contents {
				nodes {
					key
					product {
						node {
							databaseId
						}
					}
				}
			}
		}
	}
';

$response         = $I->sendGraphQLRequest(
	$cart_query,
	null,
	[
		'Authorization'       => "Bearer {$authToken}",
		'woocommerce-session' => "Session {$initial_session_token}",
	]
);
$expected_results = [
	'data' => [
		'cart' => [
			'contents' => [
				'nodes' => [
					[
						'key'     => $shirt_key,
						'product' => [
							'node' => [
								'databaseId' => $product_catalog['t-shirt'],
							],
						],
					],
				],
			],
		],
	],
];

$I->assertEquals( $expected_results, $response );

$I->wantTo( 'End session' );
/**
 * Simply not sending the previous Authorization or woocommerce-session headers in the next request does the trick.
 * ¯\_(ツ)_/¯
 */

$I->wantTo( 'Login and continue previous session.' );

$login_input = [
	'clientMutationId' => 'someId',
	'username'         => 'jimbo1234',
	'password'         => 'password',
];
$success     = $I->login( $login_input );

$I->assertArrayNotHasKey( 'errors', $success );
$I->assertArrayHasKey( 'data', $success );
$I->assertArrayHasKey( 'login', $success['data'] );
$I->assertArrayHasKey( 'customer', $success['data']['login'] );
$I->assertArrayHasKey( 'authToken', $success['data']['login'] );
$I->assertArrayHasKey( 'refreshToken', $success['data']['login'] );
$I->assertEquals( $customer_id, $success['data']['login']['customer']['databaseId'] );

// Retrieve new JWT Authorization Token for later use.
$authToken = $success['data']['login']['authToken'];

$I->wantTo( 'Check the cart again, should still contain t-shirts.' );

$cart_query = '
	query {
		cart {
			contents {
				nodes {
					key
					product {
						node {
							databaseId
						}
					}
				}
			}
		}
	}
';

$response         = $I->sendGraphQLRequest( $cart_query, null, [ 'Authorization' => "Bearer {$authToken}" ] );
$expected_results = [
	'data' => [
		'cart' => [
			'contents' => [
				'nodes' => [
					[
						'key'     => $shirt_key,
						'product' => [
							'node' => [
								'databaseId' => $product_catalog['t-shirt'],
							],
						],
					],
				],
			],
		],
	],
];

$I->assertEquals( $expected_results, $response );

$I->wantTo( 'Put more items in the cart.' );

/**
 * Add "Belt" to cart and confirm response data.
 */
$add_to_cart_input = [
	'clientMutationId' => 'someId',
	'productId'        => $product_catalog['belt'],
	'quantity'         => 2,
];

$success = $I->addToCart( $add_to_cart_input, [ 'Authorization' => "Bearer {$authToken}" ] );

$I->assertArrayNotHasKey( 'errors', $success );
$I->assertArrayHasKey( 'data', $success );
$I->assertArrayHasKey( 'addToCart', $success['data'] );
$I->assertArrayHasKey( 'cartItem', $success['data']['addToCart'] );
$I->assertArrayHasKey( 'key', $success['data']['addToCart']['cartItem'] );
$belt_key = $success['data']['addToCart']['cartItem']['key'];

// Retrieve refreshed session token created when a new item is added to the cart.
// Use this token to continue previous session.
// Can also retrieve current session token by querying the following "query{ customer { sessionToken } }"
$I->seeHttpHeaderOnce( 'woocommerce-session' );
$refreshed_session_token = $I->grabHttpHeader( 'woocommerce-session' );

$I->wantTo( 'Check the cart again, should contain t-shirts and belts.' );

$cart_query = '
	query {
		cart {
			contents {
				nodes {
					key
					product {
						node {
							databaseId
						}
					}
				}
			}
		}
	}
';

$response         = $I->sendGraphQLRequest(
	$cart_query,
	null,
	[
		'Authorization'       => "Bearer {$authToken}",
		'woocommerce-session' => "Session {$refreshed_session_token}",
	]
);
$expected_results = [
	'data' => [
		'cart' => [
			'contents' => [
				'nodes' => [
					[
						'key'     => $shirt_key,
						'product' => [
							'node' => [
								'databaseId' => $product_catalog['t-shirt'],
							],
						],
					],
					[
						'key'     => $belt_key,
						'product' => [
							'node' => [
								'databaseId' => $product_catalog['belt'],
							],
						],
					],
				],
			],
		],
	],
];

$I->assertEquals( $expected_results, $response );

$I->wantTo( 'End session' );
/**
 * Simply not sending the previous Authorization or woocommerce-session headers in the next request does the trick.
 * ¯\_(ツ)_/¯
 */

$I->wantTo( 'Login and start a new session.' );

$login_input = [
	'clientMutationId' => 'someId',
	'username'         => 'jimbo1234',
	'password'         => 'password',
];
$success     = $I->login( $login_input );

$I->assertArrayNotHasKey( 'errors', $success );
$I->assertArrayHasKey( 'data', $success );
$I->assertArrayHasKey( 'login', $success['data'] );
$I->assertArrayHasKey( 'customer', $success['data']['login'] );
$I->assertArrayHasKey( 'authToken', $success['data']['login'] );
$I->assertArrayHasKey( 'refreshToken', $success['data']['login'] );
$I->assertArrayHasKey( 'sessionToken', $success['data']['login'] );
$I->assertEquals( $customer_id, $success['data']['login']['customer']['databaseId'] );

// Retrieve new JWT Authorization Token for later use.
$authToken = $success['data']['login']['authToken'];

// Retrieve new session token using this in any future requests will destroy
// any existing sessions.
$new_session_token = $success['data']['login']['sessionToken'];

$I->wantTo( 'Check the cart and should be empty' );

$cart_query = '
	query {
		cart {
			contents {
				nodes {
					key
					product {
						node {
							databaseId
						}
					}
				}
			}
		}
	}
';

$response         = $I->sendGraphQLRequest(
	$cart_query,
	null,
	[
		'Authorization'       => "Bearer {$authToken}",
		'woocommerce-session' => "Session {$new_session_token}",
	]
);
$expected_results = [
	'data' => [
		'cart' => [
			'contents' => [
				'nodes' => [],
			],
		],
	],
];

$I->assertEquals( $expected_results, $response );
