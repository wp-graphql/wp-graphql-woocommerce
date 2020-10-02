<?php

use GraphQLRelay\Relay;

class MetaDataQueriesTest extends \Codeception\TestCase\WPTestCase {

    public function setUp() {
        // before
        parent::setUp();

        // Create users.
        $this->shop_manager = $this->factory->user->create( array( 'role' => 'shop_manager' ) );
        $this->customer     = $this->factory->user->create( array( 'role' => 'customer' ) );

        // Assign helpers.
        $this->cart         = $this->getModule('\Helper\Wpunit')->cart();
        $this->coupons      = $this->getModule('\Helper\Wpunit')->coupon();
        $this->customers    = $this->getModule('\Helper\Wpunit')->customer();
        $this->orders       = $this->getModule('\Helper\Wpunit')->order();
        $this->order_items  = $this->getModule('\Helper\Wpunit')->item();
        $this->products     = $this->getModule('\Helper\Wpunit')->product();
        $this->refunds      = $this->getModule('\Helper\Wpunit')->refund();
        $this->variations   = $this->getModule('\Helper\Wpunit')->product_variation();

        // Create test objects.
        $this->createObjects();
    }

    public function tearDown() {
        // Clear cart.
		WC()->cart->empty_cart( true );

        // then
        parent::tearDown();
    }

    public function set_user( $user ) {
		wp_set_current_user( $user );
		WC()->customer = new WC_Customer( get_current_user_id(), true );
	}

    private function createObjects() {
        $data = array(
            'meta_data' => array(
                array(
                    'id'    => 0,
                    'key'   => 'meta_1',
                    'value' => 'test_meta_1'
                ),
                array(
                    'id'    => 0,
                    'key'   => 'meta_2',
                    'value' => 'test_meta_2'
                ),
                array(
                    'id'    => 0,
                    'key'   => 'meta_1',
                    'value' => 'test_meta_3'
                ),
            ),
        );

        // Create Coupon with meta data.
        $this->coupon_id   = $this->coupons->create( $data );

        // Create Customer with meta data.
        $this->customer_id = $this->customers->create( $data );

        // Create Order and Refund with meta data.
        $this->order_id = $this->orders->create( array_merge( $data, array( 'customer_id' => $this->customer ) ) );
        $this->order_items->add_fee( $this->order_id, $data );
        $this->refund_id = $this->refunds->create( $this->order_id, $data );

        // Create Products with meta data.
        $this->product_id    = $this->products->create_variable( $data );
        $this->variation_ids = $this->variations->create( $this->product_id, $data );

        // Add Cart Item with extra data.
        $cart_meta_data = array(
            'meta_1' => 'test_meta_1',
            'meta_2' => 'test_meta_2'
		);

		// Clear cart.
		WC()->cart->empty_cart( true );

		// Add item to cart.
		$this->cart_item_key = $this->cart->add(
			array(
				'product_id'      => $this->variation_ids['product'],
				'quantity'        => 2,
				'variation_id'    => $this->variation_ids['variations'][0],
				'variation'       => array( 'attribute_pa_color' => 'red' ),
				'cart_item_data'  => $cart_meta_data
			)
		)[0];
    }

    // tests
    public function testCartMetaDataQueries() {
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
         * query w/o filter
         */
        $actual   = graphql( array( 'query' => $query ) );
        $expected = array(
            'data' => array(
                'cart' => array(
                    'contents' => array(
                        'nodes' => array(
                            array(
                                'key'      => $this->cart_item_key,
                                'extraData' => array(
                                    array(
                                        'key'   => 'meta_1',
                                        'value' => 'test_meta_1',
                                    ),
                                    array(
                                        'key'   => 'meta_2',
                                        'value' => 'test_meta_2',
                                    ),
                                ),
                            )
                        )
                    )
                )
            ),
        );

        // use --debug flag to view.
        codecept_debug( $actual );

        $this->assertEquals( $expected, $actual );

        /**
         * Assertion Two
         *
         * query w/ "key" filter
         */
        $variables = array( 'key' => 'meta_2' );
        $actual    = graphql( array( 'query' => $query, 'variables' => $variables ) );
        $expected  = array(
            'data' => array(
                'cart' => array(
                    'contents' => array(
                        'nodes' => array(
                            array(
                                'key'      => $this->cart_item_key,
                                'extraData' => array(
                                    array(
                                        'key'   => 'meta_2',
                                        'value' => 'test_meta_2',
                                    ),
                                ),
                            )
                        )
                    )
                )
            ),
        );

        // use --debug flag to view.
        codecept_debug( $actual );

        $this->assertEquals( $expected, $actual );

        /**
         * Assertion Three
         *
         * query w/ "keysIn" filter
         */
        $variables = array( 'keysIn' => array( 'meta_2' ) );
        $actual    = graphql( array( 'query' => $query, 'variables' => $variables ) );
        $expected  = array(
            'data' => array(
                'cart' => array(
                    'contents' => array(
                        'nodes' => array(
                            array(
                                'key'      => $this->cart_item_key,
                                'extraData' => array(
                                    array(
                                        'key'   => 'meta_2',
                                        'value' => 'test_meta_2',
                                    ),
                                ),
                            )
                        )
                    )
                )
            ),
        );

        // use --debug flag to view.
        codecept_debug( $actual );

        $this->assertEquals( $expected, $actual );
    }

