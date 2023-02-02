<?php

class CartTransactionQueueCest {
	private $product_catalog;

	public function _before( FunctionalTester $I ) {
		// Create Products
		$this->product_catalog = $I->getCatalog();
	}

	public function _addTshirtToCart( FunctionalTester $I, $headers = [] ) {
		/**
		 * Add t-shirt to the cart
		 */
		$success = $I->addToCart(
			[
				'clientMutationId' => 'someId',
				'productId'        => $this->product_catalog['t-shirt'],
				'quantity'         => 5,
			],
			$headers
		);

		$I->assertArrayNotHasKey( 'errors', $success );
		$I->assertArrayHasKey( 'data', $success );
		$I->assertArrayHasKey( 'addToCart', $success['data'] );
		$I->assertArrayHasKey( 'cartItem', $success['data']['addToCart'] );
		$I->assertArrayHasKey( 'key', $success['data']['addToCart']['cartItem'] );
		$key = $success['data']['addToCart']['cartItem']['key'];

		/**
		 * Assert existence and validity of "woocommerce-session" HTTP header.
		 */
		$I->seeHttpHeaderOnce( 'woocommerce-session' );
		$session_token = $I->grabHttpHeader( 'woocommerce-session' );

		return compact( 'key', 'session_token' );
	}

	public function _startAuthenticatedSession( $I ) {
		$I->setupStoreAndUsers();

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

		// Retrieve JWT Authorization Token for later use.
		$auth_token = $success['data']['login']['authToken'];

		// Retrieve session token. Add as "Session %s" in the woocommerce-session HTTP header to future requests
		// so WooCommerce can identify the user session associated with actions made in the GraphQL requests.
		// You can also retrieve the token from the "woocommerce-session" HTTP response header.
		$initial_session_token = $success['data']['login']['sessionToken'];

		$headers = [
			'Authorization'       => "Bearer {$auth_token}",
			'woocommerce-session' => "Session {$initial_session_token}",
		];

		extract( $this->_addTshirtToCart( $I, $headers ) );

		return compact( 'auth_token', 'key', 'session_token' );
	}

	// tests
	public function testCartTransactionQueueWithConcurrentRequest( FunctionalTester $I, $scenario ) {
		$scenario->skip( 'The test is unstable, and will be skipped until success is guaranteed on each run.' );
		$I->wantTo( 'Add Item to cart' );
		extract( $this->_startAuthenticatedSession( $I ) );

		$I->wantTo( 'Running a bunch of cart mutations one after the another wait for all the response at once' );
		$update_item_quantities_mutation = '
			mutation( $input: UpdateItemQuantitiesInput! ) {
				updateItemQuantities( input: $input ) {
					clientMutationId
					updated {
						key
						quantity
					}
					removed {
						key
						quantity
					}
					items {
						key
						quantity
					}
				}
			}
		';
		$remove_item_mutation            = '
			mutation ( $input: RemoveItemsFromCartInput! ) {
				removeItemsFromCart( input: $input ) {
					clientMutationId
					cart {
						contents {
							nodes {
								key
								quantity
							}
						}
					}
				}
			}
		';
		$restore_item_mutation           = '
			mutation ( $input: RestoreCartItemsInput! ) {
				restoreCartItems( input: $input ) {
					clientMutationId
					cart {
						contents {
							nodes {
								key
								quantity
							}
						}
					}
				}
			}
		';
		$cart_query                      = '
			query {
				cart {
					contents {
						nodes {
							key
							quantity
						}
					}
				}
			}
		';

		$requests           = [
			[
				'query'     => $update_item_quantities_mutation,
				'variables' => [
					'input' => [
						'clientMutationId' => 'some_id',
						'items'            => [
							[
								'key'      => $key,
								'quantity' => 3,
							],
						],
					],
				],
			],
			[
				'query'     => $update_item_quantities_mutation,
				'variables' => [
					'input' => [
						'clientMutationId' => 'some_id',
						'items'            => [
							[
								'key'      => $key,
								'quantity' => 4,
							],
						],
					],
				],
			],
			[
				'query'     => $remove_item_mutation,
				'variables' => [
					'input' => [
						'clientMutationId' => 'some_id',
						'keys'             => [ $key ],
					],
				],
			],
			[
				'query'     => $restore_item_mutation,
				'variables' => [
					'input' => [
						'clientMutationId' => 'some_id',
						'keys'             => [ $key ],
					],
				],
			],
		];
		$expected_responses = [
			[
				'updateItemQuantities' => [
					'clientMutationId' => 'some_id',
					'updated'          => [
						[
							'key'      => $key,
							'quantity' => 3,
						],
					],
					'removed'          => [],
					'items'            => [
						[
							'key'      => $key,
							'quantity' => 3,
						],
					],
				],
			],
			[
				'updateItemQuantities' => [
					'clientMutationId' => 'some_id',
					'updated'          => [
						[
							'key'      => $key,
							'quantity' => 4,
						],
					],
					'removed'          => [],
					'items'            => [
						[
							'key'      => $key,
							'quantity' => 4,
						],
					],
				],
			],
			[
				'removeItemsFromCart' => [
					'clientMutationId' => 'some_id',
					'cart'             => [
						'contents' => [
							'nodes' => [],
						],
					],
				],
			],
			[
				'restoreCartItems' => [
					'clientMutationId' => 'some_id',
					'cart'             => [
						'contents' => [
							'nodes' => [
								[
									'key'      => $key,
									'quantity' => 4,
								],
							],
						],
					],
				],
			],
		];

		$base_uri = getenv( 'WORDPRESS_URL' ) ? getenv( 'WORDPRESS_URL' ) : 'http://localhost';
		$headers  = [
			'Content-Type'        => 'application/json',
			'Authorization'       => "Bearer ${auth_token}",
			'woocommerce-session' => "Session {$session_token}",
		];
		$timeout  = 300;
		$client   = new \GuzzleHttp\Client( compact( 'base_uri', 'headers', 'timeout' ) );

		$iterator = function( $requests ) use ( $client ) {
			$stagger = 1000;
			foreach ( $requests as $index => $payload ) {
				yield function() use ( $client, $stagger, $index, $payload ) {
					$body      = json_encode( $payload );
					$delay     = $stagger * $index + 1;
					$connected = false;
					$progress  = function( $downloadTotal, $downloadedBytes, $uploadTotal, $uploadedBytes ) use ( $index, &$connected ) {
						if ( $uploadTotal === $uploadedBytes && 0 === $downloadTotal && ! $connected ) {
							\codecept_debug( "Session mutation request $index connected @ " . ( new \Carbon\Carbon() )->format( 'Y-m-d H:i:s' ) );
							$connected = true;
						}
					};
					return $client->postAsync( '/graphql', compact( 'body', 'delay', 'progress' ) );
				};
			}
		};

		$pool = new \GuzzleHttp\Pool(
			$client,
			$iterator( $requests ),
			[
				'concurrency' => 5,
				'fulfilled'   => function ( $response, $index ) use ( $I, $expected_responses ) {
					\codecept_debug( "Finished session mutation request $index @ " . ( new \Carbon\Carbon() )->format( 'Y-m-d H:i:s' ) );

					$expected = $expected_responses[ $index ];
					$body     = json_decode( $response->getBody(), true );

					\codecept_debug( $body );
					$I->assertEquals( $expected, $body['data'] );
				},
			]
		);

		$promise = $pool->promise();

		$promise->wait();
	}
}
