<?php

class RefundMutationsTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {
	private $order_id;

	public function setUp(): void {
		parent::setUp();

		update_option( 'woocommerce_prices_include_tax', 'no' );
		update_option( 'woocommerce_calc_taxes', 'yes' );

		// Create a completed order as shop manager.
		$this->loginAsShopManager();

		$product_id = $this->factory->product->createSimple( [ 'regular_price' => '100' ] );

		$order = new \WC_Order();
		$order->set_status( 'completed' );
		$order->set_customer_id( 0 );
		$order->set_payment_method( 'bacs' );
		$order->add_product( wc_get_product( $product_id ), 2 );
		$order->set_total( 200 );
		$order->save();

		$this->order_id = $order->get_id();
	}

	public function testCreateRefundMutation() {
		$query = '
			mutation createRefund( $input: CreateRefundInput! ) {
				createRefund( input: $input ) {
					refund {
						databaseId
						amount
						reason
					}
					order {
						databaseId
						total
					}
				}
			}
		';

		// Assertion One: Customer cannot create refund.
		$this->loginAsCustomer();
		$response = $this->graphql(
			[
				'query'     => $query,
				'variables' => [
					'input' => [
						'orderId' => $this->order_id,
						'amount'  => '50',
						'reason'  => 'Test refund',
					],
				],
			]
		);
		$this->assertQueryError( $response );

		// Assertion Two: Shop manager can create refund.
		$this->loginAsShopManager();
		$response = $this->graphql(
			[
				'query'     => $query,
				'variables' => [
					'input' => [
						'orderId' => $this->order_id,
						'amount'  => '50',
						'reason'  => 'Test refund',
					],
				],
			]
		);

		$expected = [
			$this->expectedField( 'createRefund.refund.databaseId', self::NOT_NULL ),
			$this->expectedField( 'createRefund.refund.amount', 50.0 ),
			$this->expectedField( 'createRefund.refund.reason', 'Test refund' ),
			$this->expectedField( 'createRefund.order.databaseId', $this->order_id ),
		];

		$this->assertQuerySuccessful( $response, $expected );

		// Assertion Three: Invalid amount.
		$response = $this->graphql(
			[
				'query'     => $query,
				'variables' => [
					'input' => [
						'orderId' => $this->order_id,
						'amount'  => '0',
						'reason'  => 'Zero refund',
					],
				],
			]
		);
		$this->assertQueryError( $response );
	}

	public function testDeleteRefundMutation() {
		$this->loginAsShopManager();

		// Create a refund first.
		$refund = \wc_create_refund(
			[
				'order_id' => $this->order_id,
				'amount'   => '25',
				'reason'   => 'Refund to delete',
			]
		);
		$this->assertNotWPError( $refund );
		$refund_id = $refund->get_id();

		$query = '
			mutation deleteRefund( $input: DeleteRefundInput! ) {
				deleteRefund( input: $input ) {
					refund {
						databaseId
						amount
					}
					order {
						databaseId
					}
				}
			}
		';

		// Assertion One: Customer cannot delete refund.
		$this->loginAsCustomer();
		$response = $this->graphql(
			[
				'query'     => $query,
				'variables' => [
					'input' => [
						'id' => $this->toRelayId( 'order', $refund_id ),
					],
				],
			]
		);
		$this->assertQueryError( $response );

		// Assertion Two: Shop manager can delete refund.
		$this->loginAsShopManager();
		$response = $this->graphql(
			[
				'query'     => $query,
				'variables' => [
					'input' => [
						'id' => $this->toRelayId( 'order', $refund_id ),
					],
				],
			]
		);

		$expected = [
			$this->expectedField( 'deleteRefund.refund.databaseId', $refund_id ),
			$this->expectedField( 'deleteRefund.refund.amount', 25.0 ),
			$this->expectedField( 'deleteRefund.order.databaseId', $this->order_id ),
		];

		$this->assertQuerySuccessful( $response, $expected );

		// Verify refund is actually deleted.
		$deleted_refund = \wc_get_order( $refund_id );
		$this->assertFalse( $deleted_refund );
	}
}
