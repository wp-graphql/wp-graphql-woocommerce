<?php
/**
 * ConnectionResolver - Product_Attribute_Connection_Resolver
 *
 * Resolves connections to ProductAttributes
 *
 * @package WPGraphQL\WooCommerce\Data\Connection
 * @since 0.0.1
 */

namespace WPGraphQL\WooCommerce\Data\Connection;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;

/**
 * Class Product_Attribute_Connection_Resolver
 */
class Product_Attribute_Connection_Resolver {
	/**
	 * The source from the field calling the connection.
	 *
	 * @var \WPGraphQL\WooCommerce\Model\Product|\WPGraphQL\Model\Term|null
	 */
	protected $source;

	/**
	 * The args input on the field calling the connection.
	 *
	 * @var ?array<string,mixed>
	 */
	protected $args;

	/**
	 * The AppContext for the GraphQL Request
	 *
	 * @var \WPGraphQL\AppContext
	 */
	protected $context;

	/**
	 * The ResolveInfo for the GraphQL Request
	 *
	 * @var \GraphQL\Type\Definition\ResolveInfo
	 */
	protected $info;

	/**
	 * The attribute type.
	 *
	 * @var string|null
	 */
	protected $type;

	/**
	 * Product_Attribute_Connection_Resolver constructor.
	 *
	 * @param \WPGraphQL\WooCommerce\Model\Product|null $source   Source node.
	 * @param array                                     $args     Connection arguments.
	 * @param \WPGraphQL\AppContext                     $context  AppContext object.
	 * @param \GraphQL\Type\Definition\ResolveInfo      $info     ResolveInfo object.
	 * @param string|null                               $type     Attribute type.
	 */
	public function __construct( $source, array $args, AppContext $context, ResolveInfo $info, $type = null ) {
		$this->source  = $source;
		$this->args    = $args;
		$this->context = $context;
		$this->info    = $info;
		$this->type    = $type;
	}

	/**
	 * Creates connection
	 *
	 * @param mixed                                $source   Connection source Model instance.
	 * @param array                                $args     Connection arguments.
	 * @param \WPGraphQL\AppContext                $context  AppContext object.
	 * @param \GraphQL\Type\Definition\ResolveInfo $info     ResolveInfo object.
	 * @param string|null                          $type     Attribute type.
	 *
	 * @return array|null
	 *
	 * @deprecated TBD
	 */
	public function resolve( $source, array $args, AppContext $context, ResolveInfo $info, $type = null ) {
		_deprecated_function( __METHOD__, 'TBD', static::class . '::get_connection()' );

		$this->source  = $source;
		$this->args    = $args;
		$this->context = $context;
		$this->info    = $info;
		$this->type    = $type;

		return $this->get_connection();
	}

	/**
	 * Builds connection nodes from source product's attributes.
	 *
	 * @throws \GraphQL\Error\UserError If an invalid attribute type is provided in the connection args.
	 * @return array
	 */
	private function build_nodes_from_source_attributes() {
		$items = [];
		if ( ! $this->source instanceof \WPGraphQL\WooCommerce\Model\Product ) {
			return $items;
		}

		$attributes = $this->source->attributes;

		if ( empty( $attributes ) ) {
			return $items;
		}

		foreach ( $attributes as $attribute_name => $data ) {
			$data->_product_id = $this->source->ID;
			$items[]           = $data;
		}

		$attribute_type = ! empty( $this->args['where'] ) && ! empty( $this->args['where']['type'] )
			? $this->args['where']['type']
			: $this->type;

		if ( ! is_null( $attribute_type ) ) {
			switch ( $attribute_type ) {
				case 'local':
					$items = array_filter(
						$items,
						static function ( $item ) {
							return ! $item->is_taxonomy();
						}
					);
					break;
				case 'global':
					$items = array_filter(
						$items,
						static function ( $item ) {
							return $item->is_taxonomy();
						}
					);
					break;
				default:
					throw new UserError( __( 'Invalid product attribute type provided', 'wp-graphql-woocommerce' ) );
			}
		}//end if

		return $items;
	}

