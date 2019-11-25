<?php 

use Firebase\JWT\JWT;

class QLSessionHandlerCest {
    private $product_id;

    public function _before( FunctionalTester $I ) {
        // Create Product
        $this->product_catalog = $I->getCatalog();
    }

    // tests
    public function testCartSessionToken( FunctionalTester $I ) {
        /**
         * Add item to the cart
         */
        $success = $I->addToCart(
            array(
                'clientMutationId' => 'someId',
                'productId'        => $this->product_catalog['t-shirt'],
                'quantity'         => 5,
            )
        );
        
        $I->assertArrayNotHasKey( 'error', $success );
        $I->assertArrayHasKey('data', $success );
        $I->assertArrayHasKey('addToCart', $success['data'] );
        $I->assertArrayHasKey('cartItem', $success['data']['addToCart'] );
        $I->assertArrayHasKey('key', $success['data']['addToCart']['cartItem'] );
        $cart_item_key = $success['data']['addToCart']['cartItem']['key'];

        /**
         * Assert existence and validity of "woocommerce-session" HTTP header.
         */
        $I->seeHttpHeaderOnce( 'woocommerce-session' );
        $session_token = $I->grabHttpHeader( 'woocommerce-session' );

        // Decode token
        JWT::$leeway = 60;
        $token_data  = ! empty( $session_token )
            ? JWT::decode( $session_token, 'graphql-woo-cart-session', array( 'HS256' ) )
            : null;

        $I->assertNotEmpty( $token_data );
        $I->assertNotEmpty( $token_data->iss );
        $I->assertNotEmpty( $token_data->iat );
        $I->assertNotEmpty( $token_data->nbf );
        $I->assertNotEmpty( $token_data->exp );
        $I->assertNotEmpty( $token_data->data );
        $I->assertNotEmpty( $token_data->data->customer_id );

        $wp_url = getenv( 'WP_URL' );
        $I->assertEquals( $token_data->iss, $wp_url );

        /**
         * Make a cart query request with "woocommerce-session" HTTP Header and confirm
         * correct cart contents. 
         */
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

        $actual = $I->sendGraphQLRequest( $query, null, array( 'woocommerce-session' => "Session {$session_token}" ) );
        $expected = array(
            'data' => array(
                'cart' => array(
                    'contents' => array(
                        'nodes' => array(
                            array(
                                'key' => $cart_item_key,
                            ),
                        ),
                    ),
                ),
            ),
        );

        $I->assertEquals( $expected, $actual );

        /**
         * Remove item from the cart
         */        
        $success = $I->removeItemsFromCart(
            array(
                'clientMutationId' => 'someId',
                'keys'             => $cart_item_key,
            ),
            array( 'woocommerce-session' => "Session {$session_token}" )
        );
        
        $I->assertArrayNotHasKey( 'error', $success );
        $I->assertArrayHasKey('data', $success );
        $I->assertArrayHasKey('removeItemsFromCart', $success['data'] );
        $I->assertArrayHasKey('cartItems', $success['data']['removeItemsFromCart'] );
        $I->assertCount( 1, $success['data']['removeItemsFromCart']['cartItems'] );

        /**
         * Make a cart query request with "woocommerce-session" HTTP Header and confirm
         * correct cart contents. 
         */
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

        $actual = $I->sendGraphQLRequest( $query, null, array( 'woocommerce-session' => "Session {$session_token}" ) );
        $expected = array(
            'data' => array(
                'cart' => array(
                    'contents' => array(
                        'nodes' => array(),
                    ),
                ),
            ),
        );

        $I->assertEquals( $expected, $actual );

        /**
         * Restore item to the cart
         */        
        $success = $I->restoreCartItems(
            array(
                'clientMutationId' => 'someId',
                'keys'             => array( $cart_item_key ),
            ),
            array( 'woocommerce-session' => "Session {$session_token}" )
        );
        
        $I->assertArrayNotHasKey( 'error', $success );
        $I->assertArrayHasKey('data', $success );
        $I->assertArrayHasKey('restoreCartItems', $success['data'] );
        $I->assertArrayHasKey('cartItems', $success['data']['restoreCartItems'] );
        $I->assertCount( 1, $success['data']['restoreCartItems']['cartItems'] );

        /**
         * Make a cart query request with "woocommerce-session" HTTP Header and confirm
         * correct cart contents. 
         */
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

        $actual = $I->sendGraphQLRequest( $query, null, array( 'woocommerce-session' => "Session {$session_token}" ) );
        $expected = array(
            'data' => array(
                'cart' => array(
                    'contents' => array(
                        'nodes' => array(
                            array(
                                'key' => $cart_item_key,
                            ),
                        ),
                    ),
                ),
            ),
        );

        $I->assertEquals( $expected, $actual );
    }
}
