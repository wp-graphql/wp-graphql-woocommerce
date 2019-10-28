<?php
/**
 * ConnectionResolver - Payment_Gateway_Connection_Resolver
 *
 * Resolves connections to PaymentGateway
 *
 * @package WPGraphQL\WooCommerce\Data\Connection
 * @since 0.2.1
 */

namespace WPGraphQL\WooCommerce\Data\Connection;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;

/**
 * Class Payment_Gateway_Connection_Resolver
 */
class Payment_Gateway_Connection_Resolver {
	/**
	 * Creates connection
	 *
	 * @param mixed       $source     - Connection source Model instance.
	 * @param array       $args       - Connection arguments.
	 * @param AppContext  $context    - AppContext object.
	 * @param ResolveInfo $info       - ResolveInfo object.
	 *
	 * @throws UserError User not authorized.
	 * @return array
	 */
	public function resolve( $source, array $args, AppContext $context, ResolveInfo $info ) {
		if ( ( ! empty( $args['where']['all'] ) ) && true === $args['where']['all'] ) {
			if ( ! current_user_can( 'edit_theme_options' ) ) {
				throw new UserError( __( 'Not authorized to view these settings', 'wp-graphql-woocommerce' ) );
			}
			$gateways = \WC()->payment_gateways()->payment_gateways();
		} else {
			$gateways = \WC()->payment_gateways()->get_available_payment_gateways();
		}

		$connection = Relay::connectionFromArray( array_values( $gateways ), $args );
		$nodes      = array();
		if ( ! empty( $connection['edges'] ) && is_array( $connection['edges'] ) ) {
			foreach ( $connection['edges'] as $edge ) {
				$nodes[] = ! empty( $edge['node'] ) ? $edge['node'] : null;
			}
		}
		$connection['nodes'] = ! empty( $nodes ) ? $nodes : null;
		return ! empty( $gateways ) ? $connection : null;
	}
}
