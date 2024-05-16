<?php
/**
 * Mutation - deleteTaxClass
 *
 * Registers mutation for deleting a tax class.
 *
 * @package WPGraphQL\WooCommerce\Mutation
 * @since TBD
 */

namespace WPGraphQL\WooCommerce\Mutation;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;

/**
 * Class - Tax_Class_Delete
 */
class Tax_Class_Delete {
	/**
	 * Registers mutation
	 *
	 * @return void
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'deleteTaxClass',
			[
				'inputFields'         => self::get_input_fields(),
				'outputFields'        => self::get_output_fields(),
				'mutateAndGetPayload' => self::mutate_and_get_payload(),
			]
		);
	}

	/**
	 * Defines the mutation input field configuration
	 *
	 * @return array
	 */
	public static function get_input_fields() {
		return [
			'slug' => [
				'type'        => [ 'non_null' => 'String' ],
				'description' => __( 'Slug of the tax class.', 'wp-graphql-woocommerce' ),
			],
		];
	}

	/**
	 * Defines the mutation output field configuration
	 *
	 * @return array
	 */
	public static function get_output_fields() {
		return [
			'taxClass' => [
				'type'    => 'TaxClass',
				'resolve' => static function ( $payload ) {
					return ! empty( $payload['taxClass'] ) ? $payload['taxClass'] : null;
				},
			],
		];
	}

	/**
	 * Defines the mutation data modification closure.
	 *
	 * @return callable
	 */
	public static function mutate_and_get_payload() {
		return static function ( $input, AppContext $context, ResolveInfo $info ) {
			$slug = $input['slug'];
			$tax_class = \WC_Tax::get_tax_class_by( 'slug', $slug );
			if ( ! $tax_class ) {
				throw new UserError( __( 'Invalid tax class slug.', 'wp-graphql-woocommerce' ) );
			}

			$deleted   = \WC_Tax::delete_tax_class_by( 'slug', $slug );
			if ( ! $deleted ) {
				throw new UserError( __( 'Failed to delete tax class.', 'wp-graphql-woocommerce' ) );
			}

			return [ 'taxClass' => $tax_class ];
		};
	}
}
