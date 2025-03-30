<?php
/**
 * Mutation - createTaxClass
 *
 * Registers mutation for creating a tax class.
 *
 * @package WPGraphQL\WooCommerce\Mutation
 * @since 0.20.0
 */

namespace WPGraphQL\WooCommerce\Mutation;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;

/**
 * Class - Tax_Class_Create
 */
class Tax_Class_Create {
	/**
	 * Registers mutation
	 *
	 * @return void
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'createTaxClass',
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
			'name' => [
				'type'        => [ 'non_null' => 'String' ],
				'description' => __( 'Name of the tax class.', 'wp-graphql-woocommerce' ),
			],
			'slug' => [
				'type'        => 'String',
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
			if ( ! \wc_rest_check_manager_permissions( 'settings', 'create' ) ) {
				throw new UserError( __( 'Sorry, you are not allowed to create tax classes.', 'wp-graphql-woocommerce' ), \rest_authorization_required_code() );
			}
			$name = $input['name'];
			$slug = ! empty( $input['slug'] ) ? $input['slug'] : '';

			$tax_class = \WC_Tax::create_tax_class( $name, $slug );

			if ( is_wp_error( $tax_class ) ) {
				throw new UserError( $tax_class->get_error_message() );
			}

			/**
			 * Filter tax class object before responding.
			 *
			 * @param array $tax_class  The shipping method object.
			 * @param array $input   Request input.
			 */
			$tax_class = apply_filters( 'graphql_woocommerce_tax_class_create', $tax_class, $input );

			return [ 'taxClass' => $tax_class ];
		};
	}
}
