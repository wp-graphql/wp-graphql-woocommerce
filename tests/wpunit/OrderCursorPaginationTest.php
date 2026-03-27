<?php

class OrderCursorPaginationTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {
	private $order_ids = [];

	public function setUp(): void {
		parent::setUp();

		$this->loginAsShopManager();

		// Create 5 orders with staggered dates and different totals.
		for ( $i = 0; $i < 5; $i++ ) {
			$this->order_ids[] = $this->factory->order->createNew(
				[
					'status'      => 'completed',
					'customer_id' => $this->customer,
				]
			);

			// Stagger dates so ordering is deterministic.
			$order = wc_get_order( $this->order_ids[ $i ] );
			$order->set_date_created( gmdate( 'Y-m-d H:i:s', strtotime( "+{$i} minutes" ) ) );
			$order->set_total( ( $i + 1 ) * 10 );
			$order->save();
		}
	}

	public function testForwardPaginationWithFirstAfter() {
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

		// First page: 2 orders.
		$variables = [ 'first' => 2 ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );

		$this->assertQuerySuccessful( $response, [] );

		$nodes = $this->lodashGet( $response, 'data.orders.nodes' );
		$this->assertCount( 2, $nodes );

		$has_next = $this->lodashGet( $response, 'data.orders.pageInfo.hasNextPage' );
		$this->assertTrue( $has_next );

		$end_cursor = $this->lodashGet( $response, 'data.orders.pageInfo.endCursor' );

		// Second page.
		$variables = [
			'first' => 2,
			'after' => $end_cursor,
		];
		$response = $this->graphql( compact( 'query', 'variables' ) );

		$this->assertQuerySuccessful( $response, [] );

		$nodes_page2 = $this->lodashGet( $response, 'data.orders.nodes' );
		$this->assertCount( 2, $nodes_page2 );

		// Ensure no overlap between pages.
		$page1_ids = array_column( $nodes, 'databaseId' );
		$page2_ids = array_column( $nodes_page2, 'databaseId' );
		$this->assertEmpty( array_intersect( $page1_ids, $page2_ids ), 'Pages should not have overlapping orders.' );

		// Third page: should have 1 remaining.
		$end_cursor2 = $this->lodashGet( $response, 'data.orders.pageInfo.endCursor' );
		$variables   = [
			'first' => 2,
			'after' => $end_cursor2,
		];
		$response = $this->graphql( compact( 'query', 'variables' ) );

		$this->assertQuerySuccessful( $response, [] );

		$nodes_page3 = $this->lodashGet( $response, 'data.orders.nodes' );
		$this->assertCount( 1, $nodes_page3 );

		$has_next = $this->lodashGet( $response, 'data.orders.pageInfo.hasNextPage' );
		$this->assertFalse( $has_next );

		// All 5 orders accounted for.
		$all_ids = array_merge( $page1_ids, $page2_ids, array_column( $nodes_page3, 'databaseId' ) );
		$this->assertCount( 5, $all_ids, 'All 5 orders should be returned across pages.' );
	}

	public function testBackwardPaginationWithLastBefore() {
		$query = '
			query ($last: Int, $before: String) {
				orders(last: $last, before: $before) {
					nodes {
						databaseId
					}
					pageInfo {
						hasPreviousPage
						startCursor
					}
				}
			}
		';

		// Last 2 orders.
		$variables = [ 'last' => 2 ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );

		$this->assertQuerySuccessful( $response, [] );

		$nodes = $this->lodashGet( $response, 'data.orders.nodes' );
		$this->assertCount( 2, $nodes );

		$has_previous = $this->lodashGet( $response, 'data.orders.pageInfo.hasPreviousPage' );
		$this->assertTrue( $has_previous );

		// Previous page.
		$start_cursor = $this->lodashGet( $response, 'data.orders.pageInfo.startCursor' );
		$variables    = [
			'last'   => 2,
			'before' => $start_cursor,
		];
		$response = $this->graphql( compact( 'query', 'variables' ) );

		$this->assertQuerySuccessful( $response, [] );

		$nodes_page2 = $this->lodashGet( $response, 'data.orders.nodes' );
		$this->assertCount( 2, $nodes_page2 );

		// No overlap.
		$page1_ids = array_column( $nodes, 'databaseId' );
		$page2_ids = array_column( $nodes_page2, 'databaseId' );
		$this->assertEmpty( array_intersect( $page1_ids, $page2_ids ), 'Pages should not overlap.' );
	}

