<?php
/**
 * Factory class for the WooCommerce's refund data objects.
 *
 * @since v0.10.0
 * @package Tests\WPGraphQL\WooCommerce\Factory
 */

namespace Tests\WPGraphQL\WooCommerce\Factory;

use Tests\WPGraphQL\WooCommerce\Utils\Dummy;

/**
 * Refund factory class for testing.
 */
class RefundFactory extends \WP_UnitTest_Factory_For_Thing {
	public function __construct( $factory = null ) {
		parent::__construct( $factory );

		$this->default_generation_definitions = [
			'amount'         => 0,
			'order_id'       => 0,
			'reason'         => 'defective',
			'refund_payment' => false,
			'restock_items'  => false,
		];
	}

	public function create_object( $args ) {
		$refund = wc_create_refund( $args );

		if ( ! empty( $refund ) && ! empty( $args['meta_data'] ) ) {
			$refund->set_meta_data( $args['meta_data'] );
			$refund->save_meta_data();
		}

		return ! empty( $refund ) ? $refund->get_id() : 0;
	}

	public function update_object( $object, $fields ) {
		if ( ! $object instanceof \WC_Order_Refund && 0 !== absint( $object ) ) {
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
		return \wc_get_order( $id );
	}

	public function createNew( $order, $args = [] ) {
		$order = \wc_get_order( $order );

		if ( empty( $order ) ) {
			return false;
		}

		return $this->create(
			array_merge(
				[
					'amount'         => floatval( $order->get_total() ),
					'order_id'       => $order->get_id(),
					'reason'         => 'defective',
					'refund_payment' => false,
					'restock_items'  => false,
				],
				$args
			)
		);
	}


}
