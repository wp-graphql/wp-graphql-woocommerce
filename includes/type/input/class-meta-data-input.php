<?php
/**
 * WPInputObjectType - MetaDataInput
 *
 * @package WPGraphQL\WooCommerce\Type\WPInputObject
 * @since   0.2.0
 */

namespace WPGraphQL\WooCommerce\Type\WPInputObject;

/**
 * Class Meta_Data_Input
 */
class Meta_Data_Input {

	/**
	 * Registers type
	 */
	public static function register() {
		register_graphql_input_type(
			'MetaDataInput',
			array(
				'description' => __( 'Meta data.', 'wp-graphql-woocommerce' ),
				'fields'      => array(
					'key'   => array(
						'type'        => array( 'non_null' => 'String' ),
						'description' => __( 'Meta key.', 'wp-graphql-woocommerce' ),
					),
					'value' => array(
						'type'        => array( 'non_null' => 'String' ),
						'description' => __( 'Meta value.', 'wp-graphql-woocommerce' ),
					),
				),
			)
		);
	}
}
