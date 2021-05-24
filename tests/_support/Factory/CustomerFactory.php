<?php
/**
 * Factory class for the WooCommerce's customer data objects.
 *
 * @since v0.10.0
 * @package Tests\WPGraphQL\WooCommerce\Factory
 */

namespace Tests\WPGraphQL\WooCommerce\Factory;

use Tests\WPGraphQL\WooCommerce\Utils\Dummy;

/**
 * Customer factory class for testing.
 */
class CustomerFactory extends \WP_UnitTest_Factory_For_Thing {
	function __construct( $factory = null ) {
		parent::__construct( $factory );

		$this->default_generation_definitions = array();
		$this->dummy = Dummy::instance();
	}

	public function create_object( $args ) {
		if ( is_wp_error( $args ) ) codecept_debug( $args );

		$customer = new \WC_Customer();

		// Create customer details
		$username   = $this->dummy->username();
		$first_name = $this->dummy->firstname();
		$last_name  = $this->dummy->lastname();
		$street     = $this->dummy->street();
		$city       = $this->dummy->city();
		$state      = $this->dummy->state();
		$postcode   = $this->dummy->zipcode();
		$country    = 'US';
		$email      = $this->dummy->email();
		$phone      = $this->dummy->telephone();

		// Set data.
		$customer->set_props(
			array_merge(
				array(
					'email'              => $email,
					'first_name'         => $first_name,
					'last_name'          => $last_name,
					'display_name'       => $username,
					'role'               => 'customer',
					'username'           => $username,
					'billing'            => array(
						'first_name'     => $first_name,
						'last_name'      => $last_name,
						'company'        => '',
						'address_1'      => $street,
						'address_2'      => '',
						'city'           => $city,
						'state'          => $state,
						'postcode'       => $postcode,
						'country'        => $country,
						'email'          => $email,
						'phone'          => $phone,
					),
					'shipping'           => array(
						'first_name'     => $first_name,
						'last_name'      => $last_name,
						'company'        => '',
						'address_1'      => $street,
						'address_2'      => '',
						'city'           => $city,
						'state'          => $state,
						'postcode'       => $postcode,
						'country'        => $country,
					),
					'is_paying_customer' => false,
				),
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

		foreach( $fields as $field => $field_value ) {
			if ( ! is_callable( array( $object, "set_{$field}" ) ) ) {
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
