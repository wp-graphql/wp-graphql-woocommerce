<?php

/**
 * Tests that querying variable products with many variations does not
 * trigger 429 errors from excessive response times or resource usage.
 *
 * @see https://github.com/wp-graphql/wp-graphql-woocommerce/issues/897
 */
class VariableProductPerformanceCest {
	public function _before( FunctionalTester $I ) {
		$I->createVariableProductCatalog( 10, 18 );
	}

	/**
	 * Test that sending multiple rapid product queries does not return 429 errors.
	 */
	public function testRapidVariableProductQueriesDoNotReturn429( FunctionalTester $I ) {
		$query = '
			query {
				products(first: 10, where: { type: VARIABLE }) {
					nodes {
						... on VariableProduct {
							databaseId
							name
							price
							regularPrice
							salePrice
							variations(first: 5) {
								nodes {
									databaseId
									price
									regularPrice
								}
							}
						}
					}
				}
			}
		';

		// Fire 6 rapid queries — the reporter says 429 hits at ~6-7 executions.
		$error_count = 0;
		for ( $i = 0; $i < 6; $i++ ) {
			$response = $I->sendGraphQLRequest( $query, null );

			if ( empty( $response ) || ! isset( $response['data'] ) ) {
				$error_count++;
				codecept_debug( "Request {$i} failed or returned non-JSON response." );
				continue;
			}

			$I->assertArrayHasKey( 'products', $response['data'] );
			$nodes = $response['data']['products']['nodes'];
			$I->assertNotEmpty( $nodes, "Request {$i} returned empty products." );
		}

		$I->assertEquals( 0, $error_count, "{$error_count} of 6 rapid queries returned errors (likely 429)." );
	}
}
