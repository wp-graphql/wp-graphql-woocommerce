<?php

function create_size_attribute() {
    require_once __DIR__ . '/../_support/Helper/crud-helpers/wcg-helper.php';
    require_once __DIR__ . '/../_support/Helper/crud-helpers/product-variation.php';

    $helper = ProductVariationHelper::instance();
    $helper->create_attribute( 'size', array( 'small', 'medium', 'large' ) );
}