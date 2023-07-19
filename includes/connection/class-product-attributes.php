<?php
/**
 * Connection type - ProductAttributes
 *
 * Registers connections to ProductAttribute
 *
 * @package WPGraphQL\WooCommerce\Connection
 * @since 0.0.1
 */

namespace WPGraphQL\WooCommerce\Connection;

use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Data\Connection\Product_Attribute_Connection_Resolver;

/**
 * Class Product_Attributes
 */
class Product_Attributes {
	/**
	 * Registers the various connections from other Types to ProductAttribute.
	 *
	 * @return void
	 */
	public static function register_connections() {
		// From Product to ProductAttribute.
		register_graphql_connection(
			self::get_connection_config()
		);

		// From Product to LocalProductAttribute.
		register_graphql_connection(
			self::get_connection_config(
				[
					'toType'         => 'LocalProductAttribute',
					'fromFieldName'  => 'localAttributes',
					'connectionArgs' => [],
				]
			)
		);

		// From Product to GlobalProductAttribute.
		register_graphql_connection(
			self::get_connection_config(
				[
					'toType'         => 'GlobalProductAttribute',
					'fromFieldName'  => 'globalAttributes',
					'connectionArgs' => [],
				]
			)
		);
	}

	/**
	 * Given an array of $args, this returns the connection config, merging the provided args
	 * with the defaults.
	 *
	 * @param array $args - Connection configuration.
	 * @return array
	 */
	public static function get_connection_config( $args = [] ): array {
		return array_merge(
			[
				'fromType'       => 'Product',
				'toType'         => 'ProductAttribute',
				'fromFieldName'  => 'attributes',
				'connectionArgs' => self::get_connection_args(),
				'resolve'        => static function ( $source, array $args, AppContext $context, ResolveInfo $info ) {
					$resolver = new Product_Attribute_Connection_Resolver();
					// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
					switch ( $info->fieldName ) {
						case 'globalAttributes':
							return $resolver->resolve( $source, $args, $context, $info, 'global' );
						case 'localAttributes':
							return $resolver->resolve( $source, $args, $context, $info, 'local' );
						default:
							return $resolver->resolve( $source, $args, $context, $info );
					}
				},
			],
			$args
		);
	}

	/**
	 * Returns array of where args.
	 *
	 * @return array
	 */
	public static function get_connection_args(): array {
		return [
			'type' => [
				'type'        => 'ProductAttributeTypesEnum',
				'description' => __( 'Filter results by attribute scope.', 'wp-graphql-woocommerce' ),
			],
		];
	}
}
