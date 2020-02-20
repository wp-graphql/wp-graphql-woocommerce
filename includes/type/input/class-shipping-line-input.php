<?php
/**
 * WPInputObjectType - ShippingLineInput
 *
 * @package WPGraphQL\WooCommerce\Type\WPInputObject
 * @since   0.2.0
 */

namespace WPGraphQL\WooCommerce\Type\WPInputObject;

/**
 * Class Shipping_Line_Input
 */
class Shipping_Line_Input {

	/**
	 * Registers type
	 */
	public static function register() {
		register_graphql_input_type(
			'ShippingLineInput',
			array(
				'description' => __( 'Shipping lines data.', 'wp-graphql-woocommerce' ),
				'fields'      => array(
					'id'          => array(
						'type'        => 'ID',
						'description' => __( 'Shipping Line ID', 'wp-graphql-woocommerce' ),
					),
					'methodTitle' => array(
						'type'        => array( 'non_null' => 'String' ),
						'description' => __( 'Shipping method name.', 'wp-graphql-woocommerce' ),
					),
					'methodId'    => array(
						'type'        => array( 'non_null' => 'String' ),
						'description' => __( 'Shipping method ID.', 'wp-graphql-woocommerce' ),
					),
					'instanceId'  => array(
						'type'        => 'String',
						'description' => __( 'Shipping instance ID.', 'wp-graphql-woocommerce' ),
					),
					'total'       => array(
						'type'        => array( 'non_null' => 'String' ),
						'description' => __( 'Line total (after discounts).', 'wp-graphql-woocommerce' ),
					),
					'metaData'    => array(
						'type'        => array( 'list_of' => 'MetaDataInput' ),
						'description' => __( 'Meta data.', 'wp-graphql-woocommerce' ),
					),
				),
			)
		);
	}
}
