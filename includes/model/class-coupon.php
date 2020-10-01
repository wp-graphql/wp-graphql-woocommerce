<?php


namespace WPGraphQL\WooCommerce\Model;

use GraphQLRelay\Relay;
use WC_Coupon;

/**
 * Class Coupon
 */
class Coupon extends WC_Post {

	/**
	 * Coupon constructor
	 *
	 * @param int $id - shop_coupon post-type ID.
	 */
	public function __construct( $id ) {
		$data = new WC_Coupon( $id );

		parent::__construct( $data );
	}

	/**
	 * Initializes the Coupon field resolvers
	 */
	protected function init() {
		if ( empty( $this->fields ) ) {
			parent::init();

			$fields = array(
				'id'                            => function() {
					return ! empty( $this->wc_data->get_id() ) ? Relay::toGlobalId( 'shop_coupon', $this->wc_data->get_id() ) : null;
				},
				'code'                          => function() {
					return ! empty( $this->wc_data->get_code() ) ? $this->wc_data->get_code() : null;
				},
				'date'                          => function() {
					return ! empty( $this->wc_data->get_date_created() ) ? $this->wc_data->get_date_created() : null;
				},
				'modified'                      => function() {
					return ! empty( $this->wc_data->get_date_modified() ) ? $this->wc_data->get_date_modified() : null;
				},
				'description'                   => function() {
					return ! empty( $this->wc_data->get_description() ) ? $this->wc_data->get_description() : null;
				},
				'discountType'                  => function() {
					return ! empty( $this->wc_data->get_discount_type() ) ? $this->wc_data->get_discount_type() : null;
				},
				'amount'                        => function() {
					return ! empty( $this->wc_data->get_amount() ) ? $this->wc_data->get_amount() : null;
				},
				'dateExpiry'                    => function() {
					return ! empty( $this->wc_data->get_date_expires() ) ? $this->wc_data->get_date_expires() : null;
				},
				'usageCount'                    => function() {
					return ! is_null( $this->wc_data->get_usage_count() ) ? $this->wc_data->get_usage_count() : null;
				},
				'individualUse'                 => function() {
					return ! is_null( $this->wc_data->get_individual_use() ) ? $this->wc_data->get_individual_use() : null;
				},
				'usageLimit'                    => function() {
					return ! empty( $this->wc_data->get_usage_limit() ) ? $this->wc_data->get_usage_limit() : null;
				},
				'usageLimitPerUser'             => function() {
					return ! empty( $this->wc_data->get_usage_limit_per_user() ) ? $this->wc_data->get_usage_limit_per_user() : null;
				},
				'limitUsageToXItems'            => function() {
					return ! empty( $this->wc_data->get_limit_usage_to_x_items() ) ? $this->wc_data->get_limit_usage_to_x_items() : null;
				},
				'freeShipping'                  => function() {
					return ! is_null( $this->wc_data->get_free_shipping() ) ? $this->wc_data->get_free_shipping() : null;
				},
				'excludeSaleItems'              => function() {
					return ! is_null( $this->wc_data->get_exclude_sale_items() ) ? $this->wc_data->get_exclude_sale_items() : null;
				},
				'minimumAmount'                 => function() {
					return ! empty( $this->wc_data->get_minimum_amount() ) ? $this->wc_data->get_minimum_amount() : null;
				},
				'maximumAmount'                 => function() {
					return ! empty( $this->wc_data->get_maximum_amount() ) ? $this->wc_data->get_maximum_amount() : null;
				},
				'emailRestrictions'             => function() {
					return ! empty( $this->wc_data->get_email_restrictions() ) ? $this->wc_data->get_email_restrictions() : null;
				},
				/**
				 * Connection resolvers fields
				 *
				 * These field resolvers are used in connection resolvers to define WP_Query argument
				 * Note: underscore naming style is used as a quick identifier
				 */
				'product_ids'                   => function() {
					return ! empty( $this->wc_data->get_product_ids() ) ? $this->wc_data->get_product_ids() : array( '0' );
				},
				'excluded_product_ids'          => function() {
					return ! empty( $this->wc_data->get_excluded_product_ids() ) ? $this->wc_data->get_excluded_product_ids() : array( '0' );
				},
				'product_category_ids'          => function() {
					return ! empty( $this->wc_data->get_product_categories() ) ? $this->wc_data->get_product_categories() : array( '0' );
				},
				'excluded_product_category_ids' => function() {
					return ! empty( $this->wc_data->get_excluded_product_categories() ) ? $this->wc_data->get_excluded_product_categories() : array( '0' );
				},
				'used_by_ids'                   => function() {
					return ! empty( $this->wc_data->get_used_by() ) ? $this->wc_data->get_used_by() : array( '0' );
				},
			);

			$this->fields = array_merge( $this->fields, $fields );
		}
	}
}
