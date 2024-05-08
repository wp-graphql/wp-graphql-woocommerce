<?php
/**
 * Mutation - updateProductVariation
 *
 * Registers mutation for updating a product variation.
 *
 * @package WPGraphQL\WooCommerce\Mutation
 * @since TBD
 */

namespace WPGraphQL\WooCommerce\Mutation;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Data\Mutation\Product_Mutation;
use WPGraphQL\WooCommerce\Model\Product_Variation;

/**
 * Class Product_Variation_Update
 */
class Product_Variation_Update {
	/**
	 * Registers mutation
	 *
	 * @return void
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'updateProductVariation',
			[
				'inputFields'         => self::get_input_fields(),
				'outputFields'        => self::get_output_fields(),
				'mutateAndGetPayload' => [ Product_Variation_Create::class, 'mutate_and_get_payload' ],
			]
		);
	}

    /**
	 * Defines the mutation input field configuration
	 *
	 * @return array
	 */
	public static function get_input_fields() {
		return array_merge( 
			[
				'id' => [
					'type'        => [ 'non_null' => 'ID' ],
					'description' => __( 'Unique identifier for the product.', 'wp-graphql-woocommerce' ),
				],
			],
			Product_Variation_Create::get_input_fields()
		);
    }

    /**
	 * Defines the mutation output field configuration
	 *
	 * @return array
	 */
	public static function get_output_fields() {
		return [
			'variation'   => [
				'type'    => 'ProductVariation',
				'resolve' => static function ( $payload ) {
					return new Product_Variation( $payload['id'] );
				},
			],
		];
	}
}
