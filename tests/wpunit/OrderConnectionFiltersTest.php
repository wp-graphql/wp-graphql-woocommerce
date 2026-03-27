<?php

class OrderConnectionFiltersTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {
	/**
	 * Test that a non-admin customer can paginate their own orders with first/after.
	 */
	public function testCustomerCanPaginateOrders() {
		$customer_id = $this->factory->customer->create();

		// Create 3 orders for this customer.
		$order_ids = [];
		for ( $i = 0; $i < 3; $i++ ) {
			$order_ids[] = $this->factory->order->createNew(
				[ 'customer_id' => $customer_id ]
			);
		}

		$this->loginAs( $customer_id );

		// Query first 2 orders.
		$query = '
			query ($first: Int, $after: String) {
				orders(first: $first, after: $after) {
					nodes {
						databaseId
					}
					pageInfo {
						hasNextPage
						endCursor
					}
				}
			}
		';

		$variables = [ 'first' => 2 ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );

		$this->assertQuerySuccessful( $response, [] );

		$nodes = $this->lodashGet( $response, 'data.orders.nodes', [] );
		$this->assertCount( 2, $nodes, 'Should return exactly 2 orders with first: 2.' );

		$has_next_page = $this->lodashGet( $response, 'data.orders.pageInfo.hasNextPage' );
		$this->assertTrue( $has_next_page, 'Should have a next page.' );

		// Query the next page.
		$end_cursor = $this->lodashGet( $response, 'data.orders.pageInfo.endCursor' );
		$variables  = [
			'first' => 2,
			'after' => $end_cursor,
		];
		$response = $this->graphql( compact( 'query', 'variables' ) );

		$this->assertQuerySuccessful( $response, [] );

		$nodes = $this->lodashGet( $response, 'data.orders.nodes', [] );
		$this->assertCount( 1, $nodes, 'Should return 1 order on the second page.' );

		$has_next_page = $this->lodashGet( $response, 'data.orders.pageInfo.hasNextPage' );
		$this->assertFalse( $has_next_page, 'Should not have a next page.' );
	}

	/**
	 * Test that a non-admin customer can filter their own orders with where args.
	 */
	public function testCustomerCanFilterOrdersByStatus() {
		$customer_id = $this->factory->customer->create();

		// Create orders with different statuses.
		$completed_id = $this->factory->order->createNew(
			[
				'customer_id' => $customer_id,
				'status'      => 'completed',
			]
		);
		$pending_id   = $this->factory->order->createNew(
			[
				'customer_id' => $customer_id,
				'status'      => 'pending',
			]
		);
		$processing_id = $this->factory->order->createNew(
			[
				'customer_id' => $customer_id,
				'status'      => 'processing',
			]
		);

		$this->loginAs( $customer_id );

		$query = '
			query ($statuses: [OrderStatusEnum]) {
				orders(where: { statuses: $statuses }) {
					nodes {
						databaseId
						status
					}
				}
			}
		';

		// Filter by completed only.
		$variables = [ 'statuses' => [ 'COMPLETED' ] ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedField( 'orders.nodes.#.databaseId', $completed_id ),
			$this->not()->expectedField( 'orders.nodes.#.databaseId', $pending_id ),
			$this->not()->expectedField( 'orders.nodes.#.databaseId', $processing_id ),
		];

		$this->assertQuerySuccessful( $response, $expected );

		// Filter by processing only.
		$variables = [ 'statuses' => [ 'PROCESSING' ] ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedField( 'orders.nodes.#.databaseId', $processing_id ),
			$this->not()->expectedField( 'orders.nodes.#.databaseId', $completed_id ),
			$this->not()->expectedField( 'orders.nodes.#.databaseId', $pending_id ),
		];

		$this->assertQuerySuccessful( $response, $expected );

		// Filter by multiple statuses as the only filter arg.
		$variables = [ 'statuses' => [ 'PENDING', 'PROCESSING' ] ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedField( 'orders.nodes.#.databaseId', $pending_id ),
			$this->expectedField( 'orders.nodes.#.databaseId', $processing_id ),
			$this->not()->expectedField( 'orders.nodes.#.databaseId', $completed_id ),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}

	/**
	 * Test that non-admin customer where args are properly restricted
	 * (customerId, customersIn, billingEmail should be stripped).
	 */
	public function testCustomerCannotUsePrivateWhereArgs() {
		$customer_id   = $this->factory->customer->create();
		$other_customer = $this->factory->customer->create();

		$own_order_id   = $this->factory->order->createNew( [ 'customer_id' => $customer_id ] );
		$other_order_id = $this->factory->order->createNew( [ 'customer_id' => $other_customer ] );

		$this->loginAs( $customer_id );

		// Try to query another customer's orders — the customerId arg should be stripped.
		$query = '
			query ($customerId: Int) {
				orders(where: { customerId: $customerId }) {
					nodes {
						databaseId
					}
				}
			}
		';

		$variables = [ 'customerId' => $other_customer ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );

		$this->assertQuerySuccessful( $response, [] );

		// Should only see own orders, not the other customer's.
		$nodes        = $this->lodashGet( $response, 'data.orders.nodes', [] );
		$returned_ids = array_column( $nodes, 'databaseId' );
		$this->assertContains( $own_order_id, $returned_ids );
		$this->assertNotContains( $other_order_id, $returned_ids );
	}
}
