<?php
/**
 * Adds filters that modify core schema.
 *
 * @package \WPGraphQL\WooCommerce
 * @since   0.0.1
 */

namespace WPGraphQL\WooCommerce;

use WPGraphQL\WooCommerce\Data\Loader\WC_Customer_Loader;
use WPGraphQL\WooCommerce\Data\Loader\WC_CPT_Loader;
use WPGraphQL\WooCommerce\Data\Loader\WC_Db_Loader;
use WPGraphQL\WooCommerce\Data\Factory;

/**
 * Class Core_Schema_Filters
 */
class Core_Schema_Filters {
	/**
	 * Register filters
	 */
	public static function add_filters() {
		// Registers WooCommerce CPTs.
		add_filter( 'register_post_type_args', array( __CLASS__, 'register_post_types' ), 10, 2 );
		add_filter( 'graphql_post_entities_allowed_post_types', array( __CLASS__, 'skip_type_registry' ), 10 );

		// Registers WooCommerce taxonomies.
		add_filter( 'register_taxonomy_args', array( __CLASS__, 'register_taxonomy_args' ), 10, 2 );

		// Add data-loaders to AppContext.
		add_filter( 'graphql_data_loaders', array( __CLASS__, 'graphql_data_loaders' ), 10, 2 );

		// Add node resolvers.
		add_filter(
			'graphql_resolve_node',
			array( '\WPGraphQL\WooCommerce\Data\Factory', 'resolve_node' ),
			10,
			4
		);
		add_filter(
			'graphql_resolve_node_type',
			array( '\WPGraphQL\WooCommerce\Data\Factory', 'resolve_node_type' ),
			10,
			2
		);

		// Filter Unions.
		add_filter(
			'graphql_wp_union_type_config',
			array( __CLASS__, 'inject_union_types' ),
			10,
			2
		);

		add_filter(
			'graphql_union_resolve_type',
			array( __CLASS__, 'inject_type_resolver' ),
			10,
			3
		);

		add_filter(
			'graphql_interface_resolve_type',
			array( __CLASS__, 'inject_type_resolver' ),
			10,
			3
		);

		add_filter(
			'graphql_dataloader_pre_get_model',
			array( '\WPGraphQL\WooCommerce\Data\Loader\WC_CPT_Loader', 'inject_post_loader_models' ),
			10,
			3
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
			$args['show_in_graphql']            = true;
			$args['graphql_single_name']        = 'Product';
			$args['graphql_plural_name']        = 'Products';
			$args['skip_graphql_type_registry'] = true;
		}
		if ( 'product_variation' === $post_type ) {
			$args['show_in_graphql']            = true;
			$args['graphql_single_name']        = 'ProductVariation';
			$args['graphql_plural_name']        = 'ProductVariations';
			$args['skip_graphql_type_registry'] = true;
		}
		if ( 'shop_coupon' === $post_type ) {
			$args['show_in_graphql']            = true;
			$args['graphql_single_name']        = 'Coupon';
			$args['graphql_plural_name']        = 'Coupons';
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
				array(
					'show_in_graphql'            => true,
					'skip_graphql_type_registry' => true,
				)
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
		$attributes = \WP_GraphQL_WooCommerce::get_product_attribute_taxonomies();
		if ( in_array( $taxonomy, $attributes, true ) ) {
			$singular_name               = graphql_format_field_name( $taxonomy );
			$args['show_in_graphql']     = true;
			$args['graphql_single_name'] = $singular_name;
			$args['graphql_plural_name'] = \Inflect::pluralize( $singular_name );
		}

		return $args;
	}

	/**
	 * Registers data-loaders to be used when resolving WooCommerce-related GraphQL types
	 *
	 * @param array      $loaders - assigned loaders.
	 * @param AppContext $context - AppContext instance.
	 *
	 * @return array
	 */
	public static function graphql_data_loaders( $loaders, $context ) {
		// WooCommerce customer loader.
		$customer_loader        = new WC_Customer_Loader( $context );
		$loaders['wc_customer'] = &$customer_loader;

		// WooCommerce CPT loader.
		$cpt_loader        = new WC_CPT_Loader( $context );
		$loaders['wc_cpt'] = &$cpt_loader;

		// WooCommerce DB loaders.
		$cart_item_loader             = new WC_Db_Loader( $context, 'CART_ITEM' );
		$loaders['cart_item']         = &$cart_item_loader;
		$downloadable_item_loader     = new WC_Db_Loader( $context, 'DOWNLOADABLE_ITEM' );
		$loaders['downloadable_item'] = &$downloadable_item_loader;
		$tax_rate_loader              = new WC_Db_Loader( $context, 'TAX_RATE' );
		$loaders['tax_rate']          = &$tax_rate_loader;

		return $loaders;
	}

	/**
	 * Inject Union types that resolve to Product with Product types
	 *
	 * @param array                       $config    WPUnion config.
	 * @param \WPGraphQL\Type\WPUnionType $wp_union  WPUnion object.
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
				array_values( \WP_GraphQL_WooCommerce::get_enabled_product_types() )
			);
			$refresh_callback    = true;
		}

		// Update 'types' callback.
		if ( $refresh_callback ) {
			$config['types'] = function () use ( $config, $wp_union ) {
				$prepared_types = array();
				if ( ! empty( $config['typeNames'] ) && is_array( $config['typeNames'] ) ) {
					$prepared_types = array();
					foreach ( $config['typeNames'] as $type_name ) {
						$prepared_types[] = $wp_union->type_registry->get_type( $type_name );
					}
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
	 * @param \WPGraphQL\Type\WPObjectType $type           Type be resolve to.
	 * @param mixed                        $value          Object for which the type is being resolve config.
	 * @param WPUnionType|WPInterfaceType  $abstract_type  WPGraphQL abstract class object.
	 */
	public static function inject_type_resolver( $type, $value, $abstract_type ) {
		switch ( $type ) {
			case 'Product':
			case 'Coupon':
			case 'Order':
				$new_type = Factory::resolve_node_type( $type, $value );
				if ( $new_type ) {
					$type = $abstract_type->type_registry->get_type( $new_type );
				}
				break;
		}

		return $type;
	}
}
