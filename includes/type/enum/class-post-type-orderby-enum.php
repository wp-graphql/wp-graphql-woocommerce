<?php
/**
 * WPEnum type - Post_Type_Orderby_Enum
 * Defines common post-type ordering fields
 *
 * @package WPGraphQL\WooCommerce\Type\WPEnum
 * @since   0.2.2
 */

namespace WPGraphQL\WooCommerce\Type\WPEnum;

/**
 * Class Post_Type_Orderby_Enum
 */
class Post_Type_Orderby_Enum {
	/**
	 * Holds ordering enumeration base name.
	 *
	 * @var string
	 */
	protected static $name = 'PostType';

	/**
	 * Defines enumeration value definitions for common post-type ordering fields
	 *
	 * @return array
	 */
	protected static function post_type_values() {
		return [
			'NAME'       => [
				'value'       => 'post_title',
				'description' => __( 'Order by name', 'wp-graphql-woocommerce' ),
			],
			'SLUG'       => [
				'value'       => 'post_name',
				'description' => __( 'Order by slug', 'wp-graphql-woocommerce' ),
			],
			'MODIFIED'   => [
				'value'       => 'post_modified',
				'description' => __( 'Order by last modified date', 'wp-graphql-woocommerce' ),
			],
			'DATE'       => [
				'value'       => 'post_date',
				'description' => __( 'Order by publish date', 'wp-graphql-woocommerce' ),
			],
			'PARENT'     => [
				'value'       => 'post_parent',
				'description' => __( 'Order by parent ID', 'wp-graphql-woocommerce' ),
			],
			'IN'         => [
				'value'       => 'post__in',
				'description' => __( 'Preserve the ID order given in the IN array', 'wp-graphql-woocommerce' ),
			],
			'NAME_IN'    => [
				'value'       => 'post_name__in',
				'description' => __( 'Preserve slug order given in the NAME_IN array', 'wp-graphql-woocommerce' ),
			],
			'MENU_ORDER' => [
				'value'       => 'menu_order',
				'description' => __( 'Order by the menu order value', 'wp-graphql-woocommerce' ),
			],
		];
	}

	/**
	 * Return enumeration values.
	 *
	 * @return array
	 */
	protected static function values() {
		return self::post_type_values();
	}

	/**
	 * Registers type
	 *
	 * @return void
	 */
	public static function register() {
		$name = static::$name;
		register_graphql_enum_type(
			$name . 'OrderByEnum',
			[
				'description' => sprintf(
					/* translators: ordering enumeration description */
					__( 'Fields to order the %s connection by', 'wp-graphql-woocommerce' ),
					$name
				),
				// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
				'values'      => apply_filters( "{$name}_orderby_enum_values", static::values() ),
			]
		);
	}
}
