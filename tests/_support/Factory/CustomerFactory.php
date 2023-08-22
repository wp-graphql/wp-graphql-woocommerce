<?php
/**
 * Factory class for the WooCommerce's customer data objects.
 *
 * @since v0.10.0
 * @package Tests\WPGraphQL\WooCommerce\Factory
 */

namespace Tests\WPGraphQL\WooCommerce\Factory;

use Faker\Factory;

/**
 * Customer factory class for testing.
 */
class CustomerFactory extends \WP_UnitTest_Factory_For_Thing {
	public function __construct( $factory = null ) {
		parent::__construct( $factory );

		$this->default_generation_definitions = [];
		$this->dummy                          = Factory::create();
	}

	public function create_object( $args ) {
		if ( is_wp_error( $args ) ) {
			codecept_debug( $args );
		}

		$customer = new \WC_Customer();

		// Create customer details
		$username   = $this->dummy->userName();
		$first_name = $this->dummy->firstName();
		$last_name  = $this->dummy->lastName();
		$street     = $this->dummy->streetAddress();
		$city       = $this->dummy->city();
		$state      = $this->dummy->state();
		$postcode   = $this->dummy->postcode();
		$country    = 'US';
		$email      = $this->dummy->email();
		$phone      = $this->dummy->phoneNumber();

		$args = array_merge(
			[
				'billing'  => [],
				'shipping' => [],
			],
			$args
		);

		$customer->set_billing_first_name( ! empty( $args['billing']['first_name'] ) ? $args['billing']['first_name'] : $first_name );
		$customer->set_billing_last_name( ! empty( $args['billing']['last_name'] ) ? $args['billing']['last_name'] : $last_name );
		$customer->set_billing_address_1( ! empty( $args['billing']['address_1'] ) ? $args['billing']['address_1'] : $street );
		$customer->set_billing_address_2( ! empty( $args['billing']['address_2'] ) ? $args['billing']['address_2'] : '' );
		$customer->set_billing_city( ! empty( $args['billing']['city'] ) ? $args['billing']['city'] : $city );
		$customer->set_billing_state( ! empty( $args['billing']['state'] ) ? $args['billing']['state'] : $state );
		$customer->set_billing_postcode( ! empty( $args['billing']['postcode'] ) ? $args['billing']['postcode'] : $postcode );
		$customer->set_billing_country( ! empty( $args['billing']['country'] ) ? $args['billing']['country'] : $country );
		$customer->set_billing_email( ! empty( $args['billing']['email'] ) ? $args['billing']['email'] : $email );
		$customer->set_billing_phone( ! empty( $args['billing']['phone'] ) ? $args['billing']['phone'] : $phone );
		$customer->set_shipping_first_name( ! empty( $args['shipping']['first_name'] ) ? $args['shipping']['first_name'] : $first_name );
		$customer->set_shipping_last_name( ! empty( $args['shipping']['last_name'] ) ? $args['shipping']['last_name'] : $last_name );
		$customer->set_shipping_address_1( ! empty( $args['shipping']['address_1'] ) ? $args['shipping']['address_1'] : $street );
		$customer->set_shipping_address_2( ! empty( $args['shipping']['address_2'] ) ? $args['shipping']['address_2'] : '' );
		$customer->set_shipping_city( ! empty( $args['shipping']['city'] ) ? $args['shipping']['city'] : $city );
		$customer->set_shipping_state( ! empty( $args['shipping']['state'] ) ? $args['shipping']['state'] : $state );
		$customer->set_shipping_postcode( ! empty( $args['shipping']['postcode'] ) ? $args['shipping']['postcode'] : $postcode );
		$customer->set_shipping_country( ! empty( $args['shipping']['country'] ) ? $args['shipping']['country'] : $country );
		$customer->set_shipping_phone( ! empty( $args['shipping']['phone'] ) ? $args['shipping']['phone'] : $phone );

		// Set data.
		$customer->set_props(
			array_merge(
				[
					'email'              => $email,
					'first_name'         => $first_name,
					'last_name'          => $last_name,
					'display_name'       => $username,
					'role'               => 'customer',
					'username'           => $username,
					'is_paying_customer' => false,
				],
				$args
			)
		);

		// Set meta data.
		if ( ! empty( $args['meta_data'] ) ) {
			$customer->set_meta_data( $args['meta_data'] );
		}

		return $customer->save();
	}

	public function update_object( $object, $fields ) {
		if ( ! $object instanceof \WC_Customer && 0 !== absint( $object ) ) {
			$object = $this->get_object_by_id( $object );
		}

		foreach ( $fields as $field => $field_value ) {
			if ( ! is_callable( [ $object, "set_{$field}" ] ) ) {
				throw new \Exception(
					sprintf( '"%1$s" is not a valid %2$s coupon field.', $field, $object->get_type() )
				);
			}

			$object->{"set_{$field}"}( $field_value );
		}

		$object->save();
	}

	public function get_object_by_id( $id ) {
		return new \WC_Customer( $id );
	}
}
