<?php

use GraphQLRelay\Relay;
use WPGraphQL\Type\WPEnumType;

class OrderItemQueriesTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {

	// tests
	public function testCouponLinesQuery() {
		$order_id = $this->factory->order->createNew();
		$this->factory->order->add_coupon_line( $order_id );
		$order        = new WC_Order( $order_id );
		$coupon_lines = $order->get_items( 'coupon' );
		$id           = $this->toRelayId( 'order', $order_id );

		$query = '
            query ($id: ID!) {
                order(id: $id) {
                    couponLines {
                        nodes {
                            databaseId
                            orderId
                            code
                            discount
                            discountTax
                            coupon {
                                id
                            }
                        }
                    }
                }
            }
        ';

		/**
		 * Assertion One
		 *
		 * Tests query and results
		 */
		$this->loginAsShopManager();
		$variables = [ 'id' => $id ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array_map(
			function( $item ) {
				return $this->expectedNode(
					'order.couponLines.nodes',
					[
						$this->expectedField( 'databaseId', $item->get_id() ),
						$this->expectedField( 'orderId', $item->get_order_id() ),
						$this->expectedField( 'code', $item->get_code() ),
						$this->expectedField( 'discount', $this->maybe( $item->get_discount(), self::IS_NULL ) ),
						$this->expectedField( 'discountTax', $this->maybe( $item->get_discount_tax(), self::IS_NULL ) ),
						$this->expectedField( 'coupon.id', $this->toRelayId( 'shop_coupon', \wc_get_coupon_id_by_code( $item->get_code() ) ) ),
					]
				);
			},
			$coupon_lines
		);

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testFeeLinesQuery() {
		$order_id = $this->factory->order->createNew();
		$this->factory->order->add_fee( $order_id );
		$order     = new WC_Order( $order_id );
		$fee_lines = $order->get_items( 'fee' );
		$id        = $this->toRelayId( 'order', $order_id );

		$query = '
            query ($id: ID!) {
                order(id: $id) {
                    feeLines {
                        nodes {
                            databaseId
                            orderId
                            amount
                            name
                            taxStatus
                            total
                            totalTax
                            taxClass
                        }
                    }
                }
            }
        ';

		/**
		 * Assertion One
		 *
		 * Tests query and results
		 */
		$this->loginAsShopManager();
		$variables = [ 'id' => $id ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array_map(
			function( $item ) {
				return $this->expectedNode(
					'order.feeLines.nodes',
					[
						$this->expectedField( 'databaseId', $item->get_id() ),
						$this->expectedField( 'orderId', $item->get_order_id() ),
						$this->expectedField( 'amount', $item->get_amount() ),
						$this->expectedField( 'name', $item->get_name() ),
						$this->expectedField( 'taxStatus', strtoupper( $item->get_tax_status() ) ),
						$this->expectedField( 'total', $item->get_total() ),
						$this->expectedField( 'totalTax', $this->maybe( $item->get_total_tax(), self::IS_NULL ) ),
						$this->expectedField(
							'taxClass',
							! empty( $item->get_tax_class() )
								? WPEnumType::get_safe_name( $item->get_tax_class() )
								: 'STANDARD'
						),
					]
				);
			},
			$fee_lines
		);

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testShippingLinesQuery() {
		$order_id       = $this->factory->order->createNew();
		$order          = new WC_Order( $order_id );
		$shipping_lines = $order->get_items( 'shipping' );
		$id             = $this->toRelayId( 'order', $order_id );

		$query = '
            query ($id: ID!) {
                order(id: $id) {
                    shippingLines {
                        nodes {
                            databaseId
                            orderId
                            methodTitle
                            total
                            totalTax
                            taxClass
                        }
                    }
                }
            }
        ';

		/**
		 * Assertion One
		 *
		 * Tests query and results
		 */
		$this->loginAsShopManager();
		$variables = [ 'id' => $id ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array_map(
			function( $item ) {
				return $this->expectedNode(
					'order.shippingLines.nodes',
					[
						$this->expectedField( 'databaseId', $item->get_id() ),
						$this->expectedField( 'orderId', $item->get_order_id() ),
						$this->expectedField( 'methodTitle', $item->get_method_title() ),
						$this->expectedField( 'total', $item->get_total() ),
						$this->expectedField( 'totalTax', $this->maybe( $item->get_total_tax(), self::IS_NULL ) ),
						$this->expectedField(
							'taxClass',
							! empty( $item->get_tax_class() )
								? $item->get_tax_class() === 'inherit'
									? WPEnumType::get_safe_name( 'inherit cart' )
									: WPEnumType::get_safe_name( $item->get_tax_class() )
								: 'STANDARD'
						),
					]
				);
			},
			$shipping_lines
		);

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testTaxLinesQuery() {
		$order_id = $this->factory->order->createNew();
		$this->factory->order->add_tax( $order_id );
		$order     = new WC_Order( $order_id );
		$tax_lines = $order->get_items( 'tax' );
		$id        = $this->toRelayId( 'order', $order_id );

		$query = '
            query ($id: ID!) {
                order(id: $id) {
                    taxLines {
                        nodes {
                            rateCode
                            label
                            taxTotal
                            shippingTaxTotal
                            isCompound
                            taxRate {
                                databaseId
                            }
                        }
                    }
                }
            }
        ';

		/**
		 * Assertion One
		 *
		 * Tests query and results
		 */
		$this->loginAsShopManager();
		$variables = [ 'id' => $id ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array_map(
			function( $item ) {
				return $this->expectedNode(
					'order.taxLines.nodes',
					[
						$this->expectedField( 'rateCode', $item->get_rate_code() ),
						$this->expectedField( 'label', $item->get_label() ),
						$this->expectedField( 'taxTotal', $item->get_tax_total() ),
						$this->expectedField( 'shippingTaxTotal', $item->get_shipping_tax_total() ),
						$this->expectedField( 'isCompound', $item->is_compound() ),
						$this->expectedField( 'taxRate.databaseId', $item->get_rate_id() ),
					]
				);
			},
			$tax_lines
		);

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testLineItemsQuery() {
		$order_id   = $this->factory->order->createNew();
		$order      = new WC_Order( $order_id );
		$line_items = $order->get_items();
		$id         = $this->toRelayId( 'order', $order_id );

		$query = '
            query ($id: ID!) {
                order(id: $id) {
                    lineItems {
                        nodes {
                            productId
                            variationId
                            quantity
                            taxClass
                            subtotal
                            subtotalTax
                            total
                            totalTax
                            itemDownloads {
                                downloadId
                            }
                            taxStatus
                            product {
								node {
									... on SimpleProduct {
										id
									}
									... on VariableProduct {
										id
									}
								}
                            }
                            variation {
                                node { id }
                            }
                        }
                    }
                }
            }
        ';

		/**
		 * Assertion One
		 *
		 * Tests query and results
		 */
		$this->loginAsShopManager();
		$variables = [ 'id' => $id ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array_map(
			function( $item ) {
				return $this->expectedNode(
					'order.lineItems.nodes',
					[
						$this->expectedField( 'productId', $item->get_product_id() ),
						$this->expectedField( 'variationId', $this->maybe( $item->get_variation_id(), self::IS_NULL ) ),
						$this->expectedField( 'quantity', $item->get_quantity() ),
						$this->expectedField(
							'taxClass',
							! empty( $item->get_tax_class() )
								? strtoupper( $item->get_tax_class() )
								: 'STANDARD'
						),
						$this->expectedField( 'subtotal', $this->maybe( $item->get_subtotal(), self::IS_NULL ) ),
						$this->expectedField( 'subtotalTax', $this->maybe( $item->get_subtotal_tax(), self::IS_NULL ) ),
						$this->expectedField( 'total', $this->maybe( $item->get_total(), self::IS_NULL ) ),
						$this->expectedField( 'totalTax', $this->maybe( $item->get_total_tax(), self::IS_NULL ) ),
						$this->expectedField( 'itemDownloads', null ),
						$this->expectedField( 'taxStatus', strtoupper( $item->get_tax_status() ) ),
						$this->expectedField( 'product.node.id', $this->toRelayId( 'product', $item->get_product_id() ) ),
						$this->expectedField(
							'variation.node.id',
							! empty( $item->get_variation_id() )
								? $this->toRelayId( 'product_variation', $item->get_variation_id() )
								: self::IS_NULL
						),
					]
				);
			},
			$line_items
		);

		$this->assertQuerySuccessful( $response, $expected );
	}
}
