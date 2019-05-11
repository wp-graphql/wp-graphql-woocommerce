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
	}

	public function tearDown()
	{
		// your tear down methods here

		// then
		parent::tearDown();
	}

	// tests
	public function testCartQuery() {
		$cart = WC()->cart;
		$cart->add_to_cart( $this->product_helper->create_simple(), 2 );
		$cart->add_to_cart( $this->product_helper->create_simple(), 1 );

		$query = '
			query cartQuery {
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
				}
			}
		';

		/**
		 * Assertion One
		 */
		$actual    = do_graphql_request( $query, 'cartQuery' );
		$expected  = array( 'data' => array( 'cart' => $this->helper->print_query() ) );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEqualSets( $expected, $actual );
	}

	public function testCartItemQuery() {
		$cart = WC()->cart;
		$variations = $this->variation_helper->create( $this->product_helper->create_variable() );
		$key = $cart->add_to_cart(
			$variations['product'],
			3,
			$variations['variations'][0]
		);

		$query = '
			query cartItemQuery( $key: ID! ) {
				cartItem( key: $key ) {
					key
					product {
						id
						productId
					}
					variation {
						id
						variationId
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
		$actual    = do_graphql_request( $query, 'cartItemQuery', $variables );
		$expected  = array( 'data' => array( 'cartItem' => $this->helper->print_item_query( $key ) ) );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEqualSets( $expected, $actual );
	}

	public function testCartItemConnection() {
		$cart = WC()->cart;
		$cart->add_to_cart( $this->product_helper->create_simple(), 2 );
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
			query cartItemConnection {
				cart {
					contents {
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
		$actual    = do_graphql_request( $query, 'cartItemConnection' );
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

		$this->assertEqualSets( $expected, $actual );
	}

	public function testCartFeeQuery() {
		$product_id = $this->product_helper->create_simple();
		WC()->cart->add_to_cart( $product_id, 3 );
		WC()->cart->add_fee( 'Test fee', 30.50 );
		$fee_ids = array_keys( WC()->cart->get_fees() );

		$query = '
			query cartFeeQuery( $id: ID! ) {
				cartFee( id: $id ) {
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
		$actual    = do_graphql_request( $query, 'cartFeeQuery', $variables );
		$expected  = array( 'data' => array( 'cartFee' => $this->helper->print_fee_query( $fee_ids[0] ) ) );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEqualSets( $expected, $actual );
	}

	public function testCartToCartFeeQuery() {
		$product_id = $this->product_helper->create_simple();
		WC()->cart->add_to_cart( $product_id, 3 );
		WC()->cart->add_fee( 'Test fee', 30.50 );

		$query = '
			query cartQuery {
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
		$actual    = do_graphql_request( $query, 'cartFeeQuery' );
		$expected  = array(
			'data' => array(
				'cart' => array(
					'fees' => $this->helper->print_fee_nodes(),
				),
			),
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEqualSets( $expected, $actual );
	}
}