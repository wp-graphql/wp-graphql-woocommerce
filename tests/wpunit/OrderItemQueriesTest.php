<?php

use GraphQLRelay\Relay;
use WPGraphQL\Type\WPEnumType;

class OrderItemQueriesTest extends \Codeception\TestCase\WPTestCase {
    private $shop_manager;
	private $customer;
    private $order;
    private $item_helper;
	private $order_helper;

    public function setUp() {
        parent::setUp();

        $this->shop_manager    = $this->factory->user->create( array( 'role' => 'shop_manager' ) );
		$this->customer        = $this->factory->user->create( array( 'role' => 'customer' ) );
		$this->order_helper    = $this->getModule('\Helper\Wpunit')->order();
        $this->item_helper     = $this->getModule('\Helper\Wpunit')->item();
		$this->order           = $this->order_helper->create();
    }

    public function tearDown() {
        // your tear down methods here

        // then
        parent::tearDown();
    }

    // tests
    public function testCouponLinesQuery() {
        $this->item_helper->add_coupon( $this->order );
        $order        = new WC_Order( $this->order );
        $coupon_lines = $order->get_items( 'coupon' );
        $id           = Relay::toGlobalId( 'shop_order', $this->order );

        $query        = '
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
		 * tests query and results
		 */
        wp_set_current_user( $this->shop_manager );
        $variables = array( 'id' => $id );
		$actual    = graphql( array( 'query' => $query, 'variables' => $variables ) );
		$expected  = array(
			'data' => array(
				'order' => array(
                    'couponLines' => array(
                        'nodes' => array_reverse(
                            array_map(
                                function( $item ) {
                                    return array(
                                        'databaseId'      => $item->get_id(),
                                        'orderId'     => $item->get_order_id(),
                                        'code'        => $item->get_code(),
                                        'discount'    => ! empty( $item->get_discount() ) ? $item->get_discount() : null,
                                        'discountTax' => ! empty( $item->get_discount_tax() ) ? $item->get_discount_tax() : null,
                                        'coupon'      => array(
                                            'id' => Relay::toGlobalId( 'shop_coupon', \wc_get_coupon_id_by_code( $item->get_code() ) ),
                                        ),
                                    );
                                },
                                $coupon_lines
                            )
                        ),
                    )
				),
			),
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );
    }

