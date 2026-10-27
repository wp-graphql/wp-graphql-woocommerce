<?php

use GraphQL\Deferred;
use WPGraphQL\WooCommerce\WooCommerce;

class NodeContextGlobalProductTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {
	public function tearDown(): void {
		unset( $GLOBALS['product'] );

		parent::tearDown();
	}

	/**
	 * Returns a Deferred that resolves after the given number of further Deferred hops, so the
	 * field it belongs to resolves after the rest of the query.
	 *
	 * @param int $hops Number of hops left.
	 *
	 * @return \GraphQL\Deferred
	 */
	public static function late( int $hops ) {
		return new Deferred(
			static function () use ( $hops ) {
				return $hops > 0 ? self::late( $hops - 1 ) : [];
			}
		);
	}

	/**
	 * Registers a root field that reports the global $product's ID once the rest of the query has
	 * resolved.
	 */
	private function registerLateGlobalProductField() {
		add_action(
			'graphql_register_types',
			static function () {
				register_graphql_object_type(
					'TestGlobalProductState',
					[
						'fields' => [
							'productId' => [
								'type'    => 'Int',
								'resolve' => static function () {
									return isset( $GLOBALS['product'] ) && $GLOBALS['product'] instanceof \WC_Product
										? $GLOBALS['product']->get_id()
										: null;
								},
							],
						],
					]
				);
				register_graphql_field(
					'RootQuery',
					'testGlobalProductLate',
					[
						'type'    => 'TestGlobalProductState',
						'resolve' => static function () {
							return self::late( 25 );
						},
					]
				);
			}
		);

		$this->clearSchema();
	}

	public function testProductIsRestoredWithThePostDataForProducts() {
		$product_id = $this->factory->product->createSimple();

		$globals = apply_filters( 'graphql_model_node_context_globals', [ 'post' ], new \WPGraphQL\WooCommerce\Model\Product( $product_id ) );

		$this->assertSame( [ 'post', 'product' ], $globals );
	}

	public function testProductIsRestoredWithThePostDataForPostsThatArentProducts() {
		// wc_setup_product_data() unsets $product when a post that isn't a product is set up.
		$post_id = $this->factory->post->create();

		$globals = apply_filters( 'graphql_model_node_context_globals', [ 'post' ], new \WPGraphQL\Model\Post( get_post( $post_id ) ) );

		$this->assertSame( [ 'post', 'product' ], $globals );
	}

	public function testProductIsNotAddedForModelsThatArentPosts() {
		$term_id = $this->factory->product->createProductCategory( 'node-context-category' );

		$globals = apply_filters( 'graphql_model_node_context_globals', [ 'post' ], new \WPGraphQL\Model\Term( get_term( $term_id ) ) );

		$this->assertSame( [ 'post' ], $globals );
	}

	public function testGlobalProductIsRestoredAfterTheQueryLeavesTheProducts() {
		if ( ! function_exists( 'graphql_bind_node_context' ) ) {
			$this->markTestSkipped( 'Restoring globals when resolution leaves a node needs WPGraphQL 2.27.0 or later.' );
		}

		$this->factory->product->createManySimple( 2 );
		$this->registerLateGlobalProductField();
		unset( $GLOBALS['product'] );

		$query = '
			query {
				products {
					nodes {
						databaseId
						name
					}
				}
				testGlobalProductLate {
					productId
				}
			}
		';

		// Without the filter, $product is left on one of the products once the query has left them.
		remove_filter( 'graphql_model_node_context_globals', [ WooCommerce::class, 'restore_global_product' ], 10 );
		$response = $this->graphql( compact( 'query' ) );
		add_filter( 'graphql_model_node_context_globals', [ WooCommerce::class, 'restore_global_product' ], 10, 2 );

		$this->assertQuerySuccessful(
			$response,
			[ $this->expectedField( 'testGlobalProductLate.productId', static::NOT_NULL ) ]
		);

		// With it, $product is restored to what it was before the query: unset.
		unset( $GLOBALS['product'] );
		$response = $this->graphql( compact( 'query' ) );

		$this->assertQuerySuccessful(
			$response,
			[ $this->expectedField( 'testGlobalProductLate.productId', static::IS_NULL ) ]
		);
	}
}
