<?php 

class QLSessionHandlerCest {
    private $product_id;

    public function _before( FunctionalTester $I ) {
        // Create Product
        $this->product_catalog = $I->getCatalog();
    }

    // tests
    public function testCartSessionToken( FunctionalTester $I ) {
        $mutation = '
            mutation addToCart( $input: AddToCartInput! ) {
                addToCart( input: $input ) {
                    clientMutationId
                    cartItem {
                        key
                        product {
                            ... on SimpleProduct {
                                id
                            }
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
            'productId'        => $this->product_catalog['t-shirt'],
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
        $wc_session_header = 'Session ' . $I->grabHttpHeader( 'woocommerce-session' );
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