    public function testCouponMetaDataQueries() {
        $id    = Relay::toGlobalId( 'shop_coupon', $this->coupon_id );
        $query = '
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
         * query w/o filters
         */
        wp_set_current_user( $this->shop_manager );
        $variables = array( 'id' => $id );
        $actual    = graphql(
            array(
                'query'     => $query,
                'variables' => $variables,
            )
        );
        $expected = array(
            'data' => array(
                'coupon' => array(
                    'id' => $id,
                    'metaData' => array(
                        array(
                            'key'   => 'meta_1',
                            'value' => 'test_meta_1',
                        ),
                        array(
                            'key'   => 'meta_2',
                            'value' => 'test_meta_2',
                        ),
                    )
                ),
            ),
        );

        // use --debug flag to view.
        codecept_debug( $actual );

        $this->assertEquals( $expected, $actual );

        /**
         * Assertion Two
         *
         * query w/ "key" filter
         */
        $variables = array( 'id' => $id, 'key' => 'meta_2' );
        $actual    = graphql( array( 'query' => $query, 'variables' => $variables ) );
        $expected = array(
            'data' => array(
                'coupon' => array(
                    'id' => $id,
                    'metaData' => array(
                        array(
                            'key'   => 'meta_2',
                            'value' => 'test_meta_2',
                        ),
                    )
                ),
            ),
        );

        // use --debug flag to view.
        codecept_debug( $actual );

        $this->assertEquals( $expected, $actual );

        /**
         * Assertion Three
         *
         * query w/ "keysIn" filter
         */
        $variables = array( 'id' => $id, 'keysIn' => array( 'meta_2' ) );
        $actual    = graphql( array( 'query' => $query, 'variables' => $variables ) );
        $expected = array(
            'data' => array(
                'coupon' => array(
                    'id' => $id,
                    'metaData' => array(
                        array(
                            'key'   => 'meta_2',
                            'value' => 'test_meta_2',
                        ),
                    )
                ),
            ),
        );

        // use --debug flag to view.
        codecept_debug( $actual );

        $this->assertEquals( $expected, $actual );

        /**
         * Assertion Four
         *
         * query w/ "key" filter and "multiple" set to true to get non-unique results.
         */
        $variables = array( 'id' => $id, 'key' => 'meta_1', 'multiple' => true );
        $actual    = graphql( array( 'query' => $query, 'variables' => $variables ) );
        $expected = array(
            'data' => array(
                'coupon' => array(
                    'id' => $id,
                    'metaData' => array(
                        array(
                            'key'   => 'meta_1',
                            'value' => 'test_meta_1',
                        ),
                        array(
                            'key'   => 'meta_1',
                            'value' => 'test_meta_3',
                        ),
                    )
                ),
            ),
        );

        // use --debug flag to view.
        codecept_debug( $actual );

        $this->assertEquals( $expected, $actual );

        /**
         * Assertion Five
         *
         * query w/ "keysIn" filter and "multiple" set to true to get non-unique results.
         */
        $variables = array( 'id' => $id, 'keysIn' => array( 'meta_1' ), 'multiple' => true );
        $actual    = graphql( array( 'query' => $query, 'variables' => $variables ) );
        $expected = array(
            'data' => array(
                'coupon' => array(
                    'id' => $id,
                    'metaData' => array(
                        array(
                            'key'   => 'meta_1',
                            'value' => 'test_meta_1',
                        ),
                        array(
                            'key'   => 'meta_1',
                            'value' => 'test_meta_3',
                        ),
                    )
                ),
            ),
        );

        // use --debug flag to view.
        codecept_debug( $actual );

        $this->assertEquals( $expected, $actual );

        /**
         * Assertion Six
         *
         * query w/o filters and "multiple" set to true to get non-unique results.
         */
        $variables = array( 'id' => $id, 'multiple' => true );
        $actual    = graphql( array( 'query' => $query, 'variables' => $variables ) );
        $expected = array(
            'data' => array(
                'coupon' => array(
                    'id' => $id,
                    'metaData' => array(
                        array(
                            'key'   => 'meta_1',
                            'value' => 'test_meta_1',
                        ),
                        array(
                            'key'   => 'meta_2',
                            'value' => 'test_meta_2',
                        ),
                        array(
                            'key'   => 'meta_1',
                            'value' => 'test_meta_3',
                        ),
                    )
                ),
            ),
        );

        // use --debug flag to view.
        codecept_debug( $actual );

        $this->assertEquals( $expected, $actual );
    }

