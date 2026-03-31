<?php

/**
 * Tests performance of querying variable products with many variations.
 *
 * @see https://github.com/wp-graphql/wp-graphql-woocommerce/issues/897
 */
class VariableProductPerformanceTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {
	private $product_ids = [];

	public function setUp(): void {
		parent::setUp();

		// Create 15 variable products each with 20+ variations.
		for ( $i = 0; $i < 15; $i++ ) {
			$product_id = $this->factory->product->createVariable();
			$product    = wc_get_product( $product_id );

			// createVariable creates size (small/medium/large), color (red/blue/green), logo (Yes/No)
			// That's 3x3x2 = 18 combinations. createSome only creates a few.
			// Let's create all 18 variations + a few extra with different prices.
			$sizes  = [ 'small', 'medium', 'large' ];
			$colors = [ 'red', 'blue', 'green' ];
			$logos  = [ 'Yes', 'No' ];

			$variation_count = 0;
			foreach ( $sizes as $size ) {
				foreach ( $colors as $color ) {
					foreach ( $logos as $logo ) {
						$regular_price = 10 + $variation_count;
						$sale_price    = $variation_count % 3 === 0 ? $regular_price - 2 : '';

						$this->factory->product_variation->create(
							[
								'parent_id'     => $product_id,
								'attributes'    => [
									'pa_size'  => $size,
									'pa_color' => $color,
									'logo'     => $logo,
								],
								'regular_price' => $regular_price,
								'sale_price'    => $sale_price,
							]
						);
						$variation_count++;
					}
				}
			}

			// Clear transients so prices are recalculated.
			delete_transient( 'wc_var_prices_' . $product_id );
			wc_delete_product_transients( $product_id );

			$this->product_ids[] = $product_id;
		}
	}

	/**
	 * Test that querying many variable products with pricing fields completes
	 * in a reasonable time and does not generate excessive DB queries.
	 */
	public function testQueryManyVariableProductsWithPricing() {
		$query = '
			query {
				products(first: 15, where: { type: VARIABLE }) {
					nodes {
						... on VariableProduct {
							databaseId
							name
							price
							regularPrice
							salePrice
							priceRaw: price(format: RAW)
							regularPriceRaw: regularPrice(format: RAW)
							salePriceRaw: salePrice(format: RAW)
							variations(first: 5) {
								nodes {
									databaseId
									price
									regularPrice
									salePrice
								}
							}
						}
					}
				}
			}
		';

		// Track DB queries.
		global $wpdb;
		$wpdb->queries = [];
		if ( ! defined( 'SAVEQUERIES' ) ) {
			define( 'SAVEQUERIES', true );
		}

		$start    = microtime( true );
		$response = $this->graphql( [ 'query' => $query ] );
		$duration = microtime( true ) - $start;

		$query_count = count( $wpdb->queries );

		codecept_debug( "Query duration: {$duration}s" );
		codecept_debug( "DB queries: {$query_count}" );

		// Categorize queries for analysis.
		$categories = [
			'variation_children' => 0,
			'variation_objects'  => 0,
			'product_objects'    => 0,
			'options'            => 0,
			'template'           => 0,
			'hpos'               => 0,
			'other'              => 0,
		];
		foreach ( $wpdb->queries as $q ) {
			$sql = $q[0];
			if ( stripos( $sql, 'post_type = \'product_variation\'' ) !== false && stripos( $sql, 'post_parent' ) !== false && stripos( $sql, 'wp_posts.ID' ) !== false && stripos( $sql, 'IN' ) === false ) {
				$categories['variation_children']++;
			} elseif ( stripos( $sql, 'product_variation' ) !== false || ( stripos( $sql, 'post_parent =' ) !== false && stripos( $sql, 'wp_posts.ID IN' ) !== false ) ) {
				$categories['variation_objects']++;
			} elseif ( stripos( $sql, 'wp_template' ) !== false ) {
				$categories['template']++;
			} elseif ( stripos( $sql, 'shop_order' ) !== false ) {
				$categories['hpos']++;
			} elseif ( stripos( $sql, 'wp_options' ) !== false ) {
				$categories['options']++;
			} elseif ( stripos( $sql, 'wp_posts' ) !== false ) {
				$categories['product_objects']++;
			} else {
				$categories['other']++;
			}
		}
		codecept_debug( 'Query categories: ' . wp_json_encode( $categories ) );


		// Verify the response is successful.
		$this->assertArrayHasKey( 'data', $response );
		$this->assertArrayHasKey( 'products', $response['data'] );

		$nodes = $response['data']['products']['nodes'];
		$this->assertCount( 15, $nodes );

		// Verify pricing fields are resolved.
		foreach ( $nodes as $node ) {
			$this->assertNotEmpty( $node['databaseId'] );
			$this->assertNotNull( $node['price'] );
			$this->assertNotNull( $node['regularPrice'] );
		}

		// Performance assertion: should complete within 5 seconds.
		$this->assertLessThan( 5.0, $duration, "Query took {$duration}s — too slow for 15 variable products." );

		// DB query count guard: prevents regressions. The ~212 query baseline
		// includes WP template lookups, HPOS compat checks, and WC transient
		// reads that are outside our control. This threshold catches N+1 regressions.
		$this->assertLessThan( 250, $query_count, "Generated {$query_count} DB queries — possible N+1 regression." );
	}
}
