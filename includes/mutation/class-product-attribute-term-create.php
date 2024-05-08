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
use WPGraphQL\WooCommerce\Data\Mutation\Product_Mutation;
use WPGraphQL\WooCommerce\Model\Product;

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
				'description' => __( 'The ID of the attribute to which the term belongs.', 'wp-graphql' ),
			],
			'name'        => [
				'type'        => [ 'non_null' => 'String' ],
				'description' => __( 'The name of the term.', 'wp-graphql' ),
			],
			'slug'        => [
				'type'        => 'String',
				'description' => __( 'The slug of the term.', 'wp-graphql' ),
			],
			'description' => [
				'type'        => 'String',
				'description' => __( 'The description of the term.', 'wp-graphql' ),
			],
			'menuOrder'   => [
				'type'        => 'Int',
				'description' => __( 'The order of the term in the menu.', 'wp-graphql' ),
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
			'term'   => [
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
	public static function mutate_and_get_payload( $input, AppContext $context, ResolveInfo $info ) {
		if ( ! $input['attributeId'] ) {
			throw new UserError( __( 'An attributeId is required to create a new product attribute term.', 'wp-graphql' ) );
		}

		$taxonomy = \wc_attribute_taxonomy_name_by_id( $input['attributeId'] );
		$id       = isset( $input['id'] ) ? $input['id'] : null;
		$args     = [];

		if ( ! empty( $input['description'] ) ) {
			$args['description'] = $input['description'];
		}

		if ( ! empty( $input['slug'] ) ) {
			$args['slug'] = $input['slug'];
		}

		if ( $id && ! empty( $input['name'] ) ) {
			$args['name'] = $input['name'];
		}

		if ( $id && ! empty( $args ) ) {
			$term = wp_update_term( $id, $taxonomy, $args );
		} elseif ( $id && isset( $input['menuOrder'] ) ) {
			$term = get_term( $id, $taxonomy );
		} elseif ( ! empty( $input['name'] ) ) {
			$name = $input['name'];
			$term = wp_insert_term( $name, $taxonomy, $args );
		} else {
			$updating = 'updateProductAttributeTerm' === $info->fieldName;
			throw new UserError( 
				$updating
					? __( 'A name is required to create a new product attribute term.', 'wp-graphql' )
					: __( 'A valid term "id" and changeable parameter are required to update a product attribute term.', 'wp-graphql' )
			);
		}

		if ( is_wp_error( $term ) ) {
			throw new UserError( $term->get_error_message() );
		}

		$term = get_term( $term['term_id'], $taxonomy );

		if ( isset( $input['menuOrder'] ) ) {
			update_term_meta( $term->term_id, 'order_' .$taxonomy, $input['menuOrder'] );
		}

		$menu_order = get_term_meta( $term->term_id, 'order_' . $taxonomy, true );
		$data = [
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
