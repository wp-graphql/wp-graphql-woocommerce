<?php
/**
 * Model - Coupon
 *
 * Resolves coupon crud object model
 *
 * @package WPGraphQL\Extensions\WooCommerce\Model
 * @since 0.0.1
 */

namespace WPGraphQL\Extensions\WooCommerce\Model;

use GraphQLRelay\Relay;

/**
 * Class Coupon
 */
class Coupon extends Crud_CPT {
	/**
	 * Coupon constructor
	 *
	 * @param int $id - shop_coupon post-type ID.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct( $id ) {
		$this->data                = new \WC_Coupon( $id );
		$allowed_restricted_fields = [
			'isRestricted',
			'isPrivate',
			'isPublic',
			'id',
			'couponId',
		];

		parent::__construct( $allowed_restricted_fields, 'shop_coupon', $id );
	}

	/**
	 * Retrieve the cap to check if the data should be restricted for the coupon
	 *
	 * @access protected
	 * @return string
	 */
	protected function get_restricted_cap() {
		if ( post_password_required( $this->data->get_id() ) ) {
			return $this->post_type_object->cap->edit_others_posts;
		}
		switch ( get_post_status( $this->data->get_id() ) ) {
			case 'trash':
				$cap = $this->post_type_object->cap->edit_posts;
				break;
			case 'draft':
				$cap = $this->post_type_object->cap->edit_others_posts;
				break;
			default:
				$cap = '';
				break;
		}
		return $cap;
	}

	/**
	 * Initializes the Coupon field resolvers
	 *
	 * @access protected
	 */
	protected function init() {
		if ( empty( $this->fields ) ) {
			$this->fields = array(
				'ID'                            => function() {
					return $this->data->get_id();
				},
				'id'                            => function() {
					return ! empty( $this->data->get_id() ) ? Relay::toGlobalId( 'shop_coupon', $this->data->get_id() ) : null;
				},
				'couponId'                      => function() {
					return ! empty( $this->data->get_id() ) ? $this->data->get_id() : null;
				},
				'code'                          => function() {
					return ! empty( $this->data->get_code() ) ? $this->data->get_code() : null;
				},
				'date'                          => function() {
					return ! empty( $this->data->get_date_created() ) ? $this->data->get_date_created() : null;
				},
				'modified'                      => function() {
					return ! empty( $this->data->get_date_modified() ) ? $this->data->get_date_modified() : null;
				},
				'description'                   => function() {
					return ! empty( $this->data->get_description() ) ? $this->data->get_description() : null;
				},
				'discountType'                  => function() {
					return ! empty( $this->data->get_discount_type() ) ? $this->data->get_discount_type() : null;
				},
				'amount'                        => function() {
					return ! empty( $this->data->get_amount() ) ? $this->data->get_amount() : null;
				},
				'dateExpiry'                    => function() {
					return ! empty( $this->data->get_date_expires() ) ? $this->data->get_date_expires() : null;
				},
				'usageCount'                    => function() {
					return ! is_null( $this->data->get_usage_count() ) ? $this->data->get_usage_count() : null;
				},
				'individualUse'                 => function() {
					return ! is_null( $this->data->get_individual_use() ) ? $this->data->get_individual_use() : null;
				},
				'usageLimit'                    => function() {
					return ! empty( $this->data->get_usage_limit() ) ? $this->data->get_usage_limit() : null;
				},
				'usageLimitPerUser'             => function() {
					return ! empty( $this->data->get_usage_limit_per_user() ) ? $this->data->get_usage_limit_per_user() : null;
				},
				'limitUsageToXItems'            => function() {
					return ! empty( $this->data->get_limit_usage_to_x_items() ) ? $this->data->get_limit_usage_to_x_items() : null;
				},
				'freeShipping'                  => function() {
					return ! is_null( $this->data->get_free_shipping() ) ? $this->data->get_free_shipping() : null;
				},
				'excludeSaleItems'              => function() {
					return ! is_null( $this->data->get_exclude_sale_items() ) ? $this->data->get_exclude_sale_items() : null;
				},
				'minimumAmount'                 => function() {
					return ! empty( $this->data->get_minimum_amount() ) ? $this->data->get_minimum_amount() : null;
				},
				'maximumAmount'                 => function() {
					return ! empty( $this->data->get_maximum_amount() ) ? $this->data->get_maximum_amount() : null;
				},
				'emailRestrictions'             => function() {
					return ! empty( $this->data->get_email_restrictions() ) ? $this->data->get_email_restrictions() : null;
				},
				/**
				 * Connection resolvers fields
				 *
				 * These field resolvers are used in connection resolvers to define WP_Query argument
				 * Note: underscore naming style is used as a quick identifier
				 */
				'product_ids'                   => function() {
					return ! empty( $this->data->get_product_ids() ) ? $this->data->get_product_ids() : array( '0' );
				},
				'excluded_product_ids'          => function() {
					return ! empty( $this->data->get_excluded_product_ids() ) ? $this->data->get_excluded_product_ids() : array( '0' );
				},
				'product_category_ids'          => function() {
					return ! empty( $this->data->get_product_categories() ) ? $this->data->get_product_categories() : array( '0' );
				},
				'excluded_product_category_ids' => function() {
					return ! empty( $this->data->get_excluded_product_categories() ) ? $this->data->get_excluded_product_categories() : array( '0' );
				},
				'used_by_ids'                   => function() {
					return ! empty( $this->data->get_used_by() ) ? $this->data->get_used_by() : array( '0' );
				},
			);
		}

		parent::prepare_fields();
	}
}
