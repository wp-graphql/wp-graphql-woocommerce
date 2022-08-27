<?php
/**
 * Factory class for the WooCommerce's coupon data objects.
 *
 * @since v0.8.0
 * @package Tests\WPGraphQL\WooCommerce\Factory
 */

namespace Tests\WPGraphQL\WooCommerce\Factory;

use Tests\WPGraphQL\WooCommerce\Utils\Dummy;

/**
 * Coupon factory class for testing.
 */
class CouponFactory extends \WP_UnitTest_Factory_For_Thing {
	public function __construct( $factory = null ) {
		parent::__construct( $factory );

		$this->default_generation_definitions = [
			'coupon_class' => '\WC_Coupon',
		];
	}

	public function create_object( $args ) {
		if ( is_wp_error( $args ) ) {
			codecept_debug( $args );
		}
		$coupon_class = $args['coupon_class'];
		unset( $args['coupon_class'] );

		$coupon = new $coupon_class();

		$amount = Dummy::instance()->number( 25, 75 );
		$coupon->set_props(
			array_merge(
				[
					'code'          => $amount . 'off',
					'amount'        => floatval( $amount ),
					'date_expires'  => null,
					'discount_type' => 'percent',
					'description'   => 'Test coupon',
				],
				$args
			)
		);

		// Set meta data.
		if ( ! empty( $args['meta_data'] ) ) {
			$coupon->set_meta_data( $args['meta_data'] );
		}

		return $coupon->save();
	}

	public function update_object( $object, $fields ) {
		if ( ! $object instanceof \WC_Coupon && 0 !== absint( $object ) ) {
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
		return new \WC_Coupon( $id );
	}
}
