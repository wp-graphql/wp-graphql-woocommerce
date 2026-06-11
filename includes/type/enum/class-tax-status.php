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
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_enum_type(
			'TaxStatusEnum',
			[
				'description' => static function () {
					return __( 'Product tax status enumeration', 'graphql-for-ecommerce' );
				},
				'values'      => [
					'TAXABLE'  => [ 'value' => 'taxable' ],
					'SHIPPING' => [ 'value' => 'shipping' ],
					'NONE'     => [ 'value' => 'none' ],
				],
			]
		);
	}
}