	/**
	 * Builds connection nodes from WooCommerce global product attributes.
	 *
	 * @return array
	 */
	private function build_nodes_from_global_attributes() {
		$items                = [];
		$attribute_taxonomies = wc_get_attribute_taxonomies();

		if ( empty( $attribute_taxonomies ) ) {
			return $items;
		}

		foreach ( $attribute_taxonomies as $attribute_taxonomy ) {
			$taxonomy_name = wc_attribute_taxonomy_name( $attribute_taxonomy->attribute_name );
			$terms         = get_terms(
				[
					'taxonomy'   => $taxonomy_name,
					'hide_empty' => false,
				]
			);

			$term_values = [];
			if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
				$term_values = wp_list_pluck( $terms, 'slug' );
			}

			$attribute = new \WC_Product_Attribute();
			$attribute->set_id( (int) $attribute_taxonomy->attribute_id );
			$attribute->set_name( $taxonomy_name );
			$attribute->set_options( $term_values );
			$attribute->set_position( (int) $attribute_taxonomy->attribute_orderby );
			$attribute->set_visible( (bool) $attribute_taxonomy->attribute_public );
			$attribute->set_variation( false );

			$items[] = $attribute;
		}

		return $items;
	}

	/**
	 * Builds connection nodes from product attributes scoped to a product category.
	 *
	 * @return array
	 */
	private function build_nodes_from_category_attributes() {
		$items = [];

		if ( ! $this->source instanceof \WPGraphQL\Model\Term ) {
			return $items;
		}

		$category_id = $this->source->term_id;

		// Get all products in this category.
		$query       = new \WP_Query(
			[
				'post_type'      => 'product',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'tax_query'      => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					[
						'taxonomy' => 'product_cat',
						'field'    => 'term_id',
						'terms'    => $category_id,
					],
				],
			]
		);
		$product_ids = $query->posts;

		if ( empty( $product_ids ) ) {
			return $items;
		}

		// Collect unique attributes across all products in the category.
		$seen_attributes = [];
		foreach ( $product_ids as $product_id ) {
			$product = wc_get_product( $product_id );
			if ( ! $product ) {
				continue;
			}

			$attributes = $product->get_attributes();
			foreach ( $attributes as $attribute_name => $attribute ) {
				if ( isset( $seen_attributes[ $attribute_name ] ) ) {
					continue;
				}

				$seen_attributes[ $attribute_name ] = true;
				$items[]                            = $attribute;
			}
		}

		return $items;
	}

	/**
	 * Builds connection from nodes array.
	 *
	 * @param array $nodes  Array of connection nodes.
	 *
	 * @return array|null
	 */
	private function build_connection( $nodes = [] ) {
		if ( empty( $nodes ) ) {
			return null;
		}

		$connection       = Relay::connectionFromArray( $nodes, $this->args );
		$connection_nodes = [];
		if ( ! empty( $connection['edges'] ) && is_array( $connection['edges'] ) ) {
			foreach ( $connection['edges'] as $edge ) {
				$connection_nodes[] = ! empty( $edge['node'] ) ? $edge['node'] : null;
			}
		}
		$connection['nodes'] = ! empty( $connection_nodes ) ? $connection_nodes : null;

		return $connection;
	}

	/**
	 * Constructs the connection.
	 *
	 * @return array|null
	 */
	public function get_connection() {
		if ( ! $this->source ) {
			$attributes = $this->build_nodes_from_global_attributes();
		} elseif ( $this->source instanceof \WPGraphQL\Model\Term ) {
			$attributes = $this->build_nodes_from_category_attributes();
		} else {
			$attributes = $this->build_nodes_from_source_attributes();
		}

		return $this->build_connection( $attributes );
	}
}
