<?php 

class QLSessionHandlerCest {
    private $product_id;

    public function _before( FunctionalTester $I ) {
        // Create Product
        $this->product_id = $I->havePostInDatabase(
            array(
                'post_type'  => 'product',
                'post_name' => 't-shirt',
                'meta_input' => array(
                    '_visibility'             => 'visible',
                    '_sku'                    => '',
                    '_price'                  => '100',
                    '_regular_price'          => '100',
                    '_sale_price'             => '',
                    '_sale_date_on_sale_from' => null,
                    '_sale_date_on_sale_to'   => null,
                    'total_sales'             => '0',
                    '_tax_status'             => 'taxable',
                    '_tax_class'              => '',
                    '_manage_stock'           => false,
                    '_stock_quantity'         => null,
                    '_stock_status'           => 'instock',
                    '_backorders'             => 'no',
                    '_low_stock_amount'       => '',
                    '_sold_individually'      => false,
                    '_weight'                 => '',
                    '_length'                 => '',
                    '_width'                  => '',
                    '_height'                 => '',
                    '_upsell_ids'             => array(),
                    '_cross_sell_ids'         => array(),
                    '_purchase_note'          => '',
                    '_default_attributes'     => array(),
                    '_product_attributes'     => array(),
                    '_virtual'                => false,
                    '_downloadable'           => false,
                    '_download_limit'         => -1,
                    '_download_expiry'        => -1,
                    '_featured'               => false,
                    '_wc_rating_counts'       => array(),
                    '_wc_average_rating'      => 0,
                    '_wc_review_count'        => 0,        
                ),
            )
        );
        $term_id          = $I->grabTermIdFromDatabase( [ 'name' => 'simple', 'slug' => 'simple' ] );
        $term_taxonomy_id = $I->grabTermTaxonomyIdFromDatabase( [ 'term_id' => $term_id, 'taxonomy' => 'product_type' ] );
        $I->haveTermRelationshipInDatabase( $this->product_id, $term_id );
    }

    // tests
    public function test_session_update( FunctionalTester $I ) {
        $mutation = '
            mutation addToCart( $input: AddToCartInput! ) {
                addToCart( input: $input ) {
                    clientMutationId
                    cartItem {
                        key
                        product {
                            id
                        }
                        variation {
                            id
                        }
                        quantity
                        subtotal
                        subtotalTax
                        total
                        tax
                    }
                }
            }
        ';
        
        $input = array(
            'clientMutationId' => 'someId',
            'productId'        => $this->product_id,
            'quantity'         => 2,
        );
        
        // Add item to cart.
        $I->haveHttpHeader( 'Content-Type', 'application/json' );
        $I->sendPOST(
            '/graphql',
            json_encode(
                array(
                    'query'     => $mutation,
                    'variables' => array( 'input' => $input ),
                )
            )
        );

        $I->seeResponseCodeIs( 200 );
        $I->seeHttpHeaderOnce('woocommerce-session');
        $wc_session_header = $I->grabHttpHeader( 'woocommerce-session' );
        $I->seeResponseIsJson();
        $mutation_response = $I->grabResponse();
        $mutation_data     = json_decode( $mutation_response, true );

        // use --debug flag to view
        codecept_debug( $mutation_data );

        $I->assertArrayHasKey('data', $mutation_data );
        $I->assertArrayHasKey('addToCart', $mutation_data['data'] );
        $I->assertArrayHasKey('cartItem', $mutation_data['data']['addToCart'] );
        $I->assertArrayHasKey('key', $mutation_data['data']['addToCart']['cartItem'] );
        $key = $mutation_data['data']['addToCart']['cartItem']['key'];

        $query = '
            query {
                cart {
                    contents {
                        nodes {
                            key
                        }
                    }
                }
            }
        ';

        // Set session header and query cart.
        $I->haveHttpHeader( 'woocommerce-session', $wc_session_header );
        $I->sendPOST(
            '/graphql',
            json_encode( array( 'query' => $query ) )
        );

        $I->seeResponseCodeIs( 200 );
        $I->seeResponseIsJson();
        $query_response = $I->grabResponse();
        $query_data     = json_decode( $query_response, true );

        // use --debug flag to view.
        codecept_debug( $query_data );

        $expected = array(
            'data' => array(
                'cart' => array(
                    'contents' => array(
                        'nodes' => array(
                            array(
                                'key' => $key,
                            ),
                        ),
                    ),
                ),
            ),
        );

        $I->assertEquals( $expected, $query_data );
        
    }
}
