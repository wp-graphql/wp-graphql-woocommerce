<?php 
$I = new AcceptanceTester($scenario);
$product_catalog = $I->getCatalog();
$I->wantTo('add an some items to the cart and checkout.');

$add_to_cart_input = array(
    'clientMutationId' => 'someId',
    'productId'        => $product_catalog['t-shirt'],
    'quantity'         => 3,
);

$success = $I->addToCart( $add_to_cart_input, null );

// use --debug flag to view
codecept_debug( $success );

$I->assertArrayNotHasKey( 'error', $success );
$I->assertArrayHasKey('data', $success );
$I->assertArrayHasKey('addToCart', $success['data'] );
$I->assertArrayHasKey('cartItem', $success['data']['addToCart'] );
$I->assertArrayHasKey('key', $success['data']['addToCart']['cartItem'] );

$checkout_input = array(
    'clientMutationId'   => 'someId',
    'paymentMethod'      => 'bacs',
    'shippingMethod'     => 'flat rate',
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
);

$success = $I->checkout( $checkout_input, $success['session_header'] );

// use --debug flag to view
codecept_debug( $success );

$I->assertArrayNotHasKey( 'error', $success );
$I->assertArrayHasKey('data', $success );
$I->assertArrayHasKey('checkout', $success['data'] );
$I->assertArrayHasKey('order', $success['data']['checkout'] );
$I->assertArrayHasKey('id', $success['data']['checkout']['order'] );