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
		 * Assertion Seventeen
		 *
		 * Tests "taxonomyFilter" with new "or" syntax
		 */
		$variables = [
			'taxonomyFilter' => [
				'or' => [
					[
						'taxonomy' => 'PRODUCT_CAT',
						'terms'    => [ 'category-three' ],
					],
					[
						'taxonomy' => 'PRODUCT_CAT',
						'terms'    => [ 'category-four' ],
					],
				],
			],
		];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array_filter(
			$all_expected_product_nodes,
			static function ( $node, $index ) use ( $product_ids, $category_4, $category_3 ) {
				$product = \wc_get_product( $product_ids[ $index ] );
				return in_array( $category_4, $product->get_category_ids(), true )
					|| in_array( $category_3, $product->get_category_ids(), true );
			},
			ARRAY_FILTER_USE_BOTH
		);
		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Eighteen  
		 *
		 * Tests "taxonomyFilter" with new "and" syntax
		 */
		$variables = [
			'taxonomyFilter' => [
				'and' => [
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
		 * Assertion 19-20
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

	public function testProductsQueryWithNewTaxonomyFilterSyntax() {
		// Create test categories using WooCommerce factory
		$category1 = $this->factory->product->createProductCategory('electronics');
		$category2 = $this->factory->product->createProductCategory('clothing');
		$category3 = $this->factory->product->createProductCategory('books');

		$products = [
			// Product in Electronics category
			$this->factory->product->createSimple([
				'name'         => 'Laptop',
				'category_ids' => [$category1],
			]),
			// Product in Clothing category
			$this->factory->product->createSimple([
				'name'         => 'T-shirt', 
				'category_ids' => [$category2],
			]),
			// Product in both Electronics and Books categories
			$this->factory->product->createSimple([
				'name'         => 'E-book Reader',
				'category_ids' => [$category1, $category3],
			]),
			// Product in Books category only
			$this->factory->product->createSimple([
				'name'         => 'Novel',
				'category_ids' => [$category3],
			]),
		];

		// Query using new "or" syntax
		$query = '
			query testProductsWithTaxonomyOr($taxonomyFilter: ProductTaxonomyInput) {
				products(where: {taxonomyFilter: $taxonomyFilter}) {
					nodes {
						id
						databaseId
						name
					}
				}
			}
		';

		// Test OR syntax - should return products from Electronics OR Books
		$variables = [
			'taxonomyFilter' => [
				'or' => [
					[
						'taxonomy' => 'PRODUCT_CAT',
						'ids'      => [$category1],
						'operator' => 'IN',
					],
					[
						'taxonomy' => 'PRODUCT_CAT',
						'ids'      => [$category3],
						'operator' => 'IN',
					],
				],
			],
		];

		$response = $this->graphql( compact( 'query', 'variables' ) );
		
		$expected = [
			$this->expectedNode(
				'products.nodes',
				[
					$this->expectedField( 'id', $this->toRelayId( 'post', $products[0] ) ),
					$this->expectedField( 'name', 'Laptop' ),
				]
			),
			$this->expectedNode(
				'products.nodes',
				[
					$this->expectedField( 'id', $this->toRelayId( 'post', $products[2] ) ),
					$this->expectedField( 'name', 'E-book Reader' ),
				]
			),
			$this->expectedNode(
				'products.nodes',
				[
					$this->expectedField( 'id', $this->toRelayId( 'post', $products[3] ) ),
					$this->expectedField( 'name', 'Novel' ),
				]
			),
		];

		$this->assertQuerySuccessful( $response, $expected, 'OR taxonomy filter should work correctly' );

		// Test AND syntax - should return products that have BOTH Electronics AND Books
		$variables = [
			'taxonomyFilter' => [
				'and' => [
					[
						'taxonomy' => 'PRODUCT_CAT',
						'ids'      => [$category1],
						'operator' => 'IN',
					],
					[
						'taxonomy' => 'PRODUCT_CAT',
						'ids'      => [$category3],
						'operator' => 'IN',
					],
				],
			],
		];

		$response = $this->graphql( compact( 'query', 'variables' ) );
		
		$expected = [
			$this->expectedNode(
				'products.nodes',
				[
					$this->expectedField( 'id', $this->toRelayId( 'post', $products[2] ) ),
					$this->expectedField( 'name', 'E-book Reader' ),
				]
			),
		];

		$this->assertQuerySuccessful( $response, $expected, 'AND taxonomy filter should work correctly' );
	}

	public function testProductsQueryWithLegacyTaxonomyFilterSyntax() {
		// Create test categories using WooCommerce factory
		$category1 = $this->factory->product->createProductCategory('legacy-electronics');
		$category2 = $this->factory->product->createProductCategory('legacy-clothing');

		$products = [
			$this->factory->product->createSimple([
				'name'         => 'Legacy Laptop',
				'category_ids' => [$category1],
			]),
			$this->factory->product->createSimple([
				'name'         => 'Legacy T-shirt',
				'category_ids' => [$category2],
			]),
		];

		// Test legacy syntax still works
		$query = '
			query testProductsWithLegacyTaxonomy($taxonomyFilter: ProductTaxonomyInput) {
				products(where: {taxonomyFilter: $taxonomyFilter}) {
					nodes {
						id
						databaseId
						name
					}
				}
			}
		';

		$variables = [
			'taxonomyFilter' => [
				'relation' => 'OR',
				'filters' => [
					[
						'taxonomy' => 'PRODUCT_CAT',
						'ids'      => [$category1],
						'operator' => 'IN',
					],
					[
						'taxonomy' => 'PRODUCT_CAT',
						'ids'      => [$category2],
						'operator' => 'IN',
					],
				],
			],
		];

		$response = $this->graphql( compact( 'query', 'variables' ) );
		
		$expected = [
			$this->expectedNode(
				'products.nodes',
				[
					$this->expectedField( 'id', $this->toRelayId( 'post', $products[0] ) ),
					$this->expectedField( 'name', 'Legacy Laptop' ),
				]
			),
			$this->expectedNode(
				'products.nodes',
				[
					$this->expectedField( 'id', $this->toRelayId( 'post', $products[1] ) ),
					$this->expectedField( 'name', 'Legacy T-shirt' ),
				]
			),
		];

		$this->assertQuerySuccessful( $response, $expected, 'Legacy taxonomy filter syntax should work correctly' );
	}

	public function testTaxonomyFilterPriority() {
		// Test that new syntax takes priority over legacy syntax
		$category1 = $this->factory->product->createProductCategory('priority-electronics');
		$category2 = $this->factory->product->createProductCategory('priority-clothing');

		$products = [
			$this->factory->product->createSimple([
				'name'         => 'Priority Laptop',
				'category_ids' => [$category1],
			]),
			$this->factory->product->createSimple([
				'name'         => 'Priority T-shirt',
				'category_ids' => [$category2],
			]),
		];

		$query = '
			query testTaxonomyPriority($taxonomyFilter: ProductTaxonomyInput) {
				products(where: {taxonomyFilter: $taxonomyFilter}) {
					nodes {
						id
						databaseId
						name
					}
				}
			}
		';

		// Use both new "or" syntax and legacy "filters" syntax - "or" should take priority
		$variables = [
			'taxonomyFilter' => [
				'or' => [
					[
						'taxonomy' => 'PRODUCT_CAT',
						'ids'      => [$category1],
						'operator' => 'IN',
					],
				],
				'relation' => 'OR',
				'filters' => [
					[
						'taxonomy' => 'PRODUCT_CAT',
						'ids'      => [$category2],
						'operator' => 'IN',
					],
				],
			],
		];

		$response = $this->graphql( compact( 'query', 'variables' ) );
		
		$expected = [
			$this->expectedNode(
				'products.nodes',
				[
					$this->expectedField( 'id', $this->toRelayId( 'post', $products[0] ) ),
					$this->expectedField( 'name', 'Priority Laptop' ),
				]
			),
		];

		$this->assertQuerySuccessful( $response, $expected, 'New OR syntax should take priority over legacy filters syntax' );
	}

	/**
	 * Test taxonomyFilter with multiple terms in a single filter entry.
	 *
	 * Reproduces the scenario from #821 where passing multiple terms like
	 * terms: ["zielony", "kremowy"] only returned products for the first term.
	 *
	 * @see https://github.com/wp-graphql/wp-graphql-woocommerce/issues/821
	 */
	public function testTaxonomyFilterWithMultipleTermsInSingleFilter() {
		// Create the color attribute with all terms.
		$color_attr = $this->factory->product->createAttribute( 'color', [ 'red', 'blue', 'green' ] );

		// Get individual term IDs.
		$red_term_id   = get_term_by( 'slug', 'red', 'pa_color' )->term_id;
		$blue_term_id  = get_term_by( 'slug', 'blue', 'pa_color' )->term_id;
		$green_term_id = get_term_by( 'slug', 'green', 'pa_color' )->term_id;

		// Product with red + blue.
		$red_blue_product = $this->factory->product->createVariable(
			[
				'attribute_data' => [
					[
						'attribute_id'       => $color_attr['attribute_id'],
						'attribute_taxonomy' => $color_attr['attribute_taxonomy'],
						'term_ids'           => [ $red_term_id, $blue_term_id ],
					],
				],
			]
		);
		$this->factory->product_variation->create(
			[
				'parent_id'     => $red_blue_product,
				'attributes'    => [ 'pa_color' => 'red' ],
				'image_id'      => null,
				'regular_price' => 10,
			]
		);

		// Product with blue + green.
		$blue_green_product = $this->factory->product->createVariable(
			[
				'attribute_data' => [
					[
						'attribute_id'       => $color_attr['attribute_id'],
						'attribute_taxonomy' => $color_attr['attribute_taxonomy'],
						'term_ids'           => [ $blue_term_id, $green_term_id ],
					],
				],
			]
		);
		$this->factory->product_variation->create(
			[
				'parent_id'     => $blue_green_product,
				'attributes'    => [ 'pa_color' => 'green' ],
				'image_id'      => null,
				'regular_price' => 15,
			]
		);

		// Product with only green.
		$green_product = $this->factory->product->createVariable(
			[
				'attribute_data' => [
					[
						'attribute_id'       => $color_attr['attribute_id'],
						'attribute_taxonomy' => $color_attr['attribute_taxonomy'],
						'term_ids'           => [ $green_term_id ],
					],
				],
			]
		);
		$this->factory->product_variation->create(
			[
				'parent_id'     => $green_product,
				'attributes'    => [ 'pa_color' => 'green' ],
				'image_id'      => null,
				'regular_price' => 20,
			]
		);

		$this->clearSchema();

		$query = '
			query ($taxonomyFilter: ProductTaxonomyInput) {
				products(where: { taxonomyFilter: $taxonomyFilter }) {
					nodes {
						databaseId
					}
				}
			}
		';

		// Pass multiple terms in a single filter — should return products matching ANY of the terms.
		// red + blue should match red_blue_product and blue_green_product (both have blue).
		$variables = [
			'taxonomyFilter' => [
				'filters' => [
					[
						'taxonomy' => 'PA_COLOR',
						'terms'    => [ 'red', 'blue' ],
					],
				],
			],
		];

		$response = $this->graphql( compact( 'query', 'variables' ) );
		$expected = [
			$this->expectedField( 'products.nodes.#.databaseId', $red_blue_product ),
			$this->expectedField( 'products.nodes.#.databaseId', $blue_green_product ),
			$this->not()->expectedField( 'products.nodes.#.databaseId', $green_product ),
		];

		$this->assertQuerySuccessful( $response, $expected );

		// Also test with the new "or" syntax — red only should match red_blue_product.
		$variables = [
			'taxonomyFilter' => [
				'or' => [
					[
						'taxonomy' => 'PA_COLOR',
						'terms'    => [ 'red' ],
					],
				],
			],
		];

		$response = $this->graphql( compact( 'query', 'variables' ) );
		$expected = [
			$this->expectedField( 'products.nodes.#.databaseId', $red_blue_product ),
			$this->not()->expectedField( 'products.nodes.#.databaseId', $blue_green_product ),
			$this->not()->expectedField( 'products.nodes.#.databaseId', $green_product ),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}

	/**
	 * Test taxonomyFilter with multiple terms across multiple taxonomies.
	 *
	 * @see https://github.com/wp-graphql/wp-graphql-woocommerce/issues/821
	 */
	public function testTaxonomyFilterWithMultipleTermsAcrossMultipleTaxonomies() {
		$shade_attr  = $this->factory->product->createAttribute( 'shade', [ 'light', 'dark' ] );
		$weight_attr = $this->factory->product->createAttribute( 'weight', [ 'light-weight', 'heavy-weight' ] );

		$light_term  = get_term_by( 'slug', 'light', 'pa_shade' )->term_id;
		$dark_term   = get_term_by( 'slug', 'dark', 'pa_shade' )->term_id;
		$lw_term     = get_term_by( 'slug', 'light-weight', 'pa_weight' )->term_id;
		$hw_term     = get_term_by( 'slug', 'heavy-weight', 'pa_weight' )->term_id;

		// light shade + light-weight
		$product_a = $this->factory->product->createVariable(
			[
				'attribute_data' => [
					[
						'attribute_id'       => $shade_attr['attribute_id'],
						'attribute_taxonomy' => $shade_attr['attribute_taxonomy'],
						'term_ids'           => [ $light_term ],
					],
					[
						'attribute_id'       => $weight_attr['attribute_id'],
						'attribute_taxonomy' => $weight_attr['attribute_taxonomy'],
						'term_ids'           => [ $lw_term ],
					],
				],
			]
		);
		$this->factory->product_variation->create(
			[
				'parent_id'     => $product_a,
				'attributes'    => [ 'pa_shade' => 'light', 'pa_weight' => 'light-weight' ],
				'image_id'      => null,
				'regular_price' => 10,
			]
		);

		// dark shade + heavy-weight
		$product_b = $this->factory->product->createVariable(
			[
				'attribute_data' => [
					[
						'attribute_id'       => $shade_attr['attribute_id'],
						'attribute_taxonomy' => $shade_attr['attribute_taxonomy'],
						'term_ids'           => [ $dark_term ],
					],
					[
						'attribute_id'       => $weight_attr['attribute_id'],
						'attribute_taxonomy' => $weight_attr['attribute_taxonomy'],
						'term_ids'           => [ $hw_term ],
					],
				],
			]
		);
		$this->factory->product_variation->create(
			[
				'parent_id'     => $product_b,
				'attributes'    => [ 'pa_shade' => 'dark', 'pa_weight' => 'heavy-weight' ],
				'image_id'      => null,
				'regular_price' => 20,
			]
		);

		// light shade + heavy-weight
		$product_c = $this->factory->product->createVariable(
			[
				'attribute_data' => [
					[
						'attribute_id'       => $shade_attr['attribute_id'],
						'attribute_taxonomy' => $shade_attr['attribute_taxonomy'],
						'term_ids'           => [ $light_term ],
					],
					[
						'attribute_id'       => $weight_attr['attribute_id'],
						'attribute_taxonomy' => $weight_attr['attribute_taxonomy'],
						'term_ids'           => [ $hw_term ],
					],
				],
			]
		);
		$this->factory->product_variation->create(
			[
				'parent_id'     => $product_c,
				'attributes'    => [ 'pa_shade' => 'light', 'pa_weight' => 'heavy-weight' ],
				'image_id'      => null,
				'regular_price' => 30,
			]
		);

		$this->clearSchema();

		$query = '
			query ($taxonomyFilter: ProductTaxonomyInput) {
				products(where: { taxonomyFilter: $taxonomyFilter }) {
					nodes {
						databaseId
					}
				}
			}
		';

		// AND: shade IN (light) AND weight IN (heavy-weight) — should return only product_c.
		$variables = [
			'taxonomyFilter' => [
				'and' => [
					[
						'taxonomy' => 'PA_SHADE',
						'terms'    => [ 'light' ],
					],
					[
						'taxonomy' => 'PA_WEIGHT',
						'terms'    => [ 'heavy-weight' ],
					],
				],
			],
		];

		$response = $this->graphql( compact( 'query', 'variables' ) );
		$expected = [
			$this->expectedField( 'products.nodes.#.databaseId', $product_c ),
			$this->not()->expectedField( 'products.nodes.#.databaseId', $product_a ),
			$this->not()->expectedField( 'products.nodes.#.databaseId', $product_b ),
		];

		$this->assertQuerySuccessful( $response, $expected );

		// OR: shade IN (dark) OR weight IN (light-weight) — should return product_a and product_b.
		$variables = [
			'taxonomyFilter' => [
				'or' => [
					[
						'taxonomy' => 'PA_SHADE',
						'terms'    => [ 'dark' ],
					],
					[
						'taxonomy' => 'PA_WEIGHT',
						'terms'    => [ 'light-weight' ],
					],
				],
			],
		];

		$response = $this->graphql( compact( 'query', 'variables' ) );
		$expected = [
			$this->expectedField( 'products.nodes.#.databaseId', $product_a ),
			$this->expectedField( 'products.nodes.#.databaseId', $product_b ),
			$this->not()->expectedField( 'products.nodes.#.databaseId', $product_c ),
		];

		$this->assertQuerySuccessful( $response, $expected );

		// AND with multiple terms: shade IN (light, dark) AND weight IN (heavy-weight)
		// — should return product_b and product_c.
		$variables = [
			'taxonomyFilter' => [
				'and' => [
					[
						'taxonomy' => 'PA_SHADE',
						'terms'    => [ 'light', 'dark' ],
					],
					[
						'taxonomy' => 'PA_WEIGHT',
						'terms'    => [ 'heavy-weight' ],
					],
				],
			],
		];

		$response = $this->graphql( compact( 'query', 'variables' ) );
		$expected = [
			$this->expectedField( 'products.nodes.#.databaseId', $product_b ),
			$this->expectedField( 'products.nodes.#.databaseId', $product_c ),
			$this->not()->expectedField( 'products.nodes.#.databaseId', $product_a ),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}

	/**
	 * Test basic products query with categoryId, status, visibility, and pagination.
	 *
	 * Mirrors the exact query pattern from #891 that stopped returning products.
	 *
	 * @see https://github.com/wp-graphql/wp-graphql-woocommerce/issues/891
	 */
	public function testProductsQueryWithCategoryStatusVisibilityAndPagination() {
		$category = $this->factory->product->createProductCategory( 'wetsuits' );

		// Create 3 published products in the category.
		$product_ids = [];
		for ( $i = 0; $i < 3; $i++ ) {
			$product_ids[] = $this->factory->product->createSimple(
				[
					'category_ids' => [ $category ],
					'status'       => 'publish',
				]
			);
		}

		// Create a draft product in the same category — should not appear.
		$draft_product = $this->factory->product->createSimple(
			[
				'category_ids' => [ $category ],
				'status'       => 'draft',
			]
		);

		$query = '
			query ($categoryId: Int, $first: Int, $after: String) {
				products(
					where: {
						categoryId: $categoryId
						status: "publish"
						visibility: VISIBLE
					}
					first: $first
					after: $after
				) {
					nodes {
						... on Product {
							databaseId
							name
							status
						}
					}
					pageInfo {
						endCursor
						hasNextPage
					}
				}
			}
		';

		// First page — request 2 of 3 products.
		$variables = [
			'categoryId' => $category,
			'first'      => 2,
		];

		$response = $this->graphql( compact( 'query', 'variables' ) );
		$expected = [
			$this->not()->expectedField( 'products.nodes.#.databaseId', $draft_product ),
		];
		$this->assertQuerySuccessful( $response, $expected );

		$nodes = $this->lodashGet( $response, 'data.products.nodes', [] );
		$this->assertCount( 2, $nodes, 'Should return 2 products on first page.' );

		$has_next = $this->lodashGet( $response, 'data.products.pageInfo.hasNextPage' );
		$this->assertTrue( $has_next, 'Should have a next page.' );

		// Second page.
		$end_cursor = $this->lodashGet( $response, 'data.products.pageInfo.endCursor' );
		$variables  = [
			'categoryId' => $category,
			'first'      => 2,
			'after'      => $end_cursor,
		];

		$response = $this->graphql( compact( 'query', 'variables' ) );
		$this->assertQuerySuccessful( $response, [] );

		$nodes = $this->lodashGet( $response, 'data.products.nodes', [] );
		$this->assertCount( 1, $nodes, 'Should return 1 product on second page.' );

		$has_next = $this->lodashGet( $response, 'data.products.pageInfo.hasNextPage' );
		$this->assertFalse( $has_next, 'Should not have a next page.' );

		// All returned products should be from the category.
		foreach ( $product_ids as $id ) {
			$product = wc_get_product( $id );
			$this->assertContains( $category, $product->get_category_ids() );
		}
	}
}
