<?php

class ConnectionPaginationTest extends \Codeception\TestCase\WPTestCase {
    private $shop_manager;
    private $simple_customer;

    private $coupons;
    private $orders;
    private $products;
    private $refunds;

    public function setUp() {
        // before
        parent::setUp();

        // Create users.
        $this->shop_manager = $this->factory->user->create( array( 'role' => 'shop_manager' ) );

        // Setup helpers.
        $this->coupons   = $this->getModule('\Helper\Wpunit')->coupon();
        $this->orders    = $this->getModule('\Helper\Wpunit')->order();
        $this->products  = $this->getModule('\Helper\Wpunit')->product();
        $this->refunds   = $this->getModule('\Helper\Wpunit')->refund();
        $this->customers = $this->getModule('\Helper\Wpunit')->customer();
    }

    public function tearDown() {
        // your tear down methods here

        // then
        parent::tearDown();
    }

    public function set_user( $user ) {
		wp_set_current_user( $user );
		WC()->customer = new WC_Customer( get_current_user_id(), true );
	}

    // tests
    public function testCouponsPagination() {
		$coupons = array(
            $this->coupons->create(),
			$this->coupons->create(),
			$this->coupons->create(),
            $this->coupons->create(),
			$this->coupons->create(),
        );

        usort(
            $coupons,
            function( $key_a, $key_b ) {
				return $key_a < $key_b;
			}
        );

		$query = '
			query ($first: Int, $last: Int, $after: String, $before: String) {
				coupons(first: $first, last: $last, after: $after, before: $before) {
					nodes {
						id
                    }
                    pageInfo {
                        hasPreviousPage
                        hasNextPage
                        startCursor
                        endCursor
                    }
                }
			}
        ';

        $this->set_user( $this->shop_manager );

        /**
		 * Assertion One
		 *
		 * Test "first" parameter.
		 */
        $variables = array( 'first' => 2 );
		$results   = graphql(
            array(
                'query'     => $query,
                'variables' => $variables,
            )
        );

        // use --debug flag to view.
		codecept_debug( $results );

        // Check pageInfo.
        $this->assertNotEmpty( $results['data'] );
        $this->assertNotEmpty( $results['data']['coupons'] );
        $this->assertNotEmpty( $results['data']['coupons']['pageInfo'] );
        $this->assertTrue( $results['data']['coupons']['pageInfo']['hasNextPage'] );
        $this->assertNotEmpty( $results['data']['coupons']['pageInfo']['endCursor'] );
        $end_cursor = $results['data']['coupons']['pageInfo']['endCursor'];

        // Check coupons.
        $actual   = $results['data']['coupons']['nodes'];
		$expected = $this->coupons->print_nodes( array_slice( $coupons, 0, 2 ) );

        $this->assertEquals( $expected, $actual );

        /**
		 * Assertion Two
		 *
		 * Test "after" parameter.
		 */
        $variables = array( 'first' => 3, 'after' => $end_cursor );
		$results    = graphql(
            array(
                'query'     => $query,
                'variables' => $variables,
            )
        );

        // use --debug flag to view.
		codecept_debug( $results );

        // Check pageInfo.
        $this->assertNotEmpty( $results['data'] );
        $this->assertNotEmpty( $results['data']['coupons'] );
        $this->assertNotEmpty( $results['data']['coupons']['pageInfo'] );
        $this->assertFalse( $results['data']['coupons']['pageInfo']['hasNextPage'] );
        $this->assertNotEmpty( $results['data']['coupons']['pageInfo']['endCursor'] );

        // Check coupons.
        $actual   = $results['data']['coupons']['nodes'];
        $expected = $this->coupons->print_nodes( array_slice( $coupons, 2, 3 ) );

        $this->assertEquals( $expected, $actual );
    }

