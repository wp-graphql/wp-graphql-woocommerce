<?php
class ProductQueriesTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {

	public function getExpectedProductData( $product_id ) {
		$product         = \wc_get_product( $product_id );
		$is_shop_manager = false;
		$user            = wp_get_current_user();
		if ( $user && in_array( 'shop_manager', (array) $user->roles, true ) ) {
			$is_shop_manager = true;
		}

		return [
			$this->expectedField( 'product.id', $this->toRelayId( 'product', $product_id ) ),
			$this->expectedField( 'product.databaseId', $product->get_id() ),
			$this->expectedField( 'product.name', $product->get_name() ),
			$this->expectedField( 'product.slug', $product->get_slug() ),
			$this->expectedField( 'product.date', $product->get_date_created()->__toString() ),
			$this->expectedField( 'product.status', $product->get_status() ),
			$this->expectedField( 'product.featured', $product->get_featured() ),
			$this->expectedField(
				'product.description',
				$this->maybe(
					[ $product->get_description(), apply_filters( 'the_content', $product->get_description() ) ],
					self::IS_NULL
				)
			),
			$this->expectedField(
				'product.shortDescription',
				$this->maybe(
					[
						$product->get_short_description(),
						apply_filters(
							'get_the_excerpt',
							apply_filters( 'the_excerpt', $product->get_short_description() )
						),
					],
					self::IS_NULL
				)
			),
			$this->expectedField( 'product.sku', $product->get_sku() ),
			$this->expectedField(
				'product.price',
				$this->maybe(
					[ $product->get_price(), \wc_graphql_price( $product->get_price() ) ],
					self::IS_NULL
				)
			),
			$this->expectedField(
				'product.regularPrice',
				$this->maybe(
					[ $product->get_regular_price(), \wc_graphql_price( $product->get_regular_price() ) ],
					self::IS_NULL
				)
			),
			$this->expectedField(
				'product.salePrice',
				$this->maybe(
					[ $product->get_sale_price(), \wc_graphql_price( $product->get_sale_price() ) ],
					self::IS_NULL
				)
			),
			$this->expectedField(
				'product.dateOnSaleFrom',
				$this->maybe( $product->get_date_on_sale_from(), self::IS_NULL )
			),
			$this->expectedField(
				'product.dateOnSaleTo',
				$this->maybe( $product->get_date_on_sale_to(), self::IS_NULL )
			),
			$this->expectedField(
				'product.taxStatus',
				$this->maybe( strtoupper( $product->get_tax_status() ), self::IS_NULL )
			),
			$this->expectedField(
				'product.taxClass',
				$this->maybe( $product->get_tax_class(), 'STANDARD' )
			),
			$this->expectedField( 'product.manageStock', $product->get_manage_stock() ),
			$this->expectedField(
				'product.stockQuantity',
				$this->maybe( $product->get_stock_quantity(), self::IS_NULL )
			),
			$this->expectedField(
				'product.stockStatus',
				$this->maybe(
					$this->factory->product->getStockStatusEnum( $product->get_stock_status() ),
					self::IS_NULL
				)
			),
			$this->expectedField(
				'product.backorders',
				$this->maybe(
					\WPGraphQL\Type\WPEnumType::get_safe_name( $product->get_backorders() ),
					self::IS_NULL
				)
			),
			$this->expectedField( 'product.soldIndividually', $product->get_sold_individually() ),
			$this->expectedField(
				'product.weight',
				$this->maybe( $product->get_weight(), self::IS_NULL )
			),
			$this->expectedField(
				'product.length',
				$this->maybe( $product->get_length(), self::IS_NULL )
			),
			$this->expectedField(
				'product.width',
				$this->maybe( $product->get_width(), self::IS_NULL )
			),
			$this->expectedField(
				'product.height',
				$this->maybe( $product->get_height(), self::IS_NULL )
			),
			$this->expectedField(
				'product.reviewsAllowed',
				$this->maybe( $product->get_reviews_allowed(), self::IS_NULL )
			),
			$this->expectedField(
				'product.purchaseNote',
				$this->maybe( $product->get_purchase_note(), self::IS_NULL )
			),
			$this->expectedField( 'product.menuOrder', $product->get_menu_order() ),
			$this->expectedField( 'product.virtual', $product->get_virtual() ),
			$this->expectedField( 'product.downloadable', $product->get_downloadable(), self::IS_NULL ),
			$this->expectedField(
				'product.downloadLimit',
				$this->maybe( $product->get_download_limit(), self::IS_NULL )
			),
			$this->expectedField(
				'product.downloadExpiry',
				$this->maybe( $product->get_download_expiry(), self::IS_NULL )
			),
			$this->expectedField( 'product.averageRating', (float) $product->get_average_rating() ),
			$this->expectedField( 'product.reviewCount', (int) $product->get_review_count() ),
			$this->expectedField(
				'product.backordersAllowed',
				$this->maybe( $product->backorders_allowed(), self::IS_FALSY )
			),
			$this->expectedField( 'product.onSale', $product->is_on_sale() ),
			$this->expectedField( 'product.purchasable', $product->is_purchasable() ),
			$this->expectedField( 'product.shippingRequired', $product->needs_shipping() ),
			$this->expectedField( 'product.shippingTaxable', $product->is_shipping_taxable() ),
			$this->expectedField(
				'product.link',
				$this->maybe( get_post_permalink( $product_id ), self::IS_NULL )
			),
			$this->expectedField(
				'product.totalSales',
				$this->maybe(
					[
						$is_shop_manager && $product->get_total_sales(),
						$product->get_total_sales(),
					],
					self::IS_FALSY
				)
			),
			$this->expectedField(
				'product.catalogVisibility',
				$this->maybe(
					[
						$is_shop_manager && ! empty( $product->get_catalog_visibility() ),
						strtoupper( $product->get_catalog_visibility() ),
					],
					self::IS_NULL
				)
			),
		];
	}

