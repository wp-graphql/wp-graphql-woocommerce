<?php 
$I = new AcceptanceTester($scenario);
// Create products
$product_catalog = $I->getCatalog();

// Make quick helper for managing the session token.
$request_headers = function () use ( $I, &$last_request_headers ) {
    $last_request_headers = array (
        'woocommerce-session' => 'Session ' . $I->wantHTTPResponseHeaders( 'woocommerce-session' ),
    );

    return $last_request_headers;
};

// Begin test.
$I->wantTo('add items to the cart');

/**
 * Add "T-Shirt" to cart and confirm response data.
 */
$add_to_cart_input = array(
    'clientMutationId' => 'someId',
    'productId'        => $product_catalog['t-shirt'],
    'quantity'         => 3,
);

$success = $I->addToCart( $add_to_cart_input );

$I->assertArrayNotHasKey( 'errors', $success );
$I->assertArrayHasKey('data', $success );
$I->assertArrayHasKey('addToCart', $success['data'] );
$I->assertArrayHasKey('cartItem', $success['data']['addToCart'] );
$I->assertArrayHasKey('key', $success['data']['addToCart']['cartItem'] );
$shirt_key = $success['data']['addToCart']['cartItem']['key'];

/**
 * Add "Belt" to cart and confirm response data.
 */
$add_to_cart_input = array(
    'clientMutationId' => 'someId',
    'productId'        => $product_catalog['belt'],
    'quantity'         => 2,
);

$success = $I->addToCart(
    $add_to_cart_input,
    $request_headers()
);

$I->assertArrayNotHasKey( 'errors', $success );
$I->assertArrayHasKey('data', $success );
$I->assertArrayHasKey('addToCart', $success['data'] );
$I->assertArrayHasKey('cartItem', $success['data']['addToCart'] );
$I->assertArrayHasKey('key', $success['data']['addToCart']['cartItem'] );
$belt_key = $success['data']['addToCart']['cartItem']['key'];

/**
 * Add "Jeans" to cart and confirm response data.
 */
$add_to_cart_input = array(
    'clientMutationId' => 'someId',
    'productId'        => $product_catalog['jeans'],
    'quantity'         => 4,
);

$success = $I->addToCart(
    $add_to_cart_input,
    $request_headers()
);

$I->assertArrayNotHasKey( 'errors', $success );
$I->assertArrayHasKey('data', $success );
$I->assertArrayHasKey('addToCart', $success['data'] );
$I->assertArrayHasKey('cartItem', $success['data']['addToCart'] );
$I->assertArrayHasKey('key', $success['data']['addToCart']['cartItem'] );
$jeans_key = $success['data']['addToCart']['cartItem']['key'];

/**
 * Add "Socks" to cart and confirm response data.
 */
$add_to_cart_input = array(
    'clientMutationId' => 'someId',
    'productId'        => $product_catalog['socks'],
    'quantity'         => 1,
);

$success = $I->addToCart(
    $add_to_cart_input,
    $request_headers()
);

$I->assertArrayNotHasKey( 'errors', $success );
$I->assertArrayHasKey('data', $success );
$I->assertArrayHasKey('addToCart', $success['data'] );
$I->assertArrayHasKey('cartItem', $success['data']['addToCart'] );
$I->assertArrayHasKey('key', $success['data']['addToCart']['cartItem'] );
$socks_key = $success['data']['addToCart']['cartItem']['key'];

$I->wantTo('remove some items from the cart');

/**
 * Remove "Socks" from cart and confirm response data.
 */
$remove_from_cart_input = array(
    'clientMutationId' => 'someId',
    'keys'             => $socks_key,
);

$success = $I->removeItemsFromCart(
    $remove_from_cart_input,
    $request_headers()
);

$I->assertArrayNotHasKey( 'errors', $success );
$I->assertArrayHasKey('data', $success );
$I->assertArrayHasKey('removeItemsFromCart', $success['data'] );
$I->assertArrayHasKey('cartItems', $success['data']['removeItemsFromCart'] );
$I->assertCount( 1, $success['data']['removeItemsFromCart']['cartItems'] );

$I->wantTo('update an item in the cart');

/**
 * - Change "Belt" quantity to "0" removing it from the cart.
 * - Change "Jeans" quantity to "1"
 * - Confirm response data.
 */
$update_quantity_input = array(
    'clientMutationId' => 'someId',
    'items'            => array(
        array( 'key' => $belt_key, 'quantity' => 0 ),
        array( 'key' => $jeans_key, 'quantity' => 1 ),
    ),
);

$success = $I->updateItemQuantities(
    $update_quantity_input,
    /**
     * "removeItemsFromCart" mutation does not update the session token so we can
     * use the request headers used on the last request
     */
    $last_request_headers 
);

$I->assertArrayNotHasKey( 'errors', $success );
$I->assertArrayHasKey('data', $success );
$I->assertArrayHasKey('updateItemQuantities', $success['data'] );
$I->assertArrayHasKey('removed', $success['data']['updateItemQuantities'] );
$I->assertCount( 1, $success['data']['updateItemQuantities']['removed'] );
$I->assertArrayHasKey('updated', $success['data']['updateItemQuantities'] );
$I->assertCount( 1, $success['data']['updateItemQuantities']['updated'] );
$I->assertArrayHasKey('items', $success['data']['updateItemQuantities'] );
$I->assertCount( 2, $success['data']['updateItemQuantities']['items'] );

$I->wantTo('checkout');

/**
 * Place order for items in the cart using the "Checkout" mutation and confirm response data.
 */
$checkout_input = array(
    'clientMutationId'   => 'someId',
    'paymentMethod'      => 'bacs',
    'shippingMethod'     => array( 'flat rate' ),
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

$success = $I->checkout(
    $checkout_input,
    /**
     * "updateItemQuantities" mutation does not update the session token so we can
     * use the request headers used on the last request
     */
    $last_request_headers 
);

// use --debug flag to view
codecept_debug( $success );

$I->assertArrayNotHasKey( 'errors', $success );
$I->assertArrayHasKey('data', $success );
$I->assertArrayHasKey('checkout', $success['data'] );
$I->assertArrayHasKey('order', $success['data']['checkout'] );
$I->assertArrayHasKey('customer', $success['data']['checkout'] );
$I->assertArrayHasKey('result', $success['data']['checkout'] );
$I->assertEquals( 'success', $success['data']['checkout']['result'] );
$I->assertArrayHasKey('redirect', $success['data']['checkout'] );
$I->assertArrayHasKey('id', $success['data']['checkout']['order'] );