    public function testProductsPagination() {
        $products = array(
            $this->products->create_simple(),
            $this->products->create_simple(),
            $this->products->create_simple(),
            $this->products->create_simple(),
            $this->products->create_simple(),
        );

        usort(
            $products,
            function( $key_a, $key_b ) {
				return $key_a < $key_b;
			}
        );

		$query = '
			query ($first: Int, $last: Int, $after: String, $before: String) {
				products(first: $first, last: $last, after: $after, before: $before) {
					nodes {
						id
                    }
                    pageInfo {
                        hasPreviousPage
                        hasNextPage
                        startCursor
                        endCursor
                    }
                }
			}
        ';

        /**
		 * Assertion One
		 *
		 * Test "first" parameter.
		 */
        $variables = array( 'first' => 2 );
		$results   = graphql(
            array(
                'query'     => $query,
                'variables' => $variables,
            )
        );

        // use --debug flag to view.
		codecept_debug( $results );

        // Check pageInfo.
        $this->assertNotEmpty( $results['data'] );
        $this->assertNotEmpty( $results['data']['products'] );
        $this->assertNotEmpty( $results['data']['products']['pageInfo'] );
        $this->assertTrue( $results['data']['products']['pageInfo']['hasNextPage'] );
        $this->assertNotEmpty( $results['data']['products']['pageInfo']['endCursor'] );
        $end_cursor = $results['data']['products']['pageInfo']['endCursor'];

        // Check products.
        $actual   = $results['data']['products']['nodes'];
		$expected = $this->products->print_nodes( array_slice( $products, 0, 2 ) );

        $this->assertEquals( $expected, $actual );

        /**
		 * Assertion Two
		 *
		 * Test "after" parameter.
		 */
        $variables = array( 'first' => 3, 'after' => $end_cursor );
		$results    = graphql(
            array(
                'query'     => $query,
                'variables' => $variables,
            )
        );

        // use --debug flag to view.
		codecept_debug( $results );

        // Check pageInfo.
        $this->assertNotEmpty( $results['data'] );
        $this->assertNotEmpty( $results['data']['products'] );
        $this->assertNotEmpty( $results['data']['products']['pageInfo'] );
        $this->assertFalse( $results['data']['products']['pageInfo']['hasNextPage'] );
        $this->assertNotEmpty( $results['data']['products']['pageInfo']['endCursor'] );

        // Check coupons.
        $actual   = $results['data']['products']['nodes'];
        $expected = $this->products->print_nodes( array_slice( $products, 2, 3 ) );

        $this->assertEquals( $expected, $actual );
    }

    public function testOrdersPagination() {
		wp_set_current_user( $this->shop_manager );

		$query      = new \WC_Order_Query();
		$old_orders = $query->get_orders();
		foreach ( $old_orders as $order ) {
			$this->orders->delete_order( $order );
		}
		unset( $old_orders );
		unset( $query );

        $orders = array(
            $this->orders->create(),
			$this->orders->create(),
			$this->orders->create(),
            $this->orders->create(),
			$this->orders->create(),
        );

        usort(
            $orders,
            function( $key_a, $key_b ) {
				return $key_a < $key_b;
			}
        );

		$query = '
			query ($first: Int, $last: Int, $after: String, $before: String) {
				orders(first: $first, last: $last, after: $after, before: $before) {
					nodes {
						id
                    }
                    pageInfo {
                        hasPreviousPage
                        hasNextPage
                        startCursor
                        endCursor
                    }
                }
			}
        ';

        /**
		 * Assertion One
		 *
		 * Test "first" parameter.
		 */
        $variables = array( 'first' => 2 );
		$results   = graphql(
            array(
                'query'     => $query,
                'variables' => $variables,
            )
        );

        // use --debug flag to view.
		codecept_debug( $results );

        // Check pageInfo.
        $this->assertNotEmpty( $results['data'] );
        $this->assertNotEmpty( $results['data']['orders'] );
        $this->assertNotEmpty( $results['data']['orders']['pageInfo'] );
        $this->assertTrue( $results['data']['orders']['pageInfo']['hasNextPage'] );
        $this->assertNotEmpty( $results['data']['orders']['pageInfo']['endCursor'] );
        $end_cursor = $results['data']['orders']['pageInfo']['endCursor'];

        // Check orders.
        $actual   = $results['data']['orders']['nodes'];
		$expected = $this->orders->print_nodes( array_slice( $orders, 0, 2 ) );

        $this->assertEquals( $expected, $actual );

        /**
		 * Assertion Two
		 *
		 * Test "after" parameter.
		 */
        $variables = array( 'first' => 3, 'after' => $end_cursor );
		$results    = graphql(
            array(
                'query'     => $query,
                'variables' => $variables,
            )
        );

        // use --debug flag to view.
		codecept_debug( $results );

        // Check pageInfo.
        $this->assertNotEmpty( $results['data'] );
        $this->assertNotEmpty( $results['data']['orders'] );
        $this->assertNotEmpty( $results['data']['orders']['pageInfo'] );
        $this->assertFalse( $results['data']['orders']['pageInfo']['hasNextPage'] );
        $this->assertNotEmpty( $results['data']['orders']['pageInfo']['endCursor'] );

        // Check orders.
        $actual   = $results['data']['orders']['nodes'];
        $expected = $this->orders->print_nodes( array_slice( $orders, 2, 3 ) );

        $this->assertEquals( $expected, $actual );
    }

