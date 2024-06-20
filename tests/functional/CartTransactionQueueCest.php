<?php

use Tests\WPGraphQL\Logger\CodeceptLogger as Signal;

class CartTransactionQueueCest {
	private $product_catalog;

	public function _before( FunctionalTester $I, $scenario ) {
		$scenario->skip( 'This test is unstable' );
		// Create Products
		$this->product_catalog = $I->getCatalog();
	}

	public function _addTshirtToCart( FunctionalTester $I, $headers = [] ) {
		/**
		 * Add t-shirt to the cart
		 */
		$add_to_cart_query = 'mutation ( $input: AddToCartInput! ) {
			addToCart( input: $input ) {
				clientMutationId
				cartItem {
					key
					product {
						node {
							id
						}
					}
					variation {
						node {
							id
						}
					}
					quantity
					subtotal
					subtotalTax
					total
					tax
				}
			}
		}';
		$success = $I->postRawRequest(
			$add_to_cart_query,
			[
				'input' => [
					'clientMutationId' => 'someId',
					'productId'        => $this->product_catalog['t-shirt'],
					'quantity'         => 5,
				]
			],
			[ 'headers' => $headers ]
		);


		$response_body = json_decode( $success->getBody(), true );
		$I->assertQuerySuccessful(
			$response_body,
			[
				$I->expectField( 'addToCart.cartItem.key', Signal::NOT_NULL ),
			]
		);

		$key = $I->lodashGet( $response_body, 'data.addToCart.cartItem.key' );

		/**
		 * Assert existence and validity of "woocommerce-session" HTTP header.
		 */
		$session_header = $success->getHeader('woocommerce-session');
		$I->assertNotEmpty( $session_header );
		$session_token = $session_header[0];

		return compact( 'key', 'session_token' );
	}

	public function _startAuthenticatedSession( $I ) {
		$I->setupStoreAndUsers();

		// Begin Tests.
		$I->wantTo( 'login' );
		$login_input = [
			'clientMutationId' => 'someId',
			'username'         => 'jimbo1234@example.com',
			'password'         => 'password',
		];

		$success = $I->login( $login_input );

		// Validate response.
		$I->assertQuerySuccessful(
			$success,
			[
				$I->expectField( 'login.customer.databaseId', Signal::NOT_NULL ),
				$I->expectField( 'login.authToken', Signal::NOT_NULL ),
				$I->expectField( 'login.refreshToken', Signal::NOT_NULL ),
				$I->expectField( 'login.sessionToken', Signal::NOT_NULL ),
			]
		);

		// Retrieve JWT Authorization Token for later use.
		$auth_token = $I->lodashGet( $success, 'data.login.authToken' );

		// Retrieve session token. Add as "Session %s" in the woocommerce-session HTTP header to future requests
		// so WooCommerce can identify the user session associated with actions made in the GraphQL requests.
		// You can also retrieve the token from the "woocommerce-session" HTTP response header.
		$initial_session_token = $I->lodashGet( $success, 'data.login.sessionToken' );

		$headers = [
			'Authorization'       => "Bearer {$auth_token}",
			'woocommerce-session' => "Session {$initial_session_token}",
		];

		$tokens = $this->_addTshirtToCart( $I, $headers );

		return array_merge(
			$tokens,
			[ 'auth_token' => $auth_token ]
		);
	}

	// tests
	public function testCartTransactionQueueWithConcurrentRequest( FunctionalTester $I, $scenario ) {
		//$scenario->skip( 'The test is unstable, and will be skipped until success is guaranteed on each run.' );
		$tokens = $this->_startAuthenticatedSession( $I );

		$key           = $tokens['key'];
		$auth_token    = $tokens['auth_token'];
		$session_token = $tokens['session_token'];

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

		$operations                      = [
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

		$selected_options  = [
			'headers' => [
				'Content-Type'        => 'application/json',
				'Authorization'       => "Bearer {$auth_token}",
				'woocommerce-session' => "Session {$session_token}",
			],
		];
		$responses = $I->concurrentRequests( $operations, $selected_options, 800 );

		$I->assertQuerySuccessful(
			$responses[0],
			[
				$I->expectObject(
					'updateItemQuantities',
					[
						$I->expectObject(
							'updated.0',
							[
								$I->expectField( 'key', $key ),
								$I->expectField( 'quantity', 3 )
							]
						),
						$I->expectField( 'removed', Signal::IS_FALSY ),
						$I->expectObject(
							'items.0',
							[
								$I->expectField( 'key', $key ),
								$I->expectField( 'quantity', 3 )
							]
						)
					]
				)
			]
		);

		$I->assertQuerySuccessful(
			$responses[1],
			[
				$I->expectObject(
					'updateItemQuantities',
					[
						$I->expectObject(
							'updated.0',
							[
								$I->expectField( 'key', $key ),
								$I->expectField( 'quantity', 4 )
							]
						),
						$I->expectField( 'removed', Signal::IS_FALSY ),
						$I->expectObject(
							'items.0',
							[
								$I->expectField( 'key', $key ),
								$I->expectField( 'quantity', 4 )
							]
						)
					]
				)
			]
		);

		$I->assertQuerySuccessful(
			$responses[2],
			[
				$I->expectObject(
					'removeItemsFromCart',
					[
						$I->expectField(
							'cart.contents.nodes',
							Signal::IS_FALSY
						)
					]
				)
			]
		);

		$I->assertQuerySuccessful(
			$responses[3],
			[
				$I->expectObject(
					'restoreCartItems',
					[
						$I->expectObject(
							'cart.contents.nodes.0',
							[
								$I->expectField( 'key', $key ),
								$I->expectField( 'quantity', 4 )
							]
						)
					]
				)
			]
		);
	}
}
