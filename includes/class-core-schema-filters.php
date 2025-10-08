<?php
/**
 * Adds filters that modify core schema.
 *
 * @package \WPGraphQL\WooCommerce
 * @since   0.0.1
 */

namespace WPGraphQL\WooCommerce;

use GraphQL\Error\UserError;
use WPGraphQL\WooCommerce\Data\Factory;
use WPGraphQL\WooCommerce\Data\Loader\WC_CPT_Loader;
use WPGraphQL\WooCommerce\Data\Loader\WC_Customer_Loader;
use WPGraphQL\WooCommerce\Data\Loader\WC_Cart_Item_Loader;
use WPGraphQL\WooCommerce\Data\Loader\WC_Downloadable_Item_Loader;
use WPGraphQL\WooCommerce\Data\Loader\WC_Order_Item_Loader;
use WPGraphQL\WooCommerce\Data\Loader\WC_Shipping_Method_Loader;
use WPGraphQL\WooCommerce\Data\Loader\WC_Shipping_Zone_Loader;
use WPGraphQL\WooCommerce\Data\Loader\WC_Tax_Class_Loader;
use WPGraphQL\WooCommerce\Data\Loader\WC_Tax_Rate_Loader;
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
		add_filter( 'register_post_type_args', [ self::class, 'register_post_types' ], 10, 2 );
		add_filter( 'graphql_post_entities_allowed_post_types', [ self::class, 'skip_type_registry' ], 10 );

		// Registers WooCommerce taxonomies.
		add_filter( 'register_taxonomy_args', [ self::class, 'register_taxonomy_args' ], 10, 2 );

		// Add data-loaders to AppContext.
		add_filter( 'graphql_data_loader_classes', [ self::class, 'graphql_data_loader_classes' ], 10, 2 );

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
			[ self::class, 'inject_union_types' ],
			10,
			2
		);

		add_filter(
			'graphql_union_resolve_type',
			[ self::class, 'inject_type_resolver' ],
			10,
			2
		);

		add_filter(
			'graphql_interface_resolve_type',
			[ self::class, 'inject_type_resolver' ],
			10,
			2
		);

		add_filter(
			'graphql_dataloader_pre_get_model',
			[ '\WPGraphQL\WooCommerce\Data\Loader\WC_CPT_Loader', 'inject_post_loader_models' ],
			10,
			3
		);

		// Filter to allow order notes to be visible in GraphQL queries.
		add_filter(
			'graphql_data_is_private',
			[ self::class, 'make_order_notes_visible' ],
			10,
			3
		);

		// Filter to set order notes visibility to public for authorized users.
		add_filter(
			'graphql_object_visibility',
			[ self::class, 'set_order_notes_visibility' ],
			10,
			5
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
			$args['model']                            = \WPGraphQL\WooCommerce\Model\Product::class;
			$args['graphql_single_name']              = 'Product';
			$args['graphql_plural_name']              = 'Products';
			$args['graphql_kind']                     = 'interface';
			$args['graphql_interfaces']               = [ 'ContentNode', 'ProductUnion' ];
			$args['graphql_register_root_field']      = false;
			$args['graphql_register_root_connection'] = false;
			$args['graphql_resolve_type']             = [ self::class, 'resolve_product_type' ];
		}
		if ( 'product_variation' === $post_type ) {
			$args['show_in_graphql']                  = true;
			$args['model']                            = \WPGraphQL\WooCommerce\Model\Product_Variation::class;
			$args['graphql_single_name']              = 'ProductVariation';
			$args['graphql_plural_name']              = 'ProductVariations';
			$args['publicly_queryable']               = true;
			$args['graphql_kind']                     = 'interface';
			$args['graphql_interfaces']               = [
				'Node',
				'NodeWithFeaturedImage',
				'ContentNode',
				'ProductUnion',
				'UniformResourceIdentifiable',
				'ProductWithPricing',
				'ProductWithDimensions',
				'InventoriedProduct',
				'DownloadableProduct',
			];
			$args['graphql_register_root_field']      = false;
			$args['graphql_register_root_connection'] = false;
			$args['graphql_resolve_type']             = [ self::class, 'resolve_product_variation_type' ];
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
	public static function graphql_data_loader_classes( $loaders ) {
		// WooCommerce customer loader.
		$loaders['wc_customer'] = WC_Customer_Loader::class;

		// WooCommerce CPT loader.
		$loaders['wc_post'] = WC_CPT_Loader::class;

		// WooCommerce DB loaders.
		$loaders['cart_item']         = WC_Cart_Item_Loader::class;
		$loaders['downloadable_item'] = WC_Downloadable_Item_Loader::class;
		$loaders['tax_class']         = WC_Tax_Class_Loader::class;
		$loaders['tax_rate']          = WC_Tax_Rate_Loader::class;
		$loaders['order_item']        = WC_Order_Item_Loader::class;
		$loaders['shipping_method']   = WC_Shipping_Method_Loader::class;
		$loaders['shipping_zone']     = WC_Shipping_Zone_Loader::class;
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
					static function ( $type ) {
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
			$config['types'] = static function () use ( $config, $wp_union ) {
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
	 * @throws \GraphQL\Error\UserError Invalid product type received.
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
			case 'ProductVariation':
				$type = self::resolve_product_variation_type( $value );
				break;
			case 'Product':
				$type = self::resolve_product_type( $value );
		}//end switch

		return $type;
	}

	/**
	 * Resolves GraphQL type for provided product model.
	 *
	 * @param \WPGraphQL\WooCommerce\Model\Product|\WPGraphQL\WooCommerce\Model\Product_Variation $value  Product model.
	 *
	 * @throws \GraphQL\Error\UserError Invalid product type requested.
	 *
	 * @return mixed
	 */
	public static function resolve_product_type( $value ) {
		$type_registry  = \WPGraphQL::get_type_registry();
		$possible_types = WooGraphQL::get_enabled_product_types();
		$product_type   = $value->get_type();
		if ( isset( $possible_types[ $product_type ] ) ) {
			return $type_registry->get_type( $possible_types[ $product_type ] );
		} elseif ( $value instanceof \WPGraphQL\WooCommerce\Model\Product_Variation ) {
			return self::resolve_product_variation_type( $value );
		} elseif ( 'on' === woographql_setting( 'enable_unsupported_product_type', 'off' ) ) {
			$unsupported_type = WooGraphQL::get_supported_product_type();
			return $type_registry->get_type( $unsupported_type );
		}

		throw new UserError(
			sprintf(
			/* translators: %s: Product type */
				__( 'The "%s" product type is not supported by the core WPGraphQL for WooCommerce (WooGraphQL) schema.', 'wp-graphql-woocommerce' ),
				$value->type
			)
		);
	}

	/**
	 * Resolves GraphQL type for provided product variation model.
	 *
	 * @param \WPGraphQL\WooCommerce\Model\Product_Variation $value  Product model.
	 *
	 * @throws \GraphQL\Error\UserError Invalid product type requested.
	 *
	 * @return mixed
	 */
	public static function resolve_product_variation_type( $value ) {
		$type_registry  = \WPGraphQL::get_type_registry();
		$possible_types = WooGraphQL::get_enabled_product_variation_types();
		$product_type   = $value->get_type();

		if ( isset( $possible_types[ $product_type ] ) ) {
			return $type_registry->get_type( $possible_types[ $product_type ] );
		}

		throw new UserError(
			sprintf(
			/* translators: %s: Product type */
				__( 'The "%s" product variation type is not supported by the core WPGraphQL for WooCommerce (WooGraphQL) schema.', 'wp-graphql-woocommerce' ),
				$value->type
			)
		);
	}

	/**
	 * Filter to make order notes visible in GraphQL queries for authorized users.
	 *
	 * @param bool   $is_private Whether the data is private.
	 * @param string $model_name The name of the model being checked.
	 * @param mixed  $data       The data being checked.
	 *
	 * @return bool
	 */
	public static function make_order_notes_visible( $is_private, $model_name, $data ) {
		// Only apply to Comment models.
		if ( 'CommentObject' !== $model_name ) {
			return $is_private;
		}

		// Check if this is an order note.
		if ( $data instanceof \WP_Comment && 'order_note' === $data->comment_type ) {
			// Get the parent order.
			$order_id = absint( $data->comment_post_ID );
			$order = wc_get_order( $order_id );
			
			if ( ! $order ) {
				return true; // Keep it private if order not found.
			}

			// Allow shop managers and admins to see all order notes.
			if ( current_user_can( 'edit_shop_orders' ) ) {
				return false; // Not private.
			}

			// Allow customers to see customer notes on their own orders.
			$is_customer_note = get_comment_meta( $data->comment_ID, 'is_customer_note', true );
			if ( $is_customer_note && get_current_user_id() === $order->get_customer_id() ) {
				return false; // Not private.
			}

			// Otherwise keep it private.
			return true;
		}

		return $is_private;
	}

	/**
	 * Filter to set order notes visibility to public for authorized users.
	 *
	 * @param string     $visibility   The visibility of the object.
	 * @param string     $model_name   The name of the model being checked.
	 * @param mixed      $data         The data being checked.
	 * @param int|null   $owner        The owner of the object.
	 * @param \WP_User   $current_user The current user.
	 *
	 * @return string
	 */
	public static function set_order_notes_visibility( $visibility, $model_name, $data, $owner, $current_user ) {
		// Only apply to Comment models.
		if ( 'CommentObject' !== $model_name ) {
			return $visibility;
		}

		// Check if this is an order note and if user owns the order.
		if ( $data instanceof \WP_Comment && 'order_note' === $data->comment_type ) {
			$order = wc_get_order( $data->comment_post_ID );
			
			// If user is the order owner, make it public.
			if ( $order && get_current_user_id() === $order->get_customer_id() ) {
				return 'public';
			}
		}

		return $visibility;
	}
}
