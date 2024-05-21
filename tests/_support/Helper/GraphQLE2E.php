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

		$response_headers = [];
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
	public function login( $input, $request_headers = [] ) {
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
		return $this->sendGraphQLRequest( $mutation, $input, $request_headers );
	}

	/**
	 * Adds item to cart.
	 *
	 * @param array  $input
	 * @param string $session_header
	 *
	 * @return array
	 */
	public function addToCart( $input, $request_headers = [] ) {
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
		return $this->sendGraphQLRequest( $mutation, $input, $request_headers );
	}

	/**
	 * Update cart items quantities.
	 *
	 * @param array  $input
	 * @param string $session_header
	 *
	 * @return array
	 */
	public function updateItemQuantities( $input, $request_headers = [] ) {
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
		return $this->sendGraphQLRequest( $mutation, $input, $request_headers );
	}

	/**
	 * Removes an items from the cart.
	 *
	 * @param array  $input
	 * @param string $session_header
	 * @return array
	 */
	public function removeItemsFromCart( $input, $request_headers = [] ) {
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
		return $this->sendGraphQLRequest( $mutation, $input, $request_headers );
	}

	/**
	 * Restores items removed from the cart.
	 *
	 * @param array  $input
	 * @param string $session_header
	 * @return array
	 */
	public function restoreCartItems( $input, $request_headers = [] ) {
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
		return $this->sendGraphQLRequest( $mutation, $input, $request_headers );
	}

	/**
	 * Removes all items from the cart.
	 *
	 * @param array  $input
	 * @param string $session_header
	 * @return array
	 */
	public function emptyCart( $input, $request_headers = [] ) {
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
		return $this->sendGraphQLRequest( $mutation, $input, $request_headers );
	}

	/**
	 * Adds fee on cart.
	 *
	 * @param array  $input
	 * @param string $session_header
	 * @return array
	 */
	public function addFee( $input, $request_headers = [] ) {
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
		return $this->sendGraphQLRequest( $mutation, $input, $request_headers );
	}

	/**
	 * Applies coupon to the cart.
	 *
	 * @param array  $input
	 * @param string $session_header
	 * @return array
	 */
	public function applyCoupon( $input, $request_headers = [] ) {
		$mutation = '
            mutation applyCoupon( $input: ApplyCouponInput! ) {
                applyCoupon( input: $input ) {
                    clientMutationId
                    cart {
                        appliedCoupons {
							code
                        }
                        contents {
                            nodes {
                                key
                                product {
									node {
										node { id }
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
		return $this->sendGraphQLRequest( $mutation, $input, $request_headers );
	}

	/**
	 * Removes coupons on the cart.
	 *
	 * @param array  $input
	 * @param string $session_header
	 * @return array
	 */
	public function removeCoupons( $input, $request_headers = [] ) {
		$mutation = '
            mutation removeCoupons( $input: RemoveCouponsInput! ) {
                removeCoupons( input: $input ) {
                    clientMutationId
                    cart {
                        appliedCoupons {
							code
                        }
                        contents {
                            nodes {
                                key
                                product {
                                    node { id }
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
		return $this->sendGraphQLRequest( $mutation, $input, $request_headers );
	}

	/**
	 * Updates customers chosen shipping method.
	 *
	 * @param array  $input
	 * @param string $session_header
	 * @return array
	 */
	public function updateShippingMethod( $input, $request_headers = [] ) {
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
                        chosenShippingMethods
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
		return $this->sendGraphQLRequest( $mutation, $input, $request_headers );
	}

	/**
	 * Place customer order.
	 *
	 * @param array  $input
	 * @param string $session_header
	 *
	 * @return array
	 */
	public function checkout( $input, $request_headers = [] ) {
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
                                    node {
										id 
									}
                                }
                                variation {
                                    node {
										id 
									}
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
		return $this->sendGraphQLRequest( $mutation, $input, $request_headers );
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
	public function sendGraphQLRequest( $query, $input, $request_headers = [] ) {
		$rest = $this->getModule( 'REST' );

		// Add item to cart.
		$rest->haveHttpHeader( 'Content-Type', 'application/json' );

		// Set request headers
		foreach ( $request_headers as $header => $value ) {
			$rest->haveHttpHeader( $header, $value );
		}

		// Send request.
		$rest->sendPost(
			'/graphql',
			json_encode(
				[
					'query'     => $query,
					'variables' => [ 'input' => $input ],
				]
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
		foreach ( $request_headers as $header => $value ) {
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

		$product_catalog = [];
		$products        = [
			[
				'post_title' => 't-shirt',
				'meta_input' => [
					'_price'         => 45,
					'_regular_price' => 45,
				],
			],
			[
				'post_title' => 'jeans',
				'meta_input' => [
					'_price'         => 60,
					'_regular_price' => 60,
				],
			],
			[
				'post_title' => 'belt',
				'meta_input' => [
					'_price'         => 45,
					'_regular_price' => 45,
				],
			],
			[
				'post_title' => 'shoes',
				'meta_input' => [
					'_price'         => 115,
					'_regular_price' => 115,
				],
			],
			[
				'post_title' => 'socks',
				'meta_input' => [
					'_price'         => 20,
					'_regular_price' => 20,
				],
			],
		];
		foreach ( $products as $product ) {
			$this->haveAProductInTheDatabase( $product, $product_id );
			$product_catalog[ $product['post_title'] ] = $product_id;
		}

		$cart_page_content = '<!-- wp:woocommerce/cart -->
		<div class="wp-block-woocommerce-cart alignwide is-loading"><!-- wp:woocommerce/filled-cart-block -->
		<div class="wp-block-woocommerce-filled-cart-block"><!-- wp:woocommerce/cart-items-block -->
		<div class="wp-block-woocommerce-cart-items-block"><!-- wp:woocommerce/cart-line-items-block -->
		<div class="wp-block-woocommerce-cart-line-items-block"></div>
		<!-- /wp:woocommerce/cart-line-items-block -->
		
		<!-- wp:woocommerce/cart-cross-sells-block -->
		<div class="wp-block-woocommerce-cart-cross-sells-block"><!-- wp:heading {"fontSize":"large"} -->
		<h2 class="wp-block-heading has-large-font-size">' . __( 'You may be interested in…', 'woocommerce' ) . '</h2>
		<!-- /wp:heading -->
		
		<!-- wp:woocommerce/cart-cross-sells-products-block -->
		<div class="wp-block-woocommerce-cart-cross-sells-products-block"></div>
		<!-- /wp:woocommerce/cart-cross-sells-products-block --></div>
		<!-- /wp:woocommerce/cart-cross-sells-block --></div>
		<!-- /wp:woocommerce/cart-items-block -->
		
		<!-- wp:woocommerce/cart-totals-block -->
		<div class="wp-block-woocommerce-cart-totals-block"><!-- wp:woocommerce/cart-order-summary-block -->
		<div class="wp-block-woocommerce-cart-order-summary-block"><!-- wp:woocommerce/cart-order-summary-heading-block -->
		<div class="wp-block-woocommerce-cart-order-summary-heading-block"></div>
		<!-- /wp:woocommerce/cart-order-summary-heading-block -->
		
		<!-- wp:woocommerce/cart-order-summary-coupon-form-block -->
		<div class="wp-block-woocommerce-cart-order-summary-coupon-form-block"></div>
		<!-- /wp:woocommerce/cart-order-summary-coupon-form-block -->
		
		<!-- wp:woocommerce/cart-order-summary-subtotal-block -->
		<div class="wp-block-woocommerce-cart-order-summary-subtotal-block"></div>
		<!-- /wp:woocommerce/cart-order-summary-subtotal-block -->
		
		<!-- wp:woocommerce/cart-order-summary-fee-block -->
		<div class="wp-block-woocommerce-cart-order-summary-fee-block"></div>
		<!-- /wp:woocommerce/cart-order-summary-fee-block -->
		
		<!-- wp:woocommerce/cart-order-summary-discount-block -->
		<div class="wp-block-woocommerce-cart-order-summary-discount-block"></div>
		<!-- /wp:woocommerce/cart-order-summary-discount-block -->
		
		<!-- wp:woocommerce/cart-order-summary-shipping-block -->
		<div class="wp-block-woocommerce-cart-order-summary-shipping-block"></div>
		<!-- /wp:woocommerce/cart-order-summary-shipping-block -->
		
		<!-- wp:woocommerce/cart-order-summary-taxes-block -->
		<div class="wp-block-woocommerce-cart-order-summary-taxes-block"></div>
		<!-- /wp:woocommerce/cart-order-summary-taxes-block --></div>
		<!-- /wp:woocommerce/cart-order-summary-block -->
		
		<!-- wp:woocommerce/cart-express-payment-block -->
		<div class="wp-block-woocommerce-cart-express-payment-block"></div>
		<!-- /wp:woocommerce/cart-express-payment-block -->
		
		<!-- wp:woocommerce/proceed-to-checkout-block -->
		<div class="wp-block-woocommerce-proceed-to-checkout-block"></div>
		<!-- /wp:woocommerce/proceed-to-checkout-block -->
		
		<!-- wp:woocommerce/cart-accepted-payment-methods-block -->
		<div class="wp-block-woocommerce-cart-accepted-payment-methods-block"></div>
		<!-- /wp:woocommerce/cart-accepted-payment-methods-block --></div>
		<!-- /wp:woocommerce/cart-totals-block --></div>
		<!-- /wp:woocommerce/filled-cart-block -->
		
		<!-- wp:woocommerce/empty-cart-block -->
		<div class="wp-block-woocommerce-empty-cart-block"><!-- wp:heading {"textAlign":"center","className":"with-empty-cart-icon wc-block-cart__empty-cart__title"} -->
		<h2 class="wp-block-heading has-text-align-center with-empty-cart-icon wc-block-cart__empty-cart__title">' . __( 'Your cart is currently empty!', 'woocommerce' ) . '</h2>
		<!-- /wp:heading -->
		
		<!-- wp:separator {"className":"is-style-dots"} -->
		<hr class="wp-block-separator has-alpha-channel-opacity is-style-dots"/>
		<!-- /wp:separator -->
		
		<!-- wp:heading {"textAlign":"center"} -->
		<h2 class="wp-block-heading has-text-align-center">' . __( 'New in store', 'woocommerce' ) . '</h2>
		<!-- /wp:heading -->
		
		<!-- wp:woocommerce/product-new {"columns":4,"rows":1} /--></div>
		<!-- /wp:woocommerce/empty-cart-block --></div>
		<!-- /wp:woocommerce/cart -->';
		$checkout_page_content = '<!-- wp:woocommerce/checkout -->
		<div class="wp-block-woocommerce-checkout alignwide wc-block-checkout is-loading"><!-- wp:woocommerce/checkout-fields-block -->
		<div class="wp-block-woocommerce-checkout-fields-block"><!-- wp:woocommerce/checkout-express-payment-block -->
		<div class="wp-block-woocommerce-checkout-express-payment-block"></div>
		<!-- /wp:woocommerce/checkout-express-payment-block -->
		
		<!-- wp:woocommerce/checkout-contact-information-block -->
		<div class="wp-block-woocommerce-checkout-contact-information-block"></div>
		<!-- /wp:woocommerce/checkout-contact-information-block -->
		
		<!-- wp:woocommerce/checkout-shipping-method-block -->
		<div class="wp-block-woocommerce-checkout-shipping-method-block"></div>
		<!-- /wp:woocommerce/checkout-shipping-method-block -->
		
		<!-- wp:woocommerce/checkout-pickup-options-block -->
		<div class="wp-block-woocommerce-checkout-pickup-options-block"></div>
		<!-- /wp:woocommerce/checkout-pickup-options-block -->
		
		<!-- wp:woocommerce/checkout-shipping-address-block -->
		<div class="wp-block-woocommerce-checkout-shipping-address-block"></div>
		<!-- /wp:woocommerce/checkout-shipping-address-block -->
		
		<!-- wp:woocommerce/checkout-billing-address-block -->
		<div class="wp-block-woocommerce-checkout-billing-address-block"></div>
		<!-- /wp:woocommerce/checkout-billing-address-block -->
		
		<!-- wp:woocommerce/checkout-shipping-methods-block -->
		<div class="wp-block-woocommerce-checkout-shipping-methods-block"></div>
		<!-- /wp:woocommerce/checkout-shipping-methods-block -->
		
		<!-- wp:woocommerce/checkout-payment-block -->
		<div class="wp-block-woocommerce-checkout-payment-block"></div>
		<!-- /wp:woocommerce/checkout-payment-block -->
		
		<!-- wp:woocommerce/checkout-additional-information-block -->
		<div class="wp-block-woocommerce-checkout-additional-information-block"></div>
		<!-- /wp:woocommerce/checkout-additional-information-block -->
		
		<!-- wp:woocommerce/checkout-order-note-block -->
		<div class="wp-block-woocommerce-checkout-order-note-block"></div>
		<!-- /wp:woocommerce/checkout-order-note-block -->
		
		<!-- wp:woocommerce/checkout-terms-block -->
		<div class="wp-block-woocommerce-checkout-terms-block"></div>
		<!-- /wp:woocommerce/checkout-terms-block -->
		
		<!-- wp:woocommerce/checkout-actions-block -->
		<div class="wp-block-woocommerce-checkout-actions-block"></div>
		<!-- /wp:woocommerce/checkout-actions-block --></div>
		<!-- /wp:woocommerce/checkout-fields-block -->
		
		<!-- wp:woocommerce/checkout-totals-block -->
		<div class="wp-block-woocommerce-checkout-totals-block"><!-- wp:woocommerce/checkout-order-summary-block -->
		<div class="wp-block-woocommerce-checkout-order-summary-block"><!-- wp:woocommerce/checkout-order-summary-cart-items-block -->
		<div class="wp-block-woocommerce-checkout-order-summary-cart-items-block"></div>
		<!-- /wp:woocommerce/checkout-order-summary-cart-items-block -->
		
		<!-- wp:woocommerce/checkout-order-summary-coupon-form-block -->
		<div class="wp-block-woocommerce-checkout-order-summary-coupon-form-block"></div>
		<!-- /wp:woocommerce/checkout-order-summary-coupon-form-block -->
		
		<!-- wp:woocommerce/checkout-order-summary-subtotal-block -->
		<div class="wp-block-woocommerce-checkout-order-summary-subtotal-block"></div>
		<!-- /wp:woocommerce/checkout-order-summary-subtotal-block -->
		
		<!-- wp:woocommerce/checkout-order-summary-fee-block -->
		<div class="wp-block-woocommerce-checkout-order-summary-fee-block"></div>
		<!-- /wp:woocommerce/checkout-order-summary-fee-block -->
		
		<!-- wp:woocommerce/checkout-order-summary-discount-block -->
		<div class="wp-block-woocommerce-checkout-order-summary-discount-block"></div>
		<!-- /wp:woocommerce/checkout-order-summary-discount-block -->
		
		<!-- wp:woocommerce/checkout-order-summary-shipping-block -->
		<div class="wp-block-woocommerce-checkout-order-summary-shipping-block"></div>
		<!-- /wp:woocommerce/checkout-order-summary-shipping-block -->
		
		<!-- wp:woocommerce/checkout-order-summary-taxes-block -->
		<div class="wp-block-woocommerce-checkout-order-summary-taxes-block"></div>
		<!-- /wp:woocommerce/checkout-order-summary-taxes-block --></div>
		<!-- /wp:woocommerce/checkout-order-summary-block --></div>
		<!-- /wp:woocommerce/checkout-totals-block --></div>
		<!-- /wp:woocommerce/checkout -->';

		// Create cart page.
		$wpdb         = $this->getModule( 'WPDb' );
		
		$cart_page_id = $wpdb->havePostInDatabase(
			[
				'post_type'    => 'page',
				'post_title'   => 'Cart',
				'post_name'    => 'cart',
				'post_author'  => 1,
				'post_content' => $cart_page_content,
				'post_status'  => 'publish',
			]
		);
		$wpdb->haveOptionInDatabase( 'woocommerce_cart_page_id', $cart_page_id );
		$checkout_page_id = $wpdb->havePostInDatabase(
			[
				'post_type'    => 'page',
				'post_title'   => 'Checkout',
				'post_name'    => 'checkout',
				'post_author'  => 1,
				'post_content' => $checkout_page_content,
				'post_status'  => 'publish',
			]
		);
		$wpdb->haveOptionInDatabase( 'woocommerce_checkout_page_id', $checkout_page_id );

		return $product_catalog;
	}

	/**
	 * Initializes store options and actions
	 *
	 * @param \Helper\AcceptanceTester $I
	 * @return void
	 */
	public function _setupStore() {
		$wpdb = $this->getModule( 'WPDb' );

		$wpdb->useTheme( 'twentytwentyone' );
		// Turn on tax calculations and store shipping countries. Important!
		$wpdb->haveOptionInDatabase( 'woocommerce_ship_to_countries', 'all' );
		$wpdb->haveOptionInDatabase( 'woocommerce_prices_include_tax', 'no' );
		$wpdb->haveOptionInDatabase( 'woocommerce_calc_taxes', 'yes' );
		$wpdb->haveOptionInDatabase( 'woocommerce_tax_round_at_subtotal', 'no' );

		// Enable payment gateway.
		$wpdb->haveOptionInDatabase(
			'woocommerce_bacs_settings',
			[
				'enabled'      => 'yes',
				'title'        => 'Direct bank transfer',
				'description'  => 'Make your payment directly into our bank account. Please use your Order ID as the payment reference. Your order will not be shipped until the funds have cleared in our account.',
				'instructions' => 'Instructions that will be added to the thank you page and emails.',
				'account'      => '',
			]
		);

		// forcing has_block_template to be false
		add_filter( 'woocommerce_has_block_template', '__return_false', 10, 2 );

		//Additional cart fees.
		add_action(
			'woocommerce_cart_calculate_fees',
			static function () {
				$percentage = 0.01;
				$surcharge  = ( WC()->cart->cart_contents_total + WC()->cart->shipping_total ) * $percentage;
				WC()->cart->add_fee( 'Surcharge', $surcharge, true, '' );
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

		global $wp_rewrite; 

		//Write the rule
		$wp_rewrite->set_permalink_structure('/%postname%/'); 

		//Set the option
		update_option( "rewrite_rules", FALSE ); 

		//Flush the rules and tell it to write htaccess
		$wp_rewrite->flush_rules( true );
	}

	/**
	 * Adds Product in database
	 *
	 * @param \Helper\AcceptanceTester $I
	 * @param array                    $args        Product args.
	 * @param integer                  $product_id  ID for product being created.
	 * @param string                   $term        Product type. Defaults to 'simple'.
	 * @param integer                  $term_id     Product type term ID.
	 * @return void
	 */
	public function haveAProductInTheDatabase( $args, &$product_id, $term = 'simple', &$term_id = 0 ) {
		$wpdb = $this->getModule( 'WPDb' );

		// Create Product
		$product_id = $wpdb->havePostInDatabase(
			array_replace_recursive(
				[
					'post_type'  => 'product',
					'post_title' => 't-shirt',
					'meta_input' => [
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
						'_upsell_ids'             => [],
						'_cross_sell_ids'         => [],
						'_purchase_note'          => '',
						'_default_attributes'     => [],
						'_product_attributes'     => [],
						'_virtual'                => false,
						'_downloadable'           => false,
						'_download_limit'         => -1,
						'_download_expiry'        => -1,
						'_featured'               => false,
						'_wc_rating_counts'       => [],
						'_wc_average_rating'      => 0,
						'_wc_review_count'        => 0,
					],
				],
				$args
			)
		);

		if ( ! $term_id ) {
			$term_id = $wpdb->grabTermIdFromDatabase(
				[
					'name' => $term,
					'slug' => $term,
				]
			);
		}
		$term_taxonomy_id = $wpdb->grabTermTaxonomyIdFromDatabase(
			[
				'term_id'  => $term_id,
				'taxonomy' => 'product_type',
			]
		);
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

	public function verifyRedirect( $startUrl, $endUrl, $redirectCode = 301 ) {
		$phpBrowser = $this->getModule( 'WPBrowser' );
		$guzzle     = $phpBrowser->client;

		// Disable the following of redirects
		$guzzle->followRedirects( false );

		$phpBrowser->_loadPage( 'GET', $startUrl );
		$response       = $guzzle->getInternalResponse();
		$responseCode   = $response->getStatusCode();
		$locationHeader = $response->getHeader( 'Location' );

		$this->assertEquals( $responseCode, $redirectCode );
		$this->assertEquals( $endUrl, $locationHeader );

		$guzzle->followRedirects( true );
	}
}