    public function testRefundsPagination() {
        $order   = $this->orders->create();
        $refunds = array(
            $this->refunds->create( $order, array( 'amount' => 0.5 ) ),
			$this->refunds->create( $order, array( 'amount' => 0.5 ) ),
			$this->refunds->create( $order, array( 'amount' => 0.5 ) ),
            $this->refunds->create( $order, array( 'amount' => 0.5 ) ),
			$this->refunds->create( $order, array( 'amount' => 0.5 ) ),
        );

        usort(
            $refunds,
            function( $key_a, $key_b ) {
				return $key_a < $key_b;
			}
        );

		$query = '
			query ($first: Int, $last: Int, $after: String, $before: String) {
				refunds(first: $first, last: $last, after: $after, before: $before) {
					nodes {
						id
                    }
                    pageInfo {
                        hasPreviousPage
                        hasNextPage
                        startCursor
                        endCursor
                    }
                }
			}
        ';

        wp_set_current_user( $this->shop_manager );

        /**
		 * Assertion One
		 *
		 * Test "first" parameter.
		 */
        $variables = array( 'first' => 2 );
		$results   = graphql(
            array(
                'query'     => $query,
                'variables' => $variables,
            )
        );

        // use --debug flag to view.
		codecept_debug( $results );

        // Check pageInfo.
        $this->assertNotEmpty( $results['data'] );
        $this->assertNotEmpty( $results['data']['refunds'] );
        $this->assertNotEmpty( $results['data']['refunds']['pageInfo'] );
        $this->assertTrue( $results['data']['refunds']['pageInfo']['hasNextPage'] );
        $this->assertNotEmpty( $results['data']['refunds']['pageInfo']['endCursor'] );
        $end_cursor = $results['data']['refunds']['pageInfo']['endCursor'];

        // Check refunds.
        $actual   = $results['data']['refunds']['nodes'];
		$expected = $this->refunds->print_nodes( array_slice( $refunds, 0, 2 ) );

        $this->assertEquals( $expected, $actual );

        /**
		 * Assertion Two
		 *
		 * Test "after" parameter.
		 */
        $variables = array( 'first' => 3, 'after' => $end_cursor );
		$results    = graphql(
            array(
                'query'     => $query,
                'variables' => $variables,
            )
        );

        // use --debug flag to view.
		codecept_debug( $results );

        // Check pageInfo.
        $this->assertNotEmpty( $results['data'] );
        $this->assertNotEmpty( $results['data']['refunds'] );
        $this->assertNotEmpty( $results['data']['refunds']['pageInfo'] );
        $this->assertFalse( $results['data']['refunds']['pageInfo']['hasNextPage'] );
        $this->assertNotEmpty( $results['data']['refunds']['pageInfo']['endCursor'] );

        // Check refunds.
        $actual   = $results['data']['refunds']['nodes'];
        $expected = $this->refunds->print_nodes( array_slice( $refunds, 2, 3 ) );

        $this->assertEquals( $expected, $actual );
    }

