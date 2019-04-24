<?php

use GraphQLRelay\Relay;

class ProductVariationQueriesTest extends \Codeception\TestCase\WPTestCase {
	private $shop_manager;
	private $customer;
    private $variation;
    
    public function setUp() {
        parent::setUp();

        $this->shop_manager   = $this->factory->user->create( array( 'role' => 'shop_manager' ) );
        $this->customer       = $this->factory->user->create( array( 'role' => 'customer' ) );
        $this->product_helper = $this->getModule('\Helper\Wpunit')->product();
		$this->helper         = $this->getModule('\Helper\Wpunit')->product_variation();
		$this->products       = $this->helper->create( $this->product_helper->create_variable() );
    }

    public function tearDown() {
        // your tear down methods here

        // then
        parent::tearDown();
    }

    // tests
    public function testVariationQuery() {
        $variation_id = $this->products['variations'][0];
        $id           = Relay::toGlobalId( 'product_variation', $variation_id );

        $query        = '
            query variationQuery( $id: ID, $variationId: Int ) {
                productVariation( id: $id, variationId: $variationId ) {
                    id
                    variationId
                    sku
                    weight
                    length
                    width
                    height
                    taxClass
                    manageStock
                    stockQuantity
                    backorders
                    purchaseNote
                    shippingClass
                    catalogVisibility
                    hasAttributes
                    isPurchasable
                    price
                    regularPrice
                    salePrice
                }
            }
        ';

        /**
		 * Assertion One
		 * 
		 * test query and "id" query argument
		 */
		$variables = array( 'id' => $id );
		$actual = do_graphql_request( $query, 'variationQuery', $variables );
		$expected = array( 'data' => array( 'productVariation' => $this->helper->print_query( $variation_id ) ) );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEqualSets( $expected, $actual );

		/**
		 * Assertion Two
		 * 
		 * test query and "methodId" query argument
		 */
		$variables = array( 'variationId' => $variation_id );
		$actual = do_graphql_request( $query, 'variationQuery', $variables );
		$expected = array( 'data' => array( 'productVariation' => $this->helper->print_query( $variation_id ) ) );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEqualSets( $expected, $actual );
    }

    public function testVariationsQueryAndWhereArgs() {
        $id = Relay::toGlobalId( 'product', $this->products['product'] );
        $variations = $this->products['variations'];

        $query      = '
            query variationsQuery( $id: ID!, $minPrice: Float ) {
                product( id: $id ) {
                    variations( where: { minPrice: $minPrice } ) {
                        nodes {
                            id
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
        wp_set_current_user( $this->shop_manager );
        $variables = array( 'id' => $id );
		$actual    = do_graphql_request( $query, 'variationsQuery', $variables );
		$expected  = array(
			'data' => array(
                'product' => array(
                    'variations' => array(
                        'nodes' => $this->helper->print_nodes( $variations ),
                    ),
                ),
			),
		);

		// use --debug flag to view.
		codecept_debug( $actual );

        $this->assertEqualSets( $expected, $actual );
        
        /**
		 * Assertion Two
		 * 
		 * Test "minPrice" where argument
		 */
        $variables = array( 'id' => $id, 'minPrice' => 15 );
		$actual    = do_graphql_request( $query, 'variationsQuery', $variables );
		$expected  = array(
			'data' => array(
                'product' => array(
                    'variations' => array(
                        'nodes' => $this->helper->print_nodes(
                            $variations,
                            array(
                                'filter' => function( $id ) {
                                    $variation = new WC_Product_Variation( $id );
                                    return 15.00 <= floatval( $variation->get_price() );
                                }
                            )
                        ),
                    ),
                ),
			),
		);

		// use --debug flag to view.
		codecept_debug( $actual );

        $this->assertEqualSets( $expected, $actual );
    }

}