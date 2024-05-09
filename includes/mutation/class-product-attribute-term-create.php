<?php
/**
 * Mutation - createProductAttributeTerm
 *
 * Registers mutation for creating a product attribute term.
 *
 * @package WPGraphQL\WooCommerce\Mutation
 * @since TBD
 */

namespace WPGraphQL\WooCommerce\Mutation;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;

/**
 * Class Product_Attribute_Term_Create
 */
class Product_Attribute_Term_Create {
	/**
	 * Registers mutation
	 *
	 * @return void
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'createProductAttributeTerm',
			[
				'inputFields'         => self::get_input_fields(),
				'outputFields'        => self::get_output_fields(),
				'mutateAndGetPayload' => [ self::class, 'mutate_and_get_payload' ],
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
			'name'        => [
				'type'        => [ 'non_null' => 'String' ],
				'description' => __( 'The name of the term.', 'wp-graphql-woocommerce' ),
			],
			'slug'        => [
				'type'        => 'String',
				'description' => __( 'The slug of the term.', 'wp-graphql-woocommerce' ),
			],
			'description' => [
				'type'        => 'String',
				'description' => __( 'The description of the term.', 'wp-graphql-woocommerce' ),
			],
			'menuOrder'   => [
				'type'        => 'Int',
				'description' => __( 'The order of the term in the menu.', 'wp-graphql-woocommerce' ),
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
	 * @param array                                $input    Mutation input.
	 * @param \WPGraphQL\AppContext                $context  AppContext instance.
	 * @param \GraphQL\Type\Definition\ResolveInfo $info     ResolveInfo instance. Can be
	 * use to get info about the current node in the GraphQL tree.
	 *
	 * @throws \GraphQL\Error\UserError Invalid ID provided | Lack of capabilities.
	 *
	 * @return array
	 */
	public static function mutate_and_get_payload( $input, AppContext $context, ResolveInfo $info ) {
		if ( ! $input['attributeId'] ) {
			throw new UserError( __( 'An attributeId is required to create a new product attribute term.', 'wp-graphql-woocommerce' ) );
		}

		$context  = 'createProductAttributeTerm' === $info->fieldName ? 'create' : 'edit';
		$taxonomy = wc_attribute_taxonomy_name_by_id( $input['attributeId'] );
		if ( empty( $taxonomy ) ) {
			throw new UserError( __( 'Invalid attributeId.', 'wp-graphql-woocommerce' ) );
		}

		if ( ! wc_rest_check_product_term_permissions( $taxonomy, $context ) ) {
			throw new UserError( __( 'Sorry, you are not allowed to create product attribute terms.', 'wp-graphql-woocommerce' ) );
		}

		$id   = isset( $input['id'] ) ? $input['id'] : null;
		$args = [];

		if ( ! empty( $input['description'] ) ) {
			$args['description'] = $input['description'];
		}

		if ( ! empty( $input['slug'] ) ) {
			$args['slug'] = $input['slug'];
		}

		if ( $id && ! empty( $input['name'] ) ) {
			$args['name'] = $input['name'];
		}

		$term = null;
		if ( $id ) {
			$term = get_term( $id, $taxonomy );
		}

		if ( is_wp_error( $term ) ) {
			throw new UserError( $term->get_error_message() );
		} elseif ( $term && ! wc_rest_check_product_term_permissions( $taxonomy, $context, $term->term_id ) ) {
			throw new UserError( __( 'Sorry, you are not allowed to update this product attribute term.', 'wp-graphql-woocommerce' ) );
		}

		if ( $id ) {
			$term = wp_update_term( $id, $taxonomy, $args );
		} elseif ( ! empty( $input['name'] ) ) {
			$name = $input['name'];
			$term = wp_insert_term( $name, $taxonomy, $args );
		} else {
			$updating = 'updateProductAttributeTerm' === $info->fieldName;
			throw new UserError( 
				$updating
					? __( 'A name is required to create a new product attribute term.', 'wp-graphql-woocommerce' )
					: __( 'A valid term "id" and changeable parameter are required to update a product attribute term.', 'wp-graphql-woocommerce' )
			);
		}

		if ( is_wp_error( $term ) ) {
			throw new UserError( $term->get_error_message() );
		}

		/**
		 * Newly created product attribute term.
		 * 
		 * @var \WP_Term|\WP_Error|null $term
		 */
		$term = get_term( $term['term_id'], $taxonomy );
		if ( ! $term ) {
			throw new UserError( __( 'Failed to retrieve term for modification. Please check input.', 'wp-graphql-woocommerce' ) );
		} elseif ( is_wp_error( $term ) ) {
			throw new UserError( $term->get_error_message() );
		}

		if ( isset( $input['menuOrder'] ) ) {
			$success = update_term_meta( $term->term_id, 'order_' . $taxonomy, $input['menuOrder'] );
			if ( is_wp_error( $success ) ) {
				throw new UserError( $success->get_error_message() );
			}
		}

		$menu_order = get_term_meta( $term->term_id, 'order_' . $taxonomy, true );
		$data       = [
			'id'          => $term->term_id,
			'name'        => $term->name,
			'slug'        => $term->slug,
			'description' => $term->description,
			'menu_order'  => (int) $menu_order,
			'count'       => (int) $term->count,
		];

		return [ 'term' => $data ];
	}
}
