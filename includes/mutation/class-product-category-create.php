<?php
/**
 * Mutation - CreateProductCategory (additional fields)
 *
 * Registers additional WooCommerce-specific input fields and processes them
 * on the core WPGraphQL CreateProductCategory mutation.
 *
 * @package WPGraphQL\WooCommerce\Mutation
 * @since   0.22.0
 */

namespace WPGraphQL\WooCommerce\Mutation;

use WPGraphQL\Utils\Utils;

/**
 * Class - Product_Category_Create
 */
class Product_Category_Create {
	/**
	 * Registers the additional input fields and hooks.
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_fields(
			'CreateProductCategoryInput',
			self::get_input_fields()
		);

		// Pass custom input fields through into the prepared args.
		add_filter( 'graphql_term_object_insert_term_args', [ self::class, 'pass_through_input_fields' ], 10, 4 );

		// Process custom fields after term creation.
		add_action( 'graphql_insert_product_cat', [ self::class, 'process_additional_fields' ], 10, 2 );
	}

	/**
	 * Returns the additional WooCommerce-specific input fields.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	public static function get_input_fields() {
		return [
			'display'   => [
				'type'        => 'ProductCategoryDisplay',
				'description' => static function () {
					return __( 'Category archive display type.', 'wp-graphql-woocommerce' );
				},
			],
			'menuOrder' => [
				'type'        => 'Integer',
				'description' => static function () {
					return __( 'Menu order, used to custom sort the category.', 'wp-graphql-woocommerce' );
				},
			],
			'imageId'   => [
				'type'        => 'ID',
				'description' => static function () {
					return __( 'The ID of an image attachment to associate with the category.', 'wp-graphql-woocommerce' );
				},
			],
		];
	}

	/**
	 * Passes custom input fields through into the prepared args so they are
	 * available in the `graphql_insert_product_cat` action.
	 *
	 * @param array<string,mixed> $insert_args   The prepared insert args.
	 * @param array<string,mixed> $input         The raw GraphQL input.
	 * @param \WP_Taxonomy        $taxonomy      The taxonomy object.
	 * @param string              $mutation_name The mutation name.
	 *
	 * @return array<string,mixed>
	 */
	public static function pass_through_input_fields( $insert_args, $input, $taxonomy, $mutation_name ) {
		if ( 'product_cat' !== $taxonomy->name ) {
			return $insert_args;
		}

		$custom_fields = [ 'display', 'menuOrder', 'imageId' ];
		foreach ( $custom_fields as $field ) {
			if ( isset( $input[ $field ] ) ) {
				$insert_args[ $field ] = $input[ $field ];
			}
		}

		return $insert_args;
	}

	/**
	 * Processes the additional WooCommerce-specific fields after term insertion.
	 *
	 * @param int                 $term_id The created term ID.
	 * @param array<string,mixed> $args    The prepared args from the mutation input.
	 *
	 * @return void
	 */
	public static function process_additional_fields( $term_id, $args ) {
		if ( isset( $args['display'] ) ) {
			update_term_meta( $term_id, 'display_type', sanitize_text_field( $args['display'] ) );
		}

		if ( isset( $args['menuOrder'] ) ) {
			update_term_meta( $term_id, 'order', absint( $args['menuOrder'] ) );
		}

		if ( isset( $args['imageId'] ) ) {
			$image_database_id = Utils::get_database_id_from_id( $args['imageId'] );
			if ( $image_database_id ) {
				update_term_meta( $term_id, 'thumbnail_id', absint( $image_database_id ) );
			}
		}
	}
}
