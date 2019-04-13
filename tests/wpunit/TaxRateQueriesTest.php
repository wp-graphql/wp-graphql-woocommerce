<?php

class TaxQueriesTest extends \Codeception\TestCase\WPTestCase {

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
	public function testTaxQuery() {
		$query = '
			query {
				tax(id: "") {
					id
					country
					state
					postcode
					city
					rate
					name
					priority
					compound
					shipping
					order
					class
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

	public function testTaxesQuery() {
		$query = '
			query {
				taxes() {
					nodes {
						id
						country
						state
						postcode
						city
						rate
						name
						priority
						compound
						shipping
						order
						class
					}
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
