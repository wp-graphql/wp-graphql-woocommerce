<?php

class CartQueriesTest extends \Codeception\TestCase\WPTestCase {
	private $shop_manager;
	private $customer;
	private $product_helper;
	private $variation_helper;
	private $coupon_helper;
	private $helper;

	public function setUp() {
		// before
		parent::setUp();

		$this->shop_manager     = $this->factory->user->create( array( 'role' => 'shop_manager' ) );
		$this->customer         = $this->factory->user->create( array( 'role' => 'customer' ) );
		$this->product_helper   = $this->getModule('\Helper\Wpunit')->product();
		$this->variation_helper = $this->getModule('\Helper\Wpunit')->product_variation();
		$this->coupon_helper    = $this->getModule('\Helper\Wpunit')->coupon();
		$this->helper           = $this->getModule('\Helper\Wpunit')->cart();
		$this->tax              = $this->getModule('\Helper\Wpunit')->tax_rate();

        // Turn on tax calculations. Important!
        update_option( 'woocommerce_prices_include_tax', 'no' );
		update_option( 'woocommerce_calc_taxes', 'yes' );
		update_option( 'woocommerce_tax_round_at_subtotal', 'no' );

        // Create a tax rate.
        $this->tax->create(
            array(
                'country'  => '',
                'state'    => '',
                'rate'     => 20.000,
                'name'     => 'VAT',
                'priority' => '1',
                'compound' => '0',
                'shipping' => '1',
                'class'    => ''
            )
        );
	}

	public function tearDown() {
		WC()->cart->empty_cart( true );

		// then
		parent::tearDown();
	}

	private function key_to_cursor( $key ) {
		return base64_encode( "CI:{$key}" );
	}

