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

		// Page 1: 2 orders.
		$variables = [ 'first' => 2 ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );

		$this->assertQuerySuccessful(
			$response,
			[
				$this->expectedField( 'orders.nodes.0.databaseId', static::NOT_FALSY ),
				$this->expectedField( 'orders.nodes.1.databaseId', static::NOT_FALSY ),
				$this->expectedField( 'orders.pageInfo.hasNextPage', true ),
				$this->expectedField( 'orders.pageInfo.endCursor', static::NOT_FALSY ),
			]
		);

		$page1_nodes = $this->lodashGet( $response, 'data.orders.nodes' );
		$end_cursor  = $this->lodashGet( $response, 'data.orders.pageInfo.endCursor' );

		// Page 2.
		$variables = [
			'first' => 2,
			'after' => $end_cursor,
		];
		$response = $this->graphql( compact( 'query', 'variables' ) );

		$expected = [
			$this->expectedField( 'orders.nodes.0.databaseId', static::NOT_FALSY ),
			$this->expectedField( 'orders.nodes.1.databaseId', static::NOT_FALSY ),
		];
		foreach ( $page1_nodes as $node ) {
			$expected[] = $this->not()->expectedNode(
				'orders.nodes',
				[ $this->expectedField( 'databaseId', $node['databaseId'] ) ]
			);
		}

		$this->assertQuerySuccessful( $response, $expected );

		$page2_nodes = $this->lodashGet( $response, 'data.orders.nodes' );
		$end_cursor  = $this->lodashGet( $response, 'data.orders.pageInfo.endCursor' );

		// Page 3: should have 1 remaining.
		$variables = [
			'first' => 2,
			'after' => $end_cursor,
		];
		$response = $this->graphql( compact( 'query', 'variables' ) );

		$expected = [
			$this->expectedField( 'orders.nodes.0.databaseId', static::NOT_FALSY ),
			$this->expectedField( 'orders.pageInfo.hasNextPage', false ),
		];
		foreach ( array_merge( $page1_nodes, $page2_nodes ) as $node ) {
			$expected[] = $this->not()->expectedNode(
				'orders.nodes',
				[ $this->expectedField( 'databaseId', $node['databaseId'] ) ]
			);
		}

		$this->assertQuerySuccessful( $response, $expected );
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

		$this->assertQuerySuccessful(
			$response,
			[
				$this->expectedField( 'orders.nodes.0.databaseId', static::NOT_FALSY ),
				$this->expectedField( 'orders.nodes.1.databaseId', static::NOT_FALSY ),
				$this->expectedField( 'orders.pageInfo.hasPreviousPage', true ),
				$this->expectedField( 'orders.pageInfo.startCursor', static::NOT_FALSY ),
			]
		);

		$page1_nodes  = $this->lodashGet( $response, 'data.orders.nodes' );
		$start_cursor = $this->lodashGet( $response, 'data.orders.pageInfo.startCursor' );

		// Previous page.
		$variables = [
			'last'   => 2,
			'before' => $start_cursor,
		];
		$response = $this->graphql( compact( 'query', 'variables' ) );

		$expected = [
			$this->expectedField( 'orders.nodes.0.databaseId', static::NOT_FALSY ),
			$this->expectedField( 'orders.nodes.1.databaseId', static::NOT_FALSY ),
		];
		foreach ( $page1_nodes as $node ) {
			$expected[] = $this->not()->expectedNode(
				'orders.nodes',
				[ $this->expectedField( 'databaseId', $node['databaseId'] ) ]
			);
		}

		$this->assertQuerySuccessful( $response, $expected );
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

		$expected = [];
		foreach ( $this->order_ids as $order_id ) {
			$expected[] = $this->expectedNode(
				'orders.nodes',
				[ $this->expectedField( 'databaseId', $order_id ) ]
			);
		}

		$this->assertQuerySuccessful( $response, $expected );
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

		$this->assertQuerySuccessful(
			$response,
			[
				$this->expectedField( 'orders.nodes.0.databaseId', static::NOT_FALSY ),
				$this->expectedField( 'orders.nodes.0.date', static::NOT_FALSY ),
			]
		);

		$nodes  = $this->lodashGet( $response, 'data.orders.nodes' );
		$dates  = array_column( $nodes, 'date' );
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

		$this->assertQuerySuccessful(
			$response,
			[
				$this->expectedField( 'orders.nodes.0.databaseId', static::NOT_FALSY ),
				$this->expectedField( 'orders.nodes.0.date', static::NOT_FALSY ),
			]
		);

		$nodes  = $this->lodashGet( $response, 'data.orders.nodes' );
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

		$this->assertQuerySuccessful(
			$response,
			[
				$this->expectedField( 'orders.nodes.0.date', static::NOT_FALSY ),
				$this->expectedField( 'orders.nodes.1.date', static::NOT_FALSY ),
				$this->expectedField( 'orders.pageInfo.endCursor', static::NOT_FALSY ),
			]
		);

		$page1_nodes = $this->lodashGet( $response, 'data.orders.nodes' );
		$end_cursor  = $this->lodashGet( $response, 'data.orders.pageInfo.endCursor' );

		// Page 2.
		$variables = [
			'first' => 2,
			'after' => $end_cursor,
		];
		$response = $this->graphql( compact( 'query', 'variables' ) );

		$expected = [
			$this->expectedField( 'orders.nodes.0.date', static::NOT_FALSY ),
		];
		foreach ( $page1_nodes as $node ) {
			$expected[] = $this->not()->expectedNode(
				'orders.nodes',
				[ $this->expectedField( 'databaseId', $node['databaseId'] ) ]
			);
		}

		$this->assertQuerySuccessful( $response, $expected );

		$page2_nodes      = $this->lodashGet( $response, 'data.orders.nodes' );
		$last_date_page1  = end( $page1_nodes )['date'];
		$first_date_page2 = $page2_nodes[0]['date'];
		$this->assertLessThanOrEqual(
			$first_date_page2,
			$last_date_page1,
			'Cursor pagination should maintain date ordering across pages.'
		);
	}

	public function testCursorPaginationOrderedByTotal() {
		$query = '
			query ($first: Int, $after: String) {
				orders(first: $first, after: $after, where: { orderby: { field: TOTAL, order: ASC } }) {
					nodes {
						databaseId
						total(format: RAW)
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

		$this->assertQuerySuccessful(
			$response,
			[
				$this->expectedField( 'orders.nodes.0.databaseId', static::NOT_FALSY ),
				$this->expectedField( 'orders.nodes.0.total', static::NOT_FALSY ),
				$this->expectedField( 'orders.nodes.1.databaseId', static::NOT_FALSY ),
				$this->expectedField( 'orders.pageInfo.hasNextPage', true ),
				$this->expectedField( 'orders.pageInfo.endCursor', static::NOT_FALSY ),
			]
		);

		$page1_nodes = $this->lodashGet( $response, 'data.orders.nodes' );
		$end_cursor  = $this->lodashGet( $response, 'data.orders.pageInfo.endCursor' );

		// Page 2 — triggers COT cursor compare_with() for _order_total column.
		$variables = [
			'first' => 2,
			'after' => $end_cursor,
		];
		$response = $this->graphql( compact( 'query', 'variables' ) );

		$expected = [
			$this->expectedField( 'orders.nodes.0.databaseId', static::NOT_FALSY ),
			$this->expectedField( 'orders.nodes.1.databaseId', static::NOT_FALSY ),
		];
		foreach ( $page1_nodes as $node ) {
			$expected[] = $this->not()->expectedNode(
				'orders.nodes',
				[ $this->expectedField( 'databaseId', $node['databaseId'] ) ]
			);
		}

		$this->assertQuerySuccessful( $response, $expected );

		$page2_nodes       = $this->lodashGet( $response, 'data.orders.nodes' );
		$last_total_page1  = (float) end( $page1_nodes )['total'];
		$first_total_page2 = (float) $page2_nodes[0]['total'];
		$this->assertLessThanOrEqual(
			$first_total_page2,
			$last_total_page1,
			'Cursor pagination should maintain total ordering across pages.'
		);
	}

	public function testCursorPaginationOrderedByTotalDesc() {
		$query = '
			query ($first: Int, $after: String) {
				orders(first: $first, after: $after, where: { orderby: { field: TOTAL, order: DESC } }) {
					nodes {
						databaseId
						total(format: RAW)
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

		$this->assertQuerySuccessful(
			$response,
			[
				$this->expectedField( 'orders.nodes.0.databaseId', static::NOT_FALSY ),
				$this->expectedField( 'orders.nodes.0.total', static::NOT_FALSY ),
				$this->expectedField( 'orders.pageInfo.endCursor', static::NOT_FALSY ),
			]
		);

		$page1_nodes = $this->lodashGet( $response, 'data.orders.nodes' );
		$end_cursor  = $this->lodashGet( $response, 'data.orders.pageInfo.endCursor' );

		// Page 2.
		$variables = [
			'first' => 2,
			'after' => $end_cursor,
		];
		$response = $this->graphql( compact( 'query', 'variables' ) );

		$expected = [
			$this->expectedField( 'orders.nodes.0.databaseId', static::NOT_FALSY ),
		];
		foreach ( $page1_nodes as $node ) {
			$expected[] = $this->not()->expectedNode(
				'orders.nodes',
				[ $this->expectedField( 'databaseId', $node['databaseId'] ) ]
			);
		}

		$this->assertQuerySuccessful( $response, $expected );

		$page2_nodes       = $this->lodashGet( $response, 'data.orders.nodes' );
		$last_total_page1  = (float) end( $page1_nodes )['total'];
		$first_total_page2 = (float) $page2_nodes[0]['total'];
		$this->assertGreaterThanOrEqual(
			$first_total_page2,
			$last_total_page1,
			'Cursor pagination should maintain descending total ordering across pages.'
		);
	}

	public function testCursorPaginationOrderedByDateCompleted() {
		// Set completed dates on orders.
		foreach ( $this->order_ids as $i => $order_id ) {
			$order = wc_get_order( $order_id );
			$order->set_date_completed( gmdate( 'Y-m-d H:i:s', strtotime( "+{$i} hours" ) ) );
			$order->save();
		}

		$query = '
			query ($first: Int, $after: String) {
				orders(first: $first, after: $after, where: { orderby: { field: DATE_COMPLETED, order: ASC } }) {
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

		// Page 1.
		$variables = [ 'first' => 2 ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );

		$this->assertQuerySuccessful( $response,
			[
				$this->expectedField( 'orders.nodes.0.databaseId', static::NOT_FALSY ),
				$this->expectedField( 'orders.nodes.1.databaseId', static::NOT_FALSY ),
				$this->expectedField( 'orders.pageInfo.endCursor', static::NOT_FALSY ),
			]
		);

		$page1_nodes = $this->lodashGet( $response, 'data.orders.nodes' );
		$end_cursor  = $this->lodashGet( $response, 'data.orders.pageInfo.endCursor' );

		// Page 2 — triggers COT cursor compare_with() for _date_completed column.
		$variables = [
			'first' => 2,
			'after' => $end_cursor,
		];
		$response = $this->graphql( compact( 'query', 'variables' ) );

		$this->assertQuerySuccessful(
			$response,
			array_map(
				function( $order ) {
					return $this->not()->expectedNode(
						'orders.nodes',
						[
							$this->expectedField( 'databaseId', $order['databaseId'] ),
						]
					);
				},
				$page1_nodes
			)
		);
	}
}
