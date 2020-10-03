<?php

use GraphQLRelay\Relay;

class CouponHelper extends WCG_Helper {
	public function __construct() {
		$this->node_type = 'shop_coupon';

		parent::__construct();
	}

	public function to_relay_id( $id ) {
		return Relay::toGlobalId( 'shop_coupon', $id );
	}

	public function create( $args = array(), $save = true ) {
		// Create new coupon crud object instance.
		$coupon = new WC_Coupon();

		// Set props.
		$amount = $this->dummy->number( 0, 75 );
		$coupon->set_props(
			array_merge(
				array(
					'code'                        => $amount . 'off',
					'amount'                      => floatval( $amount ),
					'date_expires'                => null,
					'discount_type'               => 'percent',
					'description'                 => 'Test coupon',
				),
				$args
			)
		);

		// Set meta data.
		if ( ! empty( $args['meta_data'] ) ) {
			$coupon->set_meta_data( $args['meta_data'] );
		}

		// Return instance in not saving.
		if( ! $save ) {
			return $coupon;
		}

		// Return ID upon saving.
		return $coupon->save();
	}

	public function print_query( $id ) {
		$data = new WC_Coupon( $id );

		return array(
			'id'                        => $this->to_relay_id( $id ),
			'databaseId'                => $data->get_id(),
			'code'                      => $data->get_code(),
			'amount'                    => $data->get_amount(),
			'date'                      => $data->get_date_created()->__toString(),
			'modified'                  => $data->get_date_modified()->__toString(),
			'discountType'              => strtoupper( $data->get_discount_type() ),
			'description'               => $data->get_description(),
			'dateExpiry'                => $data->get_date_expires(),
			'usageCount'                => $data->get_usage_count(),
			'individualUse'             => $data->get_individual_use(),
			'usageLimit'                => ! empty( $data->get_usage_limit() )
				? $data->get_usage_limit()
				: null,
			'usageLimitPerUser'         => ! empty( $data->get_usage_limit_per_user() )
				? $data->get_usage_limit_per_user()
				: null,
			'limitUsageToXItems'        => $data->get_limit_usage_to_x_items(),
			'freeShipping'              => $data->get_free_shipping(),
			'excludeSaleItems'          => $data->get_exclude_sale_items(),
			'minimumAmount'             => ! empty( $data->get_minimum_amount() )
				? $data->get_minimum_amount()
				: null,
			'maximumAmount'             => ! empty( $data->get_maximum_amount() )
				? $data->get_maximum_amount()
				: null,
			'emailRestrictions'         => ! empty( $data->get_email_restrictions() )
				? $data->get_email_restrictions()
				: null,
			'products'                  => [
				'nodes' => array_map(
					function( $id ) {
						return array( 'databaseId' => $id );
					},
					$data->get_product_ids()
				),
			],
			'excludedProducts'          => [
				'nodes' => array_map(
					function( $id ) {
						return array( 'databaseId' => $id );
					},
					$data->get_excluded_product_ids()
				),
			],
			'productCategories'         => [
				'nodes' => array_map(
					function( $id ) {
						return array( 'productCategoryId' => $id );
					},
					$data->get_product_categories()
				),
			],
			'excludedProductCategories' => [
				'nodes' => array_map(
					function( $id ) {
						return array( 'productCategoryId' => $id );
					},
					$data->get_excluded_product_categories()
				),
			],
			'usedBy'                    => [
				'nodes' => array_map(
					function( $id ) {
						return array( 'databaseId' => $id );
					},
					$data->get_used_by()
				),
			],
		);
	}

	public function print_failed_query( $id ) {
		$data = new WC_Coupon( $id );

		return array();
	}
}
