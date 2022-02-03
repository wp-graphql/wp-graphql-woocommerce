<?php

class CartQueriesTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {
	private function key_to_cursor( $key ) {
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		return base64_encode( 'arrayconnection:' . $key );
	}

	public function getExpectedCartData() {
		$cart = WC()->cart;
		return array(
			$this->expectedField( 'cart.subtotal', \wc_graphql_price( $cart->get_subtotal() ) ),
			$this->expectedField( 'cart.subtotalTax', \wc_graphql_price( $cart->get_subtotal_tax() ) ),
			$this->expectedField( 'cart.discountTotal', \wc_graphql_price( $cart->get_discount_total() ) ),
			$this->expectedField( 'cart.discountTax', \wc_graphql_price( $cart->get_discount_tax() ) ),
			$this->expectedField( 'cart.shippingTotal', \wc_graphql_price( $cart->get_shipping_total() ) ),
			$this->expectedField( 'cart.shippingTax', \wc_graphql_price( $cart->get_shipping_tax() ) ),
			$this->expectedField( 'cart.contentsTotal', \wc_graphql_price( $cart->get_cart_contents_total() ) ),
			$this->expectedField( 'cart.contentsTax', \wc_graphql_price( $cart->get_cart_contents_tax() ) ),
			$this->expectedField( 'cart.feeTotal', \wc_graphql_price( $cart->get_fee_total() ) ),
			$this->expectedField( 'cart.feeTax', \wc_graphql_price( $cart->get_fee_tax() ) ),
			$this->expectedField( 'cart.total', \wc_graphql_price( $cart->get_totals()['total'] ) ),
			$this->expectedField( 'cart.totalTax', \wc_graphql_price( $cart->get_total_tax() ) ),
			$this->expectedField( 'cart.isEmpty', $cart->is_empty() ),
			$this->expectedField( 'cart.displayPricesIncludeTax', $cart->display_prices_including_tax() ),
			$this->expectedField( 'cart.needsShippingAddress', $cart->needs_shipping_address() ),
		);
	}

	public function getExpectedCartItemData( $path, $cart_item_key ) {
		$cart = WC()->cart;
		$item = $cart->get_cart_item( $cart_item_key );
		return array(
			$this->expectedObject(
				$path,
				array(
					$this->expectedField( 'key', $item['key'] ),
					$this->expectedField( 'product.node.id', $this->toRelayId( 'product', $item['product_id'] ) ),
					$this->expectedField( 'product.node.databaseId', $item['product_id'] ),
					$this->expectedField(
						'variation.node.id',
						! empty( $item['variation_id'] )
							? $this->toRelayId( 'product_variation', $item['variation_id'] )
							: 'NULL'
					),
					$this->expectedField(
						'variation.node.databaseId',
						! empty( $item['variation_id'] ) ? $item['variation_id'] : 'NULL'
					),
					$this->expectedField( 'quantity', $item['quantity'] ),
					$this->expectedField( 'subtotal', \wc_graphql_price( $item['line_subtotal'] ) ),
					$this->expectedField( 'subtotalTax', \wc_graphql_price( $item['line_subtotal_tax'] ) ),
					$this->expectedField( 'total', \wc_graphql_price( $item['line_total'] ) ),
					$this->expectedField( 'tax', \wc_graphql_price( $item['line_tax'] ) ),
				)
			),
		);
	}

	// tests
	public function testCartQuery() {
		$cart = WC()->cart;
		$this->factory->cart->add(
			array(
				'product_id' => $this->factory->product->createSimple(),
				'quantity'   => 2,
			),
			array(
				'product_id' => $this->factory->product->createSimple(),
				'quantity'   => 1,
			)
		);

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
		$response = $this->graphql( compact( 'query' ) );

		$this->assertQuerySuccessful( $response, $this->getExpectedCartData() );
	}

	public function testCartItemQuery() {
		$cart       = \WC()->cart;
		$variations = $this->factory->product_variation->createSome();

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
		$response  = $this->graphql( compact( 'query', 'variables' ) );

		$this->assertQuerySuccessful( $response, $this->getExpectedCartItemData( 'cartItem', $key ) );
	}