    public function testCustomerMetaDataQueries() {
        $query = '
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
        $this->set_user( $this->customer_id );
        $actual   = graphql( array( 'query' => $query ) );
        $expected = array(
            'data' => array(
                'customer' => array(
                    'id' => Relay::toGlobalId( 'customer', $this->customer_id ),
                    'metaData' => array(
                        array(
                            'key'   => 'meta_1',
                            'value' => 'test_meta_1',
                        ),
                        array(
                            'key'   => 'meta_2',
                            'value' => 'test_meta_2',
                        ),
                    )
                ),
            ),
        );

        // use --debug flag to view.
        codecept_debug( $actual );

        $this->assertEquals( $expected, $actual );
    }

    public function testOrderMetaDataQueries() {
        $id    = Relay::toGlobalId( 'shop_order', $this->order_id );
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

        // Must be an "shop_manager" or "admin" to query orders not owned by the user.
        wp_set_current_user( $this->shop_manager );

        /**
         * Assertion One
         */
        $variables = array( 'id' => $id );
        $actual    = graphql(
            array(
                'query'     => $query,
                'variables' => $variables,
            )
        );
        $expected = array(
            'data' => array(
                'order' => array(
                    'id'          => $id,
                    'metaData'    => array(
                        array(
                            'key'   => 'meta_1',
                            'value' => 'test_meta_1',
                        ),
                        array(
                            'key'   => 'meta_2',
                            'value' => 'test_meta_2',
                        ),
                    ),
                    'feeLines' => array(
                        'nodes' => array(
                            array(
                                'metaData' => array(
                                    array(
                                        'key'   => 'meta_1',
                                        'value' => 'test_meta_1',
                                    ),
                                    array(
                                        'key'   => 'meta_2',
                                        'value' => 'test_meta_2',
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        );

        // use --debug flag to view.
        codecept_debug( $actual );

        $this->assertEquals( $expected, $actual );
    }

    public function testProductMetaDataQueries() {
        $id    = Relay::toGlobalId( 'product', $this->product_id );
        $query = '
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
        $variables = array( 'id' => $id );
        $actual    = graphql(
            array(
                'query'     => $query,
                'variables' => $variables,
            )
        );
        $expected = array(
            'data' => array(
                'product' => array(
                    'id' => $id,
                    'metaData' => array(
                        array(
                            'key'   => 'meta_1',
                            'value' => 'test_meta_1',
                        ),
                        array(
                            'key'   => 'meta_2',
                            'value' => 'test_meta_2',
                        ),
                    )
                ),
            ),
        );

        // use --debug flag to view.
        codecept_debug( $actual );

        $this->assertEquals( $expected, $actual );
    }

    public function testProductVariationMetaDataQueries() {
        $id    = Relay::toGlobalId( 'product_variation', $this->variation_ids['variations'][0] );
        $query = '
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
        $variables = array( 'id' => $id );
        $actual    = graphql(
            array(
                'query'     => $query,
                'variables' => $variables,
            )
        );
        $expected = array(
            'data' => array(
                'productVariation' => array(
                    'id' => $id,
                    'metaData' => array(
                        array(
                            'key'   => 'meta_1',
                            'value' => 'test_meta_1',
                        ),
                        array(
                            'key'   => 'meta_2',
                            'value' => 'test_meta_2',
                        ),
                    )
                ),
            ),
        );

        // use --debug flag to view.
        codecept_debug( $actual );

        $this->assertEquals( $expected, $actual );
    }

    public function testRefundMetaDataQueries() {
        $id    = Relay::toGlobalId( 'shop_order_refund', $this->refund_id );
		$query = '
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
        wp_set_current_user( $this->customer );
        $variables = array( 'id' => $id );
        $actual    = graphql(
            array(
                'query'     => $query,
                'variables' => $variables,
            )
        );
        $expected = array(
            'data' => array(
                'refund' => array(
                    'id'          => $id,
                    'metaData'    => array(
                        array(
                            'key'   => 'meta_1',
                            'value' => 'test_meta_1',
                        ),
                        array(
                            'key'   => 'meta_2',
                            'value' => 'test_meta_2',
                        ),
                    ),
                ),
            ),
        );

        // use --debug flag to view.
        codecept_debug( $actual );

        $this->assertEquals( $expected, $actual );
    }
}
