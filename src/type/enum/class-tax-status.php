<?php

namespace WPGraphQL\Extensions\WooCommerce\Type\Enum;

class Tax_Status
{
	public static function register()
	{
		$values = [
			'TAXABLE'  => array( 'value' => 'taxable' ),
			'SHIPPING' => array( 'value' => 'shipping' ),
			'NONE'     => array( 'value' => 'none' ),
		];

		register_graphql_enum_type(
			'TaxStatusEnum',
			array(
				'description' => __('Product tax status enumeration', 'wp-graphql'),
				'values'      => $values
			)
		);
	}
}
