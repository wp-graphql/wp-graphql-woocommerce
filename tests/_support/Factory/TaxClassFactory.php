<?php
/**
 * Factory class for the WooCommerce's tax class data objects.
 *
 * @since 0.20.0
 * @package Tests\WPGraphQL\WooCommerce\Factory
 */

namespace Tests\WPGraphQL\WooCommerce\Factory;

use Tests\WPGraphQL\WooCommerce\Utils\Dummy;

/**
 * Tax Class factory class for testing.
 */
class TaxClassFactory extends \WP_UnitTest_Factory_For_Thing {
	public function __construct( $factory = null ) {
		parent::__construct( $factory );

		$this->default_generation_definitions = [
			'name' => '',
            'slug' => '',
		];
		$this->dummy                          = Dummy::instance();
	}

	public function create_object( $args = [] ) {
        $name = ! empty( $args['name'] ) ? $args['name'] : 'TaxClassNo' . Dummy::instance()->number();
        $slug = ! empty( $args['slug'] ) ? $args['slug'] : '';

		$tax_class = \WC_Tax::create_tax_class( $name, $slug );

        if ( is_wp_error( $tax_class ) ) {
            \codecept_debug( $tax_class->get_error_message() );
			throw new \Exception( $tax_class->get_error_message() );
		}

        return $tax_class;
	}

	public function update_object( $object, $fields ) {
		throw new \Exception( 'You doing it wrong. You can only create or delete tax classes.' );
	}

	public function get_object_by_id( $slug ) {
		$tax_class = \WC_Tax::get_tax_class_by( 'slug', $slug );
        if ( ! $tax_class ) {
            return null;
        }

        if ( is_wp_error( $tax_class ) ) {
            \codecept_debug( $tax_class->get_error_message() );
            return null;
        }

        return $tax_class;
	}
}
