<?php
/**
 * Adds filters that modify core schema.
 *
 * @package \WPGraphQL\WooCommerce
 * @since   0.0.1
 */

namespace WPGraphQL\WooCommerce;

use GraphQL\Error\UserError;
use WPGraphQL\WooCommerce\Data\Loader\WC_Customer_Loader;
use WPGraphQL\WooCommerce\Data\Loader\WC_CPT_Loader;
use WPGraphQL\WooCommerce\Data\Loader\WC_Db_Loader;
use WPGraphQL\WooCommerce\Data\Factory;
use WPGraphQL\WooCommerce\WP_GraphQL_WooCommerce as WooGraphQL;

/**
 * Class Core_Schema_Filters
 */
class Core_Schema_Filters {
	/**
	 * Register filters
	 *
	 * @return void
	 */
	public static function add_filters() {
		// Registers WooCommerce CPTs.
		add_filter( 'register_post_type_args', [ __CLASS__, 'register_post_types' ], 10, 2 );
		add_filter( 'graphql_post_entities_allowed_post_types', [ __CLASS__, 'skip_type_registry' ], 10 );

		// Registers WooCommerce taxonomies.
		add_filter( 'register_taxonomy_args', [ __CLASS__, 'register_taxonomy_args' ], 10, 2 );

		// Add data-loaders to AppContext.
		add_filter( 'graphql_data_loaders', [ __CLASS__, 'graphql_data_loaders' ], 10, 2 );

		// Add node resolvers.
		add_filter(
			'graphql_resolve_node',
			[ '\WPGraphQL\WooCommerce\Data\Factory', 'resolve_node' ],
			10,
			4
		);
		add_filter(
			'graphql_resolve_node_type',
			[ '\WPGraphQL\WooCommerce\Data\Factory', 'resolve_node_type' ],
			10,
			2
		);

		// Filter Unions.
		add_filter(
			'graphql_wp_union_type_config',
			[ __CLASS__, 'inject_union_types' ],
			10,
			2
		);

		add_filter(
			'graphql_union_resolve_type',
			[ __CLASS__, 'inject_type_resolver' ],
			10,
			2
		);

		add_filter(
			'graphql_interface_resolve_type',
			[ __CLASS__, 'inject_type_resolver' ],
			10,
			2
		);

		add_filter(
			'graphql_dataloader_pre_get_model',
			[ '\WPGraphQL\WooCommerce\Data\Loader\WC_CPT_Loader', 'inject_post_loader_models' ],
			10,
			3
		);

		add_filter(
			'graphql_dataloader_get_model',
			[ '\WPGraphQL\WooCommerce\Data\Loader\WC_Customer_Loader', 'inject_user_loader_models' ],
			10,
			3
		);

		add_filter(
			'graphql_map_input_fields_to_wp_query',
			[ '\WPGraphQL\WooCommerce\Connection\Coupons', 'map_input_fields_to_wp_query' ],
			10,
			7
		);

		add_filter(
			'graphql_map_input_fields_to_wp_query',
			[ '\WPGraphQL\WooCommerce\Connection\Products', 'map_input_fields_to_wp_query' ],
			10,
			7
		);

		add_filter(
			'graphql_map_input_fields_to_wp_user_query',
			[ '\WPGraphQL\WooCommerce\Connection\Customers', 'map_input_fields_to_wp_query' ],
			10,
			6
		);

		add_filter(
			'graphql_connection',
			[ '\WPGraphQL\WooCommerce\Connection\Customers', 'upgrade_models' ],
			10,
			2
		);

		add_filter(
			'graphql_wp_connection_type_config',
			[ '\WPGraphQL\WooCommerce\Connection\Products', 'set_connection_config' ]
		);

		add_filter(
			'woographql_cart_connection_definitions',
			[ __CLASS__, 'skip_cart_item_connection' ],
		);
	}

