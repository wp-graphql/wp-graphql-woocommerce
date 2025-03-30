<?php

class ProductsQueriesTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {
	private function createProducts() {
		$products = [
			$this->factory->product->createSimple([
				'name'          => 'Product Blue',
				'slug'          => 'product-blue',
				'description'   => 'A peach description',
				'price'         => 100,
				'regular_price' => 100,
				'sale_price'    => 90,
				'stock_status'  => 'instock',
				'stock_quantity' => 10,
				'reviews_allowed' => true,
				'average_rating' => 4.5,
			]),
			$this->factory->product->createSimple([
				'name'          => 'Product Green',
				'slug'          => 'product-green',
				'description'   => 'A turquoise description',
				'sku' 		    => 'green-sku',
				'price'         => 200,
				'regular_price' => 200,
				'sale_price'    => 180,
				'stock_status'  => 'instock',
				'stock_quantity' => 20,
				'reviews_allowed' => true,
				'average_rating' => 4.0,
			]),
			$this->factory->product->createSimple([
				'name'          => 'Product Red',
				'slug'          => 'product-red',
				'description'   => 'A maroon description',
				'price'         => 300,
				'regular_price' => 300,
				'sale_price'    => 270,
				'stock_status'  => 'instock',
				'stock_quantity' => 30,
				'reviews_allowed' => true,
				'average_rating' => 3.5,
			]),
			$this->factory->product->createSimple([
				'name'          => 'Product Yellow',
				'slug'          => 'product-yellow',
				'description'   => 'A teal description',
				'price'         => 400,
				'regular_price' => 400,
				'sale_price'    => 360,
				'stock_status'  => 'instock',
				'stock_quantity' => 40,
				'reviews_allowed' => true,
				'average_rating' => 3.0,
			]),
			$this->factory->product->createSimple([
				'name'          => 'Product Purple',
				'slug'          => 'product-purple',
				'description'   => 'A magenta description',
				'price'         => 500,
				'regular_price' => 500,
				'sale_price'    => 450,
				'stock_status'  => 'instock',
				'stock_quantity' => 50,
				'reviews_allowed' => true,
				'average_rating' => 2.5,
			]),
		];

		$order_id = $this->factory->order->createNew(
			[
				'payment_method' => 'cod',
			],
			[
				'line_items' => [
					[
						'product' => $products[0],
						'qty'     => 10,
					],
					[
						'product' => $products[1],
						'qty'     => 8,
					],
					[
						'product' => $products[2],
						'qty'     => 6,
					],
					[
						'product' => $products[3],
						'qty'     => 4,
					],
					[
						'product' => $products[4],
						'qty'     => 2,
					],
				],
			]
		);

		$order = \wc_get_order( $order_id );
		$order->calculate_totals();
		$order->update_status( 'completed' );

		wc_update_total_sales_counts( $order_id );

		$review_one = $this->factory()->comment->create(
			[
				'comment_author'       => 'Customer',
				'comment_author_email' => 'customer@example.com',
				'comment_post_ID'      => $products[0],
				'comment_content'      => 'It worked great!',
				'comment_approved'     => 1,
				'comment_type'         => 'review',
			]
		);
		update_comment_meta( $review_one, 'rating', 5.0 );
		$review_one = $this->factory()->comment->create(
			[
				'comment_author'       => 'Customer',
				'comment_author_email' => 'customer@example.com',
				'comment_post_ID'      => $products[0],
				'comment_content'      => 'It worked great!',
				'comment_approved'     => 1,
				'comment_type'         => 'review',
			]
		);
		update_comment_meta( $review_one, 'rating', 5.0 );

		$review_two = $this->factory()->comment->create(
			[
				'comment_author'       => 'Customer',
				'comment_author_email' => 'customer@example.com',
				'comment_post_ID'      => $products[2],
				'comment_content'      => 'It was basic',
				'comment_approved'     => 1,
				'comment_type'         => 'review',
			]
		);
		update_comment_meta( $review_two, 'rating', 3.0 );

		$review_three = $this->factory()->comment->create(
			[
				'comment_author'       => 'Customer',
				'comment_author_email' => 'customer@example.com',
				'comment_post_ID'      => $products[2],
				'comment_content'      => 'Overpriced',
				'comment_approved'     => 1,
				'comment_type'         => 'review',
			]
		);
		update_comment_meta( $review_three, 'rating', 2.0 );

		$review_four = $this->factory()->comment->create(
			[
				'comment_author'       => 'Customer',
				'comment_author_email' => 'customer@example.com',
				'comment_post_ID'      => $products[4],
				'comment_content'      => 'Overpriced',
				'comment_approved'     => 1,
				'comment_type'         => 'review',
			]
		);
		update_comment_meta( $review_four, 'rating', 3.5 );

		$review_five = $this->factory()->comment->create(
			[
				'comment_author'       => 'Customer',
				'comment_author_email' => 'customer@example.com',
				'comment_post_ID'      => $products[4],
				'comment_content'      => 'Overpriced and ugly',
				'comment_approved'     => 1,
				'comment_type'         => 'review',
			]
		);
		update_comment_meta( $review_five, 'rating', 2.5 );

		$review_six = $this->factory()->comment->create(
			[
				'comment_author'       => 'Customer',
				'comment_author_email' => 'customer@example.com',
				'comment_post_ID'      => $products[1],
				'comment_content'      => 'It was cheap!',
				'comment_approved'     => 1,
				'comment_type'         => 'review',
			]
		);
		update_comment_meta( $review_six, 'rating', 4.2 );
		$review_six = $this->factory()->comment->create(
			[
				'comment_author'       => 'Customer',
				'comment_author_email' => 'customer@example.com',
				'comment_post_ID'      => $products[1],
				'comment_content'      => 'It was cheap!',
				'comment_approved'     => 1,
				'comment_type'         => 'review',
			]
		);
		update_comment_meta( $review_six, 'rating', 4.2 );

		wc_update_product_lookup_tables();

		return $products;
	}    
	// Tests
    public function testProductsQueryAndWhereArgs() {
		$category_3  = $this->factory->product->createProductCategory( 'category-three' );
		$category_4  = $this->factory->product->createProductCategory( 'category-four' );
		$product_ids = [
			$this->factory->product->createSimple(
				[
					'slug'          => 'test-product-1',
					'price'         => 6000,
					'regular_price' => 6000,
				]
			),
			$this->factory->product->createSimple(
				[
					'price'         => 2,
					'regular_price' => 2,
					'category_ids'  => [ $category_3, $category_4 ],
				]
			),
			$this->factory->product->createSimple(
				[
					'featured'     => 'true',
					'category_ids' => [ $category_3 ],
				]
			),
			$this->factory->product->createExternal(),
			$this->factory->product->createSimple(
				[
					'price'             => 200,
					'regular_price'     => 300,
					'sale_price'        => 200,
					'date_on_sale_from' => ( new \DateTime( 'yesterday' ) )->format( 'Y-m-d H:i:s' ),
					'date_on_sale_to'   => ( new \DateTime( 'tomorrow' ) )->format( 'Y-m-d H:i:s' ),
					'stock_status'      => 'outofstock',
				]
			),
		];

		$query = '
			query (
				$slugIn: [String],
				$status: String,
				$category: String,
				$categoryIn: [String],
				$categoryNotIn: [String],
				$categoryId: Int,
				$categoryIdIn: [Int]
				$categoryIdNotIn: [Int]
				$type: ProductTypesEnum,
				$typeIn: [ProductTypesEnum],
				$typeNotIn: [ProductTypesEnum],
				$featured: Boolean,
				$maxPrice: Float,
				$orderby: [ProductsOrderbyInput]
				$taxonomyFilter: ProductTaxonomyInput
				$include: [Int]
				$exclude: [Int]
				$stockStatus: [StockStatusEnum]
			) {
				products( where: {
					slugIn: $slugIn,
					status: $status,
					category: $category,
					categoryIn: $categoryIn,
					categoryNotIn: $categoryNotIn,
					categoryId: $categoryId,
					categoryIdIn: $categoryIdIn,
					categoryIdNotIn: $categoryIdNotIn,
					type: $type,
					typeIn: $typeIn,
					typeNotIn: $typeNotIn,
					featured: $featured,
					maxPrice: $maxPrice,
					orderby: $orderby,
					taxonomyFilter: $taxonomyFilter
					include: $include
					exclude: $exclude
					stockStatus: $stockStatus
				} ) {
					nodes {
						id
						... on ProductWithPricing {
							databaseId
							price
						}
						... on InventoriedProduct {
							stockStatus
						}
					}
				}
			}
		';

		$all_expected_product_nodes = array_map(
			function ( $product_id ) {
				return $this->expectedNode(
					'products.nodes',
					[ $this->expectedField( 'id', $this->toRelayId( 'post', $product_id ) ) ]
				);
			},
			$product_ids
		);

		/**
		 * Assertion One
		 *
		 * Tests query with no arguments, and expect all products to be returned.
		 */
		$response = $this->graphql( compact( 'query' ) );
		$this->assertQuerySuccessful( $response, $all_expected_product_nodes );

		/**
		 * Assertion Two
		 *
		 * Tests query with "slug" where argument, and expect the product with
		 * the slug "test-product-1" to be returned.
		 */
		$variables = [ 'slugIn' => [ 'test-product-1' ] ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array_filter(
			$all_expected_product_nodes,
			static function ( $node, $index ) use ( $product_ids ) {
				$product = \wc_get_product( $product_ids[ $index ] );
				return 'test-product-1' === $product->get_slug();
			},
			ARRAY_FILTER_USE_BOTH
		);

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Three
		 *
		 * Tests query with "status" where argument, and expect the products with
		 * a status of "pending" to be returned, which there are none among the test
		 * product with that status.
		 */
		$variables = [ 'status' => 'pending' ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [ $this->expectedField( 'products.nodes', [] ) ];

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Four
		 *
		 * Tests query with "type" where argument, and expect only "simple" products
		 * to be returned.
		 */
		$variables = [ 'type' => 'SIMPLE' ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array_filter(
			$all_expected_product_nodes,
			static function ( $node, $index ) use ( $product_ids ) {
				$product = \wc_get_product( $product_ids[ $index ] );
				return 'simple' === $product->get_type();
			},
			ARRAY_FILTER_USE_BOTH
		);

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Five
		 *
		 * Tests query with "typeIn" where argument, and expect only "simple" products
		 * to be returned.
		 */
		$variables = [ 'typeIn' => [ 'SIMPLE' ] ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		// No need to reassign the $expected for this assertion.

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Six
		 *
		 * Tests query with "typeNotIn" where argument, and expect all types of products
		 * with except "simple" to be returned.
		 */
		$variables = [ 'typeNotIn' => [ 'SIMPLE' ] ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array_filter(
			$all_expected_product_nodes,
			static function ( $node, $index ) use ( $product_ids ) {
				$product = \wc_get_product( $product_ids[ $index ] );
				return 'simple' !== $product->get_type();
			},
			ARRAY_FILTER_USE_BOTH
		);

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Seven
		 *
		 * Tests query with "featured" where argument, expect only featured products
		 * to be returned.
		 */
		$variables = [ 'featured' => true ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array_filter(
			$all_expected_product_nodes,
			static function ( $node, $index ) use ( $product_ids ) {
				$product = \wc_get_product( $product_ids[ $index ] );
				return $product->get_featured();
			},
			ARRAY_FILTER_USE_BOTH
		);

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Eight
		 *
		 * Tests query with "maxPrice" where argument, and expect all product
		 * with a price of 10.00+ to be returned.
		 */
		$variables = [ 'maxPrice' => 10.00 ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array_filter(
			$all_expected_product_nodes,
			static function ( $node, $index ) use ( $product_ids ) {
				$product = \wc_get_product( $product_ids[ $index ] );
				return 10.00 >= floatval( $product->get_price() );
			},
			ARRAY_FILTER_USE_BOTH
		);

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Nine
		 *
		 * Tests query with "orderby" where argument, and expect products to
		 * be return in descending order by "price".
		 */
		$variables = [
			'orderby' => [
				[
					'field' => 'PRICE',
					'order' => 'DESC',
				],
			],
		];
		$response  = $this->graphql( compact( 'query', 'variables' ) );

		$expected = [
			$this->expectedNode(
				'products.nodes',
				[ $this->expectedField( 'id', $this->toRelayId( 'post', $product_ids[0] ) ) ],
				0
			),
			$this->expectedNode(
				'products.nodes',
				[ $this->expectedField( 'id', $this->toRelayId( 'post', $product_ids[1] ) ) ],
				4
			),
		];

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Ten
		 *
		 * Tests query with "category" where argument, and expect products in
		 * the "category-three" category to be returned.
		 */
		$variables = [ 'category' => 'category-three' ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array_filter(
			$all_expected_product_nodes,
			static function ( $node, $index ) use ( $product_ids, $category_3 ) {
				$product = \wc_get_product( $product_ids[ $index ] );
				return in_array( $category_3, $product->get_category_ids(), true );
			},
			ARRAY_FILTER_USE_BOTH
		);

		$this->assertQuerySuccessful( $response, $expected );

		$this->clearLoaderCache( 'wc_post' );

		/**
		 * Assertion Eleven
		 *
		 * Tests query with "categoryIn" where argument, and expect products in
		 * the "category-three" category to be returned.
		 */
		$variables = [ 'categoryIn' => [ 'category-three' ] ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		// No need to reassign the $expected for this assertion.

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Twelve
		 *
		 * Tests query with "categoryId" where argument, and expect products in
		 * the "category-three" category to be returned.
		 */
		$variables = [ 'categoryId' => $category_3 ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		// No need to reassign the $expected for this assertion either.

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Thirteen
		 *
		 * Tests query with "categoryNotIn" where argument, and expect all products
		 * except products in the "category-four" category to be returned.
		 */
		$variables = [ 'categoryNotIn' => [ 'category-four' ] ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array_filter(
			$all_expected_product_nodes,
			static function ( $node, $index ) use ( $product_ids, $category_4 ) {
				$product = \wc_get_product( $product_ids[ $index ] );
				return ! in_array( $category_4, $product->get_category_ids(), true );
			},
			ARRAY_FILTER_USE_BOTH
		);

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Fourteen
		 *
		 * Tests query with "categoryIdNotIn" where argument, and expect all products
		 * except products in the "category-four" category to be returned.
		 */
		$variables = [ 'categoryIdNotIn' => [ $category_4 ] ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		// No need to reassign the $expected for this assertion.

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Fifteen
		 *
		 * Tests query with "categoryIdIn" where argument, and expect products in
		 * the "category-four" category to be returned.
		 */
		$variables = [ 'categoryIdIn' => [ $category_4 ] ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array_filter(
			$all_expected_product_nodes,
			static function ( $node, $index ) use ( $product_ids, $category_4 ) {
				$product = \wc_get_product( $product_ids[ $index ] );
				return in_array( $category_4, $product->get_category_ids(), true );
			},
			ARRAY_FILTER_USE_BOTH
		);

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Sixteen
		 *
		 * Tests "taxonomyFilter" where argument
		 */
		$variables = [
			'taxonomyFilter' => [
				'relation' => 'AND',
				'filters'  => [
					[
						'taxonomy' => 'PRODUCT_CAT',
						'terms'    => [ 'category-three' ],
					],
					[
						'taxonomy' => 'PRODUCT_CAT',
						'terms'    => [ 'category-four' ],
						'operator' => 'NOT_IN',
					],
				],
			],
		];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array_filter(
			$all_expected_product_nodes,
			static function ( $node, $index ) use ( $product_ids, $category_4, $category_3 ) {
				$product = \wc_get_product( $product_ids[ $index ] );
				return ! in_array( $category_4, $product->get_category_ids(), true )
					&& in_array( $category_3, $product->get_category_ids(), true );
			},
			ARRAY_FILTER_USE_BOTH
		);

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion 17-18
		 *
		 * Tests "include" where argument
		 */
		$variables = [
			'include' => [ $product_ids[0] ],
		];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedNode(
				'products.nodes',
				[ $this->expectedField( 'id', $this->toRelayId( 'post', $product_ids[0] ) ) ]
			),
		];
		$this->assertQuerySuccessful( $response, $expected );

		$variables = [
			'include' => [ 1000 ],
		];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedField(
				'products.nodes',
				self::IS_FALSY
			),
		];
		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion 19-20
		 *
		 * Tests "exclude" where argument
		 */
		$variables = [
			'exclude' => [ $product_ids[0] ],
		];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->not()->expectedNode(
				'products.nodes',
				[ $this->expectedField( 'id', $this->toRelayId( 'post', $product_ids[0] ) ) ]
			),
		];
		$this->assertQuerySuccessful( $response, $expected );

		$variables = [ 'exclude' => $product_ids ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedField(
				'products.nodes',
				self::IS_FALSY
			),
		];
		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion 21-22
		 *
		 * Tests "stockStatus" where argument
		 */
		$variables = [ 'stockStatus' => 'IN_STOCK' ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->not()->expectedNode(
				'products.nodes',
				[ $this->expectedField( 'id', $this->toRelayId( 'post', $product_ids[4] ) ) ]
			),
		];
		$this->assertQuerySuccessful( $response, $expected );

		$variables = [ 'stockStatus' => 'OUT_OF_STOCK' ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedNode(
				'products.nodes',
				[ $this->expectedField( 'id', $this->toRelayId( 'post', $product_ids[4] ) ) ],
				0
			),
		];
		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testVariationsQueryAndWhereArgs() {
		// Create product variations.
		$products     = $this->factory->product_variation->createSome(
			$this->factory->product->createVariable()
		);
		$variation_id = $products['variations'][0];
		$id           = $this->toRelayId( 'post', $products['product'] );
		$product      = wc_get_product( $products['product'] );
		$variations   = $products['variations'];
		$prices       = $product->get_variation_prices( true );

		$query = '
            query (
                $id: ID!,
                $minPrice: Float,
                $parent: Int,
                $parentIn: [Int],
                $parentNotIn: [Int]
            ) {
                product( id: $id ) {
                    ... on VariableProduct {
                        price
                        regularPrice
                        salePrice
                        variations( where: {
                            minPrice: $minPrice,
                            parent: $parent,
                            parentIn: $parentIn,
                            parentNotIn: $parentNotIn
                        } ) {
                            nodes {
                                id
								price
                            }
                        }
                    }
                }
            }
        ';

		/**
		 * Assertion One
		 *
		 * Test query with no arguments
		 */
		$this->loginAsShopManager();
		$variables = [ 'id' => $id ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedField( 'product.variations.nodes.#.id', $this->toRelayId( 'post', $variations[0] ) ),
			$this->expectedField( 'product.variations.nodes.#.id', $this->toRelayId( 'post', $variations[1] ) ),
			$this->expectedField( 'product.variations.nodes.#.id', $this->toRelayId( 'post', $variations[2] ) ),
			$this->expectedField(
				'product.price',
				\wc_graphql_price( current( $prices['price'] ) )
					. ' - '
					. \wc_graphql_price( end( $prices['price'] ) )
			),
			$this->expectedField(
				'product.regularPrice',
				\wc_graphql_price( current( $prices['regular_price'] ) )
					. ' - '
					. \wc_graphql_price( end( $prices['regular_price'] ) )
			),
			$this->expectedField( 'product.salePrice', self::IS_NULL ),
		];

		$this->assertQuerySuccessful( $response, $expected );

		$this->clearLoaderCache( 'wc_post' );

		/**
		 * Assertion Two
		 *
		 * Test "minPrice" where argument
		 */
		$variables = [
			'id'       => $id,
			'minPrice' => 15,
		];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->not()->expectedField( 'product.variations.nodes.#.id', $this->toRelayId( 'post', $variations[0] ) ),
			$this->expectedField( 'product.variations.nodes.#.id', $this->toRelayId( 'post', $variations[1] ) ),
			$this->expectedField( 'product.variations.nodes.#.id', $this->toRelayId( 'post', $variations[2] ) ),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testProductsOrderbyArg() {
		// Create products.
		$products = $this->createProducts();

		// Query.
		$query = 'query (
			$first: Int
			$last: Int
			$after: String
			$before: String
			$orderby: [ProductsOrderbyInput]
		) {
			products(first: $first, last: $last, after: $after, before: $before, where: { orderby: $orderby }) {
				pageInfo {
					endCursor
					hasNextPage
					hasPreviousPage
					startCursor
				}
				nodes {
					id
					name
					averageRating
					reviewCount
					... on ProductWithPricing {
						databaseId
						price
					}
				}
			}
		}';

		/**
		 * Assert sorting by price functions correctly.
		 */
		$variables = [
			'first'   => 2,
			'orderby' => [
				[
					'field' => 'PRICE',
					'order' => 'ASC',
				],
			],
		];
		$response = $this->graphql( compact( 'query', 'variables' ) );
		$this->assertQuerySuccessful(
			$response,
			[
				$this->expectedNode(
					'products.nodes',
					[
						$this->expectedField( 'id', $this->toRelayId( 'post', $products[0] ) )
					],
					0
				),
				$this->expectedNode(
					'products.nodes',
					[
						$this->expectedField( 'id', $this->toRelayId( 'post', $products[1] ) )
					],
					1
				),
			],
			'Failed to sort products by price in ascending order.'
		);

		/**
		 * Assert sorting by price functions correctly w/ pagination.
		 */
		$endCursor = $this->lodashGet( $response, 'data.products.pageInfo.endCursor' );
		$this->logData( $endCursor );
		$variables = [
			'first'   => 2,
			'after'   => $endCursor,
			'orderby' => [
				[
					'field' => 'PRICE',
					'order' => 'ASC',
				],
			],
		];

		$response = $this->graphql( compact( 'query', 'variables' ) );
		$this->assertQuerySuccessful(
			$response,
			[
				$this->expectedNode(
					'products.nodes',
					[
						$this->expectedField( 'id', $this->toRelayId( 'post', $products[2] ) )
					],
					0
				),
				$this->expectedNode(
					'products.nodes',
					[
						$this->expectedField( 'id', $this->toRelayId( 'post', $products[3] ) )
					],
					1
				),
			],
			'Failed to sort products by price in ascending order with pagination.'
		);

		/**
		 * Assert sorting by popularity functions correctly.
		 */
		$variables = [
			'first'   => 2,
			'orderby' => [
				[
					'field' => 'POPULARITY',
					'order' => 'DESC',
				],
			],
		];

		$response = $this->graphql( compact( 'query', 'variables' ) );
		$this->assertQuerySuccessful(
			$response,
			[
				$this->expectedNode(
					'products.nodes',
					[
						$this->expectedField( 'id', $this->toRelayId( 'post', $products[0] ) )
					],
					0
				),
				$this->expectedNode(
					'products.nodes',
					[
						$this->expectedField( 'id', $this->toRelayId( 'post', $products[1] ) )
					],
					1
				),
			],
			'Failed to sort products by popularity in ascending order.'
		);

		/**
		 * Assert sorting by popularity functions correctly w/ pagination.
		 */
		$endCursor = $this->lodashGet( $response, 'data.products.pageInfo.endCursor' );
		$variables = [
			'first'   => 2,
			'after'   => $endCursor,
			'orderby' => [
				[
					'field' => 'POPULARITY',
					'order' => 'DESC',
				],
			],
		];
		$response = $this->graphql( compact( 'query', 'variables' ) );
		$this->assertQuerySuccessful(
			$response,
			[
				$this->expectedNode(
					'products.nodes',
					[
						$this->expectedField( 'id', $this->toRelayId( 'post', $products[2] ) )
					],
					0
				),
				$this->expectedNode(
					'products.nodes',
					[
						$this->expectedField( 'id', $this->toRelayId( 'post', $products[3] ) )
					],
					1
				),
			],
			'Failed to sort products by popularity in ascending order with pagination.'
		);

		/**
		 * Assert sorting by rating functions correctly.
		 */
		$variables = [
			'first'   => 2,
			'orderby' => [
				[
					'field' => 'RATING',
					'order' => 'DESC',
				],
			],
		];
		$response = $this->graphql( compact( 'query', 'variables' ) );
		$this->assertQuerySuccessful(
			$response,
			[
				$this->expectedNode(
					'products.nodes',
					[
						$this->expectedField( 'id', $this->toRelayId( 'post', $products[0] ) )
					],
					0
				),
				$this->expectedNode(
					'products.nodes',
					[
						$this->expectedField( 'id', $this->toRelayId( 'post', $products[1] ) )
					],
					1
				),
			],
			'Failed to sort products by rating in ascending order.'
		);

		/**
		 * Assert sorting by rating functions correctly w/ pagination.
		 */
		$endCursor = $this->lodashGet( $response, 'data.products.pageInfo.endCursor' );
		$variables = [
			'first'   => 2,
			'after'   => $endCursor,
			'orderby' => [
				[
					'field' => 'RATING',
					'order' => 'DESC',
				],
			],
		];
		$response = $this->graphql( compact( 'query', 'variables' ) );
		$this->assertQuerySuccessful(
			$response,
			[
				$this->expectedNode(
					'products.nodes',
					[
						$this->expectedField( 'id', $this->toRelayId( 'post', $products[4] ) )
					],
					0
				),
				$this->expectedNode(
					'products.nodes',
					[
						$this->expectedField( 'id', $this->toRelayId( 'post', $products[2] ) )
					],
					1
				),
			],
			'Failed to sort products by rating in ascending order with pagination.'
		);
	}

	public function testProductsSearchArg() {
		// Create products.
		$products = $this->createProducts();

		// Query.
		$query = 'query (
			$after: String,
			$search: String
		) {
			products(first: 2, after: $after, where: { search: $search }) {
				pageInfo {
					endCursor
					hasNextPage
					hasPreviousPage
					startCursor
				}
				nodes {
					id
					name
					description
					sku
					... on ProductWithPricing {
						databaseId
						price
					}
				}
			}
		}';

		/**
		 * Assert search by product title functions correctly.
		 */
		$variables = [
			'search' => 'Green',
		];
		$response = $this->graphql( compact( 'query', 'variables' ) );
		$this->assertQuerySuccessful(
			$response,
			[
				$this->expectedNode(
					'products.nodes',
					[
						$this->expectedField( 'id', $this->toRelayId( 'post', $products[1] ) )
					],
					0
				),
			],
			'Failed to search products by product title.'
		);

		/**
		 * Assert search by product sku.
		 */
		$variables = [ 'search' => 'green-sku' ];
		$response = $this->graphql( compact( 'query', 'variables' ) );
		$this->assertQuerySuccessful(
			$response,
			[
				$this->expectedNode(
					'products.nodes',
					[
						$this->expectedField( 'id', $this->toRelayId( 'post', $products[1] ) )
					],
					0
				),
			],
			'Failed to search products by product sku.'
		);

		// Search by product description.
		$variables = [ 'search' => 'magenta' ];
		$response = $this->graphql( compact( 'query', 'variables' ) );
		$this->assertQuerySuccessful(
			$response,
			[
				$this->expectedNode(
					'products.nodes',
					[
						$this->expectedField( 'id', $this->toRelayId( 'post', $products[4] ) )
					],
					0
				),
			],
			'Failed to search products by product description content.'
		);

		// Search by slug.
		$variables = [ 'search' => 'product-red' ];
		$response = $this->graphql( compact( 'query', 'variables' ) );
		$this->assertQuerySuccessful(
			$response,
			[
				$this->expectedNode(
					'products.nodes',
					[
						$this->expectedField( 'id', $this->toRelayId( 'post', $products[2] ) )
					],
					0
				),
			],
			'Failed to search products by product slug.'
		);
	}
}
