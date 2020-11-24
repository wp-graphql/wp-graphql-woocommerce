<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I
class GraphQLE2E extends \Codeception\Module {
    /**
     * Asserts existence of and returns an array of HTTP response headers
     *
     * @param string|array $headers  Headers to be evaluated and returned.
     *
     * @return array
     */
    public function wantHTTPResponseHeaders( $headers ) {
        $rest = $this->getModule( 'REST' );

        if ( $headers && ! is_array( $headers ) ) {
            $rest->seeHttpHeaderOnce( $headers );
            return $rest->grabHttpHeader( $headers );
        }

        $response_headers = array();
        foreach ( $headers as $header ) {
            $rest->seeHttpHeaderOnce( $header );
            $response_headers[] = $rest->grabHttpHeader( $header );
        }
    }

    /**
     * Authenticates User.
     *
     * @param array  $input
     * @param string $session_header
     * @return array
     */
    public function login( $input, $request_headers = array() ) {
        $mutation = '
            mutation ( $input: LoginInput! ) {
                login( input: $input ) {
                    clientMutationId
                    authToken
                    refreshToken
                    customer {
                        databaseId
                        username
					}
					sessionToken
                }
            }
        ';

        // Send GraphQL request and get response.
        $response = $this->sendGraphQLRequest( $mutation, $input, $request_headers );

        // Return response.
        return $response;
    }