	public function testCartItemConnection() {
		$keys = $this->factory->cart->add(
			array(
				'product_id' => $this->factory->product->createSimple(
					array( 'virtual' => true )
				),
				'quantity'   => 2,
			),
			array(
				'product_id' => $this->factory->product->createSimple(),
				'quantity'   => 1,
			),
			array(
				'product_id' => $this->factory->product->createSimple(),
				'quantity'   => 10,
			)
		);

		$code = \wc_get_coupon_code_by_id(
			$this->factory->coupon->create(
				array(
					'amount'        => 45.50,
					'discount_type' => 'fixed_cart',
				)
			)
		);
		$cart = \WC()->cart;
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

		$response = $this->graphql( compact( 'query' ) );

		$expected = array();
		foreach ( $keys as $key ) {
			$expected[] = $this->expectedNode( 'cart.contents.nodes', array( 'key' => $key ) );
		}

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Two
		 *
		 * Tests "needsShipping" parameter.
		 */
		$variables = array( 'needsShipping' => true );
		$response  = $this->graphql( compact( 'query', 'variables' ) );

		$this->assertQuerySuccessful(
			$response,
			array(
				$this->expectedNode( 'cart.contents.nodes', array( 'key' => $keys[1] ) ),
				$this->expectedNode( 'cart.contents.nodes', array( 'key' => $keys[2] ) ),
			)
		);

		/**
		 * Assertion Three
		 *
		 * Tests "needsShipping" parameter reversed.
		 */
		$variables = array( 'needsShipping' => false );
		$response  = $this->graphql( compact( 'query', 'variables' ) );

		$this->assertQuerySuccessful(
			$response,
			array(
				$this->expectedNode( 'cart.contents.nodes', array( 'key' => $keys[0] ) ),
			)
		);
	}

	public function testCartFeeQuery() {
		$product_id = $this->factory->product->createSimple();
		WC()->cart->add_to_cart( $product_id, 3 );
		WC()->cart->add_fee( 'Test fee', 30.50 );
		$fee_ids = array_keys( WC()->cart->get_fees() );

		$query = '
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
		$response  = $this->graphql( compact( 'query', 'variables' ) );

		$fee = ( \WC()->cart->get_fees() )[ $fee_ids[0] ];

		$this->assertQuerySuccessful(
			$response,
			array(
				$this->expectedField( 'cartFee.id', $fee->id ),
				$this->expectedField( 'cartFee.name', $fee->name ),
				$this->expectedField( 'cartFee.taxClass', $this->maybe( $fee->tax_class ) ),
				$this->expectedField( 'cartFee.taxable', $fee->taxable ),
				$this->expectedField( 'cartFee.amount', (float) $fee->amount ),
				$this->expectedField( 'cartFee.total', (float) $fee->total ),
			)
		);
	}