    public function testCustomersPagination() {
        $customers = array(
            $this->customers->create(),
            $this->customers->create(),
            $this->customers->create(),
            $this->customers->create(),
            $this->customers->create(),
        );

        usort(
            $customers,
            function( $key_a, $key_b ) {
                return $key_a < $key_b;
            }
        );

        $query = '
            query ($first: Int, $last: Int, $after: String, $before: String) {
                customers(first: $first, last: $last, after: $after, before: $before) {
                    nodes {
                        id
                    }
                    pageInfo {
                        hasPreviousPage
                        hasNextPage
                        startCursor
                        endCursor
                    }
                }
            }
        ';

        wp_set_current_user( $this->shop_manager );

        /**
         * Assertion One
         *
         * Test "first" parameter.
         */
        $variables = array( 'first' => 2 );
        $results   = graphql(
            array(
                'query'     => $query,
                'variables' => $variables,
            )
        );

        // use --debug flag to view.
        codecept_debug( $results );

        // Check pageInfo.
        $this->assertNotEmpty( $results['data'] );
        $this->assertNotEmpty( $results['data']['customers'] );
        $this->assertNotEmpty( $results['data']['customers']['pageInfo'] );
        $this->assertTrue( $results['data']['customers']['pageInfo']['hasNextPage'] );
        $this->assertNotEmpty( $results['data']['customers']['pageInfo']['endCursor'] );
        $end_cursor = $results['data']['customers']['pageInfo']['endCursor'];

        // Check customers.
        $actual   = $results['data']['customers']['nodes'];
        $expected = $this->customers->print_nodes( array_slice( $customers, 0, 2 ) );

        $this->assertEquals( $expected, $actual );

        /**
         * Assertion Two
         *
         * Test "after" parameter.
         */
        $variables = array( 'first' => 3, 'after' => $end_cursor );
        $results    = graphql(
            array(
                'query'     => $query,
                'variables' => $variables,
            )
        );

        // use --debug flag to view.
        codecept_debug( $results );

        // Check pageInfo.
        $this->assertNotEmpty( $results['data'] );
        $this->assertNotEmpty( $results['data']['customers'] );
        $this->assertNotEmpty( $results['data']['customers']['pageInfo'] );
        $this->assertFalse( $results['data']['customers']['pageInfo']['hasNextPage'] );
        $this->assertNotEmpty( $results['data']['customers']['pageInfo']['endCursor'] );

        // Check customers.
        $actual   = $results['data']['customers']['nodes'];
        $expected = $this->customers->print_nodes( array_slice( $customers, 2, 3 ) );

        $this->assertEquals( $expected, $actual );
    }

