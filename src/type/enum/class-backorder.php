<?php

namespace WPGraphQL\Extensions\WooCommerce\Type\Enum;

class Backorders
{
	public static function register()
	{
		$values = array(
			'NO'     => array( 'value' => 'no' ),
			'NOTIFY' => array( 'value' => 'notify' ),
			'YES'    => array( 'value' => 'yes' ),
		);

		register_graphql_enum_type(
			'BackorderEnum',
			array(
				'description' => __('Product backorder enumeration', 'wp-graphql-woocommerce'),
				'values'      => $values
			)
		);
	}
}
