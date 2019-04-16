<?php

use GraphQLRelay\Relay;
class ProductQueriesTest extends \Codeception\TestCase\WPTestCase {
	private $shop_manager;
	private $customer;
	private $product;

	public function setUp() {
		// before
		parent::setUp();

		$this->shop_manager  = $this->factory->user->create( array( 'role' => 'shop_manager' ) );
		$this->customer      = $this->factory->user->create( array( 'role' => 'customer' ) );
		$this->helper        = $this->getModule('\Helper\Wpunit')->product();
		$this->product       = $this->helper->create_simple();
	}

	public function tearDown() {
		// your tear down methods here
		// then
		parent::tearDown();
	}

	// tests
	public function testProductQuery() {
		$query = '
			query productQuery( $id: ID! ) {
				product(id: $id) {
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
		$id = Relay::toGlobalId( 'product', $this->product );
		$query = '
			query productQuery( $id: ID, $productId: Int ) {
				productBy(id: $id productId: $productId ) {
					id
				}
			}
		';

		$variables = array( 'productId' => $this->product );
		$actual    = do_graphql_request( $query, 'productQuery', $variables );
		$expected  = array( 'data' => array( 'productBy' => array( 'id' => $id ) ) );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );

		$variables = array( 'id' => $id );
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
				$type: String,
				$typeIn: [String],
				$typeNotIn: [String],
				$featured: Boolean,
				$maxPrice: String,
			){
				products( where: {
					slug: $slug,
					status: $status,
					type: $type,
					typeIn: $typeIn,
					typeNotIn: $typeNotIn,
					featured: $featured,
					maxPrice: $maxPrice,
					orderby: { field: SLUG, order: ASC }
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
					'nodes' => array_map(
						function( $id ) {
							return array( 'id' => Relay::toGlobalId( 'product', $id ) );
						},
						$products
					)
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
					'nodes' => array_map(
						function( $id ) {
							return array( 'id' => Relay::toGlobalId( 'product', $id ) );
						},
						array_values(
							array_filter(
								$products,
								function( $id ) {
									$product = \wc_get_product( $id );
									return 'test-product-1' === $product->get_slug();
								}
							)
						)
					)
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
		$variables = array( 'type' => 'simple' );
		$actual = do_graphql_request( $query, 'ProductsQuery', $variables );
		$expected = array(
			'data' => array(
				'products' => array(
					'nodes' => array_map(
						function( $id ) {
							return array( 'id' => Relay::toGlobalId( 'product', $id ) );
						},
						array_values(
							array_filter(
								$products,
								function( $id ) {
									$product = \wc_get_product( $id );
									return 'simple' === $product->get_type();
								}
							)
						)
					)
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
		$variables = array( 'typeIn' => array( 'simple' ) );
		$actual = do_graphql_request( $query, 'ProductsQuery', $variables );
		$expected = array(
			'data' => array(
				'products' => array(
					'nodes' => array_map(
						function( $id ) {
							return array( 'id' => Relay::toGlobalId( 'product', $id ) );
						},
						array_values(
							array_filter(
								$products,
								function( $id ) {
									$product = \wc_get_product( $id );
									return 'simple' === $product->get_type();
								}
							)
						)
					)
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
		$variables = array( 'typeNotIn' => array( 'simple' ) );
		$actual = do_graphql_request( $query, 'ProductsQuery', $variables );
		$expected = array(
			'data' => array(
				'products' => array(
					'nodes' => array_map(
						function( $id ) {
							return array( 'id' => Relay::toGlobalId( 'product', $id ) );
						},
						array_values(
							array_filter(
								$products,
								function( $id ) {
									$product = \wc_get_product( $id );
									return 'simple' !== $product->get_type();
								}
							)
						)
					)
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
					'nodes' => array_map(
						function( $id ) {
							return array( 'id' => Relay::toGlobalId( 'product', $id ) );
						},
						array_values(
							array_filter(
								$products,
								function( $id ) {
									$product = \wc_get_product( $id );
									return $product->get_featured();
								}
							)
						)
					)
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
		$variables = array( 'maxPrice' => '10.00');
		$actual = do_graphql_request( $query, 'ProductsQuery', $variables );
		$expected = array(
			'data' => array(
				'products' => array(
					'nodes' => array_map(
						function( $id ) {
							return array( 'id' => Relay::toGlobalId( 'product', $id ) );
						},
						array_values(
							array_filter(
								$products,
								function( $id ) {
									$product = \wc_get_product( $id );
									return 10.00 >= floatval( $product->get_price() );
								}
							)
						)
					)
				),
			),
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );
	}
}
