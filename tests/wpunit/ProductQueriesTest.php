<?php

use GraphQLRelay\Relay;
class ProductQueriesTest extends \Codeception\TestCase\WPTestCase {
	private $shop_manager;
	private $customer;
	private $helper;
	private $product;
	private $product_tag;
	private $product_cat;

	public function setUp() {
		// before
		parent::setUp();

		$this->shop_manager  = $this->factory->user->create( array( 'role' => 'shop_manager' ) );
		$this->customer      = $this->factory->user->create( array( 'role' => 'customer' ) );
		$this->helper        = $this->getModule('\Helper\Wpunit')->product();
		$this->product_tag   = 'tag-one';
		$this->product_cat   = 'category-one';
		$this->image_id     = $this->factory->post->create(
			array(
				'post_author'  => $this->shop_manager,
				'post_content' => '',
				'post_excerpt' => '',
				'post_status'  => 'publish',
				'post_title'   => 'Product Image',
				'post_type'    => 'attachment',
				'post_content' => 'product image',
			)
		);
		$this->product       = $this->helper->create_simple(
			array(
				'tag_ids'           => array( $this->helper->create_product_tag( $this->product_tag ) ),
				'category_ids'      => array( $this->helper->create_product_category( $this->product_cat ) ),
				'image_id'          => $this->image_id,
				'gallery_image_ids' => array( $this->image_id ),
				'downloads'         => array( ProductHelper::create_download() ),
				'slug'              => 'product-slug',
				'sku'               => 'product-sku',
			)
		);
	}

	public function tearDown() {
		// your tear down methods here
		$product = \WC()->product_factory->get_product( $this->product );
		$product->delete( true );

		parent::tearDown();
	}

	// tests
	public function testProductQuery() {
		$query = '
			query productQuery( $id: ID! ) {
				product(id: $id) {
					id
					productId
					name
					slug
					date
					modified
					status
					featured
					catalogVisibility
					description
					shortDescription
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
				}
			}
		';
		
		$variables = array( 'id' => Relay::toGlobalId( 'product', $this->product ) );
		$actual = do_graphql_request( $query, 'productQuery', $variables );
		$expected = array(
			'data' => array(
				'product' => $this->helper->print_query( $this->product ),
			),
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEqualSets( $expected, $actual );
	}

	public function testProductByQueryAndArgs() {
		$id = $this->helper->to_relay_id( $this->product );
		$query = '
			query productQuery( $id: ID, $productId: Int, $slug: String, $sku: String ) {
				productBy( id: $id, productId: $productId, slug: $slug, sku: $sku ) {
					id
				}
			}
		';

		/**
		 * Assertion One
		 * 
		 * Test querying product with "productId" argument.
		 */
		$variables = array( 'productId' => $this->product );
		$actual    = do_graphql_request( $query, 'productQuery', $variables );
		$expected  = array( 'data' => array( 'productBy' => array( 'id' => $id ) ) );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );

		/**
		 * Assertion Two
		 * 
		 * Test querying product with "id" argument.
		 */
		$variables = array( 'id' => $id );
		$actual    = do_graphql_request( $query, 'productQuery', $variables );
		$expected  = array( 'data' => array( 'productBy' => array( 'id' => $id ) ) );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );

