<?php
/**
 * Defines helper functions for user checkout.
 *
 * @package WPGraphQL\WooCommerce\Data\Mutation
 * @since 0.2.0
 */

namespace WPGraphQL\WooCommerce\Data\Mutation;

use GraphQL\Error\UserError;
use function WC;

/**
 * Class - Checkout_Mutation
 */
class Checkout_Mutation {
	/**
	 * Stores checkout fields
	 *
	 * @var array
	 */
	private static $fields;

	/**
	 * Caches customer object. @see get_value.
	 *
	 * @var WC_Customer
	 */
	private $logged_in_customer = null;

	/**
	 * Is registration required to checkout?
	 *
	 * @since  3.0.0
	 * @return boolean
	 */
	public static function is_registration_required() {
		return apply_filters( 'woocommerce_checkout_registration_required', 'yes' !== get_option( 'woocommerce_enable_guest_checkout' ) );
	}

	/**
	 * See if a fieldset should be skipped.
	 *
	 * @since 3.0.0
	 * @param string $fieldset_key Fieldset key.
	 * @param array  $data         Posted data.
	 * @return bool
	 */
	protected static function maybe_skip_fieldset( $fieldset_key, $data ) {
		if ( 'shipping' === $fieldset_key && ( ! $data['ship_to_different_address'] || ! \WC()->cart->needs_shipping_address() ) ) {
			return true;
		}

		if ( 'account' === $fieldset_key && ( is_user_logged_in() || ( ! self::is_registration_required() && empty( $data['createaccount'] ) ) ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Returns order data for use when user checking out.
	 *
	 * @param array       $input    Input data describing order.
	 * @param AppContext  $context  AppContext instance.
	 * @param ResolveInfo $info     ResolveInfo instance.
	 *
	 * @return array
	 */
	public static function prepare_checkout_args( $input, $context, $info ) {
		$data    = array(
			'terms'                     => (int) isset( $input['terms'] ),
			'createaccount'             => (int) ! empty( $input['account'] ),
			'payment_method'            => isset( $input['paymentMethod'] ) ? $input['paymentMethod'] : '',
			'shipping_method'           => isset( $input['shippingMethod'] ) ? $input['shippingMethod'] : '',
			'ship_to_different_address' => ! empty( $input['shipToDifferentAddress'] ) && ! wc_ship_to_billing_address_only(),
		);
		$skipped = array();

		foreach ( self::get_checkout_fields() as $fieldset_key => $fieldset ) {
			if ( self::maybe_skip_fieldset( $fieldset_key, $data ) ) {
				$skipped[] = $fieldset_key;
				continue;
			}

			foreach ( $fieldset as $field => $input_key ) {
				$key   = "{$fieldset_key}_{$field}";
				$value = ! empty( $input[ $fieldset_key ][ $input_key ] )
					? $input[ $fieldset_key ][ $input_key ]
					: null;
				if ( $value ) {
					$data[ $key ] = $value;
				} elseif ( 'billing_country' === $key || 'shipping_country' === $key ) {
					$data[ $key ] = self::get_value( $key );
				}
			}
		}

		if ( in_array( 'shipping', $skipped, true ) && ( \WC()->cart->needs_shipping_address() || \wc_ship_to_billing_address_only() ) ) {
			foreach ( self::get_checkout_fields( 'shipping' ) as $field => $input_key ) {
				$data[ "shipping_{$field}" ] = isset( $data[ "billing_{$field}" ] ) ? $data[ "billing_{$field}" ] : '';
			}
		}

		return apply_filters( 'woocommerce_checkout_posted_data', $data, $input, $context, $info );
	}

	/**
	 * Get an array of checkout fields.
	 *
	 * @param string  $fieldset Target fieldset.
	 * @param boolean $prefixed Prefixed field keys with fieldset name.
	 *
	 * @return bool|array
	 */
	public static function get_checkout_fields( $fieldset = '', $prefixed = false ) {
		$fields = array(
			'billing'  => array(
				'first_name' => 'firstName',
				'last_name'  => 'lastName',
				'address_1'  => 'address1',
				'address_2'  => 'address2',
				'city'       => 'city',
				'postcode'   => 'postcode',
				'state'      => 'state',
				'country'    => 'country',
				'phone'      => 'phone',
				'email'      => 'email',
			),
			'shipping' => array(
				'first_name' => 'firstName',
				'last_name'  => 'lastName',
				'address_1'  => 'address1',
				'address_2'  => 'address2',
				'city'       => 'city',
				'postcode'   => 'postcode',
				'state'      => 'state',
				'country'    => 'country',
			),
			'account'  => array(
				'username' => 'username',
				'password' => 'password',
			),
			'order'    => array(),
		);

		if ( $prefixed ) {
			foreach ( $fields as $prefix => $values ) {
				foreach ( $values as $index => $value ) {
					$fields[ $prefix ][ $index ] = "{$prefix}_{$value}";
				}
			}
		}

		if ( ! empty( $fieldset ) ) {
			return ! empty( $fields[ $fieldset ] ) ? $fields[ $fieldset ] : false;
		}

		return $fields;
	}

	/**
	 * Update customer and session data from the posted checkout data.
	 *
	 * @param array $data Order data.
	 */
	protected function update_session( $data ) {
		// Update both shipping and billing to the passed billing address first if set.
		$address_fields = array(
			'first_name',
			'last_name',
			'company',
			'email',
			'phone',
			'address_1',
			'address_2',
			'city',
			'postcode',
			'state',
			'country',
		);

		foreach ( $address_fields as $field ) {
			self::set_customer_address_fields( $field, $data );
		}
		WC()->customer->save();

		// Update customer shipping and payment method to posted method.
		$chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );

		if ( is_array( $data['shipping_method'] ) ) {
			foreach ( $data['shipping_method'] as $i => $value ) {
				$chosen_shipping_methods[ $i ] = $value;
			}
		}

		WC()->session->set( 'chosen_shipping_methods', $chosen_shipping_methods );
		WC()->session->set( 'chosen_payment_method', $data['payment_method'] );

		// Update cart totals now we have customer address.
		WC()->cart->calculate_totals();
	}

	/**
	 * Create a new customer account if needed.
	 *
	 * @param array $data Checkout data.
	 *
	 * @throws UserError When not able to create customer.
	 */
	protected static function process_customer( $data ) {
		$customer_id = apply_filters( 'woocommerce_checkout_customer_id', get_current_user_id() );

		if ( ! is_user_logged_in() && ( self::is_registration_required() || ! empty( $data['createaccount'] ) ) ) {
			$username    = ! empty( $data['account_username'] ) ? $data['account_username'] : '';
			$password    = ! empty( $data['account_password'] ) ? $data['account_password'] : '';
			$customer_id = wc_create_new_customer(
				$data['billing_email'],
				$username,
				$password,
				array(
					'first_name' => ! empty( $data['billing_first_name'] ) ? $data['billing_first_name'] : '',
					'last_name'  => ! empty( $data['billing_last_name'] ) ? $data['billing_last_name'] : '',
				)
			);

			if ( is_wp_error( $customer_id ) ) {
				throw new UserError( $customer_id->get_error_message() );
			}

			wc_set_customer_auth_cookie( $customer_id );

			// As we are now logged in, checkout will need to refresh to show logged in data.
			WC()->session->set( 'reload_checkout', true );

			// Also, recalculate cart totals to reveal any role-based discounts that were unavailable before registering.
			WC()->cart->calculate_totals();
		}

		// On multisite, ensure user exists on current site, if not add them before allowing login.
		if ( $customer_id && is_multisite() && is_user_logged_in() && ! is_user_member_of_blog() ) {
			add_user_to_blog( get_current_blog_id(), $customer_id, 'customer' );
		}

		// Add customer info from other fields.
		if ( $customer_id && apply_filters( 'woocommerce_checkout_update_customer_data', true, WC()->checkout() ) ) {
			$customer = new \WC_Customer( $customer_id );

			if ( ! empty( $data['billing_first_name'] ) && '' === $customer->get_first_name() ) {
				$customer->set_first_name( $data['billing_first_name'] );
			}

			if ( ! empty( $data['billing_last_name'] ) && '' === $customer->get_last_name() ) {
				$customer->set_last_name( $data['billing_last_name'] );
			}

			// If the display name is an email, update to the user's full name.
			if ( is_email( $customer->get_display_name() ) ) {
				$customer->set_display_name( $customer->get_first_name() . ' ' . $customer->get_last_name() );
			}

			foreach ( $data as $key => $value ) {
				// Use setters where available.
				if ( is_callable( array( $customer, "set_{$key}" ) ) ) {
					$customer->{"set_{$key}"}( $value );

					// Store custom fields prefixed with wither shipping_ or billing_.
				} elseif ( 0 === stripos( $key, 'billing_' ) || 0 === stripos( $key, 'shipping_' ) ) {
					$customer->update_meta_data( $key, $value );
				}
			}

			/**
			 * Action hook to adjust customer before save.
			 *
			 * @since 3.0.0
			 */
			do_action( 'woocommerce_checkout_update_customer', $customer, $data );

			$customer->save();
		}

		do_action( 'woocommerce_checkout_update_user_meta', $customer_id, $data );
	}

	/**
	 * Set address field for customer.
	 *
	 * @param string $field String to update.
	 * @param array  $data  Array of data to get the value from.
	 */
	protected static function set_customer_address_fields( $field, $data ) {
		$billing_value  = null;
		$shipping_value = null;

		if ( isset( $data[ "billing_{$field}" ] ) && is_callable( array( WC()->customer, "set_billing_{$field}" ) ) ) {
			$billing_value  = $data[ "billing_{$field}" ];
			$shipping_value = $data[ "billing_{$field}" ];
		}

		if ( isset( $data[ "shipping_{$field}" ] ) && is_callable( array( WC()->customer, "set_shipping_{$field}" ) ) ) {
			$shipping_value = $data[ "shipping_{$field}" ];
		}

		if ( ! is_null( $billing_value ) && is_callable( array( WC()->customer, "set_billing_{$field}" ) ) ) {
			WC()->customer->{"set_billing_{$field}"}( $billing_value );
		}

		if ( ! is_null( $shipping_value ) && is_callable( array( WC()->customer, "set_shipping_{$field}" ) ) ) {
			WC()->customer->{"set_shipping_{$field}"}( $shipping_value );
		}
	}

	/**
	 * Validates the posted checkout data based on field properties.
	 *
	 * @param array $data  Checkout data.
	 *
	 * @throws UserError Invalid input.
	 */
	protected static function validate_data( &$data ) {
		foreach ( self::get_checkout_fields( '', true ) as $fieldset_key => $fieldset ) {
			$validate_fieldset = true;
			if ( self::maybe_skip_fieldset( $fieldset_key, $data ) ) {
				$validate_fieldset = false;
			}

			foreach ( $fieldset as $key => $field_label ) {
				if ( ! isset( $data[ $key ] ) ) {
					continue;
				}

				if ( \wc_graphql_ends_with( $key, 'postcode' ) ) {
					$country      = isset( $data[ $fieldset_key . '_country' ] ) ? $data[ $fieldset_key . '_country' ] : WC()->customer->{"get_{$fieldset_key}_country"}();
					$data[ $key ] = \wc_format_postcode( $data[ $key ], $country );

					if ( $validate_fieldset && '' !== $data[ $key ] && ! \WC_Validation::is_postcode( $data[ $key ], $country ) ) {
						switch ( $country ) {
							case 'IE':
								/* translators: %1$s: field name, %2$s finder.eircode.ie URL */
								$postcode_validation_notice = sprintf( __( '%1$s is not valid. You can look up the correct Eircode. %2$s', 'wp-graphql-woocommerce' ), $field_label, 'https://finder.eircode.ie' );
								break;
							default:
								/* translators: %s: field name */
								$postcode_validation_notice = sprintf( __( '%s is not a valid postcode / ZIP.', 'wp-graphql-woocommerce' ), $field_label );
						}
						throw new UserError( apply_filters( 'woocommerce_checkout_postcode_validation_notice', $postcode_validation_notice, $country, $data[ $key ] ) );
					}
				}

				if ( \wc_graphql_ends_with( $key, 'phone' ) ) {
					if ( $validate_fieldset && '' !== $data[ $key ] && ! WC_Validation::is_phone( $data[ $key ] ) ) {
						/* translators: %s: phone number */
						throw new UserError( sprintf( __( '%s is not a valid phone number.', 'wp-graphql-woocommerce' ), $field_label ) );
					}
				}

				if ( \wc_graphql_ends_with( $key, 'email' ) && '' !== $data[ $key ] ) {
					$email_is_valid = is_email( $data[ $key ] );
					$data[ $key ]   = sanitize_email( $data[ $key ] );

					if ( $validate_fieldset && ! $email_is_valid ) {
						/* translators: %s: email address */
						throw new UserError( sprintf( __( '%s is not a valid email address.', 'wp-graphql-woocommerce' ), $field_label ) );
					}
				}

				if ( \wc_graphql_ends_with( $key, 'state' ) && '' !== $data[ $key ] ) {
					$country      = isset( $data[ $fieldset_key . '_country' ] ) ? $data[ $fieldset_key . '_country' ] : WC()->customer->{"get_{$fieldset_key}_country"}();
					$valid_states = WC()->countries->get_states( $country );

					if ( ! empty( $valid_states ) && is_array( $valid_states ) && count( $valid_states ) > 0 ) {
						$valid_state_values = array_map( 'wc_strtoupper', array_flip( array_map( 'wc_strtoupper', $valid_states ) ) );
						$data[ $key ]       = wc_strtoupper( $data[ $key ] );

						if ( isset( $valid_state_values[ $data[ $key ] ] ) ) {
							// With this part we consider state value to be valid as well, convert it to the state key for the valid_states check below.
							$data[ $key ] = $valid_state_values[ $data[ $key ] ];
						}

						if ( $validate_fieldset && ! in_array( $data[ $key ], $valid_state_values, true ) ) {
							/* translators: 1: state field 2: valid states */
							throw new UserError( sprintf( __( '%1$s is not valid. Please enter one of the following: %2$s', 'wp-graphql-woocommerce' ), $field_label, implode( ', ', $valid_states ) ) );
						}
					}
				}
			}
		}
	}

	/**
	 * Validates that the checkout has enough info to proceed.
	 *
	 * @param array $data  An array of posted data.
	 *
	 * @throws UserError Invalid input.
	 */
	protected static function validate_checkout( &$data ) {
		self::validate_data( $data );
		WC()->checkout()->check_cart_items();

		if ( WC()->cart->needs_shipping() ) {
			$shipping_country = WC()->customer->get_shipping_country();

			if ( empty( $shipping_country ) ) {
				throw new UserError( __( 'Please enter an address to continue.', 'wp-graphql-woocommerce' ) );
			} elseif ( ! in_array( WC()->customer->get_shipping_country(), array_keys( WC()->countries->get_shipping_countries() ), true ) ) {
				throw new UserError(
					sprintf(
						/* translators: %s: shipping location */
						__( 'Unfortunately, we do not ship %s. Please enter an alternative shipping address.', 'wp-graphql-woocommerce' ),
						WC()->countries->shipping_to_prefix() . ' ' . WC()->customer->get_shipping_country()
					)
				);
			} else {
				$chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );

				foreach ( WC()->shipping()->get_packages() as $i => $package ) {
					if ( ! isset( $chosen_shipping_methods[ $i ], $package['rates'][ $chosen_shipping_methods[ $i ] ] ) ) {
						throw new UserError( __( 'No shipping method has been selected. Please double check your address, or contact us if you need any help.', 'wp-graphql-woocommerce' ) );
					}
				}
			}
		}

		if ( WC()->cart->needs_payment() ) {
			$available_gateways = WC()->payment_gateways->get_available_payment_gateways();

			if ( ! isset( $available_gateways[ $data['payment_method'] ] ) ) {
				throw new UserError( __( 'Invalid payment method.', 'wp-graphql-woocommerce' ) );
			} else {
				$available_gateways[ $data['payment_method'] ]->validate_fields();
			}
		}

		do_action( 'woocommerce_after_checkout_validation', $data );
	}

	/**
	 * Process an order that does require payment.
	 *
	 * @param int    $order_id       Order ID.
	 * @param string $payment_method Payment method.
	 *
	 * @return array.
	 */
	protected function process_order_payment( $order_id, $payment_method ) {
		$available_gateways = WC()->payment_gateways->get_available_payment_gateways();

		if ( ! isset( $available_gateways[ $payment_method ] ) ) {
			return;
		}

		// Store Order ID in session so it can be re-used after payment failure.
		WC()->session->set( 'order_awaiting_payment', $order_id );

		// Process Payment.
		return $available_gateways[ $payment_method ]->process_payment( $order_id );
	}

	/**
	 * Process an order that doesn't require payment.
	 *
	 * @since 3.0.0
	 * @param int $order_id Order ID.
	 */
	protected function process_order_without_payment( $order_id ) {
		$order = wc_get_order( $order_id );
		$order->payment_complete();
		wc_empty_cart();

		return array(
			'result'   => 'success',
			'redirect' => apply_filters( 'woocommerce_checkout_no_payment_needed_redirect', $order->get_checkout_order_received_url(), $order ),
		);
	}

	/**
	 * Process the checkout.
	 *
	 * @param array       $data     Order data.
	 * @param AppContext  $context  AppContext instance.
	 * @param ResolveInfo $info     ResolveInfo instance.
	 * @param array       $results  Order status.
	 *
	 * @throws UserError When validation fails.
	 */
	public static function process_checkout( $data, $context, $info, &$results = null ) {
		wc_maybe_define_constant( 'WOOCOMMERCE_CHECKOUT', true );
		wc_set_time_limit( 0 );

		do_action( 'woocommerce_before_checkout_process' );

		if ( WC()->cart->is_empty() ) {
			throw new UserError( __( 'Sorry, your session has expired.', 'wp-graphql-woocommerce' ) );
		}

		do_action( 'woocommerce_checkout_process', $data, $context, $info );

		// Update session for customer and totals.
		self::update_session( $data );

		// Validate posted data and cart items before proceeding.
		self::validate_checkout( $data );

		self::process_customer( $data );
		$order_id = WC()->checkout->create_order( $data );
		$order    = wc_get_order( $order_id );

		if ( is_wp_error( $order_id ) ) {
			throw new UserError( $order_id->get_error_message() );
		}

		if ( ! $order ) {
			throw new UserError( __( 'Unable to create order.', 'wp-graphql-woocommerce' ) );
		}

		do_action( 'woocommerce_checkout_order_processed', $order_id, $data, $order );

		if ( WC()->cart->needs_payment() ) {
			$results = self::process_order_payment( $order_id, $data['payment_method'] );
		} else {
			$results = self::process_order_without_payment( $order_id );
		}

		return $order_id;
	}

	/**
	 * Gets the value either from 3rd party logic or the customer object. Sets the default values in checkout fields.
	 *
	 * @param string $input Name of the input we want to grab data for. e.g. billing_country.
	 * @return string The default value.
	 */
	public static function get_value( $input ) {
		// Allow 3rd parties to short circuit the logic and return their own default value.
		$value = apply_filters( 'woocommerce_checkout_get_value', null, $input );
		if ( ! is_null( $value ) ) {
			return $value;
		}

		/**
		 * For logged in customers, pull data from their account rather than the session which may contain incomplete data.
		 * Another reason is that WC sets shipping address to the billing address on the checkout updates unless the
		 * "shipToDifferentAddress" is set.
		 */
		$customer_object = false;
		if ( is_user_logged_in() ) {
			// Load customer object, but keep it cached to avoid reloading it multiple times.
			if ( is_null( self::$logged_in_customer ) ) {
				self::$logged_in_customer = new WC_Customer( get_current_user_id(), true );
			}
			$customer_object = new WC_Customer( get_current_user_id(), true );
		}

		if ( ! $customer_object ) {
			$customer_object = WC()->customer;
		}

		if ( is_callable( array( $customer_object, "get_$input" ) ) ) {
			$value = $customer_object->{"get_$input"}();
		} elseif ( $customer_object->meta_exists( $input ) ) {
			$value = $customer_object->get_meta( $input, true );
		}
		if ( '' === $value ) {
			$value = null;
		}
		return apply_filters( 'default_checkout_' . $input, $value, $input );
	}
}
