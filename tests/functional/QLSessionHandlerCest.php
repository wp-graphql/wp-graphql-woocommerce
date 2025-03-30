<?php

use WPGraphQL\WooCommerce\Vendor\Firebase\JWT\JWT;
use WPGraphQL\WooCommerce\Vendor\Firebase\JWT\Key;
use Tests\WPGraphQL\Logger\CodeceptLogger as Signal;
class QLSessionHandlerCest {
	private $product_catalog;

	public function _before( FunctionalTester $I ) {
		// Create products.
		$this->product_catalog = $I->getCatalog();

		if ( ! defined( 'GRAPHQL_WOOCOMMERCE_SECRET_KEY' ) ) {
			define( 'GRAPHQL_WOOCOMMERCE_SECRET_KEY', 'testestestestest' );
		}
	}

	// tests
	public function testCartMutationsWithValidCartSessionToken( FunctionalTester $I ) {
		/**
		 * Add item to the cart
		 */
		$success = $I->addToCart(
			[
				'clientMutationId' => 'someId',
				'productId'        => $this->product_catalog['t-shirt'],
				'quantity'         => 5,
			]
		);

		$I->assertQuerySuccessful(
			$success,
			[
				$I->expectField( 'addToCart.cartItem.key', Signal::NOT_NULL ),
			]
		);

		$cart_item_key = $I->lodashGet( $success, 'data.addToCart.cartItem.key' );

		/**
		 * Assert existence and validity of "woocommerce-session" HTTP header.
		 */
		$I->seeHttpHeaderOnce( 'woocommerce-session' );
		$session_token = $I->grabHttpHeader( 'woocommerce-session' );

		// Decode token
		JWT::$leeway = 60;
		$token_data  = ! empty( $session_token )
			? JWT::decode( $session_token, new Key( GRAPHQL_WOOCOMMERCE_SECRET_KEY, 'HS256' ) )
			: null;

		$I->assertNotEmpty( $token_data );
		$I->assertNotEmpty( $token_data->iss );
		$I->assertNotEmpty( $token_data->iat );
		$I->assertNotEmpty( $token_data->nbf );
		$I->assertNotEmpty( $token_data->exp );
		$I->assertNotEmpty( $token_data->data );
		$I->assertNotEmpty( $token_data->data->customer_id );

		$wp_url = getenv( 'WORDPRESS_URL' );
		$I->assertEquals( $token_data->iss, $wp_url );

		/**
		 * Make a cart query request with "woocommerce-session" HTTP Header and confirm
		 * correct cart contents.
		 */
		$query = '
            query {
                cart {
                    contents {
                        nodes {
                            key
                        }
                    }
                }
            }
        ';

		$actual   = $I->sendGraphQLRequest( $query, null, [ 'woocommerce-session' => "Session {$session_token}" ] );
		$I->assertQuerySuccessful(
			$actual,
			[
				$I->expectField( 'cart.contents.nodes.#.key', $cart_item_key ),
			]
		);

		/**
		 * Remove item from the cart
		 */
		$success = $I->removeItemsFromCart(
			[
				'clientMutationId' => 'someId',
				'keys'             => $cart_item_key,
			],
			[ 'woocommerce-session' => "Session {$session_token}" ]
		);

		$I->assertQuerySuccessful(
			$success,
			[
				$I->expectField( 'removeItemsFromCart.cartItems.#.key', $cart_item_key ),
			]
		);

		/**
		 * Make a cart query request with "woocommerce-session" HTTP Header and confirm
		 * correct cart contents.
		 */
		$query = '
            query {
                cart {
                    contents {
                        nodes {
                            key
                        }
                    }
                }
            }
        ';

		$actual   = $I->sendGraphQLRequest( $query, null, [ 'woocommerce-session' => "Session {$session_token}" ] );
		$I->assertQuerySuccessful(
			$actual,
			[
				$I->expectField( 'cart.contents.nodes', Signal::IS_FALSY ),
			]
		);

		/**
		 * Restore item to the cart
		 */
		$success = $I->restoreCartItems(
			[
				'clientMutationId' => 'someId',
				'keys'             => [ $cart_item_key ],
			],
			[ 'woocommerce-session' => "Session {$session_token}" ]
		);

		$I->assertQuerySuccessful(
			$success,
			[
				$I->expectField( 'restoreCartItems.cartItems.#.key', $cart_item_key ),
			]
		);

		/**
		 * Make a cart query request with "woocommerce-session" HTTP Header and confirm
		 * correct cart contents.
		 */
		$query = '
            query {
                cart {
                    contents {
                        nodes {
                            key
                        }
                    }
                }
            }
        ';

		$actual   = $I->sendGraphQLRequest( $query, null, [ 'woocommerce-session' => "Session {$session_token}" ] );
		$I->assertQuerySuccessful(
			$actual,
			[
				$I->expectField( 'cart.contents.nodes.#.key', $cart_item_key ),
			]
		);
	}

