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
		$this->product  = $this->helper->create();
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
				'product' => $this->helper->get_query_data( $this->product ),
			),
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEqualSets( $expected, $actual );
	}

	public function testProductByQuery() {
		$product = \wc_get_product( $this->product );
		$query = '
			query productQuery( $productId: Int! ) {
				productBy(productId: $productId) {
					productId
					name
					slug
				}
			}
		';

		$variables = array( 'productId' => $this->product );
		$actual    = do_graphql_request( $query, 'productQuery', $variables );
		$expected  = array(
			'data' => array(
				'productBy' => array(
					'productId' => $product->get_id(),
					'name'      => $product->get_name(),
					'slug'      => $product->get_slug(),
				),
			),
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );
	}

	public function testProductsQuery() {
		$product = \wc_get_product( $this->product );
		$query = '
			query {
				products {
					nodes {
						productId
						name
						slug
					}
				}
			}
		';

		$actual = do_graphql_request( $query );

		$expected = array(
			'data' => array(
				'products' => array(
					'nodes' => array(
						array(
							'productId' => $product->get_id(),
							'name'      => $product->get_name(),
							'slug'      => $product->get_slug(),
						),
					),
				),
			),
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );
	}
}