	// tests
	public function testCartQuery() {
		$cart = WC()->cart;
		$cart->add_to_cart( $this->product_helper->create_simple(), 2 );
		$cart->add_to_cart( $this->product_helper->create_simple(), 1 );

		$query = '
			query {
				cart {
					subtotal
					subtotalTax
					discountTotal
					discountTax
					shippingTotal
					shippingTax
					contentsTotal
					contentsTax
					feeTotal
					feeTax
					total
					totalTax
					isEmpty
					displayPricesIncludeTax
					needsShippingAddress
					totalTaxes {
						id
						isCompound
						amount
						label
					}
				}
			}
		';

		/**
		 * Assertion One
		 */
		$actual    = graphql( array( 'query' => $query ) );
		$expected  = array( 'data' => array( 'cart' => $this->helper->print_query() ) );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );
	}

	public function testCartItemQuery() {
		$cart = WC()->cart;
		$variations = $this->variation_helper->create( $this->product_helper->create_variable() );
		$key = $cart->add_to_cart(
			$variations['product'],
			3,
			$variations['variations'][0],
			array( 'attribute_pa_color' => 'red' )
		);

		$query = '
			query ($key: ID!) {
				cartItem(key: $key) {
					key
					product {
						node {
							id
							databaseId
						}
					}
					variation {
						attributes {
							id
							attributeId
							name
							label
							value
						}
						node {
							id
							databaseId
						}
					}
					quantity
					subtotal
					subtotalTax
					total
					tax
				}
			}
		';

		/**
		 * Assertion One
		 */
		$variables = array( 'key' => $key );
		$actual    = graphql( array( 'query' => $query, 'variables' => $variables ) );
		$expected  = array( 'data' => array( 'cartItem' => $this->helper->print_item_query( $key ) ) );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );
	}

	public function testCartItemConnection() {
		$cart = WC()->cart;
		$cart->add_to_cart( $this->product_helper->create_simple( array( 'virtual' => true ) ), 2 );
		$cart->add_to_cart( $this->product_helper->create_simple(), 1 );
		$cart->add_to_cart( $this->product_helper->create_simple(), 10 );

		$code = \wc_get_coupon_code_by_id(
			$this->coupon_helper->create(
				array(
					'amount'        => 45.50,
					'discount_type' => 'fixed_cart',
				)
			)
		);
		$cart->apply_coupon( $code );

		$query = '
			query($needsShipping: Boolean) {
				cart {
					contents (where: {needsShipping: $needsShipping}) {
						nodes {
							key
						}
					}
				}
			}
		';

		/**
		 * Assertion One
		 */
		$actual    = graphql( array( 'query' => $query ) );
		$expected  = array(
			'data' => array(
				'cart' => array(
					'contents' => array(
						'nodes' => $this->helper->print_nodes(),
					),
				),
			),
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );

		/**
		 * Assertion Two
		 *
		 * Tests "needsShipping" parameter.
		 */
		$variables = array( 'needsShipping' => true );
		$actual    = graphql(
			array(
				'query'     => $query,
				'variables' => $variables,
			)
		);
		$expected  = array(
			'data' => array(
				'cart' => array(
					'contents' => array(
						'nodes' => $this->helper->print_nodes(
							array(
								'filter' => function( $key ) {
									$item = WC()->cart->get_cart_item( $key );
									$product = WC()->product_factory->get_product( $item['product_id'] );
									return $product->needs_shipping();
								}
							)
						),
					),
				),
			),
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );

		/**
		 * Assertion Three
		 *
		 * Tests "needsShipping" parameter reversed.
		 */
		$variables = array( 'needsShipping' => false );
		$actual    = graphql(
			array(
				'query'     => $query,
				'variables' => $variables,
			)
		);
		$expected  = array(
			'data' => array(
				'cart' => array(
					'contents' => array(
						'nodes' => $this->helper->print_nodes(
							array(
								'filter' => function( $key ) {
									$item = WC()->cart->get_cart_item( $key );
									$product = WC()->product_factory->get_product( $item['product_id'] );
									return ! $product->needs_shipping();
								}
							)
						),
					),
				),
			),
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );
	}

	public function testCartFeeQuery() {
		$product_id = $this->product_helper->create_simple();
		WC()->cart->add_to_cart( $product_id, 3 );
		WC()->cart->add_fee( 'Test fee', 30.50 );
		$fee_ids = array_keys( WC()->cart->get_fees() );

		$query   = '
			query ($id: ID!) {
				cartFee(id: $id) {
					id
					name
					taxClass
					taxable
					amount
					total
				}
			}
		';

		/**
		 * Assertion One
		 */
		$variables = array( 'id' => $fee_ids[0] );
		$actual    = graphql( array( 'query' => $query, 'variables' => $variables ) );
		$expected  = array( 'data' => array( 'cartFee' => $this->helper->print_fee_query( $fee_ids[0] ) ) );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );
	}

	public function testCartToCartFeeQuery() {
		$product_id = $this->product_helper->create_simple();
		WC()->cart->add_to_cart( $product_id, 3 );
		WC()->cart->add_fee( 'Test fee', 30.50 );

		$query = '
			query {
				cart {
					fees {
						id
					}
				}
			}
		';

		/**
		 * Assertion One
		 */
		$actual    = graphql( array( 'query' => $query ) );
		$expected  = array(
			'data' => array(
				'cart' => array(
					'fees' => $this->helper->print_fee_nodes(),
				),
			),
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );
	}

	public function testCartItemPagination() {
		$cart = WC()->cart;
		$cart_items = array(
			$cart->add_to_cart( $this->product_helper->create_simple(), 2 ),
			$cart->add_to_cart( $this->product_helper->create_simple(), 1 ),
			$cart->add_to_cart( $this->product_helper->create_simple(), 1 ),
			$cart->add_to_cart( $this->product_helper->create_simple(), 1 ),
			$cart->add_to_cart( $this->product_helper->create_simple(), 1 ),
		);

		codecept_debug( $cart_items );

		$query = '
			query ($first: Int, $last: Int, $before: String, $after: String) {
				cart {
					contents(first: $first, last: $last, before: $before, after: $after) {
					  	itemCount
					  	productCount
					  	edges {
							cursor
							node {
						  		key
							}
						}
					}
				}
			}
		';

		/**
		 * Assertion One
		 *
		 * Tests "first" parameter.
		 */
		$variables = array( 'first' => 2 );
		$actual    = graphql(
			array(
				'query' => $query,
				'variables' => $variables,
			)
		);
		$expected  = array(
			'data' => array(
				'cart' => array(
					'contents' => array(
						'itemCount'    => 6,
						'productCount' => 5,
						'edges' => array_map(
							function( $item ) {
								return array(
									'cursor' => $this->key_to_cursor( $item ),
									'node'   => array( 'key' => $item ),
								);
							},
							array_slice( $cart_items, 0, 2 )
						),
					),
				),
			),
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );

		/**
		 * Assertion Two
		 *
		 * Tests "after" parameter.
		 */
		$variables = array(
			'first' => 2,
			'after' => $this->key_to_cursor( $cart_items[1] )
		);
		$actual    = graphql(
			array(
				'query' => $query,
				'variables' => $variables,
			)
		);
		$expected  = array(
			'data' => array(
				'cart' => array(
					'contents' => array(
						'itemCount'    => 6,
						'productCount' => 5,
						'edges' => array_map(
							function( $item ) {
								return array(
									'cursor' => $this->key_to_cursor( $item ),
									'node'   => array( 'key' => $item ),
								);
							},
							array_slice( $cart_items, 2, 2 )
						),
					),
				),
			),
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );

		/**
		 * Assertion Three
		 *
		 * Tests "last" parameter.
		 */
		$variables = array( 'last' => 2 );
		$actual    = graphql(
			array(
				'query' => $query,
				'variables' => $variables,
			)
		);
		$expected  = array(
			'data' => array(
				'cart' => array(
					'contents' => array(
						'itemCount'    => 6,
						'productCount' => 5,
						'edges' => array_map(
							function( $item ) {
								return array(
									'cursor' => $this->key_to_cursor( $item ),
									'node'   => array( 'key' => $item ),
								);
							},
							array_slice( $cart_items, 0, 2 )
						),
					),
				),
			),
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );

		/**
		 * Assertion Four
		 *
		 * Tests "before" parameter.
		 */
		$variables = array( 'last' => 4, 'before' => $cart_items[4] );
		$actual    = graphql(
			array(
				'query' => $query,
				'variables' => $variables,
			)
		);
		$expected  = array(
			'data' => array(
				'cart' => array(
					'contents' => array(
						'itemCount'    => 6,
						'productCount' => 5,
						'edges' => array_map(
							function( $item ) {
								return array(
									'cursor' => $this->key_to_cursor( $item ),
									'node'   => array( 'key' => $item ),
								);
							},
							array_slice( $cart_items, 0, 4 )
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