		/**
		 * Assertion Three
		 * 
		 * Test querying product with "slug" argument.
		 */
		$variables = array( 'slug' => 'product-slug' );
		$actual    = do_graphql_request( $query, 'productQuery', $variables );
		$expected  = array( 'data' => array( 'productBy' => array( 'id' => $id ) ) );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );

		/**
		 * Assertion Four
		 * 
		 * Test querying product with "sku" argument.
		 */
		$variables = array( 'sku' => 'product-sku' );
		$actual    = do_graphql_request( $query, 'productQuery', $variables );
		$expected  = array( 'data' => array( 'productBy' => array( 'id' => $id ) ) );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );
	}

	public function testProductsQueryAndWhereArgs() {
		$products = array (
			$this->product,
			$this->helper->create_simple(
				array(
					'price'         => 10,
					'regular_price' => 10,
				)
			),
			$this->helper->create_simple(
				array(
					'featured' => "true",
				)
			),
			$this->helper->create_external(),
		);

		$query = '
			query ProductsQuery(
				$slug: String,
				$status: String,
				$type: ProductTypesEnum,
				$typeIn: [ProductTypesEnum],
				$typeNotIn: [ProductTypesEnum],
				$featured: Boolean,
				$maxPrice: Float,
				$orderby: [WCConnectionOrderbyInput]
			){
				products( where: {
					slug: $slug,
					status: $status,
					type: $type,
					typeIn: $typeIn,
					typeNotIn: $typeNotIn,
					featured: $featured,
					maxPrice: $maxPrice,
					orderby: $orderby
				} ) {
					nodes {
						id
					}
				}
			}
		';

		/**
		 * Assertion One
		 * 
		 * tests query with no arguments
		 */
		$actual = do_graphql_request( $query, 'ProductsQuery' );
		$expected = array(
			'data' => array(
				'products' => array(
					'nodes' => $this->helper->print_nodes( $products ),
				),
			),
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );

		/**
		 * Assertion Two
		 * 
		 * tests "slug" where argument
		 */
		$variables = array( 'slug' => 'test-product-1' );
		$actual = do_graphql_request( $query, 'ProductsQuery', $variables );
		$expected = array(
			'data' => array(
				'products' => array(
					'nodes' => $this->helper->print_nodes(
						$products,
						array(
							'filter' => function( $id ) {
								$product = \wc_get_product( $id );
								return 'test-product-1' === $product->get_slug();
							},
						)
					),
				),
			),
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );

		/**
		 * Assertion Three
		 * 
		 * tests "status" where argument
		 */
		$variables = array( 'status' => 'pending' );
		$actual = do_graphql_request( $query, 'ProductsQuery', $variables );
		$expected = array( 'data' => array( 'products' => array( 'nodes' => array() ) ) );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );

		/**
		 * Assertion Four
		 * 
		 * tests "type" where argument
		 */
		$variables = array( 'type' => 'SIMPLE' );
		$actual = do_graphql_request( $query, 'ProductsQuery', $variables );
		$expected = array(
			'data' => array(
				'products' => array(
					'nodes' => $this->helper->print_nodes(
						$products,
						array(
							'filter' => function( $id ) {
								$product = \wc_get_product( $id );
								return 'simple' === $product->get_type();
							},
						)
					),
				),
			),
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );

		/**
		 * Assertion Five
		 * 
		 * tests "typeIn" where argument
		 */
		$variables = array( 'typeIn' => array( 'SIMPLE' ) );
		$actual = do_graphql_request( $query, 'ProductsQuery', $variables );
		$expected = array(
			'data' => array(
				'products' => array(
					'nodes' => $this->helper->print_nodes(
						$products,
						array(
							'filter' => function( $id ) {
								$product = \wc_get_product( $id );
								return 'simple' === $product->get_type();
							},
						)
					),
				),
			),
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );

		/**
		 * Assertion Six
		 * 
		 * tests "typeNotIn" where argument
		 */
		$variables = array( 'typeNotIn' => array( 'SIMPLE' ) );
		$actual = do_graphql_request( $query, 'ProductsQuery', $variables );
		$expected = array(
			'data' => array(
				'products' => array(
					'nodes' => $this->helper->print_nodes(
						$products,
						array(
							'filter' => function( $id ) {
								$product = \wc_get_product( $id );
								return 'simple' !== $product->get_type();
							},
						)
					),
				),
			),
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );

		/**
		 * Assertion Seven
		 * 
		 * tests "featured" where argument
		 */
		$variables = array( 'featured' => true );
		$actual = do_graphql_request( $query, 'ProductsQuery', $variables );
		$expected = array(
			'data' => array(
				'products' => array(
					'nodes' => $this->helper->print_nodes(
						$products,
						array(
							'filter' => function( $id ) {
								$product = \wc_get_product( $id );
								return $product->get_featured();
							},
						)
					),
				),
			),
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );

		/**
		 * Assertion Eight
		 * 
		 * tests "maxPrice" where argument
		 */
		$variables = array( 'maxPrice' => 10.00 );
		$actual = do_graphql_request( $query, 'ProductsQuery', $variables );
		$expected = array(
			'data' => array(
				'products' => array(
					'nodes' => $this->helper->print_nodes(
						$products,
						array(
							'filter' => function( $id ) {
								$product = \wc_get_product( $id );
								return 10.00 >= floatval( $product->get_price() );
							},
						)
					),
				),
			),
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );

		/**
		 * Assertion Nine
		 * 
		 * tests "orderby" where argument
		 */
		$variables = array( 'orderby' => array( array( 'field' => 'PRICE', 'order' => 'DESC' ) ) );
		$actual = do_graphql_request( $query, 'ProductsQuery', $variables );
		$expected = array(
			'data' => array(
				'products' => array(
					'nodes' => $this->helper->print_nodes(
						$products,
						array(
							'sorter' => function( $id_a, $id_b ) {
								$product_a = new \WC_Product( $id_a );
								$product_b = new \WC_Product( $id_b );

								if ( floatval( $product_a->get_price() ) === floatval( $product_b->get_price() ) ) {
									return 0;
								}
								return floatval( $product_a->get_price() ) > floatval( $product_b->get_price() ) ? -1 : 1;
							},
						)
					),
				),
			),
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );
	}

	public function testProductToTermConnection() {
		$id = Relay::toGlobalId( 'product', $this->product );
		$query = '
			query productQuery($id: ID!) {
				product(id: $id) {
					id
					tags {
						nodes {
						  	name
						}
					}
					categories {
						nodes {
						  	name
						}
					}
				}
			}
		';

		$variables = array( 'id' => $id );
		$actual    = do_graphql_request( $query, 'productQuery', $variables );
		$expected  = array(
			'data' => array(
				'product' => array(
					'id'         => $id,
					'tags'       => array(
						'nodes' => array(
							array( 'name' => $this->product_tag ),
						),
					),
					'categories' => array(
						'nodes' => array(
							array( 'name' => $this->product_cat ),
						),
					),
				)
			)
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );
	}

	public function testTermToProductConnection() {
		$id = Relay::toGlobalId( 'product', $this->product );
		$query = '
			query tagAndCategoryQuery {
				productTags( where: { hideEmpty: true } ) {
					nodes {
						name
						products {
							nodes {
								id
							}
						}
					}
				}
				productCategories( where: { hideEmpty: true } ) {
					nodes {
						name
						products {
							nodes {
								id
							}
						}
					}
				}
			}
		';

		$actual    = do_graphql_request( $query, 'tagAndCategoryQuery' );
		$expected  = array(
			'data' => array(
				'productTags' => array(
					'nodes' => array(
						array(
							'name'     => $this->product_tag,
							'products' => array(
								'nodes' => array(
									array (
										'id' => $id
									),
								),
							),
						),
					),
				),
				'productCategories' => array(
					'nodes' => array(
						array(
							'name'     => $this->product_cat,
							'products' => array(
								'nodes' => array(
									array (
										'id' => $id
									),
								),
							),
						),
					),
				),
			),
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );
	}

	public function testProductToMediaItemConnections() {
		$id       = Relay::toGlobalId( 'product', $this->product );
		$image_id = Relay::toGlobalId( 'attachment', $this->image_id );

		$query = '
			query productQuery( $id: ID! ) {
				product( id: $id ) {
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
		';

		$variables = array( 'id' => $id );
		$actual    = do_graphql_request( $query, 'productQuery', $variables );
		$expected  = array(
			'data' => array(
				'product' => array(
					'id'            => $id,
					'image'         => array(
						'id' => $image_id,
					),
					'galleryImages' => array(
						'nodes' => array(
							array( 'id' => $image_id ),
						),
					),
				),
			),
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );
	}

	public function testProductDownloads() {
		$id       = $this->helper->to_relay_id( $this->product );

		$query = '
			query productQuery( $id: ID! ) {
				product( id: $id ) {
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
		';

		$variables = array( 'id' => $id );
		$actual    = do_graphql_request( $query, 'productQuery', $variables );
		$expected  = array(
			'data' => array(
				'product' => array(
					'id'            => $id,
					'downloads'     => $this->helper->print_downloads($this->product),
				),
			),
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );
	}

	public function testExternalProductQuery() {
		$product_id = $this->helper->create_external();
		$query = '
			query productQuery( $id: ID! ) {
				product(id: $id) {
					id
					buttonText
					externalUrl
				}
			}
		';

		$variables = array( 'id' => $this->helper->to_relay_id( $product_id ) );
		$actual = do_graphql_request( $query, 'productQuery', $variables );
		$expected = array(
			'data' => array(
				'product' => $this->helper->print_external( $product_id ),
			),
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEqualSets( $expected, $actual );
	}

	public function testGroupProductConnections() {
		$grouped_product = $this->helper->create_grouped();
		$query = '
			query productQuery( $id: ID! ) {
				product(id: $id) {
					addToCartText
					addToCartDescription
					grouped {
						nodes {
							id
							parent {
								id
							}
						}
					}
				}
			}
		';

		$variables = array( 'id' => $this->helper->to_relay_id( $grouped_product['parent'] ) );
		$actual = do_graphql_request( $query, 'productQuery', $variables );
		$expected = array(
			'data' => array(
				'product' => $this->helper->print_grouped( $grouped_product['parent'] ),
			),
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEqualSets( $expected, $actual );
	}

	public function testRelatedProductConnections() {
		$products = $this->helper->create_related();
		$query = '
			query productQuery( $id: ID! ) {
				product(id: $id) {
					related {
						nodes {
							id
						}
					}
					crossSell {
						nodes {
							id
						}
					}
					upsell {
						nodes {
							id
						}
					}
				}
			}
		';

		$variables = array( 'id' => $this->helper->to_relay_id( $products['product'] ) );
		$actual = do_graphql_request( $query, 'productQuery', $variables );
		$expected = array(
			'data' => array(
				'product' => array(
					'related'   => array(
						'nodes' => $this->helper->print_nodes(
							array_merge( $products['related'], $products['cross_sell'], $products['upsell'] )
						),
					),
					'crossSell' => array( 'nodes' => $this->helper->print_nodes( $products['cross_sell'] ) ),
					'upsell'    => array( 'nodes' => $this->helper->print_nodes( $products['upsell'] ) ),
				),
			),
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEqualSets( $expected, $actual );
	}
}