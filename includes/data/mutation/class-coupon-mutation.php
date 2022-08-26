<?php
/**
 * Defines helper functions for executing mutations related to the coupons.
 *
 * @package WPGraphQL\WooCommerce\Data\Mutation
 * @since 0.9.0
 */

namespace WPGraphQL\WooCommerce\Data\Mutation;

use GraphQL\Error\UserError;
use WPGraphQL\WooCommerce\Data\Factory;

/**
 * Class - Coupon_Mutation
 */
class Coupon_Mutation {

	/**
	 * Prepares coupon args from input.
	 *
	 * @param array $input  Mutation input data.
	 *
	 * @return array
	 */
	public static function prepare_args( array $input ) {
		$data_keys = [
			'code'                      => 'code',
			'amount'                    => 'amount',
			'discountType'              => 'discount_type',
			'description'               => 'description',
			'dateExpires'               => 'date_expires',
			'dateExpiresGmt'            => 'date_expires_gmt',
			'individualUse'             => 'individual_use',
			'productIds'                => 'product_ids',
			'excludedProductIds'        => 'excluded_product_ids',
			'usageLimit'                => 'usage_limit',
			'usageLimitPerUser'         => 'usage_limit_per_user',
			'limitUsageToXItems'        => 'limit_usage_to_x_items',
			'freeShipping'              => 'free_shipping',
			'productCategories'         => 'product_categories',
			'excludedProductCategories' => 'excluded_product_categories',
			'excludeSaleItems'          => 'exclude_sale_items',
			'minimumAmount'             => 'minimum_amount',
			'maximumAmount'             => 'maximum_amount',
			'emailRestrictions'         => 'email_restrictions',
			'metaData'                  => 'meta_data',
		];

		$args = [];
		foreach ( $input as $input_field => $value ) {
			if ( empty( $data_keys[ $input_field ] ) ) {
				continue;
			}

			$args[ $data_keys[ $input_field ] ] = $value;
		}

		return $args;
	}
}
