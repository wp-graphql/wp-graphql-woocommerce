<?php

class MetaDataQueriesTest extends \Codeception\TestCase\WPTestCase {

    public function setUp() {
        // before
        parent::setUp();

        $this->shop_manager = $this->factory->user->create( array( 'role' => 'shop_manager' ) );
        $this->customer     = $this->factory->user->create( array( 'role' => 'customer' ) );
        $this->coupons      = $this->getModule('\Helper\Wpunit')->coupon();
        $this->customers    = $this->getModule('\Helper\Wpunit')->customers();
        $this->orders       = $this->getModule('\Helper\Wpunit')->orders();
        $this->products     = $this->getModule('\Helper\Wpunit')->product();
        $this->variations   = $this->getModule('\Helper\Wpunit')->product_variations();

        $this->createObjects();
    }

    public function tearDown() {
        // your tear down methods here

        // then
        parent::tearDown();
    }

    private function createObjects() {
        $data = array(

        );

        $this->coupon_id   = $this->coupons->create( $data );
        $this->customer_id = $this->customers->create( $data );
        $this->order_id   = $this->orders->create( $data );
        $this->product_id = $this->products->create_variable( $data );
        $this->variation_ids = $this->variations->create( $this->product_id, $data  );
    }

    // tests
    public function testCouponMetaDataQueries() {
        $id    = Relay::toGlobalId( 'shop_coupon', $this->coupon_id );
        $query = '
            query ($id: ID!) {
                coupon(id: $id) {
                    id
                    metaData
                }
            }
        ';
        
        $variables = array( 'id' => $id );
        $actual    = graphql(
            array(
                'query'     => $query,
                'variables' => $variables,
            )
        );
        $expected = array(
            'data' => array(
                'coupon' => array(
                    'id' => $id,
                    'metaData' => array(
                        array(
                            'key'   => '',
                            'value' => ''
                        ),
                        array(
                            'key'   => '',
                            'value' => ''
                        ),
                    )
                ),
            ),
        );

        // use --debug flag to view.
        codecept_debug( $actual );

        $this->assertEqualSets( $expected, $actual );
    }

    public function testCustomerMetaDataQueries() {
        $query = '
            query {
                customer {
                    id
                    metaData
                }
            }
        ';
        
        wp_set_current_user( $this->customer_id );
        $actual    = graphql(
            array(
                'query'     => $query,
                'variables' => $variables,
            )
        );
        $expected = array(
            'data' => array(
                'customer' => array(
                    'id' => Relay::toGlobalId( 'customer', $this->customer_id ),
                    'metaData' => array(
                        array(
                            'key'   => '',
                            'value' => ''
                        ),
                        array(
                            'key'   => '',
                            'value' => ''
                        ),
                    )
                ),
            ),
        );

        // use --debug flag to view.
        codecept_debug( $actual );

        $this->assertEqualSets( $expected, $actual );
    }

    public function testProductMetaDataQueries() {
        $id    = Relay::toGlobalId( 'product', $this->product_id );
        $query = '
            query ($id: ID!) {
                product(id: $id) {
                    id
                    metaData
                }
            }
        ';
        
        $variables = array( 'id' => $id );
        $actual    = graphql(
            array(
                'query'     => $query,
                'variables' => $variables,
            )
        );
        $expected = array(
            'data' => array(
                'product' => array(
                    'id' => $id,
                    'metaData' => array(
                        array(
                            'key'   => '',
                            'value' => ''
                        ),
                        array(
                            'key'   => '',
                            'value' => ''
                        ),
                    )
                ),
            ),
        );

        // use --debug flag to view.
        codecept_debug( $actual );

        $this->assertEqualSets( $expected, $actual );
    }

    public function testProductVariationMetaDataQueries() {
        $id    = Relay::toGlobalId( 'product', $this->variation_ids['variations'][0] );
        $query = '
            query ($id: ID!) {
                productVariation(id: $id) {
                    id
                    metaData
                }
            }
        ';
        
        $variables = array( 'id' => $id );
        $actual    = graphql(
            array(
                'query'     => $query,
                'variables' => $variables,
            )
        );
        $expected = array(
            'data' => array(
                'product' => array(
                    'id' => $id,
                    'metaData' => array(
                        array(
                            'key'   => '',
                            'value' => ''
                        ),
                        array(
                            'key'   => '',
                            'value' => ''
                        ),
                    )
                ),
            ),
        );

        // use --debug flag to view.
        codecept_debug( $actual );

        $this->assertEqualSets( $expected, $actual );
    }

    public function testCartMetaDataQueries() {
        
    }

    public function testOrderMetaDataQueries() {
        
    }

    public function testRefundMetaDataQueries() {
        
    }

}