	public function getExpectedProductDownloadData( $product_id ) {
		$product   = wc_get_product( $product_id );
		$downloads = (array) $product->get_downloads();
		if ( empty( $downloads ) ) {
			return null;
		}

		$results = [];
		foreach ( $downloads as $download ) {
			$results[] = [
				'name'            => $download->get_name(),
				'downloadId'      => $download->get_id(),
				'filePathType'    => $download->get_type_of_file_path(),
				'fileType'        => $download->get_file_type(),
				'fileExt'         => $download->get_file_extension(),
				'allowedFileType' => $download->is_allowed_filetype(),
				'fileExists'      => $download->file_exists(),
				'file'            => $download->get_file(),
			];
		}

		return $results;
	}

	// tests
	public function testSimpleProductQuery() {
		$product_id = $this->factory->product->createSimple();
		$product    = wc_get_product( $product_id );

		$query = '
			query ( $id: ID!, $format: PostObjectFieldFormatEnum ) {
				product(id: $id) {
					... on SimpleProduct {
						id
						databaseId
						name
						slug
						date
						modified
						status
						featured
						catalogVisibility
						description(format: $format)
						shortDescription(format: $format)
						sku
						price
						regularPrice
						salePrice
						dateOnSaleFrom
						dateOnSaleTo
						totalSales
						taxStatus
						taxClass
						manageStock
						stockQuantity
						stockStatus
						backorders
						soldIndividually
						weight
						length
						width
						height
						reviewsAllowed
						purchaseNote
						menuOrder
						virtual
						downloadable
						downloadLimit
						downloadExpiry
						averageRating
						reviewCount
						backordersAllowed
						onSale
						purchasable
						shippingRequired
						shippingTaxable
						link
					}
				}
			}
		';

		/**
		 * Assertion One
		 *
		 * Test querying product.
		 */
		$variables = [ 'id' => $this->toRelayId( 'product', $product_id ) ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = $this->getExpectedProductData( $product_id );

		$this->assertQuerySuccessful( $response, $expected );

		// Clear cache
		$this->clearLoaderCache( 'wc_post' );

		/**
		 * Assertion Two
		 *
		 * Test querying product with unformatted content (edit-product cap required).
		 */
		$this->loginAsShopManager();
		$variables = [
			'id'     => $this->toRelayId( 'product', $product_id ),
			'format' => 'RAW',
		];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedField( 'product.description', $product->get_description() ),
			$this->expectedField( 'product.shortDescription', $product->get_short_description() ),
			$this->expectedField( 'product.totalSales', $product->get_total_sales() ),
			$this->expectedField( 'product.catalogVisibility', strtoupper( $product->get_catalog_visibility() ) ),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testProductTaxonomies() {
		// Create product and properties.
		$category_5    = $this->factory->product->createProductCategory( 'category-five' );
		$category_6    = $this->factory->product->createProductCategory( 'category-six', $category_5 );
		$tag_2         = $this->factory->product->createProductTag( 'tag-two' );
		$attachment_id = $this->factory->attachment->create(
			[
				'post_mime_type' => 'image/gif',
				'post_author'    => $this->admin,
			]
		);
		$product_id    = $this->factory->product->createSimple(
			[
				'price'         => 10,
				'regular_price' => 10,
				'category_ids'  => [ $category_5 ],
				'tag_ids'       => [ $tag_2 ],
				'image_id'      => $attachment_id,
			]
		);

		$query = '
			query ( $id: ID!, $idType: ProductIdTypeEnum ) {
				product( id: $id, idType: $idType ) {
					... on SimpleProduct {
						id
						image {
							id
						}
						productCategories {
							nodes {
								name
								image { id }
								display
								menuOrder
								children {
									nodes {
										name
									}
								}
							}
						}
						productTags {
							nodes {
								name
							}
						}
					}
				}
			}
		';

		/**
		 * Assertion One
		 *
		 * Test querying product with "productId" argument.
		 */
		$variables = [
			'id'     => $product_id,
			'idType' => 'DATABASE_ID',
		];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedField( 'product.id', $this->toRelayId( 'product', $product_id ) ),
			$this->expectedField( 'product.image.id', $this->toRelayId( 'post', $attachment_id ) ),
			$this->expectedNode(
				'product.productCategories.nodes',
				[
					'name'      => 'category-five',
					'image'     => null,
					'display'   => 'DEFAULT',
					'menuOrder' => 0,
					'children'  => [
						'nodes' => [
							[ 'name' => 'category-six' ],
						],
					],
				]
			),
			$this->expectedNode( 'product.productTags.nodes', [ 'name' => 'tag-two' ] ),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testProductQueryAndIds() {
		$product_id = $this->factory->product->createSimple();
		$query      = '
			query ( $id: ID!, $idType: ProductIdTypeEnum ) {
				product( id: $id, idType: $idType ) {
					... on SimpleProduct {
						id
					}
				}
			}
		';

		// Define expected data for coming assertions.
		$expected = [
			$this->expectedField( 'product.id', $this->toRelayId( 'product', $product_id ) ),
		];

		/**
		 * Assertion One
		 *
		 * Test querying product with 'DATABASE_ID' set as the "idType".
		 */
		$variables = [
			'id'     => $product_id,
			'idType' => 'DATABASE_ID',
		];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Two
		 *
		 * Test querying product with "ID" set as the "idType".
		 */
		$variables = [
			'id'     => $this->toRelayId( 'product', $product_id ),
			'idType' => 'ID',
		];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Three
		 *
		 * Test querying product with "SLUG" set as the "idType".
		 */
		$variables = [
			'id'     => get_post_field( 'post_name', $product_id ),
			'idType' => 'SLUG',
		];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Four
		 *
		 * Test querying product with "SKU" set as the "idType".
		 */
		$variables = [
			'id'     => get_post_meta( $product_id, '_sku', true ),
			'idType' => 'SKU',
		];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$this->assertQuerySuccessful( $response, $expected );
	}

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
					orderby: $orderby
					taxonomyFilter: $taxonomyFilter
					include: $include
					exclude: $exclude
				} ) {
					nodes {
						... on SimpleProduct {
							id
						}
						... on ExternalProduct {
							id
						}
					}
				}
			}
		';

