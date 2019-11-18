<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I
class Acceptance extends \Codeception\Module {
    /**
     * Adds item to cart.
     *
     * @param array  $input
     * @param string $session_header
     * @return array
     */
    public function addToCart( $input, $session_header = null ) {
        // Add to cart mutation
        $mutation = '
            mutation ( $input: AddToCartInput! ) {
                addToCart( input: $input ) {
                    clientMutationId
                    cartItem {
                        key
                        product {
                            ... on SimpleProduct {
                                id
                            }
                            ... on VariableProduct {
                                id
                            }
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

        // Execute query.
        $response = $this->executeQuery( $mutation, $input, $session_header, true );

        // Return response.
        return $response;
    }

    /**
     * Update cart items quantities.
     *
     * @param array  $input
     * @param string $session_header
     * @return array
     */
    public function updateQuantity( $input, $session_header = null ) {
        // Update cart items mutation
        $mutation = '
            mutation updateItemQuantities( $input: UpdateItemQuantitiesInput! ) {
                updateItemQuantities( input: $input ) {
                    clientMutationId
                    updated {
                        key
                        quantity
                    }
                    removed {
                        key
                        quantity
                    }
                    items {
                        key
                        quantity
                    }
                }
            }
        ';

        // Execute query.
        $response = $this->executeQuery( $mutation, $input, $session_header );

        // Return response.
        return $response;
    }

    /**
     * Removes an items from the cart.
     *
     * @param array  $input
     * @param string $session_header
     * @return array
     */
    public function removeFromCart( $input, $session_header = null ) {
        // Remove item from cart mutation
        $mutation = '
            mutation ( $input: RemoveItemsFromCartInput! ) {
                removeItemsFromCart( input: $input ) {
                    clientMutationId
                    cartItems {
                        key
                        product {
                            ... on SimpleProduct {
                                id
                            }
                            ... on VariableProduct {
                                id
                            }
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

        // Execute query.
        $response = $this->executeQuery( $mutation, $input, $session_header );

        // Return response.
        return $response;
    }

    /**
     * Place customer order.
     *
     * @param array  $input
     * @param string $session_header
     * 
     * @return array
     */
    public function checkout( $input, $session_header = null ) {
        // Checkout mutation.
        $mutation = '
            mutation checkout( $input: CheckoutInput! ) {
                checkout( input: $input ) {
                    clientMutationId
                    order {
                        id
                        orderId
                        currency
                        orderVersion
                        date
                        modified
                        status
                        discountTotal
                        discountTax
                        shippingTotal
                        shippingTax
                        cartTax
                        total
                        totalTax
                        subtotal
                        orderNumber
                        orderKey
                        createdVia
                        pricesIncludeTax
                        parent {
                            id
                        }
                        customer {
                            id
                        }
                        customerIpAddress
                        customerUserAgent
                        customerNote
                        billing {
                            firstName
                            lastName
                            company
                            address1
                            address2
                            city
                            state
                            postcode
                            country
                            email
                            phone
                        }
                        shipping {
                            firstName
                            lastName
                            company
                            address1
                            address2
                            city
                            state
                            postcode
                            country
                        }
                        paymentMethod
                        paymentMethodTitle
                        transactionId
                        dateCompleted
                        datePaid
                        cartHash
                        shippingAddressMapUrl
                        hasBillingAddress
                        hasShippingAddress
                        isDownloadPermitted
                        needsShippingAddress
                        hasDownloadableItem
                        downloadableItems {
                            downloadId
                        }
                        needsPayment
                        needsProcessing
                        couponLines {
                            nodes {
                                itemId
                                orderId
                                code
                                discount
                                discountTax
                                coupon {
                                    id
                                }
                            }
                        }
                        feeLines {
                            nodes {
                                itemId
                                orderId
                                amount
                                name
                                taxStatus
                                total
                                totalTax
                                taxClass
                            }
                        }
                        shippingLines {
                            nodes {
                                itemId
                                orderId
                                methodTitle
                                total
                                totalTax
                                taxClass
                            }
                        }
                        taxLines {
                            nodes {
                                rateCode
                                label
                                taxTotal
                                shippingTaxTotal
                                isCompound
                                taxRate {
                                    rateId
                                }
                            }
                        }
                        lineItems {
                            nodes {
                                productId
                                variationId
                                quantity
                                taxClass
                                subtotal
                                subtotalTax
                                total
                                totalTax
                                taxStatus
                                product {
                                    ... on SimpleProduct {
                                        id
                                    }
                                    ... on VariableProduct {
                                        id
                                    }
                                }
                                variation {
                                    id
                                }
                            }
                        }
                    }
                    customer {
                        id
                    }
                    result
                    redirect
                }
            }
        ';

        // Execute query.
        $response = $this->executeQuery( $mutation, $input, $session_header );

        // Return response.
        return $response;
    }

    /**
     * Executes GraphQL query and returns a response
     * 
     * @param string      $mutation
     * @param array       $input
     * @param string|null $session_header
     * @param bool        $update_header
     * 
     * @return array
     */
    public function executeQuery( $mutation, $input, $session_header = null, $update_header = false ) {
        $rest = $this->getModule( 'REST' );

        // Add item to cart.
        $rest->haveHttpHeader( 'Content-Type', 'application/json' );
        if ( ! empty( $session_header ) ) {
            $rest->haveHttpHeader( 'woocommerce-session', $session_header );
        }

        $wp_url = getenv( 'WP_URL' );

        // Send request.
        $rest->sendPOST(
            "{$wp_url}/graphql",
            json_encode(
                array(
                    'query'     => $mutation,
                    'variables' => array( 'input' => $input ),
                )
            )
        );

        // Confirm success.
        $rest->seeResponseCodeIs( 200 );
        $rest->seeResponseIsJson();

        // Get response.
        $response = json_decode( $rest->grabResponse(), true );

        if ( $update_header ) {
            // Update session header.
            $rest->seeHttpHeaderOnce('woocommerce-session');
            $response['session_header'] = 'Session ' . $rest->grabHttpHeader( 'woocommerce-session' );
        }

        return $response;
    }

    /**
     * Creates store products
     *
     * @return array
     */
    public function getCatalog() {
        $this->_setupStore();

        $product_catalog = array();
        $products = array(
            array(
                'post_name'  => 't-shirt',
                'meta_input' => array(
                    '_price'         => 45,
                    '_regular_price' => 45,
                )
            ),
            array(
                'post_name'  => 'jeans',
                'meta_input' => array(
                    '_price'         => 60,
                    '_regular_price' => 60,
                )
            ),
            array(
                'post_name'  => 'belt',
                'meta_input' => array(
                    '_price'         => 45,
                    '_regular_price' => 45,
                )
            ),
            array(
                'post_name'  => 'shoes',
                'meta_input' => array(
                    '_price'         => 115,
                    '_regular_price' => 115,
                )
            ),
            array(
                'post_name'  => 'socks',
                'meta_input' => array(
                    '_price'         => 20,
                    '_regular_price' => 20,
                )
            ),
        );
        foreach ( $products as $product ) {
            $this->haveAProductInTheDatabase( $product, $product_id );
            $product_catalog[ $product['post_name'] ] = $product_id;
        }

        return $product_catalog;
    }

    /**
     * Initializes store options and actions
     *
     * @param AcceptanceTester $I
     * @return void
     */
    public function _setupStore() {        
        // Turn on tax calculations and store shipping countries. Important!
        update_option( 'woocommerce_ship_to_countries', 'all' );
        update_option( 'woocommerce_prices_include_tax', 'no' );
        update_option( 'woocommerce_calc_taxes', 'yes' );
        update_option( 'woocommerce_tax_round_at_subtotal', 'no' );
        
        // Enable payment gateway.
        update_option(
            'woocommerce_bacs_settings',
            array(
                'enabled'      => 'yes',
                'title'        => 'Direct bank transfer',
                'description'  => 'Make your payment directly into our bank account. Please use your Order ID as the payment reference. Your order will not be shipped until the funds have cleared in our account.',
                'instructions' => 'Instructions that will be added to the thank you page and emails.',
                'account'      => '',
            )
        );


        // Additional cart fees.
        add_action(
            'woocommerce_cart_calculate_fees',
            function() {
                $percentage = 0.01;
                $surcharge = ( WC()->cart->cart_contents_total + WC()->cart->shipping_total ) * $percentage;	
                WC()->cart->add_fee( 'Surcharge', $surcharge, true, '' );
            }
        );
    }

    /**
     * Adds Product in database
     *
     * @param AcceptanceTester $I
     * @param array            $args        Product args.
     * @param integer          $product_id  ID for product being created.
     * @param string           $term        Product type. Defaults to 'simple'.
     * @param integer          $term_id     Product type term ID.
     * @return void
     */
    public function haveAProductInTheDatabase( $args, &$product_id, $term = 'simple', &$term_id = 0 ) {
        $wpdb = $this->getModule( 'WPDb' );
        // Create Product
        $product_id = $wpdb->havePostInDatabase(
            array_replace_recursive(
                array(
                    'post_type'  => 'product',
                    'post_name' => 't-shirt',
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
                ),
                $args
            )
        );

        if ( ! $term_id ) {
            $term_id = $wpdb->grabTermIdFromDatabase( [ 'name' => $term, 'slug' => $term ] );
        }        
        $term_taxonomy_id = $wpdb->grabTermTaxonomyIdFromDatabase( [ 'term_id' => $term_id, 'taxonomy' => 'product_type' ] );
        $wpdb->haveTermRelationshipInDatabase( $product_id, $term_id );
    }

}
