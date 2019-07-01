<?php

class OrderMutationsTest extends \Codeception\TestCase\WPTestCase {

    public function setUp() {
        // before
        parent::setUp();

        $this->shop_manager = $this->factory->user->create( array( 'role' => 'shop_manager' ) );
        $this->customer     = $this->factory->user->create( array( 'role' => 'customer' ) );

        $this->order        = $this->getModule('\Helper\Wpunit')->order();
        $this->coupon       = $this->getModule('\Helper\Wpunit')->coupon();
        $this->product      = $this->getModule('\Helper\Wpunit')->product();
        $this->variation    = $this->getModule('\Helper\Wpunit')->product_variation();
        $this->cart         = $this->getModule('\Helper\Wpunit')->cart();
    }

    public function tearDown() {
        // your tear down methods here

        // then
        parent::tearDown();
    }

    private function createOrder( $input ) {
        $mutation = '
            mutation createOrder( $input: CreateOrderInput! ) {
                createOrder( input: $input ) {
                    clientMutationId
                    order {

                    }
                }
            }
        ';

        $actual = graphql(
            array(
                'query'          => $mutation,
                'operation_name' => 'createOrder',
                'variables'      => array( 'input' => $input  ),
            )
        );

        return $actual;
    }

    // tests
    public function testCreateOrderMutationAndArgs() {
        $product_id = $this->product->create_simple();

        /**
		 * Assertion One
		 * 
		 * User without necessary capabilities cannot create order an order.
		 */
		wp_set_current_user( $this->customer );
        $createOrder = $this->createOrder(
            array(
                'clientMutationId' => 'someId',
            )
        );

        // use --debug flag to view.
        codecept_debug( $createOrder );

        $this->assertArrayHasKey('errors', $createOrder );

        /**
		 * Assertion Two
		 * 
		 * User without necessary capabilities cannot create order an order.
		 */
		wp_set_current_user( $this->shop_manager );
        $createOrder = $this->createOrder(
            array(
                'clientMutationId' => 'someId',
            )
        );

        // use --debug flag to view.
        codecept_debug( $createOrder );

        $this->assertArrayHasKey('data', $actual );
        $this->assertArrayHasKey('createOrder', $actual['data'] );
        $this->assertArrayHasKey('order', $actual['data']['createOrder'] );
        $this->assertArrayHasKey('id', $actual['data']['createOrder']['order'] );
        $order_id = $actual['data']['createOrder']['order']['id'];

        $expected = array(
            'data' => array(
                'createOrder' => array(
                    'clientMutationId' => 'someId',
                    'order'            => $this->order->print_query( $order_id ),
                ),
            )
        );

        $this->assertEqualSets( $expected, $actual );
    }

}