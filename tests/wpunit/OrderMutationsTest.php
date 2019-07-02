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
        $input = array(
            'clientMutationId' => 'someId',
			'customerId'         => $this->customer,
			'customerNote'       => 'Customer test note',
			'coupons'            => array(),
			'status'             => 'PENDING',
			'paymentMethod'      => '',
			'paymentMethodTitle' => '',
			'transactionId'      => '',
			'billing'            => array(
                'firstName' => 'May',
                'lastName'  => 'Parker',
                'address1'  => '20 Ingram St',
                'city'      => 'New York City',
                'state'     => 'NY',
                'postcode'  => '12345',
                'country'   => 'US',
                'email'     => 'superfreak500@gmail.com',
                'phone'     => '555-555-1234',
            ),
			'shipping'           => array(
                'firstName' => 'May',
                'lastName'  => 'Parker',
                'address1'  => '20 Ingram St',
                'city'      => 'New York City',
                'state'     => 'NY',
                'postcode'  => '12345',
                'country'   => 'US',
            ),
			'lineItems'          => array(
                array(
                    'productId' => $product_id = $this->product->create_simple(),
                    'quantity'  => 2,
                )
            ),
			'shippingLines'      => array(),
			'feeLines'           => array(),
			'metaData'           => array(),
			'isPaid'             => false,
        );

        /**
		 * Assertion One
		 * 
		 * User without necessary capabilities cannot create order an order.
		 */
		wp_set_current_user( $this->customer );
        $actual = $this->createOrder( $input );

        // use --debug flag to view.
        codecept_debug( $actual );

        $this->assertArrayHasKey('errors', $actual );

        /**
		 * Assertion Two
		 * 
		 * User without necessary capabilities cannot create order an order.
		 */
		wp_set_current_user( $this->shop_manager );
        $actual = $this->createOrder( $input );

        // use --debug flag to view.
        codecept_debug( $actual );

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