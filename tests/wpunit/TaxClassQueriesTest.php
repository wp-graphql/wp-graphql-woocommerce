<?php

class TaxClassQueriesTest extends \Codeception\TestCase\WPTestCase {

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
	public function testTaxClassQuery() {
		$query = '
			query {
				taxClass(id "") {
					slug
					name
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

	public function testTaxClassesQuery() {
		 $query = '
			query {
				taxClasses() {
					nodes {
						slug
						name
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