    /**
     * Adds item to cart.
     *
     * @param array  $input
     * @param string $session_header
     *
     * @return array
     */
    public function addToCart( $input, $request_headers = array() ) {
        // Add to cart mutation
        $mutation = '
            mutation ( $input: AddToCartInput! ) {
                addToCart( input: $input ) {
                    clientMutationId
                    cartItem {
                        key
                        product {
							node {
								id
							}
                        }
                        variation {
                            node {
								id
							}
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

        // Send GraphQL request and get response.
        $response = $this->sendGraphQLRequest( $mutation, $input, $request_headers );

        // Return response.
        return $response;
    }

    /**
     * Update cart items quantities.
     *
     * @param array  $input
     * @param string $session_header
     *
     * @return array
     */
    public function updateItemQuantities( $input, $request_headers = array() ) {
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

        // Send GraphQL request and get response.
        $response = $this->sendGraphQLRequest( $mutation, $input, $request_headers );

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
    public function removeItemsFromCart( $input, $request_headers = array() ) {
        // Remove item from cart mutation
        $mutation = '
            mutation ( $input: RemoveItemsFromCartInput! ) {
                removeItemsFromCart( input: $input ) {
                    clientMutationId
                    cartItems {
                        key
                        product {
                            node {
								id
							}
                        }
                        variation {
                            node {
								id
							}
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

        // Send GraphQL request and get response.
        $response = $this->sendGraphQLRequest( $mutation, $input, $request_headers );

        // Return response.
        return $response;
    }

    /**
     * Restores items removed from the cart.
     *
     * @param array  $input
     * @param string $session_header
     * @return array
     */
    public function restoreCartItems( $input, $request_headers = array() ) {
        $mutation = '
            mutation restoreCartItems( $input: RestoreCartItemsInput! ) {
                restoreCartItems( input: $input ) {
                    clientMutationId
                    cartItems {
                        key
                        product {
							node {
								id
							}
                        }
                        variation {
                            node {
								id
							}
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

        // Send GraphQL request and get response.
        $response = $this->sendGraphQLRequest( $mutation, $input, $request_headers );

        // Return response.
        return $response;
    }

    /**
     * Removes all items from the cart.
     *
     * @param array  $input
     * @param string $session_header
     * @return array
     */
    public function emptyCart( $input, $request_headers = array() ) {
        $mutation = '
            mutation emptyCart( $input: EmptyCartInput! ) {
                emptyCart( input: $input ) {
                    clientMutationId
                    deletedCart {
                        contents {
                            nodes {
                                key
                                product {
									node {
										id
									}
                                }
                                variation {
                                    node {
										id
									}
                                }
                                quantity
                                subtotal
                                subtotalTax
                                total
                                tax
                            }
                        }
                    }
                }
            }
        ';

        // Send GraphQL request and get response.
        $response = $this->sendGraphQLRequest( $mutation, $input, $request_headers );

        // Return response.
        return $response;
    }

    /**
     * Adds fee on cart.
     *
     * @param array  $input
     * @param string $session_header
     * @return array
     */
    public function addFee( $input, $request_headers = array() ) {
        $mutation = '
            mutation addFee( $input: AddFeeInput! ) {
                addFee( input: $input ) {
                    clientMutationId
                    cartFee {
                        id
                        name
                        taxClass
                        taxable
                        amount
                        total
                    }
                }
            }
        ';

        // Send GraphQL request and get response.
        $response = $this->sendGraphQLRequest( $mutation, $input, $request_headers );

        // Return response.
        return $response;
    }

    /**
     * Applies coupon to the cart.
     *
     * @param array  $input
     * @param string $session_header
     * @return array
     */
    public function applyCoupon( $input, $request_headers = array() ) {
        $mutation = '
            mutation applyCoupon( $input: ApplyCouponInput! ) {
                applyCoupon( input: $input ) {
                    clientMutationId
                    cart {
                        appliedCoupons {
                            nodes {
                                code
                            }
                        }
                        contents {
                            nodes {
                                key
                                product {
									node {
										id
									}
                                }
                                quantity
                                subtotal
                                subtotalTax
                                total
                                tax
                            }
                        }
                    }
                }
            }
        ';

        // Send GraphQL request and get response.
        $response = $this->sendGraphQLRequest( $mutation, $input, $request_headers );

        // Return response.
        return $response;
    }

    /**
     * Removes coupons on the cart.
     *
     * @param array  $input
     * @param string $session_header
     * @return array
     */
    public function removeCoupons( $input, $request_headers = array() ) {
        $mutation = '
            mutation removeCoupons( $input: RemoveCouponsInput! ) {
                removeCoupons( input: $input ) {
                    clientMutationId
                    cart {
                        appliedCoupons {
                            nodes {
                                code
                            }
                        }
                        contents {
                            nodes {
                                key
                                product {
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
                }
            }
        ';

        // Send GraphQL request and get response.
        $response = $this->sendGraphQLRequest( $mutation, $input, $request_headers );

        // Return response.
        return $response;
    }

    /**
     * Updates customers chosen shipping method.
     *
     * @param array  $input
     * @param string $session_header
     * @return array
     */
    public function updateShippingMethod( $input, $request_headers = array() ) {
        // updateShippingMethod mutation.
        $mutation = '
            mutation ($input: UpdateShippingMethodInput!){
                updateShippingMethod(input: $input) {
                    cart {
                        availableShippingMethods {
                            packageDetails
                            supportsShippingCalculator
                            rates {
                                id
                                cost
                                label
                            }
                        }
                        chosenShippingMethod
                        shippingTotal
                        shippingTax
                        subtotal
                        subtotalTax
                        total
                    }
                }
            }
        ';

        // Send GraphQL request and get response.
        $response = $this->sendGraphQLRequest( $mutation, $input, $request_headers );

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
    public function checkout( $input, $request_headers = array() ) {
        // Checkout mutation.
        $mutation = '
            mutation checkout( $input: CheckoutInput! ) {
                checkout( input: $input ) {
                    clientMutationId
                    order {
                        id
                        databaseId
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
                            nodes {
                                url
                                accessExpires
                                downloadId
                                downloadsRemaining
                                name
                                product {
                                    databaseId
                                }
                                download {
                                    downloadId
                                }
                            }
                        }
                        needsPayment
                        needsProcessing
                        couponLines {
                            nodes {
                                databaseId
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
                                databaseId
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
                                databaseId
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
                                    databaseId
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
                                    id
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

        // Send GraphQL request and get response.
        $response = $this->sendGraphQLRequest( $mutation, $input, $request_headers );

        // Return response.
        return $response;
    }

    /**
     * Sends GraphQL and returns a response
     *
     * @param string      $mutation
     * @param array       $input
     * @param string|null $session_header
     * @param bool        $update_header
     *
     * @return array
     */
    public function sendGraphQLRequest( $query, $input, $request_headers = array() ) {
        $rest = $this->getModule( 'REST' );

        // Add item to cart.
        $rest->haveHttpHeader( 'Content-Type', 'application/json' );

        // Set request headers
        foreach( $request_headers as $header => $value ) {
            $rest->haveHttpHeader( $header, $value );
        }

        // Send request.
        $rest->sendPOST(
            "/graphql",
            json_encode(
                array(
                    'query'     => $query,
                    'variables' => array( 'input' => $input ),
                )
            )
        );

        // Confirm success.
        $rest->seeResponseCodeIs( 200 );
        $rest->seeResponseIsJson();

        // Get response.
		$response = json_decode( $rest->grabResponse(), true );

		// Remove extensions field (temporary fix).
		unset( $response['extensions'] );

        // use --debug flag to view
        codecept_debug( json_encode( $response, JSON_PRETTY_PRINT ) );

        // Delete request headers
        foreach( $request_headers as $header => $value ) {
            $rest->deleteHeader( $header );
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
                'post_title'  => 't-shirt',
                'meta_input' => array(
                    '_price'         => 45,
                    '_regular_price' => 45,
                )
            ),
            array(
                'post_title'  => 'jeans',
                'meta_input' => array(
                    '_price'         => 60,
                    '_regular_price' => 60,
                )
            ),
            array(
                'post_title'  => 'belt',
                'meta_input' => array(
                    '_price'         => 45,
                    '_regular_price' => 45,
                )
            ),
            array(
                'post_title'  => 'shoes',
                'meta_input' => array(
                    '_price'         => 115,
                    '_regular_price' => 115,
                )
            ),
            array(
                'post_title'  => 'socks',
                'meta_input' => array(
                    '_price'         => 20,
                    '_regular_price' => 20,
                )
            ),
        );
        foreach ( $products as $product ) {
            $this->haveAProductInTheDatabase( $product, $product_id );
            $product_catalog[ $product['post_title'] ] = $product_id;
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
                $surcharge = ( \WC()->cart->cart_contents_total + \WC()->cart->shipping_total ) * $percentage;
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
                    'post_title' => 't-shirt',
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


    public function setupStoreAndUsers() {
		$this->_setupStore();

        $wpdb   = $this->getModule( 'WPDb' );
        $userId = $wpdb->haveUserInDatabase(
            'jimbo1234',
            'customer',
            [
                'user_pass'  => 'password',
                'user_email' => 'jimbo1234@example.com',
            ]
        );
    }
}
