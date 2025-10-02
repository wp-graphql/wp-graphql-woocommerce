<?php
class ProductQueriesTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {
	public function getExpectedProductData( $product_id ) {
		$product         = \wc_get_product( $product_id );
		$is_shop_manager = false;
		$user            = wp_get_current_user();
		if ( $user && in_array( 'shop_manager', (array) $user->roles, true ) ) {
			$is_shop_manager = true;
		}

		return array(
			$this->expectedField( 'product.id', $this->toRelayId( 'post', $product_id ) ),
			$this->expectedField( 'product.databaseId', $product->get_id() ),
			$this->expectedField( 'product.name', $product->get_name() ),
			$this->expectedField( 'product.slug', $product->get_slug() ),
			$this->expectedField( 'product.date', $product->get_date_created()->__toString() ),
			$this->expectedField( 'product.status', $product->get_status() ),
			$this->expectedField( 'product.featured', $product->get_featured() ),
			$this->expectedField(
				'product.description',
				$this->maybe(
					array( $product->get_description(), apply_filters( 'the_content', $product->get_description() ) ),
					static::IS_NULL
				)
			),
			$this->expectedField(
				'product.shortDescription',
				$this->maybe(
					array(
						$product->get_short_description(),
						apply_filters(
							'get_the_excerpt',
							apply_filters( 'the_excerpt', $product->get_short_description() )
						),
					),
					static::IS_NULL
				)
			),
			$this->expectedField( 'product.sku', $product->get_sku() ),
			$this->expectedField(
				'product.price',
				$this->maybe(
					array( $product->get_price(), \wc_graphql_price( $product->get_price() ) ),
					static::IS_NULL
				)
			),
			$this->expectedField(
				'product.regularPrice',
				$this->maybe(
					array( $product->get_regular_price(), \wc_graphql_price( $product->get_regular_price() ) ),
					static::IS_NULL
				)
			),
			$this->expectedField(
				'product.salePrice',
				$this->maybe(
					array( $product->get_sale_price(), \wc_graphql_price( $product->get_sale_price() ) ),
					static::IS_NULL
				)
			),
			$this->expectedField(
				'product.dateOnSaleFrom',
				$this->maybe( $product->get_date_on_sale_from(), static::IS_NULL )
			),
			$this->expectedField(
				'product.dateOnSaleTo',
				$this->maybe( $product->get_date_on_sale_to(), static::IS_NULL )
			),
			$this->expectedField(
				'product.taxStatus',
				$this->maybe( strtoupper( $product->get_tax_status() ), static::IS_NULL )
			),
			$this->expectedField(
				'product.taxClass',
				$this->maybe( $product->get_tax_class(), 'STANDARD' )
			),
			$this->expectedField(
				'product.manageStock',
				! empty( $product->get_manage_stock() )
					? \WPGraphQL\Type\WPEnumType::get_safe_name( $product->get_manage_stock() )
					: static::IS_NULL
			),
			$this->expectedField(
				'product.stockQuantity',
				$this->maybe( $product->get_stock_quantity(), static::IS_NULL )
			),
			$this->expectedField(
				'product.stockStatus',
				$this->maybe(
					$this->factory->product->getStockStatusEnum( $product->get_stock_status() ),
					static::IS_NULL
				)
			),
			$this->expectedField(
				'product.backorders',
				$this->maybe(
					\WPGraphQL\Type\WPEnumType::get_safe_name( $product->get_backorders() ),
					static::IS_NULL
				)
			),
			$this->expectedField( 'product.soldIndividually', $product->get_sold_individually() ),
			$this->expectedField(
				'product.weight',
				$this->maybe( $product->get_weight(), static::IS_NULL )
			),
			$this->expectedField(
				'product.length',
				$this->maybe( $product->get_length(), static::IS_NULL )
			),
			$this->expectedField(
				'product.width',
				$this->maybe( $product->get_width(), static::IS_NULL )
			),
			$this->expectedField(
				'product.height',
				$this->maybe( $product->get_height(), static::IS_NULL )
			),
			$this->expectedField(
				'product.reviewsAllowed',
				$this->maybe( $product->get_reviews_allowed(), static::IS_NULL )
			),
			$this->expectedField(
				'product.purchaseNote',
				$this->maybe( $product->get_purchase_note(), static::IS_NULL )
			),
			$this->expectedField( 'product.menuOrder', $product->get_menu_order() ),
			$this->expectedField( 'product.virtual', $product->get_virtual() ),
			$this->expectedField( 'product.downloadable', $product->get_downloadable(), static::IS_NULL ),
			$this->expectedField(
				'product.downloadLimit',
				$this->maybe( $product->get_download_limit(), static::IS_NULL )
			),
			$this->expectedField(
				'product.downloadExpiry',
				$this->maybe( $product->get_download_expiry(), static::IS_NULL )
			),
			$this->expectedField( 'product.averageRating', (float) $product->get_average_rating() ),
			$this->expectedField( 'product.reviewCount', (int) $product->get_review_count() ),
			$this->expectedField(
				'product.backordersAllowed',
				$this->maybe( $product->backorders_allowed(), static::IS_FALSY )
			),
			$this->expectedField( 'product.onSale', $product->is_on_sale() ),
			$this->expectedField( 'product.purchasable', $product->is_purchasable() ),
			$this->expectedField( 'product.shippingRequired', $product->needs_shipping() ),
			$this->expectedField( 'product.shippingTaxable', $product->is_shipping_taxable() ),
			$this->expectedField(
				'product.link',
				$this->maybe( get_post_permalink( $product_id ), static::IS_NULL )
			),
			$this->expectedField(
				'product.totalSales',
				$this->maybe(
					array(
						$is_shop_manager && $product->get_total_sales(),
						$product->get_total_sales(),
					),
					static::IS_FALSY
				)
			),
			$this->expectedField(
				'product.catalogVisibility',
				$this->maybe(
					array(
						$is_shop_manager && ! empty( $product->get_catalog_visibility() ),
						strtoupper( $product->get_catalog_visibility() ),
					),
					static::IS_NULL
				)
			),
		);
	}

