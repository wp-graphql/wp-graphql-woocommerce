<?php
/**
 * WPEnum Type - TaxStatusEnum
 *
 * @package WPGraphQL\WooCommerce\Type\WPEnum
 * @since   0.0.1
 */

namespace WPGraphQL\WooCommerce\Type\WPEnum;

/**
 * Class Tax_Status
 */
class Tax_Status {
	/**
	 * Registers type
	 */
	public static function register() {
		register_graphql_enum_type(
			'TaxStatusEnum',
			array(
				'description' => __( 'Product tax status enumeration', 'wp-graphql-woocommerce' ),
				'values'      => array(
					'TAXABLE'  => array( 'value' => 'taxable' ),
					'SHIPPING' => array( 'value' => 'shipping' ),
					'NONE'     => array( 'value' => 'none' ),
				),
			)
		);
	}
}
