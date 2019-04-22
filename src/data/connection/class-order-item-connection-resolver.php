<?php
/**
 * ConnectionResolver - Order_Item_Connection_Resolver
 *
 * Resolves connections to Orders
 *
 * @package WPGraphQL\Extensions\WooCommerce\Data\Connection
 * @since 0.0.2
 */

namespace WPGraphQL\Extensions\WooCommerce\Data\Connection;

use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;

/**
 * Class Order_Item_Connection_Resolver
 */
class Order_Item_Connection_Resolver {
	/**
	 * Creates connection
	 *
	 * @param mixed       $source     - Connection source Model instance.
	 * @param array       $args       - Connection arguments.
	 * @param AppContext  $context    - AppContext object.
	 * @param ResolveInfo $info       - ResolveInfo object.
	 */
	public function resolve( $source, array $args, AppContext $context, ResolveInfo $info ) {
		global $wpdb;
		// @codingStandardsIgnoreLine
		switch ( $info->fieldName ) {
			case 'taxLines':
				$type = 'tax';
				break;
			case 'shippingLines':
				$type = 'shipping';
				break;
			case 'feeLines':
				$type = 'fee';
				break;
			case 'couponLines':
				$type = 'coupon';
				break;
			default:
				$type = 'line_item';
				break;
		}
		$items = $source->get_items( $type );

		$connection = Relay::connectionFromArray( $items, $args );
		$nodes      = array();
		if ( ! empty( $connection['edges'] ) && is_array( $connection['edges'] ) ) {
			foreach ( $connection['edges'] as $edge ) {
				$nodes[] = ! empty( $edge['node'] ) ? $edge['node'] : null;
			}
		}
		$connection['nodes'] = ! empty( $nodes ) ? $nodes : null;
		return ! empty( $items ) ? $connection : null;
	}
}
