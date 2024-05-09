<?php
/**
 * Mutation - deleteProductAttributeTerm
 *
 * Registers mutation for deleting a product attribute term.
 *
 * @package WPGraphQL\WooCommerce\Mutation
 * @since TBD
 */

namespace WPGraphQL\WooCommerce\Mutation;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;

/**
 * Class Product_Attribute_Term_Delete
 */
class Product_Attribute_Term_Delete {
	/**
	 * Registers mutation
	 *
	 * @return void
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'deleteProductAttributeTerm',
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
			'attributeId' => [
				'type'        => [ 'non_null' => 'Int' ],
				'description' => __( 'The ID of the attribute to which the term belongs.', 'wp-graphql-woocommerce' ),
			],
			'id'          => [
				'type'        => [ 'non_null' => 'Int' ],
				'description' => __( 'The ID of the term to update.', 'wp-graphql-woocommerce' ),
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
			'term' => [
				'type'    => 'ProductAttributeTermObject',
				'resolve' => static function ( $payload ) {
					return (object) $payload['term'];
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
			if ( ! $input['attributeId'] ) {
				throw new UserError( __( 'A valid attributeId is required to create a new product attribute term.', 'wp-graphql-woocommerce' ) );
			}
			
			$taxonomy = wc_attribute_taxonomy_name_by_id( $input['attributeId'] );
			if ( empty( $taxonomy ) ) {
				throw new UserError( __( 'Invalid attribute ID.', 'wp-graphql-woocommerce' ) );
			}

			if ( ! $input['id'] ) {
				throw new UserError( __( 'A valid term ID is required to delete a product attribute term.', 'wp-graphql-woocommerce' ) );
			}

			$term = get_term( $input['id'], $taxonomy );
			if ( ! $term ) {
				throw new UserError( __( 'Invalid term ID.', 'wp-graphql-woocommerce' ) );
			} elseif ( is_wp_error( $term ) ) {
				throw new UserError( $term->get_error_message() );
			}

			if ( ! wc_rest_check_product_term_permissions( $taxonomy, 'delete', $term->term_id ) ) {
				throw new UserError( __( 'You do not have permission to delete this term.', 'wp-graphql-woocommerce' ) );
			}

			$menu_order = get_term_meta( $term->term_id, 'order_' . $taxonomy, true );

			$data = [
				'id'          => $term->term_id,
				'name'        => $term->name,
				'slug'        => $term->slug,
				'description' => $term->description,
				'menu_order'  => ! empty( $menu_order ) ? absint( $menu_order ) : 0,
				'count'       => absint( $term->count ),
			];

			$retval = wp_delete_term( $term->term_id, $term->taxonomy );
			if ( ! $retval ) {
				throw new UserError( __( 'Failed to delete term.', 'wp-graphql-woocommerce' ) );
			}

			/**
			 * Fires after a single term is deleted via the REST API.
			 *
			 * @param \WP_Term $term   The deleted term.
			 * @param array    $input  Mutation input.
			 */
			do_action( "graphql_woocommerce_delete_{$taxonomy}", $term, $input );

			return [ 'term' => $data ];
		};
	}
}
