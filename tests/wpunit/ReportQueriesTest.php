<?php

class ReportQueriesTest extends \Codeception\TestCase\WPTestCase {

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
	}

	public function tearDown() {
		// your tear down methods here
		// then
		parent::tearDown();
	}

	// tests
	public function testSalesReportQuery() {
		$query = '
			query {
				report(type: "sales") {
					totalSales
					netSales
					averageSales
					totalOrder
					totalItems
					totalTax
					totalShipping
					totalRefunds
					totalDiscount
					totalsGroupedBy
					totals
				}
			}
		';

		$actual = do_graphql_request( $query );

		/**
		 * use --debug flag to view
		 */
		\Codeception\Util\Debug::debug( $actual );

		$expected = [];

		$this->assertEquals( $expected, $actual );
	}

	public function testTopSellersReportQuery() {
		$query = '
			query {
				report(type: "top") {
					title
					product {
						id
					}
					quantity
				}
			}
		';

		$actual = do_graphql_request( $query );

		/**
		 * use --debug flag to view
		 */
		\Codeception\Util\Debug::debug( $actual );

		$expected = [];

		$this->assertEquals( $expected, $actual );
	}

	public function testCouponsTotalsQuery() {
		$query = '
			query {
				report(type: "coupon") {
					slug
					name
					total
				}
			}
		';

		$actual = do_graphql_request( $query );

		/**
		 * use --debug flag to view
		 */
		\Codeception\Util\Debug::debug( $actual );

		$expected = [];

		$this->assertEquals( $expected, $actual );
	}

	public function testCustomersTotalsQuery() {
		$query = '
			query {
				report(type: "customer") {
					slug
					name
					total
				}
			}
		';

		$actual = do_graphql_request( $query );

		/**
		 * use --debug flag to view
		 */
		\Codeception\Util\Debug::debug( $actual );

		$expected = [];

		$this->assertEquals( $expected, $actual );
	}

	public function testOrdersTotalsQuery() {
		$query = '
			query {
				report(type: "order") {
					slug
					name
					total
				}
			}
		';

		$actual = do_graphql_request( $query );

		/**
		 * use --debug flag to view
		 */
		\Codeception\Util\Debug::debug( $actual );

		$expected = [];

		$this->assertEquals( $expected, $actual );
	}

	public function testProductsTotalsQuery() {
		 $query = '
			query {
				report(type: "product") {
					slug
					name
					total
				}
			}
		';

		$actual = do_graphql_request( $query );

		/**
		 * use --debug flag to view
		 */
		\Codeception\Util\Debug::debug( $actual );

		$expected = [];

		$this->assertEquals( $expected, $actual );
	}

	public function testReviewsTotalsQuery() {
		$query = '
			query {
				report(type: "review") {
					slug
					name
					total
				}
			}
		';

		$actual = do_graphql_request( $query );

		/**
		 * use --debug flag to view
		 */
		\Codeception\Util\Debug::debug( $actual );

		$expected = [];

		$this->assertEquals( $expected, $actual );
	}
}
