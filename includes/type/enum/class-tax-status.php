<?php
/**
 * WPEnum Type - TaxStatusEnum
 *
 * @package \WPGraphQL\Extensions\WooCommerce\Type\WPEnum
 * @since   0.0.1
 */

namespace WPGraphQL\Extensions\WooCommerce\Type\WPEnum;

/**
 * Class Tax_Status
 */
class Tax_Status {
	/**
	 * Registers type
	 */
	public static function register() {
		$values = [
			'TAXABLE'  => array( 'value' => 'taxable' ),
			'SHIPPING' => array( 'value' => 'shipping' ),
			'NONE'     => array( 'value' => 'none' ),
		];

		register_graphql_enum_type(
			'TaxStatusEnum',
			array(
				'description' => __( 'Product tax status enumeration', 'wp-graphql' ),
				'values'      => $values,
			)
		);
	}
}
