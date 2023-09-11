<?php
/**
 * Defines the union between product types and product variation types.
 */
namespace WPGraphQL\WooCommerce\Type\WPInterface;

use WPGraphQL;
use WPGraphQL\WooCommerce\WP_GraphQL_WooCommerce as WooGraphQL;

/**
 * Class Product_Union
 */
class Product_Union {

	/**
	 * Registers the Type
	 *
	 * @return void
	 * @throws \Exception
	 */
	public static function register_interface(): void {
		register_graphql_interface_type(
			'ProductUnion',
			[
                'description' => __( 'Union between the product and product variation types', 'wp-graphql-woocommerce' ),
                'interfaces'  => [ 'Node' ],
				'fields'      => self::get_fields(),
				'resolveType' => static function ( $value ) {
                    $type_registry  = WPGraphQL::get_type_registry();
                    $possible_types = WooGraphQL::get_enabled_product_types();
                    $product_type   = $value->get_type();
                    if ( isset( $possible_types[ $product_type ] ) ) {
                        return $type_registry->get_type( $possible_types[ $product_type ] );
                    } elseif ( str_ends_with( $product_type, 'variation' ) ) {
                        return $type_registry->get_type( 'ProductVariation' );
                    }
                    elseif ( 'on' === woographql_setting( 'enable_unsupported_product_type', 'off' ) ) {
                        $unsupported_type = WooGraphQL::get_supported_product_type();
                        return $type_registry->get_type( $unsupported_type );
                    }

                    throw new UserError(
                        sprintf(
                        /* translators: %s: Product type */
                            __( 'The "%s" product type is not supported by the core WPGraphQL WooCommerce (WooGraphQL) schema.', 'wp-graphql-woocommerce' ),
                            $value->type
                        )
                    );
				},
			]
		);
	}

    /**
     * Defines ProductUnion fields. All child type must have these fields as well.
     *
     * @return array
     */
    public static function get_fields() {
        return array_merge(
            [
                'id'         => [
                    'type'        => [ 'non_null' => 'ID' ],
                    'description' => __( 'Product or variation global ID', 'wp-graphql-woocommerce' ),
                ],
                'databaseId' => [
                    'type'        => [ 'non_null' => 'Int' ],
                    'description' => __( 'Product or variation ID', 'wp-graphql-woocommerce' ),
                ],
            ],
            Product::get_fields(),
        );
    }
}
