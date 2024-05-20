<?php

//Additional cart fees.
\add_action(
    'woocommerce_cart_calculate_fees',
    static function () {
        $percentage = 0.01;
        $surcharge  = ( \WC()->cart->cart_contents_total + \WC()->cart->shipping_total ) * $percentage;
        \WC()->cart->add_fee( 'Surcharge', $surcharge, true, '' );
    }
);

// Create Shipping Zones.
$zone = new \WC_Shipping_Zone();
$zone->set_zone_name( 'Local' );
$zone->set_zone_order( 1 );
$zone->add_location( 'GB', 'country' );
$zone->add_location( 'CB*', 'postcode' );
$zone->save();
$zone->add_shipping_method( 'flat_rate' );
$zone->add_shipping_method( 'free_shipping' );

$zone = new \WC_Shipping_Zone();
$zone->set_zone_name( 'Europe' );
$zone->set_zone_order( 2 );
$zone->add_location( 'EU', 'continent' );
$zone->save();
$zone->add_shipping_method( 'flat_rate' );
$zone->add_shipping_method( 'free_shipping' );

$zone = new \WC_Shipping_Zone();
$zone->set_zone_name( 'California' );
$zone->set_zone_order( 3 );
$zone->add_location( 'US:CA', 'state' );
$zone->save();
$zone->add_shipping_method( 'flat_rate' );
$zone->add_shipping_method( 'free_shipping' );

$zone = new \WC_Shipping_Zone();
$zone->set_zone_name( 'US' );
$zone->set_zone_order( 4 );
$zone->add_location( 'US', 'country' );
$zone->save();
$zone->add_shipping_method( 'flat_rate' );
$zone->add_shipping_method( 'free_shipping' );