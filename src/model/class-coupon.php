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
use WPGraphQL\Data\DataSource;
use WPGraphQL\Model\Model;

/**
 * Class Coupon
 */
class Coupon extends Model {
	/**
	 * Stores the instance of WC_Coupon
	 *
	 * @var \WC_Coupon $coupon
	 * @access protected
	 */
	protected $coupon;

	/**
	 * Coupon constructor
	 *
	 * @param int $id - shop_coupon post-type ID.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct( $id ) {
		$this->coupon              = new \WC_Coupon( $id );
		$allowed_restricted_fields = [
			'isRestricted',
			'isPrivate',
			'isPublic',
			'id',
			'userId',
			'name',
			'firstName',
			'lastName',
			'description',
			'slug',
		];

		parent::__construct( 'CouponObject', $this->coupon, 'list_users', $allowed_restricted_fields, $id );
		$this->init();
	}

	/**
	 * Initializes the Coupon field resolvers
	 *
	 * @access public
	 */
	public function init() {
		if ( 'private' === $this->get_visibility() || is_null( $this->coupon ) ) {
			return null;
		}

		if ( empty( $this->fields ) ) {
			$this->fields = array(
				'ID'                            => function() {
					return $this->coupon->get_id();
				},
				'id'                            => function() {
					return ! empty( $this->coupon ) ? Relay::toGlobalId( 'shop_coupon', $this->coupon->get_id() ) : null;
				},
				'couponId'                      => function() {
					return ! empty( $this->coupon ) ? $this->coupon->get_id() : null;
				},
				'code'                          => function() {
					return ! empty( $this->coupon ) ? $this->coupon->get_code() : null;
				},
				'date'                          => function() {
					return ! empty( $this->coupon ) ? $this->coupon->get_date_created() : null;
				},
				'modified'                      => function() {
					return ! empty( $this->coupon ) ? $this->coupon->get_date_modified() : null;
				},
				'description'                   => function() {
					return ! empty( $this->coupon ) ? $this->coupon->get_description() : null;
				},
				'discountType'                  => function() {
					return ! empty( $this->coupon ) ? $this->coupon->get_discount_type() : null;
				},
				'amount'                        => function() {
					return ! empty( $this->coupon ) ? $this->coupon->get_amount() : null;
				},
				'dateExpiry'                    => function() {
					return ! empty( $this->coupon ) ? $this->coupon->get_date_expires() : null;
				},
				'usageCount'                    => function() {
					return ! empty( $this->coupon ) ? $this->coupon->get_usage_count() : null;
				},
				'individualUse'                 => function() {
					return ! empty( $this->coupon ) ? $this->coupon->get_individual_use() : null;
				},
				'usageLimit'                    => function() {
					return ! empty( $this->coupon ) ? $this->coupon->get_usage_limit() : null;
				},
				'usageLimitPerUser'             => function() {
					return ! empty( $this->coupon ) ? $this->coupon->get_usage_limit_per_user() : null;
				},
				'limitUsageToXItems'            => function() {
					return ! empty( $this->coupon ) ? $this->coupon->get_limit_usage_to_x_items() : null;
				},
				'freeShipping'                  => function() {
					return ! empty( $this->coupon ) ? $this->coupon->get_free_shipping() : null;
				},
				'excludeSaleItems'              => function() {
					return ! empty( $this->coupon ) ? $this->coupon->get_exclude_sale_items() : null;
				},
				'minimumAmount'                 => function() {
					return ! empty( $this->coupon ) ? $this->coupon->get_minimum_amount() : null;
				},
				'maximumAmount'                 => function() {
					return ! empty( $this->coupon ) ? $this->coupon->get_maximum_amount() : null;
				},
				'emailRestrictions'             => function() {
					return ! empty( $this->coupon ) ? $this->coupon->get_email_restrictions() : null;
				},
				/**
				 * Connection resolvers fields
				 *
				 * These field resolvers are used in connection resolvers to define WP_Query argument
				 * Note: underscore naming style is used as a quick identifier
				 */
				'product_ids'                   => function() {
					return ! empty( $this->coupon ) ? $this->coupon->get_product_ids() : null;
				},
				'excluded_product_ids'          => function() {
					return ! empty( $this->coupon ) ? $this->coupon->get_excluded_product_ids() : null;
				},
				'product_category_ids'          => function() {
					return ! empty( $this->coupon ) ? $this->coupon->get_product_categories() : null;
				},
				'excluded_product_category_ids' => function() {
					return ! empty( $this->coupon ) ? $this->coupon->get_excluded_product_categories() : null;
				},
				'used_by_ids'                   => function() {
					return ! empty( $this->coupon ) ? $this->coupon->get_used_by() : null;
				},
			);
		}

		parent::prepare_fields();
	}
}
