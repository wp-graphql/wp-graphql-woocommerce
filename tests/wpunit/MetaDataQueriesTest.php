<?php


class MetaDataQueriesTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {
	// tests
	public function testCartMetaDataQueries() {
		// Create Variation Product.
		$product_ids = $this->factory->product_variation->createSome();
		// Create Cart Item with meta data.
		$meta_data = array(
			'meta_1' => 'test_meta_1',
			'meta_2' => 'test_meta_2',
		);

		// Add item to cart.
		$cart_item_key = $this->factory->cart->add(
			array(
				'product_id'     => $product_ids['product'],
				'quantity'       => 2,
				'variation_id'   => $product_ids['variations'][0],
				'variation'      => array( 'attribute_pa_color' => 'red' ),
				'cart_item_data' => $meta_data,
			)
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
		$expected = array(
			$this->expectedObject(
				'cart.contents.nodes.0',
				array(
					$this->expectedField( 'key', $cart_item_key ),
					$this->expectedObject(
						'extraData.#',
						array(
							$this->expectedField( 'key', 'meta_1' ),
							$this->expectedField( 'value', 'test_meta_1' ),
						)
					),
					$this->expectedObject(
						'extraData.#',
						array(
							$this->expectedField( 'key', 'meta_2' ),
							$this->expectedField( 'value', 'test_meta_2' ),
						)
					),
				)
			),
		);

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Two
		 *
		 * Query w/ "key" filter
		 */
		$variables = array( 'key' => 'meta_2' );
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array(
			$this->expectedObject(
				'cart.contents.nodes.0',
				array(
					$this->expectedField( 'key', $cart_item_key ),
					$this->expectedObject(
						'extraData.0',
						array(
							$this->expectedField( 'key', 'meta_2' ),
							$this->expectedField( 'value', 'test_meta_2' ),
						)
					),
					$this->expectedObject(
						'extraData.#',
						array(
							$this->not()->expectedField( 'key', 'meta_1' ),
							$this->not()->expectedField( 'value', 'test_meta_1' ),
						)
					),
				)
			),
		);

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Three
		 *
		 * Query w/ "keysIn" filter
		 */
		$variables = array( 'keysIn' => array( 'meta_2' ) );
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array(
			$this->expectedObject(
				'cart.contents.nodes.0',
				array(
					$this->expectedField( 'key', $cart_item_key ),
					$this->expectedObject(
						'extraData.0',
						array(
							$this->expectedField( 'key', 'meta_2' ),
							$this->expectedField( 'value', 'test_meta_2' ),
						)
					),
					$this->expectedObject(
						'extraData.#',
						array(
							$this->not()->expectedField( 'key', 'meta_1' ),
							$this->not()->expectedField( 'value', 'test_meta_1' ),
						)
					),
				)
			),
		);

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testCouponMetaDataQueries() {
		// Create Coupon with meta data.
		$coupon_id = $this->factory->coupon->create(
			array(
				'meta_data' => array(
					array(
						'id'    => 0,
						'key'   => 'meta_1',
						'value' => 'test_meta_1',
					),
					array(
						'id'    => 0,
						'key'   => 'meta_2',
						'value' => 'test_meta_2',
					),
					array(
						'id'    => 0,
						'key'   => 'meta_1',
						'value' => 75,
					),
				),
			)
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
		$variables = array( 'id' => $this->toRelayId( 'shop_coupon', $coupon_id ) );
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array(
			$this->expectedObject(
				'coupon.metaData.#',
				array(
					$this->expectedField( 'key', 'meta_1' ),
					$this->expectedField( 'value', 'test_meta_1' ),
					$this->not()->expectedField( 'value', 75 ),
				)
			),
			$this->expectedObject(
				'coupon.metaData.#',
				array(
					$this->expectedField( 'key', 'meta_2' ),
					$this->expectedField( 'value', 'test_meta_2' ),
				)
			),
		);

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Two
		 *
		 * Query w/ "key" filter
		 */
		$variables = array(
			'id'  => $this->toRelayId( 'shop_coupon', $coupon_id ),
			'key' => 'meta_2',
		);
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array(
			$this->expectedObject(
				'coupon.metaData.0',
				array(
					$this->expectedField( 'key', 'meta_2' ),
					$this->expectedField( 'value', 'test_meta_2' ),
				)
			),
			$this->expectedObject(
				'coupon.metaData.#',
				array(
					$this->not()->expectedField( 'key', 'meta_1' ),
					$this->not()->expectedField( 'value', 'test_meta_1' ),
					$this->not()->expectedField( 'value', '75' ),
				)
			),
		);

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Three
		 *
		 * Query w/ "keysIn" filter
		 */
		$variables = array(
			'id'     => $this->toRelayId( 'shop_coupon', $coupon_id ),
			'keysIn' => array( 'meta_2' ),
		);
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array(
			$this->expectedObject(
				'coupon.metaData.0',
				array(
					$this->expectedField( 'key', 'meta_2' ),
					$this->expectedField( 'value', 'test_meta_2' ),
				)
			),
			$this->expectedObject(
				'coupon.metaData.#',
				array(
					$this->not()->expectedField( 'key', 'meta_1' ),
					$this->not()->expectedField( 'value', 'test_meta_1' ),
					$this->not()->expectedField( 'value', '75' ),
				)
			),
		);

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Four
		 *
		 * Query w/ "key" filter and "multiple" set to true to get non-unique results.
		 */
		$variables = array(
			'id'       => $this->toRelayId( 'shop_coupon', $coupon_id ),
			'key'      => 'meta_1',
			'multiple' => true,
		);
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array(
			$this->expectedObject(
				'coupon.metaData.#',
				array(
					$this->expectedField( 'key', 'meta_1' ),
					$this->expectedField( 'value', 'test_meta_1' ),
				)
			),
			$this->expectedObject(
				'coupon.metaData.#',
				array(
					$this->expectedField( 'key', 'meta_1' ),
					$this->expectedField( 'value', '75' ),
				)
			),
			$this->expectedObject(
				'coupon.metaData.#',
				array(
					$this->not()->expectedField( 'key', 'meta_2' ),
					$this->not()->expectedField( 'value', 'test_meta_2' ),
				)
			),
		);

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Five
		 *
		 * Query w/ "keysIn" filter and "multiple" set to true to get non-unique results.
		 */
		$variables = array(
			'id'       => $this->toRelayId( 'shop_coupon', $coupon_id ),
			'keysIn'   => array( 'meta_1' ),
			'multiple' => true,
		);
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array(
			$this->expectedObject(
				'coupon.metaData.#',
				array(
					$this->expectedField( 'key', 'meta_1' ),
					$this->expectedField( 'value', 'test_meta_1' ),
				)
			),
			$this->expectedObject(
				'coupon.metaData.#',
				array(
					$this->expectedField( 'key', 'meta_1' ),
					$this->expectedField( 'value', '75' ),
				)
			),
			$this->expectedObject(
				'coupon.metaData.#',
				array(
					$this->not()->expectedField( 'key', 'meta_2' ),
					$this->not()->expectedField( 'value', 'test_meta_2' ),
				)
			),
		);

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Six
		 *
		 * Query w/o filters and "multiple" set to true to get non-unique results.
		 */
		$variables = array(
			'id'       => $this->toRelayId( 'shop_coupon', $coupon_id ),
			'multiple' => true,
		);
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array(
			$this->expectedObject(
				'coupon.metaData.#',
				array(
					$this->expectedField( 'key', 'meta_1' ),
					$this->expectedField( 'value', 'test_meta_1' ),
				)
			),
			$this->expectedObject(
				'coupon.metaData.#',
				array(
					$this->expectedField( 'key', 'meta_2' ),
					$this->expectedField( 'value', 'test_meta_2' ),
				)
			),
			$this->expectedObject(
				'coupon.metaData.#',
				array(
					$this->expectedField( 'key', 'meta_1' ),
					$this->expectedField( 'value', '75' ),
				)
			),
		);

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testCustomerMetaDataQueries() {
		// Create Customer with meta data.
		$customer_id = $this->factory->customer->create(
			array(
				'meta_data' => array(
					array(
						'id'    => 0,
						'key'   => 'meta_1',
						'value' => 'test_meta_1',
					),
					array(
						'id'    => 0,
						'key'   => 'meta_2',
						'value' => 'test_meta_2',
					),
				),
			)
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
		$expected = array(
			$this->expectedField( 'customer.id', $this->toRelayId( 'user', $customer_id ) ),
			$this->expectedObject(
				'customer.metaData.#',
				array(
					$this->expectedField( 'key', 'meta_1' ),
					$this->expectedField( 'value', 'test_meta_1' ),
				)
			),
			$this->expectedObject(
				'customer.metaData.#',
				array(
					$this->expectedField( 'key', 'meta_2' ),
					$this->expectedField( 'value', 'test_meta_2' ),
				)
			),
		);

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testOrderMetaDataQueries() {
		// Create Order with meta data.
		$meta_data   = array(
			array(
				'id'    => 0,
				'key'   => 'meta_1',
				'value' => 'test_meta_1',
			),
			array(
				'id'    => 0,
				'key'   => 'meta_2',
				'value' => 'test_meta_2',
			),
		);
		$customer_id = $this->factory->customer->create( array( 'meta_data' => $meta_data ) );
		$order_id    = $this->factory->order->createNew(
			array(
				'customer_id' => $customer_id,
				'meta_data'   => $meta_data,
			)
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
		$variables = array( 'id' => $this->toRelayId( 'order', $order_id ) );
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array(
			$this->expectedField( 'order.id', $this->toRelayId( 'order', $order_id ) ),
			$this->expectedObject(
				'order.metaData.#',
				array(
					$this->expectedField( 'key', 'meta_1' ),
					$this->expectedField( 'value', 'test_meta_1' ),
				)
			),
			$this->expectedObject(
				'order.metaData.#',
				array(
					$this->expectedField( 'key', 'meta_2' ),
					$this->expectedField( 'value', 'test_meta_2' ),
				)
			),
			$this->expectedObject(
				'order.feeLines.nodes.0.metaData.#',
				array(
					$this->expectedField( 'key', 'meta_1' ),
					$this->expectedField( 'value', 'test_meta_1' ),
				)
			),
			$this->expectedObject(
				'order.feeLines.nodes.0.metaData.#',
				array(
					$this->expectedField( 'key', 'meta_2' ),
					$this->expectedField( 'value', 'test_meta_2' ),
				)
			),
		);

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testProductMetaDataQueries() {
		// Create Product with meta data.
		$meta_data  = array(
			array(
				'id'    => 0,
				'key'   => 'meta_1',
				'value' => 'test_meta_1',
			),
			array(
				'id'    => 0,
				'key'   => 'meta_2',
				'value' => 'test_meta_2',
			),
		);
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
		$variables = array( 'id' => $this->toRelayId( 'post', $product_id ) );
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array(
			$this->expectedField( 'product.id', $this->toRelayId( 'post', $product_id ) ),
			$this->expectedObject(
				'product.metaData.#',
				array(
					$this->expectedField( 'key', 'meta_1' ),
					$this->expectedField( 'value', 'test_meta_1' ),
				)
			),
			$this->expectedObject(
				'product.metaData.#',
				array(
					$this->expectedField( 'key', 'meta_2' ),
					$this->expectedField( 'value', 'test_meta_2' ),
				)
			),
		);

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testProductVariationMetaDataQueries() {
		// Create Product with meta data.
		$meta_data    = array(
			array(
				'id'    => 0,
				'key'   => 'meta_1',
				'value' => 'test_meta_1',
			),
			array(
				'id'    => 0,
				'key'   => 'meta_2',
				'value' => 'test_meta_2',
			),
		);
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
		$variables = array( 'id' => $this->toRelayId( 'post', $variation_id ) );
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array(
			$this->expectedField( 'productVariation.id', $this->toRelayId( 'post', $variation_id ) ),
			$this->expectedObject(
				'productVariation.metaData.#',
				array(
					$this->expectedField( 'key', 'meta_1' ),
					$this->expectedField( 'value', 'test_meta_1' ),
				)
			),
			$this->expectedObject(
				'productVariation.metaData.#',
				array(
					$this->expectedField( 'key', 'meta_2' ),
					$this->expectedField( 'value', 'test_meta_2' ),
				)
			),
		);

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testRefundMetaDataQueries() {
		$meta_data   = array(
			array(
				'id'    => 0,
				'key'   => 'meta_1',
				'value' => 'test_meta_1',
			),
			array(
				'id'    => 0,
				'key'   => 'meta_2',
				'value' => 'test_meta_2',
			),
		);
		$customer_id = $this->factory->customer->create( array( 'meta_data' => $meta_data ) );
		$order_id    = $this->factory->order->createNew(
			array(
				'customer_id' => $customer_id,
				'meta_data'   => $meta_data,
			)
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
		$variables = array( 'id' => $this->toRelayId( 'order', $refund_id ) );
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array(
			$this->expectedField( 'refund.id', $this->toRelayId( 'order', $refund_id ) ),
			$this->expectedObject(
				'refund.metaData.#',
				array(
					$this->expectedField( 'key', 'meta_1' ),
					$this->expectedField( 'value', 'test_meta_1' ),
				)
			),
			$this->expectedObject(
				'refund.metaData.#',
				array(
					$this->expectedField( 'key', 'meta_2' ),
					$this->expectedField( 'value', 'test_meta_2' ),
				)
			),
		);

		$this->assertQuerySuccessful( $response, $expected );
	}
}
