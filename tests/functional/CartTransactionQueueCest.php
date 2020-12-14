<?php

class CartTransactionQueueCest {
	private $product_catalog;

    public function _before( FunctionalTester $I ) {
        // Create Products
        $this->product_catalog = $I->getCatalog();
	}

	public function _addTshirtToCart( FunctionalTester $I, $headers = array() ) {
		/**
         * Add t-shirt to the cart
         */
        $success = $I->addToCart(
            array(
                'clientMutationId' => 'someId',
                'productId'        => $this->product_catalog['t-shirt'],
                'quantity'         => 5,
			),
			$headers
		);

		$I->assertArrayNotHasKey( 'errors', $success );
        $I->assertArrayHasKey('data', $success );
        $I->assertArrayHasKey('addToCart', $success['data'] );
        $I->assertArrayHasKey('cartItem', $success['data']['addToCart'] );
        $I->assertArrayHasKey('key', $success['data']['addToCart']['cartItem'] );
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
		$I->wantTo('login');
		$login_input = array(
			'clientMutationId' => 'someId',
			'username'         => 'jimbo1234',
			'password'         => 'password',
		);

		$success = $I->login( $login_input );

		// Validate response.
		$I->assertArrayNotHasKey( 'errors', $success );
		$I->assertArrayHasKey('data', $success );
		$I->assertArrayHasKey('login', $success['data'] );
		$I->assertArrayHasKey('customer', $success['data']['login'] );
		$I->assertArrayHasKey('authToken', $success['data']['login'] );
		$I->assertArrayHasKey('refreshToken', $success['data']['login'] );
		$I->assertArrayHasKey('sessionToken', $success['data']['login'] );

		// Retrieve JWT Authorization Token for later use.
		$auth_token = $success['data']['login']['authToken'];

		// Retrieve session token. Add as "Session %s" in the woocommerce-session HTTP header to future requests
		// so WooCommerce can identify the user session associated with actions made in the GraphQL requests.
		// You can also retrieve the token from the "woocommerce-session" HTTP response header.
		$initial_session_token = $success['data']['login']['sessionToken'];

		$headers = array(
			'Authorization'       => "Bearer {$auth_token}",
			'woocommerce-session' => "Session {$initial_session_token}",
		);

		extract( $this->_addTshirtToCart( $I, $headers ) );

		return compact( 'auth_token', 'key', 'session_token' );
	}

    // tests
    public function testCartTransactionQueueWithConcurrentRequest( FunctionalTester $I ) {
		extract( $this->_startAuthenticatedSession( $I ) );

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
		$cart_query                     = '
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

		$batch_requests = array(
			array(
				'query'     => $update_item_quantities_mutation,
				'variables' => array(
					'input' => array(
						'clientMutationId' => 'some_id',
						'items'            => array(
							array( 'key' => $key, 'quantity' => 3 ),
						),
					),
				),
			),
			array(
				'query'     => $update_item_quantities_mutation,
				'variables' => array(
					'input' => array(
						'clientMutationId' => 'some_id',
						'items'            => array(
							array( 'key' => $key, 'quantity' => 4 ),
						),
					),
				),
			),
			array(
				'query'     => $remove_item_mutation,
				'variables' => array(
					'input' => array(
						'clientMutationId' => 'some_id',
						'keys'             => array( $key )
					),
				),
			),
			array(
				'query'     => $restore_item_mutation,
				'variables' => array(
					'input' => array(
						'clientMutationId' => 'some_id',
						'keys'             => array( $key ),
					),
				),
			),
			array( 'query' => $cart_query ),
		);


		$client = new \GuzzleHttp\Client();

		$fn = function( $batch_requests ) use ( $client, $auth_token, $session_token ) {
			$base_uri = getenv( 'WORDPRESS_URL' ) ? getenv( 'WORDPRESS_URL' ) : 'http://localhost';
			$headers  = array(
				'Content-Type'        => 'application/json',
				'Authorization'       => "Bearer ${auth_token}",
				'woocommerce-session' => "Session {$session_token}",
			);

			foreach ( $batch_requests as $request ) {
				yield new \GuzzleHttp\Psr7\Request(
					'POST',
					"$base_uri/graphql",
					$headers,
					json_encode( $request )
				);
			}
		};

		$batch_expected_responses = array(
			array(
				'updateItemQuantities' => array(
					'clientMutationId' => 'some_id',
					'updated'          => array(
						array(
							'key'      => $key,
							'quantity' => 3,
						)
					),
					'removed'          => array(),
					'items'            => array(
						array(
							'key'      => $key,
							'quantity' => 3,
						)
					),
				),
			),
			array(
				'updateItemQuantities' => array(
					'clientMutationId' => 'some_id',
					'updated'          => array(
						array(
							'key'      => $key,
							'quantity' => 4,
						)
					),
					'removed'          => array(),
					'items'            => array(
						array(
							'key'      => $key,
							'quantity' => 4,
						)
					),
				),
			),
			array(
				'removeItemsFromCart' => array(
					'clientMutationId' => 'some_id',
					'cart' => array(
						'contents' => array(
							'nodes' => array(),
						),
					),
				),
			),
			array(
				'restoreCartItems' => array(
					'clientMutationId' => 'some_id',
					'cart' => array(
						'contents' => array(
							'nodes' => array(
								array(
									'key'      => $key,
									'quantity' => 4
								),
							),
						),
					),
				),
			),
			array(
				'cart' => array(
					'contents' => array(
						'nodes' => array(
							array(
								'key'      => $key,
								'quantity' => 4
							),
						),
					),
				),
			),
		);

		$pool   = new \GuzzleHttp\Pool( $client, $fn( $batch_requests ),
			array(
				'concurrency' => 5,
				'fulfilled' => function( \GuzzleHttp\Psr7\Response $response, $index ) use ( $I, $batch_expected_responses ) {
					// this is delivered each successful response
					$body = json_decode( $response->getBody(), true );
					\codecept_debug( $body );
					$I->assertEquals(
						$batch_expected_responses[ $index ],
						$body['data'],
						"Response $index doesn't match expected data"
					);
				},
				'rejected' => function( \GuzzleHttp\Exception\RequestExceptionRequestException $reason, $index ) use ( $I ) {
					// this is delivered each failed request
					$body = json_decode( $response->getBody(), true );
					\codecept_debug( $body );
					$I->assertTrue( false, "Response $index rejected" );
				},
			)
		);

		// Initiate the transfers and create a promise
		$promise = $pool->promise();

		// Force the pool of requests to complete.
		$promise->wait();
    }
}