	public function testCursorPaginationReturnsConsistentResults() {
		$query = '
			query {
				orders(first: 100) {
					nodes {
						databaseId
					}
				}
			}
		';

		$response = $this->graphql( compact( 'query' ) );

		$this->assertQuerySuccessful( $response, [] );

		$all_nodes = $this->lodashGet( $response, 'data.orders.nodes' );
		$all_ids   = array_column( $all_nodes, 'databaseId' );

		// All 5 created orders should be present.
		foreach ( $this->order_ids as $order_id ) {
			$this->assertContains( $order_id, $all_ids, "Order {$order_id} should be in results." );
		}
	}

	public function testOrderConnectionWithDateOrdering() {
		$query = '
			query {
				orders(first: 5, where: { orderby: { field: DATE, order: ASC } }) {
					nodes {
						databaseId
						date
					}
				}
			}
		';

		$response = $this->graphql( compact( 'query' ) );

		$this->assertQuerySuccessful( $response, [] );

		$nodes = $this->lodashGet( $response, 'data.orders.nodes' );
		$this->assertGreaterThanOrEqual( 2, count( $nodes ) );

		// Verify ascending date order.
		$dates = array_column( $nodes, 'date' );
		$sorted = $dates;
		sort( $sorted );
		$this->assertSame( $sorted, $dates, 'Orders should be sorted by date ascending.' );
	}

	public function testOrderConnectionWithDescDateOrdering() {
		$query = '
			query {
				orders(first: 5, where: { orderby: { field: DATE, order: DESC } }) {
					nodes {
						databaseId
						date
					}
				}
			}
		';

		$response = $this->graphql( compact( 'query' ) );

		$this->assertQuerySuccessful( $response, [] );

		$nodes = $this->lodashGet( $response, 'data.orders.nodes' );
		$this->assertGreaterThanOrEqual( 2, count( $nodes ) );

		// Verify descending date order.
		$dates  = array_column( $nodes, 'date' );
		$sorted = $dates;
		rsort( $sorted );
		$this->assertSame( $sorted, $dates, 'Orders should be sorted by date descending.' );
	}

	public function testForwardPaginationWithDateOrderingMaintainsCursorIntegrity() {
		$query = '
			query ($first: Int, $after: String) {
				orders(first: $first, after: $after, where: { orderby: { field: DATE, order: ASC } }) {
					nodes {
						databaseId
						date
					}
					pageInfo {
						hasNextPage
						endCursor
					}
				}
			}
		';

		// Page 1.
		$variables = [ 'first' => 2 ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );

		$this->assertQuerySuccessful( $response, [] );

		$page1_nodes = $this->lodashGet( $response, 'data.orders.nodes' );
		$end_cursor  = $this->lodashGet( $response, 'data.orders.pageInfo.endCursor' );

		// Page 2.
		$variables = [
			'first' => 2,
			'after' => $end_cursor,
		];
		$response = $this->graphql( compact( 'query', 'variables' ) );

		$this->assertQuerySuccessful( $response, [] );

		$page2_nodes = $this->lodashGet( $response, 'data.orders.nodes' );

		// The last date on page 1 should be <= first date on page 2.
		$last_date_page1  = end( $page1_nodes )['date'];
		$first_date_page2 = $page2_nodes[0]['date'];
		$this->assertLessThanOrEqual(
			$first_date_page2,
			$last_date_page1,
			'Cursor pagination should maintain date ordering across pages.'
		);
	}
}