    public function testDownloadableItemsPagination() {
        $customer_id = $this->customers->create();
        $downloable_products = array(
            $this->products->create_simple(
                array(
                    'downloadable' => true,
                    'downloads'    => array( $this->products->create_download() )
                )
            ),
            $this->products->create_simple(
                array(
                    'downloadable' => true,
                    'downloads'    => array( $this->products->create_download() )
                )
            ),
            $this->products->create_simple(
                array(
                    'downloadable' => true,
                    'downloads'    => array( $this->products->create_download() )
                )
            ),
            $this->products->create_simple(
                array(
                    'downloadable' => true,
                    'downloads'    => array( $this->products->create_download() )
                )
            ),
            $this->products->create_simple(
                array(
                    'downloadable' => true,
                    'downloads'    => array( $this->products->create_download() )
                )
            ),
        );

        $order_id = $this->orders->create(
            array(
                'status'      => 'completed',
                'customer_id' => $customer_id,
            ),
            array(
                'line_items' => array_map(
                    function( $product_id ) {
                        return array(
                            'product' => $product_id,
                            'qty'     => 1,
                        );
                    },
                    $downloable_products
                ),
            )
        );

        // Force download permission updated.
        wc_downloadable_product_permissions( $order_id, true );

        $query = '
            query ($first: Int, $last: Int, $after: String, $before: String) {
                customer {
                    orders {
                        nodes {
                            downloadableItems(first: $first, last: $last, after: $after, before: $before) {
                                nodes {
                                    product {
                                        databaseId
                                    }
                                }
                                pageInfo {
                                    hasPreviousPage
                                    hasNextPage
                                    startCursor
                                    endCursor
                                }
                            }
                        }

                    }
                }
            }
        ';

        $this->set_user( $customer_id );

        /**
         * Assertion One
         *
         * Test "first" parameter.
         */
        $variables = array( 'first' => 2 );
        $results   = graphql(
            array(
                'query'     => $query,
                'variables' => $variables,
            )
        );

        // use --debug flag to view.
        codecept_debug( $results );

        // Check pageInfo.
        $this->assertNotEmpty( $results['data'] );
        $this->assertNotEmpty( $results['data']['customer'] );
        $this->assertNotEmpty( $results['data']['customer']['orders'] );
        $this->assertNotEmpty( $results['data']['customer']['orders']['nodes'] );
        $this->assertNotEmpty( $results['data']['customer']['orders']['nodes'][0] );
        $this->assertNotEmpty( $results['data']['customer']['orders']['nodes'][0]['downloadableItems'] );
        $this->assertNotEmpty( $results['data']['customer']['orders']['nodes'][0]['downloadableItems']['pageInfo'] );
        $this->assertTrue( $results['data']['customer']['orders']['nodes'][0]['downloadableItems']['pageInfo']['hasNextPage'] );
        $this->assertNotEmpty( $results['data']['customer']['orders']['nodes'][0]['downloadableItems']['pageInfo']['endCursor'] );
        $end_cursor = $results['data']['customer']['orders']['nodes'][0]['downloadableItems']['pageInfo']['endCursor'];

        // Check downloadable items.
        $actual   = $results['data']['customer']['orders']['nodes'][0]['downloadableItems']['nodes'];
        $expected = array_map(
            function( $product_id ) {
                return array( 'product' => array( 'databaseId' => $product_id ) );
            },
            array_slice( $downloable_products, 0, 2 )
        );

        $this->assertEquals( $expected, $actual );

        /**
         * Assertion Two
         *
         * Test "after" parameter.
         */
        $variables = array( 'first' => 3, 'after' => $end_cursor );
        $results    = graphql(
            array(
                'query'     => $query,
                'variables' => $variables,
            )
        );

        // use --debug flag to view.
        codecept_debug( $results );

        // Check pageInfo.
        $this->assertNotEmpty( $results['data'] );
        $this->assertNotEmpty( $results['data']['customer'] );
        $this->assertNotEmpty( $results['data']['customer']['orders'] );
        $this->assertNotEmpty( $results['data']['customer']['orders']['nodes'] );
        $this->assertNotEmpty( $results['data']['customer']['orders']['nodes'][0] );
        $this->assertNotEmpty( $results['data']['customer']['orders']['nodes'][0]['downloadableItems'] );
        $this->assertNotEmpty( $results['data']['customer']['orders']['nodes'][0]['downloadableItems']['pageInfo'] );
        $this->assertFalse( $results['data']['customer']['orders']['nodes'][0]['downloadableItems']['pageInfo']['hasNextPage'] );
        $this->assertNotEmpty( $results['data']['customer']['orders']['nodes'][0]['downloadableItems']['pageInfo']['endCursor'] );

        // Check downloadable items.
        $actual   = $results['data']['customer']['orders']['nodes'][0]['downloadableItems']['nodes'];
        $expected = array_map(
            function( $product_id ) {
                return array( 'product' => array( 'databaseId' => $product_id ) );
            },
            array_slice( $downloable_products, 2, 3 )
        );

        $this->assertEquals( $expected, $actual );
    }
}
