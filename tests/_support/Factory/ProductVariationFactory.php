<?php
/**
 * Factory class for the WooCommerce's Product variation data objects.
 *
 * @since v0.8.0
 * @package Tests\WPGraphQL\WooCommerce\Factory
 */

namespace Tests\WPGraphQL\WooCommerce\Factory;

use Tests\WPGraphQL\WooCommerce\Utils\Dummy;

/**
 * Product variation factory class for testing.
 */
class ProductVariationFactory extends \WP_UnitTest_Factory_For_Thing {
	public function __construct( $factory = null ) {
		parent::__construct( $factory );

		$this->default_generation_definitions = array(
			'variation_class' => '\WC_Product_Variation',
		);
	}

	public function create_object( $args ) {
		if ( is_wp_error( $args ) ) {
			codecept_debug( $args );
		}

		$variation_class = $args['variation_class'];
		unset( $args['variation_class'] );

		// Create variation.
		$variation = new $variation_class();
		$variation->set_props( $args );
		if ( ! empty( $args['meta_data'] ) ) {
			$variation->set_meta_data( $args['meta_data'] );
		}
		if ( ! empty( $args['attributes'] ) ) {
			$variation->set_attributes( $args['attributes'] );
		}

		return $variation->save();
	}

	public function update_object( $object, $fields ) {
		if ( ! $object instanceof \WC_Product_Variation && 0 !== absint( $object ) ) {
			$object = $this->get_object_by_id( $object );
		}

		foreach ( $fields as $field => $field_value ) {
			if ( ! is_callable( array( $object, "set_{$field}" ) ) ) {
				throw new \Exception(
					sprintf( '"%1$s" is not a valid %2$s product field.', $field, $object->get_type() )
				);
			}

			$object->{"set_{$field}"}( $field_value );
		}

		$object->save();
	}

	public function get_object_by_id( $product_id ) {
		return \wc_get_product( absint( $product_id ) );
	}

	public function createSome( $product = null, $args = array() ) {
		if ( ! $product ) {
			$product = $this->factory->product->createVariable();
		}

		// Create variation stub data.
		$variation_data = array(
			array(
				'attributes'    => array( 'pa_size' => 'small' ),
				'image_id'      => null,
				'downloads'     => array( $this->factory->product->createDownload() ),
				'regular_price' => 10,
			),
			array(
				'attributes'    => array( 'pa_size' => 'medium' ),
				'image_id'      => $this->factory->post->create(
					array(
						'post_status'  => 'publish',
						'post_type'    => 'attachment',
						'post_content' => 'product image',
					)
				),
				'downloads'     => array(),
				'regular_price' => 15,
			),
			array(
				'attributes'    => array( 'pa_size' => 'large' ),
				'image_id'      => null,
				'downloads'     => array(),
				'regular_price' => 20,
			),
		);

		$variations = array();
		foreach ( $variation_data as $data ) {
			$args      = array_merge( $data, $args );
			$variation = $this->create_and_get(
				$args,
				array(
					'parent_id'       => $product,
					'sku'             => uniqid(),
					'variation_class' => '\WC_Product_Variation',
				)
			);

			$variations[] = $variation->get_id();
		}

		return compact( 'product', 'variations' );
	}
}
