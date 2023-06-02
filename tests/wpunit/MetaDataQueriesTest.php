<?php

use GraphQLRelay\Relay;

class MetaDataQueriesTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {

	// tests
	public function testCartMetaDataQueries() {
		// Create Variation Product.
		$product_ids = $this->factory->product_variation->createSome();
		// Create Cart Item with meta data.
		$meta_data = [
			'meta_1' => 'test_meta_1',
			'meta_2' => 'test_meta_2',
		];

		// Add item to cart.
		$cart_item_key = $this->factory->cart->add(
			[
				'product_id'     => $product_ids['product'],
				'quantity'       => 2,
				'variation_id'   => $product_ids['variations'][0],
				'variation'      => [ 'attribute_pa_color' => 'red' ],
				'cart_item_data' => $meta_data,
			]
		)[0];

		$query = '
            query($key: String, $keysIn: [String]) {
                cart {
                    contents {
                        nodes {
							key
                            extraData(key: $key, keysIn: $keysIn) {
                                key
                                value
                            }
                        }
                    }
                }
            }
        ';

		/**
		 * Assertion One
		 *
		 * Query w/o filter
		 */
		$response = $this->graphql( compact( 'query' ) );
		$expected = [
			$this->expectedObject(
				'cart.contents.nodes.0',
				[
					$this->expectedField( 'key', $cart_item_key ),
					$this->expectedObject(
						'extraData.#',
						[
							$this->expectedField( 'key', 'meta_1' ),
							$this->expectedField( 'value', 'test_meta_1' ),
						]
					),
					$this->expectedObject(
						'extraData.#',
						[
							$this->expectedField( 'key', 'meta_2' ),
							$this->expectedField( 'value', 'test_meta_2' ),
						]
					),
				]
			),
		];

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Two
		 *
		 * Query w/ "key" filter
		 */
		$variables = [ 'key' => 'meta_2' ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedObject(
				'cart.contents.nodes.0',
				[
					$this->expectedField( 'key', $cart_item_key ),
					$this->expectedObject(
						'extraData.0',
						[
							$this->expectedField( 'key', 'meta_2' ),
							$this->expectedField( 'value', 'test_meta_2' ),
						]
					),
					$this->expectedObject(
						'extraData.#',
						[
							$this->not()->expectedField( 'key', 'meta_1' ),
							$this->not()->expectedField( 'value', 'test_meta_1' ),
						]
					),
				]
			),
		];

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Three
		 *
		 * Query w/ "keysIn" filter
		 */
		$variables = [ 'keysIn' => [ 'meta_2' ] ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedObject(
				'cart.contents.nodes.0',
				[
					$this->expectedField( 'key', $cart_item_key ),
					$this->expectedObject(
						'extraData.0',
						[
							$this->expectedField( 'key', 'meta_2' ),
							$this->expectedField( 'value', 'test_meta_2' ),
						]
					),
					$this->expectedObject(
						'extraData.#',
						[
							$this->not()->expectedField( 'key', 'meta_1' ),
							$this->not()->expectedField( 'value', 'test_meta_1' ),
						]
					),
				]
			),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testCouponMetaDataQueries() {
		// Create Coupon with meta data.
		$coupon_id = $this->factory->coupon->create(
			[
				'meta_data' => [
					[
						'id'    => 0,
						'key'   => 'meta_1',
						'value' => 'test_meta_1',
					],
					[
						'id'    => 0,
						'key'   => 'meta_2',
						'value' => 'test_meta_2',
					],
					[
						'id'    => 0,
						'key'   => 'meta_1',
						'value' => 75,
					],
				],
			]
		);
		$query     = '
            query ($id: ID!, $key: String, $keysIn: [String], $multiple: Boolean) {
                coupon(id: $id) {
                    id
                    metaData(key: $key, keysIn: $keysIn, multiple: $multiple) {
                        key
                        value
                    }
                }
            }
        ';

		/**
		 * Assertion One
		 *
		 * Query w/o filters
		 */
		wp_set_current_user( $this->shop_manager );
		$variables = [ 'id' => $this->toRelayId( 'shop_coupon', $coupon_id ) ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedObject(
				'coupon.metaData.#',
				[
					$this->expectedField( 'key', 'meta_1' ),
					$this->expectedField( 'value', 'test_meta_1' ),
					$this->not()->expectedField( 'value', 75 ),
				]
			),
			$this->expectedObject(
				'coupon.metaData.#',
				[
					$this->expectedField( 'key', 'meta_2' ),
					$this->expectedField( 'value', 'test_meta_2' ),
				]
			),
		];

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Two
		 *
		 * Query w/ "key" filter
		 */
		$variables = [
			'id'  => $this->toRelayId( 'shop_coupon', $coupon_id ),
			'key' => 'meta_2',
		];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedObject(
				'coupon.metaData.0',
				[
					$this->expectedField( 'key', 'meta_2' ),
					$this->expectedField( 'value', 'test_meta_2' ),
				]
			),
			$this->expectedObject(
				'coupon.metaData.#',
				[
					$this->not()->expectedField( 'key', 'meta_1' ),
					$this->not()->expectedField( 'value', 'test_meta_1' ),
					$this->not()->expectedField( 'value', '75' ),
				]
			),
		];

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Three
		 *
		 * Query w/ "keysIn" filter
		 */
		$variables = [
			'id'     => $this->toRelayId( 'shop_coupon', $coupon_id ),
			'keysIn' => [ 'meta_2' ],
		];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedObject(
				'coupon.metaData.0',
				[
					$this->expectedField( 'key', 'meta_2' ),
					$this->expectedField( 'value', 'test_meta_2' ),
				]
			),
			$this->expectedObject(
				'coupon.metaData.#',
				[
					$this->not()->expectedField( 'key', 'meta_1' ),
					$this->not()->expectedField( 'value', 'test_meta_1' ),
					$this->not()->expectedField( 'value', '75' ),
				]
			),
		];

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Four
		 *
		 * Query w/ "key" filter and "multiple" set to true to get non-unique results.
		 */
		$variables = [
			'id'       => $this->toRelayId( 'shop_coupon', $coupon_id ),
			'key'      => 'meta_1',
			'multiple' => true,
		];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedObject(
				'coupon.metaData.#',
				[
					$this->expectedField( 'key', 'meta_1' ),
					$this->expectedField( 'value', 'test_meta_1' ),
				]
			),
			$this->expectedObject(
				'coupon.metaData.#',
				[
					$this->expectedField( 'key', 'meta_1' ),
					$this->expectedField( 'value', '75' ),
				]
			),
			$this->expectedObject(
				'coupon.metaData.#',
				[
					$this->not()->expectedField( 'key', 'meta_2' ),
					$this->not()->expectedField( 'value', 'test_meta_2' ),
				]
			),
		];

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Five
		 *
		 * Query w/ "keysIn" filter and "multiple" set to true to get non-unique results.
		 */
		$variables = [
			'id'       => $this->toRelayId( 'shop_coupon', $coupon_id ),
			'keysIn'   => [ 'meta_1' ],
			'multiple' => true,
		];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedObject(
				'coupon.metaData.#',
				[
					$this->expectedField( 'key', 'meta_1' ),
					$this->expectedField( 'value', 'test_meta_1' ),
				]
			),
			$this->expectedObject(
				'coupon.metaData.#',
				[
					$this->expectedField( 'key', 'meta_1' ),
					$this->expectedField( 'value', '75' ),
				]
			),
			$this->expectedObject(
				'coupon.metaData.#',
				[
					$this->not()->expectedField( 'key', 'meta_2' ),
					$this->not()->expectedField( 'value', 'test_meta_2' ),
				]
			),
		];

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Six
		 *
		 * Query w/o filters and "multiple" set to true to get non-unique results.
		 */
		$variables = [
			'id'       => $this->toRelayId( 'shop_coupon', $coupon_id ),
			'multiple' => true,
		];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedObject(
				'coupon.metaData.#',
				[
					$this->expectedField( 'key', 'meta_1' ),
					$this->expectedField( 'value', 'test_meta_1' ),
				]
			),
			$this->expectedObject(
				'coupon.metaData.#',
				[
					$this->expectedField( 'key', 'meta_2' ),
					$this->expectedField( 'value', 'test_meta_2' ),
				]
			),
			$this->expectedObject(
				'coupon.metaData.#',
				[
					$this->expectedField( 'key', 'meta_1' ),
					$this->expectedField( 'value', '75' ),
				]
			),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testCustomerMetaDataQueries() {
		// Create Customer with meta data.
		$customer_id = $this->factory->customer->create(
			[
				'meta_data' => [
					[
						'id'    => 0,
						'key'   => 'meta_1',
						'value' => 'test_meta_1',
					],
					[
						'id'    => 0,
						'key'   => 'meta_2',
						'value' => 'test_meta_2',
					],
				],
			]
		);
		$query       = '
            query {
                customer {
                    id
                    metaData {
                        key
                        value
                    }
                }
            }
        ';

		/**
		 * Assertion One
		 */
		$this->loginAs( $customer_id );
		$response = $this->graphql( compact( 'query' ) );
		$expected = [
			$this->expectedField( 'customer.id', $this->toRelayId( 'customer', $customer_id ) ),
			$this->expectedObject(
				'customer.metaData.#',
				[
					$this->expectedField( 'key', 'meta_1' ),
					$this->expectedField( 'value', 'test_meta_1' ),
				]
			),
			$this->expectedObject(
				'customer.metaData.#',
				[
					$this->expectedField( 'key', 'meta_2' ),
					$this->expectedField( 'value', 'test_meta_2' ),
				]
			),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testOrderMetaDataQueries() {
		// Create Order with meta data.
		$meta_data   = [
			[
				'id'    => 0,
				'key'   => 'meta_1',
				'value' => 'test_meta_1',
			],
			[
				'id'    => 0,
				'key'   => 'meta_2',
				'value' => 'test_meta_2',
			],
		];
		$customer_id = $this->factory->customer->create( [ 'meta_data' => $meta_data ] );
		$order_id    = $this->factory->order->createNew(
			[
				'customer_id' => $customer_id,
				'meta_data'   => $meta_data,
			]
		);
		$this->factory->order->add_fee( $order_id, compact( 'meta_data' ) );
		$query = '
            query ($id: ID!) {
                order(id: $id) {
                    id
                    metaData {
                        key
                        value
                    }
                    feeLines {
                        nodes {
                            metaData {
                                key
                                value
                            }
                        }
                    }
                }
            }
        ';

		$this->loginAsShopManager();

		/**
		 * Assertion One
		 */
		$variables = [ 'id' => $this->toRelayId( 'order', $order_id ) ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedField( 'order.id', $this->toRelayId( 'order', $order_id ) ),
			$this->expectedObject(
				'order.metaData.#',
				[
					$this->expectedField( 'key', 'meta_1' ),
					$this->expectedField( 'value', 'test_meta_1' ),
				]
			),
			$this->expectedObject(
				'order.metaData.#',
				[
					$this->expectedField( 'key', 'meta_2' ),
					$this->expectedField( 'value', 'test_meta_2' ),
				]
			),
			$this->expectedObject(
				'order.feeLines.nodes.0.metaData.#',
				[
					$this->expectedField( 'key', 'meta_1' ),
					$this->expectedField( 'value', 'test_meta_1' ),
				]
			),
			$this->expectedObject(
				'order.feeLines.nodes.0.metaData.#',
				[
					$this->expectedField( 'key', 'meta_2' ),
					$this->expectedField( 'value', 'test_meta_2' ),
				]
			),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testProductMetaDataQueries() {
		// Create Product with meta data.
		$meta_data  = [
			[
				'id'    => 0,
				'key'   => 'meta_1',
				'value' => 'test_meta_1',
			],
			[
				'id'    => 0,
				'key'   => 'meta_2',
				'value' => 'test_meta_2',
			],
		];
		$product_id = $this->factory->product->createVariable( compact( 'meta_data' ) );
		$query      = '
            query ($id: ID!) {
                product(id: $id) {
                    ... on VariableProduct {
                        id
                        metaData {
                            key
                            value
                        }
                    }
                }
            }
        ';

		/**
		 * Assertion One
		 */
		$variables = [ 'id' => $this->toRelayId( 'product', $product_id ) ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedField( 'product.id', $this->toRelayId( 'product', $product_id ) ),
			$this->expectedObject(
				'product.metaData.#',
				[
					$this->expectedField( 'key', 'meta_1' ),
					$this->expectedField( 'value', 'test_meta_1' ),
				]
			),
			$this->expectedObject(
				'product.metaData.#',
				[
					$this->expectedField( 'key', 'meta_2' ),
					$this->expectedField( 'value', 'test_meta_2' ),
				]
			),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testProductVariationMetaDataQueries() {
		// Create Product with meta data.
		$meta_data    = [
			[
				'id'    => 0,
				'key'   => 'meta_1',
				'value' => 'test_meta_1',
			],
			[
				'id'    => 0,
				'key'   => 'meta_2',
				'value' => 'test_meta_2',
			],
		];
		$product_id   = $this->factory->product->createVariable( compact( 'meta_data' ) );
		$product_ids  = $this->factory->product_variation->createSome( $product_id, compact( 'meta_data' ) );
		$variation_id = $product_ids['variations'][0];
		$query        = '
            query ($id: ID!) {
                productVariation(id: $id) {
                    id
                    metaData {
                        key
                        value
                    }
                }
            }
        ';

		/**
		 * Assertion One
		 */
		$variables = [ 'id' => $this->toRelayId( 'product_variation', $variation_id ) ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedField( 'productVariation.id', $this->toRelayId( 'product_variation', $variation_id ) ),
			$this->expectedObject(
				'productVariation.metaData.#',
				[
					$this->expectedField( 'key', 'meta_1' ),
					$this->expectedField( 'value', 'test_meta_1' ),
				]
			),
			$this->expectedObject(
				'productVariation.metaData.#',
				[
					$this->expectedField( 'key', 'meta_2' ),
					$this->expectedField( 'value', 'test_meta_2' ),
				]
			),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testRefundMetaDataQueries() {
		$meta_data   = [
			[
				'id'    => 0,
				'key'   => 'meta_1',
				'value' => 'test_meta_1',
			],
			[
				'id'    => 0,
				'key'   => 'meta_2',
				'value' => 'test_meta_2',
			],
		];
		$customer_id = $this->factory->customer->create( [ 'meta_data' => $meta_data ] );
		$order_id    = $this->factory->order->createNew(
			[
				'customer_id' => $customer_id,
				'meta_data'   => $meta_data,
			]
		);
		$refund_id   = $this->factory->refund->createNew( $order_id, compact( 'meta_data' ) );
		$query       = '
			query refundQuery( $id: ID! ) {
				refund( id: $id ) {
					id
					metaData {
                        key
                        value
                    }
				}
			}
        ';

		/**
		 * Assertion One
		 */
		$this->loginAs( $customer_id );
		$variables = [ 'id' => $this->toRelayId( 'order', $refund_id ) ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedField( 'refund.id', $this->toRelayId( 'order', $refund_id ) ),
			$this->expectedObject(
				'refund.metaData.#',
				[
					$this->expectedField( 'key', 'meta_1' ),
					$this->expectedField( 'value', 'test_meta_1' ),
				]
			),
			$this->expectedObject(
				'refund.metaData.#',
				[
					$this->expectedField( 'key', 'meta_2' ),
					$this->expectedField( 'value', 'test_meta_2' ),
				]
			),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}
}