	public function testCartMutationsWithInvalidCartSessionToken( FunctionalTester $I ) {
		/**
		 * Add item to cart and retrieve session token to corrupt.
		 */
		$success = $I->addToCart(
			[
				'clientMutationId' => 'someId',
				'productId'        => $this->product_catalog['t-shirt'],
				'quantity'         => 1,
			]
		);

		$I->assertQuerySuccessful(
			$success,
			[
				$I->expectField( 'addToCart.cartItem.key', Signal::NOT_NULL ),
			]
		);
		$cart_item_key = $I->lodashGet( $success, 'data.addToCart.cartItem.key' );

		/**
		 * Retrieve session token from "woocommerce-session" HTTP response header.
		 */
		$I->seeHttpHeaderOnce( 'woocommerce-session' );
		$valid_token = $I->grabHttpHeader( 'woocommerce-session' );

		// Decode token
		$token_data = ! empty( $valid_token )
			? JWT::decode( $valid_token, new Key( GRAPHQL_WOOCOMMERCE_SECRET_KEY, 'HS256' ) )
			: null;

		/**
		 * Attempt to add item to the cart with invalid session token.
		 * GraphQL should throw an error and mutation will fail.
		 */
		$invalid_token                    = $token_data;
		$invalid_token->data->customer_id = '';
		$invalid_token                    = JWT::encode( (array) $invalid_token, GRAPHQL_WOOCOMMERCE_SECRET_KEY, 'HS256' );

		$failed = $I->addToCart(
			[
				'clientMutationId' => 'someId',
				'productId'        => $this->product_catalog['t-shirt'],
				'quantity'         => 1,
			],
			[ 'woocommerce-session' => "Session {$invalid_token}" ]
		);

		$I->assertQueryError( $failed );

		/**
		 * Attempt to remove item from the cart with invalid session token.
		 * GraphQL should throw an error and mutation will fail.
		 */
		$invalid_token      = $token_data;
		$invalid_token->iss = '';
		$invalid_token      = JWT::encode( (array) $invalid_token, GRAPHQL_WOOCOMMERCE_SECRET_KEY, 'HS256' );

		$failed = $I->removeItemsFromCart(
			[
				'clientMutationId' => 'someId',
				'keys'             => $cart_item_key,
			],
			[ 'woocommerce-session' => "Session {$invalid_token}" ]
		);

		$I->assertQueryError( $failed );

		/**
		 * Attempt to update quantity of item in the cart with invalid session token.
		 * GraphQL should throw an error and mutation will fail.
		 */
		$failed = $I->updateItemQuantities(
			[
				'clientMutationId' => 'someId',
				'items'            => [
					[
						'key'      => $cart_item_key,
						'quantity' => 0,
					],
				],
			],
			[ 'woocommerce-session' => 'Session invalid-jwt-token-string' ]
		);

		$I->assertQueryError( $failed );

		/**
		 * Attempt to empty cart with invalid session token.
		 * GraphQL should throw an error and mutation will fail.
		 */
		$failed = $I->emptyCart(
			[ 'clientMutationId' => 'someId' ],
			[ 'woocommerce-session' => 'Session invalid-jwt-token-string' ]
		);

		$I->assertQueryError( $failed );

		/**
		 * Attempt to add fee on cart with invalid session token.
		 * GraphQL should throw an error and mutation will fail.
		 */
		$failed = $I->addFee(
			[
				'clientMutationId' => 'someId',
				'name'             => 'extra_fee',
				'amount'           => 49.99,
			],
			[ 'woocommerce-session' => 'Session invalid-jwt-token-string' ]
		);

		$I->assertQueryError( $failed );

		/**
		 * Attempt to apply coupon on cart with invalid session token.
		 * GraphQL should throw an error and mutation will fail.
		 *
		 * @Note: No coupons exist in the database, but mutation should fail before that becomes a factor.
		 */
		$failed = $I->applyCoupon(
			[
				'clientMutationId' => 'someId',
				'code'             => 'some_coupon',
			],
			[ 'woocommerce-session' => 'Session invalid-jwt-token-string' ]
		);

		$I->assertQueryError( $failed );

		/**
		 * Attempt to remove coupon from cart with invalid session token.
		 * GraphQL should throw an error and mutation will fail.
		 *
		 * @Note: No coupons exist on the cart, but mutation should failed before that becomes a factor.
		 */
		$failed = $I->removeCoupons(
			[
				'clientMutationId' => 'someId',
				'codes'            => [ 'some_coupon' ],
			],
			[ 'woocommerce-session' => 'Session invalid-jwt-token-string' ]
		);

		$I->assertQueryError( $failed );

		/**
		 * Attempt to restore item to the cart with invalid session token.
		 * GraphQL should throw an error and mutation will fail.
		 *
		 * @Note: No items have been removed from the cart in this session,
		 * but mutation should failed before that becomes a factor.
		 */
		$failed = $I->restoreCartItems(
			[
				'clientMutationId' => 'someId',
				'keys'             => [ $cart_item_key ],
			],
			[ 'woocommerce-session' => 'Session invalid-jwt-token-string' ]
		);

		$I->assertQueryError( $failed );

		/**
		 * Attempt to restore item to the cart with invalid session token.
		 * GraphQL should throw an error and mutation will fail.
		 *
		 * @Note: No items have been removed from the cart in this session,
		 * but mutation should failed before that becomes a factor.
		 */
		$failed = $I->updateShippingMethod(
			[
				'clientMutationId' => 'someId',
				'shippingMethods'  => [ 'legacy_flat_rate' ],
			],
			[ 'woocommerce-session' => 'Session invalid-jwt-token-string' ]
		);

		$I->assertQueryError( $failed );

		/**
		 * Attempt to query cart with invalid session token.
		 * GraphQL should throw an error and query will fail.
		 */
		$query = '
            query {
                cart {
                    contents {
                        nodes {
                            key
                        }
                    }
                }
            }
        ';

		$failed = $I->sendGraphQLRequest(
			$query,
			null,
			[ 'woocommerce-session' => 'Session invalid-jwt-token-string' ]
		);

		$I->assertQueryError( $failed );
	}