	public function getExpectedProductDownloadData( $product_id ) {
		$product   = wc_get_product( $product_id );
		$downloads = (array) $product->get_downloads();
		if ( empty( $downloads ) ) {
			return null;
		}

		$results = array();
		foreach ( $downloads as $download ) {
			$results[] = array(
				'name'            => $download->get_name(),
				'downloadId'      => $download->get_id(),
				'filePathType'    => $download->get_type_of_file_path(),
				'fileType'        => $download->get_file_type(),
				'fileExt'         => $download->get_file_extension(),
				'allowedFileType' => $download->is_allowed_filetype(),
				'fileExists'      => $download->file_exists(),
				'file'            => $download->get_file(),
			);
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
		$variables = array( 'id' => $this->toRelayId( 'post', $product_id ) );
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
		$variables = array(
			'id'     => $this->toRelayId( 'post', $product_id ),
			'format' => 'RAW',
		);
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array(
			$this->expectedField( 'product.description', $product->get_description() ),
			$this->expectedField( 'product.shortDescription', $product->get_short_description() ),
			$this->expectedField( 'product.totalSales', $product->get_total_sales() ),
			$this->expectedField( 'product.catalogVisibility', strtoupper( $product->get_catalog_visibility() ) ),
		);

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testProductTaxonomies() {
		// Create product and properties.
		$category_5    = $this->factory->product->createProductCategory( 'category-five' );
		$category_6    = $this->factory->product->createProductCategory( 'category-six', $category_5 );
		$tag_2         = $this->factory->product->createProductTag( 'tag-two' );
		$attachment_id = $this->factory->attachment->create(
			array(
				'post_mime_type' => 'image/gif',
				'post_author'    => $this->admin,
			)
		);
		$product_id    = $this->factory->product->createSimple(
			array(
				'price'         => 10,
				'regular_price' => 10,
				'category_ids'  => array( $category_5 ),
				'tag_ids'       => array( $tag_2 ),
				'image_id'      => $attachment_id,
			)
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
		$variables = array(
			'id'     => $product_id,
			'idType' => 'DATABASE_ID',
		);
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array(
			$this->expectedField( 'product.id', $this->toRelayId( 'post', $product_id ) ),
			$this->expectedField( 'product.image.id', $this->toRelayId( 'post', $attachment_id ) ),
			$this->expectedNode(
				'product.productCategories.nodes',
				array(
					'name'      => 'category-five',
					'image'     => null,
					'display'   => 'DEFAULT',
					'menuOrder' => 0,
					'children'  => array(
						'nodes' => array(
							array( 'name' => 'category-six' ),
						),
					),
				)
			),
			$this->expectedNode( 'product.productTags.nodes', array( 'name' => 'tag-two' ) ),
		);

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
		$expected = array(
			$this->expectedField( 'product.id', $this->toRelayId( 'post', $product_id ) ),
		);

		/**
		 * Assertion One
		 *
		 * Test querying product with 'DATABASE_ID' set as the "idType".
		 */
		$variables = array(
			'id'     => $product_id,
			'idType' => 'DATABASE_ID',
		);
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Two
		 *
		 * Test querying product with "ID" set as the "idType".
		 */
		$variables = array(
			'id'     => $this->toRelayId( 'post', $product_id ),
			'idType' => 'ID',
		);
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Three
		 *
		 * Test querying product with "SLUG" set as the "idType".
		 */
		$variables = array(
			'id'     => get_post_field( 'post_name', $product_id ),
			'idType' => 'SLUG',
		);
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Four
		 *
		 * Test querying product with "SKU" set as the "idType".
		 */
		$variables = array(
			'id'     => get_post_meta( $product_id, '_sku', true ),
			'idType' => 'SKU',
		);
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testProductToTermConnection() {
		$test_category = $this->factory->product->createProductCategory( 'test-product-category-1' );
		$test_tag      = $this->factory->product->createProductTag( 'test-product-tag-1' );
		$product_id    = $this->factory->product->createSimple(
			array(
				'tag_ids'      => array( $test_tag ),
				'category_ids' => array( $test_category ),
			)
		);
		$relay_id      = $this->toRelayId( 'post', $product_id );

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

		$variables = array( 'id' => $relay_id );
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array(
			$this->expectedField( 'product.id', $relay_id ),
			$this->expectedNode(
				'product.productTags.nodes',
				array( 'name' => 'test-product-tag-1' ),
				0
			),
			$this->expectedNode(
				'product.productCategories.nodes',
				array( 'name' => 'test-product-category-1' ),
				0
			),
		);

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testTermToProductConnection() {
		$test_tag      = $this->factory->product->createProductTag( 'test-product-tag-2' );
		$image_id      = $this->factory->post->create(
			array(
				'post_author' => $this->shop_manager,
				'post_status' => 'publish',
				'post_title'  => 'Product Image',
				'post_type'   => 'attachment',
			)
		);
		$test_category = $this->factory->product->createProductCategory( 'test-product-category-2' );
		update_term_meta( $test_category, 'thumbnail_id', $image_id );

		$product_id           = $this->factory->product->createSimple(
			array(
				'tag_ids'       => array( $test_tag ),
				'category_ids'  => array( $test_category ),
				'price'         => 10,
				'regular_price' => 10,
			)
		);
		$expensive_product_id = $this->factory->product->createSimple(
			array(
				'tag_ids'       => array( $test_tag ),
				'category_ids'  => array( $test_category ),
				'price'         => 100,
				'regular_price' => 100,
			)
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

		$variables = array(
			'orderby'  => array(
				array(
					'field' => 'PRICE',
					'order' => 'ASC',
				),
			),
			'orderby2' => array(
				array(
					'field' => 'PRICE',
					'order' => 'DESC',
				),
			),
		);
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array(
			$this->expectedNode(
				'productTags.nodes',
				array(
					$this->expectedField( 'name', 'test-product-tag-2' ),
					$this->expectedField( 'products.nodes.0.id', $this->toRelayId( 'post', $product_id ) ),
					$this->expectedField( 'products.nodes.1.id', $this->toRelayId( 'post', $expensive_product_id ) ),
				),
				0
			),
			$this->expectedNode(
				'productCategories.nodes',
				array(
					$this->expectedField( 'name', 'test-product-category-2' ),
					$this->expectedField( 'image.id', $this->toRelayId( 'post', $image_id ) ),
					$this->expectedField( 'products.nodes.1.id', $this->toRelayId( 'post', $product_id ) ),
					$this->expectedField( 'products.nodes.0.id', $this->toRelayId( 'post', $expensive_product_id ) ),
				),
				0
			),
		);

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testProductToMediaItemConnections() {
		$image_id   = $this->factory->post->create(
			array(
				'post_author' => $this->shop_manager,
				'post_status' => 'publish',
				'post_title'  => 'Product Image',
				'post_type'   => 'attachment',
			)
		);
		$product_id = $this->factory->product->createSimple(
			array(
				'image_id'          => $image_id,
				'gallery_image_ids' => array( $image_id ),
			)
		);

		$product_relay_id = $this->toRelayId( 'post', $product_id );
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

		$variables = array( 'id' => $product_relay_id );
		$response  = graphql( compact( 'query', 'variables' ) );
		$expected  = array(
			$this->expectedField( 'product.id', $product_relay_id ),
			$this->expectedField( 'product.image.id', $image_relay_id ),
			$this->expectedNode( 'product.galleryImages.nodes', array( 'id' => $image_relay_id ) ),
		);

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testProductDownloads() {
		$product_id = $this->factory->product->createSimple(
			array(
				'downloadable' => true,
				'downloads'    => array( $this->factory->product->createDownload() ),
			)
		);

		$relay_id = $this->toRelayId( 'post', $product_id );

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

		$variables = array( 'id' => $relay_id );
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array(
			$this->expectedField( 'product.id', $relay_id ),
			$this->expectedField( 'product.downloads', $this->getExpectedProductDownloadData( $product_id ) ),
		);

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testExternalProductQuery() {
		$product_id = $this->factory->product->createExternal(
			array(
				'product_url' => 'http://woographql.com',
				'button_text' => 'Buy a external product',
			)
		);
		$relay_id   = $this->toRelayId( 'post', $product_id );

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

		$variables = array( 'id' => $relay_id );
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array(
			$this->expectedField( 'product.id', $relay_id ),
			$this->expectedField( 'product.buttonText', 'Buy a external product' ),
			$this->expectedField( 'product.externalUrl', 'http://woographql.com' ),
		);

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testGroupProductConnections() {
		$product_id          = $this->factory->product->createGrouped(
			array(
				'name'     => 'Test Group',
				'children' => array(),
			)
		);
		$grouped_product_ids = array(
			$this->factory->product->createSimple( array( 'regular_price' => '1.00' ) ),
			$this->factory->product->createSimple( array( 'regular_price' => '5.00' ) ),
			$this->factory->product->createSimple( array( 'regular_price' => '10.00' ) ),
		);

		$product = \wc_get_product( $product_id );
		$this->factory->product->update_object(
			$product,
			array( 'children' => $grouped_product_ids )
		);

		$relay_id = $this->toRelayId( 'post', $product_id );

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

		$variables = array( 'id' => $relay_id );
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array(
			$this->expectedField( 'product.id', $relay_id ),
			$this->expectedField( 'product.addToCartText', 'View products' ),
			$this->expectedField(
				'product.addToCartDescription',
				/* translators: %s: Group name */
				sprintf( __( 'View products in the &ldquo;%s&rdquo; group', 'wp-graphql-woocommerce' ), 'Test Group' )
			),
		);

		foreach ( $product->get_children() as $grouped_product_id ) {
			$expected[] = $this->expectedNode(
				'product.products.nodes',
				array( 'id' => $this->toRelayId( 'post', $grouped_product_id ) )
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
		$expected = array( $this->expectedField( 'product.price', '$1.00 - $10.00' ) );

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

		$variables = array( 'id' => $this->toRelayId( 'post', $products['product'] ) );
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array();
		foreach ( $products['related'] + $products['cross_sell'] + $products['upsell'] as $product_id ) {
			$expected[] = $this->expectedNode(
				'product.related.nodes',
				array( 'id' => $this->toRelayId( 'post', $product_id ) )
			);
		}
		foreach ( $products['cross_sell'] as $product_id ) {
			$expected[] = $this->expectedNode(
				'product.crossSell.nodes',
				array( 'id' => $this->toRelayId( 'post', $product_id ) )
			);
		}
		foreach ( $products['upsell'] as $product_id ) {
			$expected[] = $this->expectedNode(
				'product.upsell.nodes',
				array( 'id' => $this->toRelayId( 'post', $product_id ) )
			);
		}

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testProductToReviewConnections() {
		$product_id = $this->factory->product->createSimple();
		$reviews    = array(
			$this->factory->product->createReview( $product_id ),
			$this->factory->product->createReview( $product_id ),
			$this->factory->product->createReview( $product_id ),
			$this->factory->product->createReview( $product_id ),
			$this->factory->product->createReview( $product_id ),
		);
		$relay_id   = $this->toRelayId( 'post', $product_id );
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

		$variables = array( 'id' => $relay_id );
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array(
			$this->expectedField( 'product.id', $relay_id ),
			$this->expectedField( 'product.reviews.averageRating', floatval( $product->get_average_rating() ) ),
		);

		foreach ( $reviews as $review_id ) {
			$expected[] = $this->expectedEdge(
				'product.reviews.edges',
				array(
					'rating' => floatval( get_comment_meta( $review_id, 'rating', true ) ),
					'node'   => array( 'id' => $this->toRelayId( 'comment', $review_id ) ),
				)
			);
		}

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testProductGalleryImagesConnection() {
		$image_id   = $this->factory->post->create(
			array(
				'post_type'    => 'attachment',
				'post_content' => 'Lorem ipsum dolor...',
			)
		);
		$product_id = $this->factory->product->createSimple(
			array( 'gallery_image_ids' => array( $image_id ) )
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

		$variables = array( 'id' => $this->toRelayId( 'post', $product_id ) );
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$this->assertQuerySuccessful(
			$response,
			array(
				$this->expectedNode(
					'product.galleryImages.nodes',
					array( 'id' => $this->toRelayId( 'post', $image_id ) )
				),
			)
		);
	}

	// tests
	public function testProductQueryWithInterfaces() {
		$product_id = $this->factory->product->createSimple();
		$product    = wc_get_product( $product_id );

		$query = '
			query ( $id: ID!, $format: PostObjectFieldFormatEnum ) {
				product(id: $id) {
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
					dateOnSaleFrom
					dateOnSaleTo
					totalSales
					averageRating
					reviewCount
					onSale
					purchasable
					link
					reviewsAllowed
					purchaseNote
					menuOrder
					virtual
					... on ProductWithPricing {
						price
						regularPrice
						salePrice
						taxStatus
						taxClass
					}
					... on InventoriedProduct {
						manageStock
						stockQuantity
						backorders
						soldIndividually
						backordersAllowed
						stockStatus
					}
					... on ProductWithDimensions {
						weight
						length
						width
						height
						shippingClassId
						shippingRequired
						shippingTaxable
					}
					... on DownloadableProduct {
						downloadExpiry
						downloadable
						downloadLimit
					}
				}
			}
		';

		/**
		 * Assertion One
		 *
		 * Test querying product.
		 */
		$variables = array( 'id' => $this->toRelayId( 'post', $product_id ) );
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
		$variables = array(
			'id'     => $this->toRelayId( 'post', $product_id ),
			'format' => 'RAW',
		);
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array(
			$this->expectedField( 'product.description', $product->get_description() ),
			$this->expectedField( 'product.shortDescription', $product->get_short_description() ),
			$this->expectedField( 'product.totalSales', $product->get_total_sales() ),
			$this->expectedField( 'product.catalogVisibility', strtoupper( $product->get_catalog_visibility() ) ),
		);

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testProductsQueryWithAttributesFilter() {
		// Create product attributes.
		$kind_attribute  = $this->factory->product->createAttribute( 'kind', array( 'special', 'normal' ), 'Product type' );
		$normal_term_id  = get_term_by( 'slug', 'normal', 'pa_kind' )->term_id;
		$special_term_id = get_term_by( 'slug', 'special', 'pa_kind' )->term_id;

		// Create attribute objects.
		$kind_attribute_normal_only = $this->factory->product->createAttributeObject(
			$kind_attribute['attribute_id'],
			$kind_attribute['attribute_taxonomy'],
			array( $normal_term_id )
		);

		$kind_attribute_special_only = $this->factory->product->createAttributeObject(
			$kind_attribute['attribute_id'],
			$kind_attribute['attribute_taxonomy'],
			array( $special_term_id )
		);

		$kind_attribute_both = $this->factory->product->createAttributeObject(
			$kind_attribute['attribute_id'],
			$kind_attribute['attribute_taxonomy'],
			array( $normal_term_id, $special_term_id )
		);

		// Create products.
		$normal_product_id  = $this->factory->product->createSimple(
			array(
				'attributes'         => array( $kind_attribute_normal_only ),
				'default_attributes' => array( 'pa_kind' => 'normal' ),
			)
		);
		$special_product_id = $this->factory->product->createSimple(
			array(
				'attributes'         => array( $kind_attribute_special_only ),
				'default_attributes' => array( 'pa_kind' => 'special' ),
			)
		);
		$both_product_id    = $this->factory->product->createSimple(
			array(
				'attributes' => array( $kind_attribute_both ),
			)
		);

		// Create query.
		$query = '
			query( $where: RootQueryToProductUnionConnectionWhereArgs ) {
				products( where: $where ) {
					nodes {
						... on SimpleProduct {
							id
						}
					}
				}
			}
		';

		$variables = array(
			'where' => array(
				'attributes' => array(
					'queries' => array(
						array(
							'taxonomy' => 'PA_KIND',
							'terms'    => array( 'normal' ),
						),
					),
				),
			),
		);
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array(
			$this->expectedNode(
				'products.nodes',
				array( 'id' => $this->toRelayId( 'post', $normal_product_id ) )
			),
		);

		$this->assertQuerySuccessful( $response, $expected );

		$variables = array(
			'where' => array(
				'attributes' => array(
					'queries' => array(
						array(
							'taxonomy' => 'PA_KIND',
							'terms'    => array( 'special' ),
						),
					),
				),
			),
		);
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array(
			$this->expectedNode(
				'products.nodes',
				array( 'id' => $this->toRelayId( 'post', $special_product_id ) )
			),
		);
		$this->assertQuerySuccessful( $response, $expected );

		$variables = array(
			'where' => array(
				'attributes' => array(
					'queries' => array(
						array(
							'taxonomy' => 'PA_KIND',
							'terms'    => array( 'normal', 'special' ),
						),
					),
				),
			),
		);
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array(
			$this->expectedNode(
				'products.nodes',
				array( 'id' => $this->toRelayId( 'post', $both_product_id ) )
			),
		);

		$this->assertQuerySuccessful( $response, $expected );

		$variables = array(
			'where' => array(
				'attributes' => array(
					'queries' => array(
						array(
							'taxonomy' => 'PA_KIND',
							'terms'    => array( 'normal', 'special' ),
							'operator' => 'NOT_IN',
						),
					),
				),
			),
		);
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array(
			$this->expectedField(
				'products.nodes',
				static::IS_FALSY
			),
		);

		$this->assertQuerySuccessful( $response, $expected );

		$variables = array(
			'where' => array(
				'attributes' => array(
					'queries' => array(
						array(
							'taxonomy' => 'PA_KIND',
							'terms'    => array( 'normal' ),
							'operator' => 'NOT_IN',
						),
					),
				),
			),
		);
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array(
			$this->expectedNode(
				'products.nodes',
				array( 'id' => $this->toRelayId( 'post', $special_product_id ) )
			),
		);

		$this->assertQuerySuccessful( $response, $expected );

		$variables = array(
			'where' => array(
				'attributes' => array(
					'queries'  => array(
						array(
							'taxonomy' => 'PA_KIND',
							'terms'    => array( 'normal' ),
						),
						array(
							'taxonomy' => 'PA_KIND',
							'terms'    => array( 'special' ),
						),
					),
					'relation' => 'AND',
				),
			),
		);
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array(
			$this->expectedNode(
				'products.nodes',
				array( 'id' => $this->toRelayId( 'post', $both_product_id ) )
			),
		);

		$this->assertQuerySuccessful( $response, $expected );
	}
}
