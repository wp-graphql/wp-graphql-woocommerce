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
	 */
	private function get_items( $attributes, $source, $args, $context, $info, $type = null ) {
		$items = [];
		foreach ( $attributes as $attribute_name => $data ) {
			// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
			$data->_relay_id = base64_encode(
				$attribute_name
				. GLOBAL_ID_DELIMITER
				. $source->ID
				. GLOBAL_ID_DELIMITER
				. $data->get_name()
			);
			$items[]         = $data;
		}

		$type = ! empty( $args['where']['type'] ) ? $args['where']['type'] : $type;

		if ( ! is_null( $type ) ) {
			switch ( $type ) {
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
	 * Creates connection
	 *
	 * @param mixed                                $source   Connection source Model instance.
	 * @param array                                $args     Connection arguments.
	 * @param \WPGraphQL\AppContext                $context  AppContext object.
	 * @param \GraphQL\Type\Definition\ResolveInfo $info     ResolveInfo object.
	 * @param string                               $type     Attribute type.
	 *
	 * @return array|null
	 */
	public function resolve( $source, array $args, AppContext $context, ResolveInfo $info, $type = null ) {
		$attributes = $this->get_items( $source->attributes, $source, $args, $context, $info, $type );

		$connection = Relay::connectionFromArray( $attributes, $args );
		$nodes      = [];
		if ( ! empty( $connection['edges'] ) && is_array( $connection['edges'] ) ) {
			foreach ( $connection['edges'] as $edge ) {
				$nodes[] = ! empty( $edge['node'] ) ? $edge['node'] : null;
			}
		}
		$connection['nodes'] = ! empty( $nodes ) ? $nodes : null;
		return ! empty( $attributes ) ? $connection : null;
	}
}