		$all_expected_product_nodes = array_map(
			function( $product_id ) {
				return $this->expectedNode(
					'products.nodes',
					[ 'id' => $this->toRelayId( 'product', $product_id ) ]
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
			function( $node, $index ) use ( $product_ids ) {
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
			function( $node, $index ) use ( $product_ids ) {
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
			function( $node, $index ) use ( $product_ids ) {
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
			function( $node, $index ) use ( $product_ids ) {
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
			function( $node, $index ) use ( $product_ids ) {
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
				[ 'id' => $this->toRelayId( 'product', $product_ids[0] ) ],
				0
			),
			$this->expectedNode(
				'products.nodes',
				[ 'id' => $this->toRelayId( 'product', $product_ids[1] ) ],
				3
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
			function( $node, $index ) use ( $product_ids, $category_3 ) {
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
			function( $node, $index ) use ( $product_ids, $category_4 ) {
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
			function( $node, $index ) use ( $product_ids, $category_4 ) {
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
						'taxonomy' => 'PRODUCTCATEGORY',
						'terms'    => [ 'category-three' ],
					],
					[
						'taxonomy' => 'PRODUCTCATEGORY',
						'terms'    => [ 'category-four' ],
						'operator' => 'NOT_IN',
					],
				],
			],
		];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array_filter(
			$all_expected_product_nodes,
			function( $node, $index ) use ( $product_ids, $category_4, $category_3 ) {
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
				[ 'id' => $this->toRelayId( 'product', $product_ids[0] ) ]
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
				[]
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
				[ 'id' => $this->toRelayId( 'product', $product_ids[0] ) ]
			),
		];
		$this->assertQuerySuccessful( $response, $expected );

		$variables = [ 'exclude' => $product_ids ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedField(
				'products.nodes',
				[]
			),
		];
		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testProductToTermConnection() {
		$test_category = $this->factory->product->createProductCategory( 'test-product-category-1' );
		$test_tag      = $this->factory->product->createProductTag( 'test-product-tag-1' );
		$product_id    = $this->factory->product->createSimple(
			[
				'tag_ids'      => [ $test_tag ],
				'category_ids' => [ $test_category ],
			]
		);
		$relay_id      = $this->toRelayId( 'product', $product_id );

		$query = '
			query ($id: ID!) {
				product(id: $id) {
					... on SimpleProduct {
						id
						productTags {
							nodes {
								name
							}
						}
						productCategories {
							nodes {
								name
							}
						}
					}
				}
			}
		';

		$variables = [ 'id' => $relay_id ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedField( 'product.id', $relay_id ),
			$this->expectedNode(
				'product.productTags.nodes',
				[ 'name' => 'test-product-tag-1' ],
				0
			),
			$this->expectedNode(
				'product.productCategories.nodes',
				[ 'name' => 'test-product-category-1' ],
				0
			),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testTermToProductConnection() {
		$test_tag      = $this->factory->product->createProductTag( 'test-product-tag-2' );
		$image_id      = $this->factory->post->create(
			[
				'post_author' => $this->shop_manager,
				'post_status' => 'publish',
				'post_title'  => 'Product Image',
				'post_type'   => 'attachment',
			]
		);
		$test_category = $this->factory->product->createProductCategory( 'test-product-category-2' );
		update_term_meta( $test_category, 'thumbnail_id', $image_id );

		$product_id           = $this->factory->product->createSimple(
			[
				'tag_ids'       => [ $test_tag ],
				'category_ids'  => [ $test_category ],
				'price'         => 10,
				'regular_price' => 10,
			]
		);
		$expensive_product_id = $this->factory->product->createSimple(
			[
				'tag_ids'       => [ $test_tag ],
				'category_ids'  => [ $test_category ],
				'price'         => 100,
				'regular_price' => 100,
			]
		);

		$query = '
			query($orderby: [ProductsOrderbyInput], $orderby2: [ProductsOrderbyInput]) {
				productTags( where: { hideEmpty: true } ) {
					nodes {
						name
						products(where: {orderby: $orderby}) {
							nodes {
								... on SimpleProduct {
									id
									price
								}
							}
						}
					}
				}
				productCategories( where: { hideEmpty: true } ) {
					nodes {
						name
						image {
							id
						}
						products(where: {orderby: $orderby2}) {
							nodes {
								... on SimpleProduct {
									id
									price
								}
							}
						}
					}
				}
			}
		';

		$variables = [
			'orderby'  => [
				[
					'field' => 'PRICE',
					'order' => 'ASC',
				],
			],
			'orderby2' => [
				[
					'field' => 'PRICE',
					'order' => 'DESC',
				],
			],
		];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedNode(
				'productTags.nodes',
				[
					$this->expectedField( 'name', 'test-product-tag-2' ),
					$this->expectedField( 'products.nodes.0.id', $this->toRelayId( 'product', $product_id ) ),
					$this->expectedField( 'products.nodes.1.id', $this->toRelayId( 'product', $expensive_product_id ) ),
				],
				0
			),
			$this->expectedNode(
				'productCategories.nodes',
				[
					$this->expectedField( 'name', 'test-product-category-2' ),
					$this->expectedField( 'image.id', $this->toRelayId( 'post', $image_id ) ),
					$this->expectedField( 'products.nodes.1.id', $this->toRelayId( 'product', $product_id ) ),
					$this->expectedField( 'products.nodes.0.id', $this->toRelayId( 'product', $expensive_product_id ) ),
				],
				0
			),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testProductToMediaItemConnections() {
		$image_id   = $this->factory->post->create(
			[
				'post_author' => $this->shop_manager,
				'post_status' => 'publish',
				'post_title'  => 'Product Image',
				'post_type'   => 'attachment',
			]
		);
		$product_id = $this->factory->product->createSimple(
			[
				'image_id'          => $image_id,
				'gallery_image_ids' => [ $image_id ],
			]
		);

		$product_relay_id = $this->toRelayId( 'product', $product_id );
		$image_relay_id   = $this->toRelayId( 'post', $image_id );

		$query = '
			query ( $id: ID! ) {
				product( id: $id ) {
					... on SimpleProduct {
						id
						image {
							id
						}
						galleryImages {
							nodes {
								id
							}
						}
					}
				}
			}
		';

		$variables = [ 'id' => $product_relay_id ];
		$response  = graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedField( 'product.id', $product_relay_id ),
			$this->expectedField( 'product.image.id', $image_relay_id ),
			$this->expectedNode( 'product.galleryImages.nodes', [ 'id' => $image_relay_id ] ),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testProductDownloads() {
		$product_id = $this->factory->product->createSimple(
			[
				'downloadable' => true,
				'downloads'    => [ $this->factory->product->createDownload() ],
			]
		);

		$relay_id = $this->toRelayId( 'product', $product_id );

		$query = '
			query ( $id: ID! ) {
				product( id: $id ) {
					... on SimpleProduct {
						id
						downloads {
							name
							downloadId
							filePathType
							fileType
							fileExt
							allowedFileType
							fileExists
							file
						}
					}
				}
			}
		';

		$variables = [ 'id' => $relay_id ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedField( 'product.id', $relay_id ),
			$this->expectedField( 'product.downloads', $this->getExpectedProductDownloadData( $product_id ) ),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testExternalProductQuery() {
		$product_id = $this->factory->product->createExternal(
			[
				'product_url' => 'http://woographql.com',
				'button_text' => 'Buy a external product',
			]
		);
		$relay_id   = $this->toRelayId( 'product', $product_id );

		$query = '
			query ( $id: ID! ) {
				product(id: $id) {
					... on ExternalProduct {
						id
						buttonText
						externalUrl
					}
				}
			}
		';

		$variables = [ 'id' => $relay_id ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedField( 'product.id', $relay_id ),
			$this->expectedField( 'product.buttonText', 'Buy a external product' ),
			$this->expectedField( 'product.externalUrl', 'http://woographql.com' ),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testGroupProductConnections() {
		$product_id          = $this->factory->product->createGrouped(
			[
				'name'     => 'Test Group',
				'children' => [],
			]
		);
		$grouped_product_ids = [
			$this->factory->product->createSimple( [ 'regular_price' => '1.00' ] ),
			$this->factory->product->createSimple( [ 'regular_price' => '5.00' ] ),
			$this->factory->product->createSimple( [ 'regular_price' => '10.00' ] ),
		];

		$product = \wc_get_product( $product_id );
		$this->factory->product->update_object(
			$product,
			[ 'children' => $grouped_product_ids ]
		);

		$relay_id = $this->toRelayId( 'product', $product_id );

		$query = '
			query ( $id: ID! ) {
				product(id: $id) {
					id
					... on GroupProduct {
						addToCartText
						addToCartDescription
						products {
							nodes { id }
						}
					}
				}
			}
		';

		$variables = [ 'id' => $relay_id ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedField( 'product.id', $relay_id ),
			$this->expectedField( 'product.addToCartText', 'View products' ),
			$this->expectedField(
				'product.addToCartDescription',
				/* translators: %s: Group name */
				sprintf( __( 'View products in the &ldquo;%s&rdquo; group', 'wp-graphql-woocommerce' ), 'Test Group' )
			),
		];

		foreach ( $product->get_children() as $grouped_product_id ) {
			$expected[] = $this->expectedNode(
				'product.products.nodes',
				[ 'id' => $this->toRelayId( 'product', $grouped_product_id ) ]
			);
		}

		$this->assertQuerySuccessful( $response, $expected );

		$query = '
			query ( $id: ID! ) {
				product( id: $id ) {
					... on GroupProduct {
						price
					}
				}
			}
		';

		$response = $this->graphql( compact( 'query', 'variables' ) );
		$expected = [ $this->expectedField( 'product.price', '$1.00 - $10.00' ) ];

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testRelatedProductConnections() {
		$products = $this->factory->product->createRelated();

		$query = '
			query ($id: ID!) {
				product(id: $id) {
					... on SimpleProduct {
						related {
							nodes {
								... on SimpleProduct {
									id
								}
							}
						}
						crossSell{
							nodes {
								... on SimpleProduct {
									id
								}
							}
						}
						upsell {
							nodes {
								... on SimpleProduct {
									id
								}
							}
						}
					}
				}
			}
		';

		$variables = [ 'id' => $this->toRelayId( 'product', $products['product'] ) ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [];
		foreach ( $products['related'] + $products['cross_sell'] + $products['upsell'] as $product_id ) {
			$expected[] = $this->expectedNode(
				'product.related.nodes',
				[ 'id' => $this->toRelayId( 'product', $product_id ) ]
			);
		}
		foreach ( $products['cross_sell'] as $product_id ) {
			$expected[] = $this->expectedNode(
				'product.crossSell.nodes',
				[ 'id' => $this->toRelayId( 'product', $product_id ) ]
			);
		}
		foreach ( $products['upsell'] as $product_id ) {
			$expected[] = $this->expectedNode(
				'product.upsell.nodes',
				[ 'id' => $this->toRelayId( 'product', $product_id ) ]
			);
		}

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testProductToReviewConnections() {
		$product_id = $this->factory->product->createSimple();
		$reviews    = [
			$this->factory->product->createReview( $product_id ),
			$this->factory->product->createReview( $product_id ),
			$this->factory->product->createReview( $product_id ),
			$this->factory->product->createReview( $product_id ),
			$this->factory->product->createReview( $product_id ),
		];
		$relay_id   = $this->toRelayId( 'product', $product_id );
		$product    = \wc_get_product( $product_id );

		$query = '
			query ($id: ID!) {
				product(id: $id) {
					id
					reviews(last: 5) {
						averageRating
						edges {
							rating
							node {
								id
							}
						}
					}
				}
			}
		';

		$variables = [ 'id' => $relay_id ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedField( 'product.id', $relay_id ),
			$this->expectedField( 'product.reviews.averageRating', floatval( $product->get_average_rating() ) ),
		];

		foreach ( $reviews as $review_id ) {
			$expected[] = $this->expectedEdge(
				'product.reviews.edges',
				[
					'rating' => floatval( get_comment_meta( $review_id, 'rating', true ) ),
					'node'   => [ 'id' => $this->toRelayId( 'comment', $review_id ) ],
				]
			);
		}

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testProductGalleryImagesConnection() {
		$image_id   = $this->factory->post->create(
			[
				'post_type'    => 'attachment',
				'post_content' => 'Lorem ipsum dolor...',
			]
		);
		$product_id = $this->factory->product->createSimple(
			[ 'gallery_image_ids' => [ $image_id ] ]
		);

		$query = '
			query( $id: ID! ) {
				product( id: $id ) {
					galleryImages {
						nodes {
							id
						}
					}
				}
			}
		';

		$variables = [ 'id' => $this->toRelayId( 'product', $product_id ) ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$this->assertQuerySuccessful(
			$response,
			[
				$this->expectedNode(
					'product.galleryImages.nodes',
					[ 'id' => $this->toRelayId( 'post', $image_id ) ]
				),
			]
		);
	}
}
