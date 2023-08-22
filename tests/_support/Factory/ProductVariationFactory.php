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

		$this->default_generation_definitions = [
			'variation_class' => '\WC_Product_Variation',
		];
	}

	public function create_object( $args ) {
		if ( is_wp_error( $args ) ) {
			codecept_debug( $args );
		}

		$variation_class = $args['variation_class'];
		unset( $args['variation_class'] );

		// Create variation.
		$variation = new $variation_class();
		foreach ( $args as $field => $field_value ) {
			if ( ! is_callable( [ $variation, "set_{$field}" ] ) ) {
				throw new \Exception(
					sprintf( '"%1$s" is not a valid %2$s product variation field.', $field, $variation->get_type() )
				);
			}

			$variation->{"set_{$field}"}( $field_value );
		}

		// if ( ! empty( $args['meta_data'] ) ) {
		// 	$variation->set_meta_data( $args['meta_data'] );
		// 	unset( $args['meta_data'] );
		// }
		// if ( ! empty( $args['attributes'] ) ) {
		// 	$variation->set_attributes( $args['attributes'] );
		// 	unset( $args['attributes'] );
		// }
		// $variation->set_props( $args );

		return $variation->save();
	}

	public function update_object( $object, $fields ) {
		if ( ! $object instanceof \WC_Product_Variation && 0 !== absint( $object ) ) {
			$object = $this->get_object_by_id( $object );
		}

		foreach ( $fields as $field => $field_value ) {
			if ( ! is_callable( [ $object, "set_{$field}" ] ) ) {
				throw new \Exception(
					sprintf( '"%1$s" is not a valid %2$s product variation field.', $field, $object->get_type() )
				);
			}

			$object->{"set_{$field}"}( $field_value );
		}

		$object->save();
	}

	public function get_object_by_id( $product_id ) {
		return wc_get_product( absint( $product_id ) );
	}

	public function createSome( $product_id = null, $args = [] ) {
		if ( ! $product_id ) {
			$product_id = $this->factory->product->createVariable();
		}
		$product = wc_get_product( $product_id );

		// Create variation stub data.
		$variation_data = [
			[
				'parent_id'     => $product_id,
				'attributes'    => [
					'pa_size' => 'small',
					'logo'    => 'Yes',
				],
				'image_id'      => null,
				'downloads'     => [ $this->factory->product->createDownload() ],
				'regular_price' => 10,
			],
			[
				'parent_id'     => $product_id,
				'attributes'    => [
					'pa_size' => 'medium',
					'logo'    => 'No',
				],
				'image_id'      => $this->factory->post->create(
					[
						'post_status'  => 'publish',
						'post_type'    => 'attachment',
						'post_content' => 'product image',
					]
				),
				'downloads'     => [],
				'regular_price' => 15,
			],
			[
				'parent_id'     => $product_id,
				'attributes'    => [
					'pa_size' => 'large',
					'logo'    => 'Yes',
				],
				'image_id'      => null,
				'downloads'     => [],
				'regular_price' => 20,
			],
		];

		$variations = [];
		foreach ( $variation_data as $data ) {
			$variation_args = array_merge( $data, $args );
			$variation      = $this->create_and_get( $variation_args, [ 'variation_class' => '\WC_Product_Variation' ] );

			$variations[] = $variation->get_id();
		}

		$product->set_default_attributes(
			[
				'pa_size' => 'medium',
			]
		);
		$product->save();

		return [
			'product'    => $product_id,
			'variations' => $variations,
		];
	}
}