	public function testCartToCartFeeQuery() {
		$product_id = $this->factory->product->createSimple();
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
		$response = $this->graphql( compact( 'query' ) );

		$expected = array();
		foreach ( \WC()->cart->get_fees() as $fee_id => $value ) {
			$expected[] = $this->expectedNode( 'cart.fees', array( 'id' => $fee_id ) );
		}

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testCartItemPagination() {
		$cart_items = $this->factory->cart->add(
			array(
				'product_id' => $this->factory->product->createSimple(),
				'quantity'   => 2,
			),
			array(
				'product_id' => $this->factory->product->createSimple(),
				'quantity'   => 1,
			),
			array(
				'product_id' => $this->factory->product->createSimple(),
				'quantity'   => 1,
			),
			array(
				'product_id' => $this->factory->product->createSimple(),
				'quantity'   => 1,
			),
			array(
				'product_id' => $this->factory->product->createSimple(),
				'quantity'   => 1,
			)
		);

		$query = '
			query ($first: Int, $last: Int, $before: String, $after: String) {
				cart {
					contents(first: $first, last: $last, before: $before, after: $after) {
					  	itemCount
					  	productCount
						pageInfo {
							hasNextPage
							hasPreviousPage
						}
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
		$variables = array(
			'first' => 2,
			'after' => '',
		);
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array(
			$this->expectedField( 'cart.contents.itemCount', 6 ),
			$this->expectedField( 'cart.contents.productCount', 5 ),
			$this->expectedField( 'cart.contents.pageInfo.hasNextPage', true ),
			$this->expectedField( 'cart.contents.pageInfo.hasPreviousPage', false ),
			$this->expectedField( 'cart.contents.edges.0.cursor', $this->key_to_cursor( $cart_items[0] ) ),
			$this->expectedField( 'cart.contents.edges.1.cursor', $this->key_to_cursor( $cart_items[1] ) ),
			$this->expectedEdge(
				'cart.contents.edges',
				array( $this->expectedField( 'key', $cart_items[0] ) ),
				0
			),
			$this->expectedEdge(
				'cart.contents.edges',
				array( $this->expectedField( 'key', $cart_items[1] ) ),
				1
			),
		);

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Two
		 *
		 * Tests "after" parameter.
		 */
		$variables = array(
			'first' => 2,
			'after' => $this->key_to_cursor( $cart_items[1] ),
		);
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array(
			$this->expectedField( 'cart.contents.pageInfo.hasNextPage', true ),
			$this->expectedField( 'cart.contents.pageInfo.hasPreviousPage', true ),
			$this->expectedField( 'cart.contents.edges.0.cursor', $this->key_to_cursor( $cart_items[2] ) ),
			$this->expectedField( 'cart.contents.edges.1.cursor', $this->key_to_cursor( $cart_items[3] ) ),
			$this->expectedEdge(
				'cart.contents.edges',
				array( $this->expectedField( 'key', $cart_items[2] ) ),
				0
			),
			$this->expectedEdge(
				'cart.contents.edges',
				array( $this->expectedField( 'key', $cart_items[3] ) ),
				1
			),
		);

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Three
		 *
		 * Tests "last" parameter.
		 */
		$variables = array( 'last' => 2 );
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array(
			$this->expectedField( 'cart.contents.pageInfo.hasNextPage', false ),
			$this->expectedField( 'cart.contents.pageInfo.hasPreviousPage', true ),
			$this->expectedField( 'cart.contents.edges.0.cursor', $this->key_to_cursor( $cart_items[3] ) ),
			$this->expectedField( 'cart.contents.edges.1.cursor', $this->key_to_cursor( $cart_items[4] ) ),
			$this->expectedEdge(
				'cart.contents.edges',
				array( $this->expectedField( 'key', $cart_items[3] ) ),
				0
			),
			$this->expectedEdge(
				'cart.contents.edges',
				array( $this->expectedField( 'key', $cart_items[4] ) ),
				1
			),
		);

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Four
		 *
		 * Tests "before" parameter.
		 */
		$variables = array(
			'last'   => 4,
			'before' => $this->key_to_cursor( $cart_items[3] ),
		);
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array(
			$this->expectedField( 'cart.contents.pageInfo.hasNextPage', true ),
			$this->expectedField( 'cart.contents.pageInfo.hasPreviousPage', false ),
			$this->expectedField( 'cart.contents.edges.0.cursor', $this->key_to_cursor( $cart_items[0] ) ),
			$this->expectedField( 'cart.contents.edges.1.cursor', $this->key_to_cursor( $cart_items[1] ) ),
			$this->expectedField( 'cart.contents.edges.2.cursor', $this->key_to_cursor( $cart_items[2] ) ),
			$this->expectedEdge(
				'cart.contents.edges',
				array( $this->expectedField( 'key', $cart_items[0] ) ),
				0
			),
			$this->expectedEdge(
				'cart.contents.edges',
				array( $this->expectedField( 'key', $cart_items[1] ) ),
				1
			),
			$this->expectedEdge(
				'cart.contents.edges',
				array( $this->expectedField( 'key', $cart_items[2] ) ),
				2
			),
		);

		$this->assertQuerySuccessful( $response, $expected );
	}
}
