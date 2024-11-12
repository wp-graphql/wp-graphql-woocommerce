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

const GLOBAL_ID_DELIMITER = ':';

/**
 * Class Product_Attribute_Connection_Resolver
 */
class Product_Attribute_Connection_Resolver {

	/**
	 * The source from the field calling the connection.
	 *
	 * @var \WPGraphQL\WooCommerce\Model\Product|null
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
	 * @var string
	 */
	protected $type;

	/**
	 * Product_Attribute_Connection_Resolver constructor.
	 *
	 * @param \WPGraphQL\WooCommerce\Model\Product|null $source   Source node.
	 * @param ?array                                     $args     Connection arguments.
	 * @param \WPGraphQL\AppContext                     $context  AppContext object.
	 * @param \GraphQL\Type\Definition\ResolveInfo      $info     ResolveInfo object.
	 * @param string                                    $type     Attribute type.
	 */
	public function __construct( $source, array $args, AppContext $context, ResolveInfo $info, string $type = null ) {
		$this->source  = $source;
		$this->args    = $args;
		$this->context = $context;
		$this->info    = $info;
		$this->type    = $type;
	}

	/**
	 * Builds Product attribute items
	 *
	 * @param array                                $attributes  Array of WC_Product_Attributes instances.
	 * @param \WPGraphQL\WooCommerce\Model\Product $source      Parent product model.
	 * @param array                                $args        Connection arguments.
	 * @param \WPGraphQL\AppContext                $context     AppContext object.
	 * @param \GraphQL\Type\Definition\ResolveInfo $info        ResolveInfo object.
	 * @param string                               $type     Attribute type.
	 *
	 * @throws \GraphQL\Error\UserError  Invalid product attribute enumeration value.
	 * @return array
	 * 
	 * @deprecated TBD
	 */
	private function get_items( $attributes, $source, $args, $context, $info, $type = null ) {
		_deprecated_function( __METHOD__, 'TBD', static::class . '::build_nodes_from_product_attributes()' );

		$this->source  = $source;
		$this->args    = $args;
		$this->context = $context;
		$this->info    = $info;
		$this->type    = $type;

		return $this->build_nodes_from_source_attributes();
	}

	/**
	 * Creates connection
	 *
	 * @param mixed                                $source   Connection source Model instance.
	 * @param array                                $args     Connection arguments.
	 * @param \WPGraphQL\AppContext                $context  AppContext object.
	 * @param \GraphQL\Type\Definition\ResolveInfo $info     ResolveInfo object.
	 * @param string                               $type     Attribute type.
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
	 * @return array
	 */
	private function build_nodes_from_source_attributes() {
		$items = [];
		if ( ! $this->source ) {
			return $items;
		}

		$attributes = $this->source->attributes;

		
		if ( empty( $attributes ) ) {
			return $items;
		}

		foreach ( $attributes as $attribute_name => $data ) {
			// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
			$data->_relay_id = base64_encode(
				$attribute_name
				. GLOBAL_ID_DELIMITER
				. $this->source->ID
				. GLOBAL_ID_DELIMITER
				. $data->get_name()
			);
			$items[]         = $data;
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
	 * Builds connection nodes from woocommerce global product attributes.
	 *
	 * @return array
	 */
	private function build_nodes_from_global_attributes() {
		// TODO: Implement this method.
		return [];
	}

	/**
	 * Builds connection from nodes array.
	 * 
	 * @param array $nodes  Array of connection nodes.
	 *
	 * @return array|null
	 */
	private function build_connection( $nodes = [] ) {
		$connection = $this->build_connection( $nodes );
		$connection = Relay::connectionFromArray( $nodes, $this->args );
		$nodes      = [];
		if ( ! empty( $connection['edges'] ) && is_array( $connection['edges'] ) ) {
			foreach ( $connection['edges'] as $edge ) {
				$nodes[] = ! empty( $edge['node'] ) ? $edge['node'] : null;
			}
		}
		$connection['nodes'] = ! empty( $nodes ) ? $nodes : null;
		return ! empty( $attributes ) ? $connection : null;
	}

	/**
	 * Constructs the connection.
	 * 
	 * @return array|null
	 */
	public function get_connection() {
		if ( ! $this->source ) {
			$attributes = $this->build_nodes_from_global_attributes();
		} else {
			$attributes = $this->build_nodes_from_source_attributes();
		}

		return $this->build_connection( $attributes );
	}
}