    public function testFeeLinesQuery() {
        $this->item_helper->add_fee( $this->order );
        $order     = new WC_Order( $this->order );
        $fee_lines = $order->get_items( 'fee' );
        $id        = Relay::toGlobalId( 'shop_order', $this->order );

        $query     = '
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
		 * tests query and results
		 */
        wp_set_current_user( $this->shop_manager );
        $variables = array( 'id' => $id );
		$actual    = graphql( array( 'query' => $query, 'variables' => $variables ) );
		$expected  = array(
			'data' => array(
				'order' => array(
                    'feeLines' => array(
                        'nodes' => array_reverse(
                            array_map(
                                function( $item ) {
                                    return array(
                                        'databaseId'    => $item->get_id(),
                                        'orderId'   => $item->get_order_id(),
                                        'amount'    => $item->get_amount(),
                                        'name'      => $item->get_name(),
                                        'taxStatus' => strtoupper( $item->get_tax_status() ),
                                        'total'     => $item->get_total(),
                                        'totalTax'  => ! empty( $item->get_total_tax() ) ? $item->get_total_tax() : null,
                                        'taxClass'  => ! empty( $item->get_tax_class() )
                                            ? WPEnumType::get_safe_name( $item->get_tax_class() )
                                            : 'STANDARD',
                                    );
                                },
                                $fee_lines
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

    public function testShippingLinesQuery() {
        $order          = new WC_Order( $this->order );
        $shipping_lines = $order->get_items( 'shipping' );
        $id             = Relay::toGlobalId( 'shop_order', $this->order );

        $query          = '
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
		 * tests query and results
		 */
        wp_set_current_user( $this->shop_manager );
        $variables = array( 'id' => $id );
		$actual    = graphql( array( 'query' => $query, 'variables' => $variables ) );
		$expected  = array(
			'data' => array(
				'order' => array(
                    'shippingLines' => array(
                        'nodes' => array_reverse(
                            array_map(
                                function( $item ) {

                                    return array(
                                        'databaseId'         => $item->get_id(),
                                        'orderId'        => $item->get_order_id(),
                                        'methodTitle'    => $item->get_method_title(),
                                        'total'          => $item->get_total(),
                                        'totalTax'       => !empty( $item->get_total_tax() )
                                            ? $item->get_total_tax()
                                            : null,
                                        'taxClass'       => ! empty( $item->get_tax_class() )
                                            ? $item->get_tax_class() === 'inherit'
                                                ? WPEnumType::get_safe_name( 'inherit cart' )
                                                : WPEnumType::get_safe_name( $item->get_tax_class() )
                                            : 'STANDARD'
                                    );
                                },
                                $shipping_lines
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

    public function testTaxLinesQuery() {
        $this->item_helper->add_tax( $this->order );
        $order     = new WC_Order( $this->order );
        $tax_lines = $order->get_items( 'tax' );
        $id        = Relay::toGlobalId( 'shop_order', $this->order );

        $query     = '
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
		 * tests query and results
		 */
        wp_set_current_user( $this->shop_manager );
        $variables = array( 'id' => $id );
		$actual    = graphql( array( 'query' => $query, 'variables' => $variables ) );
		$expected  = array(
			'data' => array(
				'order' => array(
                    'taxLines' => array(
                        'nodes' => array_reverse(
                            array_map(
                                function( $item ) {
                                    return array(
                                        'rateCode'         => $item->get_rate_code(),
                                        'label'            => $item->get_label(),
                                        'taxTotal'         => $item->get_tax_total(),
                                        'shippingTaxTotal' => $item->get_shipping_tax_total(),
                                        'isCompound'       => $item->is_compound(),
                                        'taxRate'          => array( 'databaseId' => $item->get_rate_id() ),
                                    );
                                },
                                $tax_lines
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

    public function testLineItemsQuery() {
        $order      = new WC_Order( $this->order );
        $line_items = $order->get_items();
        $id         = Relay::toGlobalId( 'shop_order', $this->order );

        $query      = '
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
                                ... on SimpleProduct {
                                    id
                                }
                                ... on VariableProduct {
                                    id
                                }
                            }
                            variation {
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
		 * tests query and results
		 */
        wp_set_current_user( $this->shop_manager );
        $variables = array( 'id' => $id );
		$actual    = graphql( array( 'query' => $query, 'variables' => $variables ) );
		$expected  = array(
			'data' => array(
				'order' => array(
                    'lineItems' => array(
                        'nodes' => array_values(
                            array_map(
                                function( $item ) {
                                    return array(
                                        'productId'     => $item->get_product_id(),
                                        'variationId'   => ! empty( $item->get_variation_id() )
                                            ? $item->get_variation_id()
                                            : null,
                                        'quantity'      => $item->get_quantity(),
                                        'taxClass'      => ! empty( $item->get_tax_class() )
                                            ? strtoupper( $item->get_tax_class() )
                                            : 'STANDARD',
                                        'subtotal'      => ! empty( $item->get_subtotal() ) ? $item->get_subtotal() : null,
                                        'subtotalTax'   => ! empty( $item->get_subtotal_tax() ) ? $item->get_subtotal_tax() : null,
                                        'total'         => ! empty( $item->get_total() ) ? $item->get_total() : null,
                                        'totalTax'      => ! empty( $item->get_total_tax() ) ? $item->get_total_tax() : null,
                                        'itemDownloads' => null,
                                        'taxStatus'     => strtoupper( $item->get_tax_status() ),
                                        'product'       => array( 'id' => Relay::toGlobalId( 'product', $item->get_product_id() ) ),
                                        'variation'     => ! empty( $item->get_variation_id() )
                                            ? array( 'id' => Relay::toGlobalId( 'product_variation', $item->get_variation_id() ) )
                                            : null,
                                    );
                                },
                                $line_items
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
}
