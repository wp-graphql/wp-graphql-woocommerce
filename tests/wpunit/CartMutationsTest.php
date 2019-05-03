<?php

class CartMutationsTest extends \Codeception\TestCase\WPTestCase {
    private $customer;
    private $product;
    private $variation;

    public function setUp() {
        parent::setUp();

        $this->customer  = $this->getModule('\Helper\Wpunit')->customer();
        $this->product   = $this->getModule('\Helper\Wpunit')->product();
        $this->variation = $this->getModule('\Helper\Wpunit')->product_variation();
    }

    public function tearDown() {
        // your tear down methods here

        parent::tearDown();
    }

    private function addToCart( $input ) {
        $mutation   = '
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

        $variables = array( 'input' => $input  );
        $actual    = graphql(
            array(
                'query'          => $mutation,
                'operation_name' => 'addToCart',
                'variables'      => $variables,
            )
        );

        return $actual;
    }

    // tests
    public function testAddToCartMutationWithProduct() {
        $product_id = $this->product->create_simple();
        $actual     = $this->addToCart(
            array(
                'clientMutationId' => 'someId',
                'productId'        => $product_id,
                'quantity'         => 2,
            )
        );

        // use --debug flag to view.
        codecept_debug( $actual );

        // Retrieve cart item key.
        $this->assertArrayHasKey('data', $actual );
        $this->assertArrayHasKey('addToCart', $actual['data'] );
        $this->assertArrayHasKey('cartItem', $actual['data']['addToCart'] );
        $this->assertArrayHasKey('key', $actual['data']['addToCart']['cartItem'] );
        $key = $actual['data']['addToCart']['cartItem']['key'];

        // Get newly created cart item data.
        $cart = WC()->cart;
        $cart_item = $cart->get_cart_item( $key );
        $this->assertNotEmpty( $cart_item );
        
        // Check cart item data.
		$expected = array(
			'data' => array(
				'addToCart' => array(
					'clientMutationId' => 'someId',
					'cartItem'         => array(
                        'key'          => $cart_item['key'],
                        'product'      => array(
                            'id'       => $this->product->to_relay_id( $cart_item['product_id'] ),
                        ),
                        'variation'    => null,
                        'quantity'     => $cart_item['quantity'],
                        'subtotal'     => floatval( $cart_item['line_subtotal'] ),
                        'subtotalTax'  => floatval( $cart_item['line_subtotal_tax'] ),
                        'total'        => floatval( $cart_item['line_total'] ),
                        'tax'          => floatval( $cart_item['line_tax'] ),
					),
				),
			),
		);
		$this->assertEqualSets( $expected, $actual );
    }

    public function testAddToCartMutationWithProductVariation() {
        $ids    = $this->variation->create( $this->product->create_variable() );
        $actual = $this->addToCart(
            array(
                'clientMutationId' => 'someId',
                'productId'        => $ids['product'],
                'quantity'         => 3,
                'variationId'      => $ids['variations'][0],
            )
        );

        // use --debug flag to view.
        codecept_debug( $actual );
        
        // Retrieve cart item key.
        $this->assertArrayHasKey('data', $actual );
        $this->assertArrayHasKey('addToCart', $actual['data'] );
        $this->assertArrayHasKey('cartItem', $actual['data']['addToCart'] );
        $this->assertArrayHasKey('key', $actual['data']['addToCart']['cartItem'] );
        $key = $actual['data']['addToCart']['cartItem']['key'];

        // Get newly created cart item data.
        $cart = WC()->cart;
        $cart_item = $cart->get_cart_item( $key );
        $this->assertNotEmpty( $cart_item );

        $expected = array(
            'data' => array(
                'addToCart' => array(
                    'clientMutationId' => 'someId',
                    'cartItem'         => array(
                        'key'          => $cart_item['key'],
                        'product'      => array(
                            'id'       => $this->product->to_relay_id( $cart_item['product_id'] ),
                        ),
                        'variation'    => array(
                            'id'       => $this->variation->to_relay_id( $cart_item['variation_id'] ),
                        ),
                        'quantity'     => $cart_item['quantity'],
                        'subtotal'     => floatval( $cart_item['line_subtotal'] ),
                        'subtotalTax'  => floatval( $cart_item['line_subtotal_tax'] ),
                        'total'        => floatval( $cart_item['line_total'] ),
                        'tax'          => floatval( $cart_item['line_tax'] ),
                    ),
                ),
            ),
        );
        $this->assertEqualSets( $expected, $actual );
    }
}