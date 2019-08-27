<?php 
$I = new AcceptanceTester($scenario);
$product_catalog = $I->getCatalog();
$I->wantTo('add items to the cart');

$add_to_cart_input = array(
    'clientMutationId' => 'someId',
    'productId'        => $product_catalog['t-shirt'],
    'quantity'         => 3,
);

$success = $I->addToCart( $add_to_cart_input, null );

// use --debug flag to view
codecept_debug( $success );

if ( ! empty ( $success['session_header'] ) ) {
    $session = $success['session_header'];
}

$I->assertArrayNotHasKey( 'error', $success );
$I->assertArrayHasKey('data', $success );
$I->assertArrayHasKey('addToCart', $success['data'] );
$I->assertArrayHasKey('cartItem', $success['data']['addToCart'] );
$I->assertArrayHasKey('key', $success['data']['addToCart']['cartItem'] );
$shirt_key = $success['data']['addToCart']['cartItem']['key'];

$add_to_cart_input = array(
    'clientMutationId' => 'someId',
    'productId'        => $product_catalog['belt'],
    'quantity'         => 2,
);

$success = $I->addToCart( $add_to_cart_input, $session );

// use --debug flag to view
codecept_debug( $success );

if ( ! empty ( $success['session_header'] ) ) {
    $session = $success['session_header'];
}

$I->assertArrayNotHasKey( 'error', $success );
$I->assertArrayHasKey('data', $success );
$I->assertArrayHasKey('addToCart', $success['data'] );
$I->assertArrayHasKey('cartItem', $success['data']['addToCart'] );
$I->assertArrayHasKey('key', $success['data']['addToCart']['cartItem'] );
$belt_key = $success['data']['addToCart']['cartItem']['key'];

$add_to_cart_input = array(
    'clientMutationId' => 'someId',
    'productId'        => $product_catalog['jeans'],
    'quantity'         => 4,
);

$success = $I->addToCart( $add_to_cart_input, $session );

// use --debug flag to view
codecept_debug( $success );

if ( ! empty ( $success['session_header'] ) ) {
    $session = $success['session_header'];
}

$I->assertArrayNotHasKey( 'error', $success );
$I->assertArrayHasKey('data', $success );
$I->assertArrayHasKey('addToCart', $success['data'] );
$I->assertArrayHasKey('cartItem', $success['data']['addToCart'] );
$I->assertArrayHasKey('key', $success['data']['addToCart']['cartItem'] );
$jeans_key = $success['data']['addToCart']['cartItem']['key'];

$add_to_cart_input = array(
    'clientMutationId' => 'someId',
    'productId'        => $product_catalog['socks'],
    'quantity'         => 1,
);

$success = $I->addToCart( $add_to_cart_input, $session );

// use --debug flag to view
codecept_debug( $success );

if ( ! empty ( $success['session_header'] ) ) {
    $session = $success['session_header'];
}

$I->assertArrayNotHasKey( 'error', $success );
$I->assertArrayHasKey('data', $success );
$I->assertArrayHasKey('addToCart', $success['data'] );
$I->assertArrayHasKey('cartItem', $success['data']['addToCart'] );
$I->assertArrayHasKey('key', $success['data']['addToCart']['cartItem'] );
$socks_key = $success['data']['addToCart']['cartItem']['key'];

$I->wantTo('remove some items from the cart');

$remove_from_cart_input = array(
    'clientMutationId' => 'someId',
    'keys'             => $socks_key,
);

$success = $I->removeFromCart( $remove_from_cart_input, $session );

// use --debug flag to view
codecept_debug( $success );

$I->assertArrayNotHasKey( 'error', $success );
$I->assertArrayHasKey('data', $success );
$I->assertArrayHasKey('removeItemsFromCart', $success['data'] );
$I->assertArrayHasKey('cartItems', $success['data']['removeItemsFromCart'] );
$I->assertCount( 1, $success['data']['removeItemsFromCart']['cartItems'] );

$I->wantTo('update an item in the cart');

$update_quantity_input = array(
    'clientMutationId' => 'someId',
    'items'            => array(
        array( 'key' => $belt_key, 'quantity' => 0 ),
        array( 'key' => $jeans_key, 'quantity' => 1 ),
    ),
);

$success = $I->updateQuantity( $update_quantity_input, $session );

// use --debug flag to view
codecept_debug( $success );

$I->assertArrayNotHasKey( 'error', $success );
$I->assertArrayHasKey('data', $success );
$I->assertArrayHasKey('updateItemQuantities', $success['data'] );
$I->assertArrayHasKey('removed', $success['data']['updateItemQuantities'] );
$I->assertCount( 1, $success['data']['updateItemQuantities']['removed'] );
$I->assertArrayHasKey('updated', $success['data']['updateItemQuantities'] );
$I->assertCount( 1, $success['data']['updateItemQuantities']['updated'] );
$I->assertArrayHasKey('items', $success['data']['updateItemQuantities'] );
$I->assertCount( 2, $success['data']['updateItemQuantities']['items'] );

$I->wantTo('checkout');

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

$success = $I->checkout( $checkout_input, $session );

// use --debug flag to view
codecept_debug( $success );

$I->assertArrayNotHasKey( 'error', $success );
$I->assertArrayHasKey('data', $success );
$I->assertArrayHasKey('checkout', $success['data'] );
$I->assertArrayHasKey('order', $success['data']['checkout'] );
$I->assertArrayHasKey('customer', $success['data']['checkout'] );
$I->assertArrayHasKey('result', $success['data']['checkout'] );
$I->assertEquals( 'success', $success['data']['checkout']['result'] );
$I->assertArrayHasKey('redirect', $success['data']['checkout'] );
$I->assertArrayHasKey('id', $success['data']['checkout']['order'] );