	public function testCartSessionDataMutations( FunctionalTester $I, $scenario ) {
		//$scenario->skip( 'Test skipped until scenario can be created properly again.' );
		/**
		 * Add item to the cart
		 */
		$success = $I->addToCart(
			[
				'clientMutationId' => 'someId',
				'productId'        => $this->product_catalog['socks'],
				'quantity'         => 2,
			]
		);

		$I->assertQuerySuccessful(
			$success,
			[
				$I->expectField( 'addToCart.cartItem.key', Signal::NOT_NULL ),
			]
		);
		$cart_item_key = $I->lodashGet( $success, 'data.addToCart.cartItem.key' );

		/**
		 * Assert existence and validity of "woocommerce-session" HTTP header.
		 */
		$I->seeHttpHeaderOnce( 'woocommerce-session' );
		$session_token = $I->grabHttpHeader( 'woocommerce-session' );

		// Decode token
		JWT::$leeway = 60;
		$token_data  = ! empty( $session_token )
			? JWT::decode( $session_token, new Key( GRAPHQL_WOOCOMMERCE_SECRET_KEY, 'HS256' ) )
			: null;

		$I->assertNotEmpty( $token_data );
		$I->assertNotEmpty( $token_data->iss );
		$I->assertNotEmpty( $token_data->iat );
		$I->assertNotEmpty( $token_data->nbf );
		$I->assertNotEmpty( $token_data->exp );
		$I->assertNotEmpty( $token_data->data );
		$I->assertNotEmpty( $token_data->data->customer_id );

		$wp_url = getenv( 'WORDPRESS_URL' );
		$I->assertEquals( $token_data->iss, $wp_url );

		/**
		 * Set shipping address, so shipping rates can be calculated
		 */
		$input = [
			'clientMutationId' => 'someId',
			'shipping'         => [
				'state'    => 'New York',
				'country'  => 'US',
				'postcode' => '12345',
			],
		];

		$mutation = '
            mutation ( $input: UpdateCustomerInput! ){
                updateCustomer ( input: $input ) {
                    customer {
                        shipping {
                            state
                            country
                            postcode
                        }
                    }
                }
            }
        ';

		$actual   = $I->sendGraphQLRequest( $mutation, $input, [ 'woocommerce-session' => "Session {$session_token}" ] );
		$I->assertQuerySuccessful(
			$actual,
			[
				$I->expectObject(
					'updateCustomer.customer.shipping',
					[
						$I->expectField( 'state', 'New York' ),
						$I->expectField( 'country', 'US' ),
						$I->expectField( 'postcode', '12345' ),
					]
				)
			]
		);

		/**
		 * Make a cart query request with "woocommerce-session" HTTP Header and confirm
		 * correct cart contents and chosen and available shipping methods.
		 */
		$query = '
            query {
                cart {
                    contents {
                        nodes {
                            key
                        }
                    }
                    availableShippingMethods {
                        packageDetails
                        supportsShippingCalculator
                        rates {
                            id
                            cost
                            label
                        }
                    }
                }
            }
        ';

		$actual   = $I->sendGraphQLRequest( $query, null, [ 'woocommerce-session' => "Session {$session_token}" ] );
		$I->assertQuerySuccessful(
			$actual,
			[
				$I->expectField( 'cart.contents.nodes.#.key', $cart_item_key ),
				$I->expectNode(
					'cart.availableShippingMethods',
					[
						$I->expectField( 'packageDetails', \html_entity_decode( 'socks &times;2' ) ),
						$I->expectField( 'supportsShippingCalculator', true ),
						$I->expectNode(
							'rates',
							[
								$I->expectField( 'cost', '0.00' ),
								$I->expectField( 'label', 'Flat rate' ),
							]
						),
						$I->expectNode(
							'rates',
							[
								$I->expectField( 'cost', '0.00' ),
								$I->expectField( 'label', 'Free shipping' ),
							]
						),
					],
					0
				),
			]
		);

		$chosen_shipping_method = $I->lodashGet( $actual, 'data.cart.availableShippingMethods.0.rates.0.id' );

		/**
		 * Update shipping method to 'flat_rate' shipping.
		 */
		$mutation = '
            mutation ($input: UpdateShippingMethodInput!){
                updateShippingMethod(input: $input) {
                    cart {
                        availableShippingMethods {
                            packageDetails
                            supportsShippingCalculator
                            rates {
                                id
                                cost
                                label
                            }
                        }
                        chosenShippingMethods
                        shippingTotal
                        shippingTax
                        subtotal
                        subtotalTax
                        total
                    }
                }
            }
        ';

		$success = $I->sendGraphQLRequest(
			$mutation,
			[
				'clientMutationId' => 'someId',
				'shippingMethods'  => [ $chosen_shipping_method ],
			],
			[ 'woocommerce-session' => "Session {$session_token}" ]
		);

		$I->assertQuerySuccessful(
			$success,
			[
				$I->expectField(
					'updateShippingMethod.cart.chosenShippingMethods.#',
					$chosen_shipping_method
				),
			]
		);
	}
}