	/**
	 * Registers WooCommerce post-types to be used in GraphQL schema
	 *
	 * @param array  $args      - allowed post-types.
	 * @param string $post_type - name of taxonomy being checked.
	 *
	 * @return array
	 */
	public static function register_post_types( $args, $post_type ) {
		if ( 'product' === $post_type ) {
			$args['show_in_graphql']                  = true;
			$args['graphql_single_name']              = 'Product';
			$args['graphql_plural_name']              = 'Products';
			$args['graphql_kind']                     = 'interface';
			$args['graphql_interfaces']               = [ 'ContentNode' ];
			$args['graphql_register_root_field']      = false;
			$args['graphql_register_root_connection'] = false;
			$args['graphql_resolve_type']             = static function( $value ) {
				$type_registry  = \WPGraphQL::get_type_registry();
				$possible_types = WooGraphQL::get_enabled_product_types();
				if ( isset( $possible_types[ $value->type ] ) ) {
					return $type_registry->get_type( $possible_types[ $value->type ] );
				} elseif ( 'on' === woographql_setting( 'enable_unsupported_product_type', 'off' ) ) {
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
			};
		}//end if
		if ( 'product_variation' === $post_type ) {
			$args['show_in_graphql']            = true;
			$args['graphql_single_name']        = 'ProductVariation';
			$args['graphql_plural_name']        = 'ProductVariations';
			$args['publicly_queryable']         = true;
			$args['skip_graphql_type_registry'] = true;
		}
		if ( 'shop_coupon' === $post_type ) {
			$args['show_in_graphql']            = true;
			$args['graphql_single_name']        = 'Coupon';
			$args['graphql_plural_name']        = 'Coupons';
			$args['publicly_queryable']         = true;
			$args['skip_graphql_type_registry'] = true;
		}
		if ( 'shop_order' === $post_type ) {
			$args['show_in_graphql']            = true;
			$args['graphql_single_name']        = 'Order';
			$args['graphql_plural_name']        = 'Orders';
			$args['skip_graphql_type_registry'] = true;
		}
		if ( 'shop_order_refund' === $post_type ) {
			$args['show_in_graphql']            = true;
			$args['graphql_single_name']        = 'Refund';
			$args['graphql_plural_name']        = 'Refunds';
			$args['skip_graphql_type_registry'] = true;
		}

		return $args;
	}

	/**
	 * Filters "allowed_post_types" and removed Woocommerce CPTs.
	 *
	 * @param array $post_types  Post types registered in GraphQL schema.
	 *
	 * @return array
	 */
	public static function skip_type_registry( $post_types ) {
		return array_diff(
			$post_types,
			get_post_types(
				[
					'show_in_graphql'            => true,
					'skip_graphql_type_registry' => true,
				]
			)
		);
	}

	/**
	 * Registers WooCommerce taxonomies to be used in GraphQL schema
	 *
	 * @param array  $args     - allowed taxonomies.
	 * @param string $taxonomy - name of taxonomy being checked.
	 *
	 * @return array
	 */
	public static function register_taxonomy_args( $args, $taxonomy ) {
		if ( 'product_type' === $taxonomy ) {
			$args['show_in_graphql']     = true;
			$args['graphql_single_name'] = 'productType';
			$args['graphql_plural_name'] = 'productTypes';
		}

		if ( 'product_visibility' === $taxonomy ) {
			$args['show_in_graphql']     = true;
			$args['graphql_single_name'] = 'visibleProduct';
			$args['graphql_plural_name'] = 'visibleProducts';
		}

		if ( 'product_cat' === $taxonomy ) {
			$args['show_in_graphql']     = true;
			$args['graphql_single_name'] = 'productCategory';
			$args['graphql_plural_name'] = 'productCategories';
		}

		if ( 'product_tag' === $taxonomy ) {
			$args['show_in_graphql']     = true;
			$args['graphql_single_name'] = 'productTag';
			$args['graphql_plural_name'] = 'productTags';
		}

		if ( 'product_shipping_class' === $taxonomy ) {
			$args['show_in_graphql']     = true;
			$args['graphql_single_name'] = 'shippingClass';
			$args['graphql_plural_name'] = 'shippingClasses';
		}

		// Filter product attributes taxonomies.
		$attributes = WooGraphQL::get_product_attribute_taxonomies();
		if ( in_array( $taxonomy, $attributes, true ) ) {
			$singular_name               = graphql_format_field_name( $taxonomy );
			$args['show_in_graphql']     = true;
			$args['graphql_single_name'] = $singular_name;
			$args['graphql_plural_name'] = 'all' . ucFirst( $singular_name );
		}

		return $args;
	}

	/**
	 * Registers data-loaders to be used when resolving WooCommerce-related GraphQL types
	 *
	 * @param array                 $loaders - assigned loaders.
	 * @param \WPGraphQL\AppContext $context - AppContext instance.
	 *
	 * @return array
	 */
	public static function graphql_data_loaders( $loaders, $context ) {
		// WooCommerce customer loader.
		$customer_loader        = new WC_Customer_Loader( $context );
		$loaders['wc_customer'] = &$customer_loader;

		// WooCommerce CPT loader.
		$cpt_loader         = new WC_CPT_Loader( $context );
		$loaders['wc_post'] = &$cpt_loader;

		// WooCommerce DB loaders.
		$cart_item_loader             = new WC_Db_Loader( $context, 'CART_ITEM' );
		$loaders['cart_item']         = &$cart_item_loader;
		$downloadable_item_loader     = new WC_Db_Loader( $context, 'DOWNLOADABLE_ITEM' );
		$loaders['downloadable_item'] = &$downloadable_item_loader;
		$tax_rate_loader              = new WC_Db_Loader( $context, 'TAX_RATE' );
		$loaders['tax_rate']          = &$tax_rate_loader;
		$order_item_loader            = new WC_Db_Loader( $context, 'ORDER_ITEM' );
		$loaders['order_item']        = &$order_item_loader;
		$shipping_item_loader         = new WC_Db_Loader( $context, 'SHIPPING_METHOD' );
		$loaders['shipping_method']   = &$shipping_item_loader;
		return $loaders;
	}

	/**
	 * Inject Union types that resolve to Product with Product types
	 *
	 * @param array                       $config    WPUnion config.
	 * @param \WPGraphQL\Type\WPUnionType $wp_union  WPUnion object.
	 *
	 * @return array
	 */
	public static function inject_union_types( $config, $wp_union ) {
		$refresh_callback = false;
		if ( in_array( 'Product', $config['typeNames'], true ) ) {
			// Strip 'Product' from config and child product types.
			$config['typeNames'] = array_merge(
				array_filter(
					$config['typeNames'],
					function( $type ) {
						return 'Product' !== $type;
					}
				),
				array_values( WooGraphQL::get_enabled_product_types() ),
				[ WooGraphQL::get_supported_product_type() ]
			);
			$refresh_callback    = true;
		}

		// Update 'types' callback.
		if ( $refresh_callback ) {
			$config['types'] = function () use ( $config, $wp_union ) {
				$prepared_types = [];
				foreach ( $config['typeNames'] as $type_name ) {
					$prepared_types[] = $wp_union->type_registry->get_type( $type_name );
				}
				return $prepared_types;
			};
		}

		return $config;
	}

	/**
	 * Inject Union type resolver that resolve to Product with Product types
	 *
	 * @param \WPGraphQL\Type\WPObjectType $type      Type be resolve to.
	 * @param mixed                        $value     Object for which the type is being resolve config.
	 * @param \WPGraphQL\Type\WPUnionType  $wp_union  WPUnion object.
	 *
	 * @return \WPGraphQL\Type\WPObjectType
	 */
	public static function inject_union_type_resolver( $type, $value, $wp_union ) {
		switch ( get_class( $value ) ) {
			case 'WPGraphQL\WooCommerce\Model\Product':
			case 'WPGraphQL\WooCommerce\Model\Coupon':
			case 'WPGraphQL\WooCommerce\Model\Order':
				$new_type = Factory::resolve_node_type( $type, $value );
				if ( $new_type ) {
					$type = $wp_union->type_registry->get_type( $new_type );
				}
				break;
		}

		return $type;
	}

	/**
	 * Inject Union type resolver that resolve to Product with Product types
	 *
	 * @param \WPGraphQL\Type\WPObjectType|null $type   Type be resolve to.
	 * @param mixed                             $value  Object for which the type is being resolve config.
	 *
	 * @throws UserError Invalid product type received.
	 *
	 * @return \WPGraphQL\Type\WPObjectType|null
	 */
	public static function inject_type_resolver( $type, $value ) {
		$type_registry = \WPGraphQL::get_type_registry();
		switch ( $type ) {
			case 'Coupon':
			case 'Order':
				$new_type = Factory::resolve_node_type( $type, $value );
				if ( $new_type ) {
					$type = $type_registry->get_type( $new_type );
				}
				break;
			case 'Product':
				$supported_types = WooGraphQL::get_enabled_product_types();
				if ( in_array( $value->type, array_keys( $supported_types ), true ) ) {
					$type_name = $supported_types[ $value->type ];
					$type      = $type_registry->get_type( $type_name );
				} elseif ( 'on' === woographql_setting( 'enable_unsupported_product_type', 'off' ) ) {
					$type_name = WooGraphQL::get_supported_product_type();
					$type      = $type_registry->get_type( $type_name );
				} else {
					throw new UserError(
						sprintf(
						/* translators: %s: Product type */
							__( 'The "%s" product type is not supported by the core WPGraphQL WooCommerce (WooGraphQL) schema.', 'wp-graphql-woocommerce' ),
							$value->type
						)
					);
				}
		}//end switch

		return $type;
	}

	/**
	 * Return true if WooGraphQL Pro is handling cart item connections.
	 *
	 * @return boolean
	 */
	private static function should_skip_cart_item_connection() {
		if ( ! class_exists( 'WPGraphQL\WooCommerce\Pro\WooGraphQL_Pro' ) ) {
			return false;
		}

		return Pro\WooGraphQL_Pro::is_composite_products_enabled()
			&& Pro\WooGraphQL_Pro::is_composite_products_active();
	}

	/**
	 * Skip core cart item connection definitions if WooGraphQL Pro is handling it.
	 *
	 * @param array $connections  Cart connection defintions.
	 * @return array
	 */
	public static function skip_cart_item_connection( $connections ) {
		if ( self::should_skip_cart_item_connection() ) {
			unset( $connections['contents'] );
		}

		return $connections;
	}
}
