<?php 

class QLSessionHandlerCest {
    public function _before( FunctionalTester $I ) {
        $I->loginAsAdmin();
        $I->amOnPluginsPage();
        $I->activatePlugin(
            array(
                'woocommerce',
                'wp-graphql',
                'wp-graphql-jwt-authentication',
                'wp-graphql-woocommerce',
            )
        );
    }

    // tests
    public function test_session_update( FunctionalTester $I ) {
        $product_id = $I->havePostInDatabase( array(
            'post_type'  => 'product',
            'post_title' => 't-shirt',
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
        ));
        
        
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
            'productId'        => $product_id,
            'quantity'         => 2,
        );
        
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
        $response = $I->canSeeHttpHeader( 'woocommerce-session' );
        
    }
}
