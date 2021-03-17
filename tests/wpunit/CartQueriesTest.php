<?php

class CartQueriesTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {
	private function key_to_cursor( $key ) {
		return base64_encode( "CI:{$key}" );
	}

	public function getExpectedCartData() {
		$cart = WC()->cart;
		return array(
			$this->expectedObject( 'cart.subtotal', \wc_graphql_price( $cart->get_subtotal() ) ),
			$this->expectedObject( 'cart.subtotalTax', \wc_graphql_price( $cart->get_subtotal_tax() ) ),
			$this->expectedObject( 'cart.discountTotal', \wc_graphql_price( $cart->get_discount_total() ) ),
			$this->expectedObject( 'cart.discountTax', \wc_graphql_price( $cart->get_discount_tax() ) ),
			$this->expectedObject( 'cart.shippingTotal', \wc_graphql_price( $cart->get_shipping_total() ) ),
			$this->expectedObject( 'cart.shippingTax', \wc_graphql_price( $cart->get_shipping_tax() ) ),
			$this->expectedObject( 'cart.contentsTotal', \wc_graphql_price( $cart->get_cart_contents_total() ) ),
			$this->expectedObject( 'cart.contentsTax', \wc_graphql_price( $cart->get_cart_contents_tax() ) ),
			$this->expectedObject( 'cart.feeTotal', \wc_graphql_price( $cart->get_fee_total() ) ),
			$this->expectedObject( 'cart.feeTax', \wc_graphql_price( $cart->get_fee_tax() ) ),
			$this->expectedObject( 'cart.total', \wc_graphql_price( $cart->get_totals()['total'] ) ),
			$this->expectedObject( 'cart.totalTax', \wc_graphql_price( $cart->get_total_tax() ) ),
			$this->expectedObject( 'cart.isEmpty', $cart->is_empty() ),
			$this->expectedObject( 'cart.displayPricesIncludeTax', $cart->display_prices_including_tax() ),
			$this->expectedObject( 'cart.needsShippingAddress', $cart->needs_shipping_address() ),
		);
	}

	public function getExpectedCartItemData( $path, $cart_item_key ) {
		$cart = WC()->cart;
		$item = $cart->get_cart_item( $cart_item_key );
		return array(
			$this->expectedObject( "{$path}.key", $item['key'] ),
			$this->expectedObject( "{$path}.product.node.id", $this->toRelayId( 'product', $item['product_id'] ) ),
			$this->expectedObject( "{$path}.product.node.databaseId", $item['product_id'] ),
			$this->expectedObject(
				"{$path}.variation.node.id",
				! empty( $item['variation_id'] )
					? $this->toRelayId( 'product_variation', $item['variation_id'] )
					: 'NULL'
			),
			$this->expectedObject(
				"{$path}.variation.node.databaseId",
				! empty( $item['variation_id'] ) ? $item['variation_id'] : 'NULL'
			),
			$this->expectedObject( "{$path}.quantity", $item['quantity'] ),
			$this->expectedObject( "{$path}.subtotal", \wc_graphql_price( $item['line_subtotal'] ) ),
			$this->expectedObject( "{$path}.subtotalTax", \wc_graphql_price( $item['line_subtotal_tax'] ) ),
			$this->expectedObject( "{$path}.total", \wc_graphql_price( $item['line_total'] ) ),
			$this->expectedObject( "{$path}.tax", \wc_graphql_price( $item['line_tax'] ) ),
		);
	}

	// tests
	public function testCartQuery() {
		$cart = WC()->cart;
		$this->factory->cart->add(
			array(
				'product_id' => $this->factory->product->createSimple(),
				'quantity'  => 2,
			),
			array(
				'product_id' => $this->factory->product->createSimple(),
				'quantity'  => 1,
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
		$cart = \WC()->cart;
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
		$response = $this->graphql( compact( 'query', 'variables' ) );

		$this->assertQuerySuccessful( $response, $this->getExpectedCartItemData( 'cartItem', $key ) );
	}

	public function testCartItemConnection() {
		$keys = $this->factory->cart->add(
			array(
				'product_id' => $this->factory->product->createSimple(
					array( 'virtual' => true )
				),
				'quantity'  => 2,
			),
			array(
				'product_id' => $this->factory->product->createSimple(),
				'quantity'  => 1,
			),
			array(
				'product_id' => $this->factory->product->createSimple(),
				'quantity'  => 10,
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
		foreach( $keys as $key ) {
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
		$response  = $this->graphql( compact( 'query', 'variables' ) );

		$fee  = ( \WC()->cart->get_fees() )[ $fee_ids[0] ];

		$this->assertQuerySuccessful(
			$response,
			array(
				$this->expectedObject( 'cartFee.id', $fee->id ),
				$this->expectedObject( 'cartFee.name', $fee->name ),
				$this->expectedObject( 'cartFee.taxClass', ! empty( $fee->tax_class ) ? $fee->tax_class : 'NULL' ),
				$this->expectedObject( 'cartFee.taxable', $fee->taxable ),
				$this->expectedObject( 'cartFee.amount', (float) $fee->amount ),
				$this->expectedObject( 'cartFee.total', (float) $fee->total ),
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
		$response  = $this->graphql( compact( 'query' ) );

		$expected = array();
		foreach( \WC()->cart->get_fees() as $fee_id => $value ) {
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

		// Array_map callback that generates the expected node data from splices of the $cart_items
		$expected_edge_mapper = function( $item, $position ) {
			return $this->expectedEdge(
				'cart.contents.edges',
				array(
					'cursor' => $this->key_to_cursor( $item ),
					'node'   => array( 'key' => $item ),
				),
				$position
			);
		};

		/**
		 * Assertion One
		 *
		 * Tests "first" parameter.
		 */
		$variables = array( 'first' => 2, 'after' => '' );
		$response  = $this->graphql( compact( 'query', 'variables' ) );

		$expected = array_merge(
			array(
				$this->expectedObject( 'cart.contents.itemCount', 6 ),
				$this->expectedObject( 'cart.contents.productCount', 5 ),
			),
			array_map( $expected_edge_mapper, array_slice( $cart_items, 0, 2 ), range( 0, 1 ) )
		);

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Two
		 *
		 * Tests "after" parameter.
		 */
		$variables = array(
			'first' => 2,
			'after' => $this->key_to_cursor( $cart_items[1] )
		);
		$response  = $this->graphql( compact( 'query', 'variables' ) );

		$this->assertQuerySuccessful(
			$response,
			// Only check the edges.
			array_map( $expected_edge_mapper, array_slice( $cart_items, 2, 2 ), range( 0, 1 ) )
		);

		/**
		 * Assertion Three
		 *
		 * Tests "last" parameter.
		 */
		$variables = array( 'last' => 2 );
		$response  = $this->graphql( compact( 'query', 'variables' ) );

		$this->assertQuerySuccessful(
			$response,
			// Only check the edges.
			array_map( $expected_edge_mapper, array_slice( $cart_items, 0, 2 ), range( 0, 1 ) )
		);

		/**
		 * Assertion Four
		 *
		 * Tests "before" parameter.
		 */
		$variables = array( 'last' => 4, 'before' => $cart_items[4] );
		$response  = $this->graphql( compact( 'query', 'variables' ) );

		$this->assertQuerySuccessful(
			$response,
			array_map( // Only check the edges.
				$expected_edge_mapper,
				array_slice( $cart_items, 0, 4 ),
				range( 3, 0, -1 ) // Reverse
			)
		);
	}
}
