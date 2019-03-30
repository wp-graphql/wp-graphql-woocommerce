<?php
/**
 * Model - WC_Post
 *
 * Models WooCommerce post-type data
 *
 * @package WPGraphQL\Extensions\WooCommerce\Model
 * @since 0.0.1
 */

namespace WPGraphQL\Extensions\WooCommerce\Model;

use GraphQLRelay\Relay;
use WPGraphQL\Data\DataSource;
use WPGraphQL\Model\Post;

/**
 * Class WC_Post
 */
class WC_Post extends Post {
	/**
	 * Stores the instance of the WC data-store
	 *
	 * @var mixed $wc_post
	 * @access protected
	 */
	protected $wc_post;

	/**
	 * WC_Post constructor
	 *
	 * @param \WP_Post $post - Model WP_Post instance.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct( \WP_Post $post ) {
		$this->wc_post = $this->get_wc_post( $post );
		add_filter( 'graphql_return_modeled_data', [ &$this, 'add_fields' ] );
		parent::__construct( $post );
	}

	/**
	 * Retrieves data-store instances for specified post-type
	 *
	 * @param \WP_Post $post - Model WP_Post instance.
	 */
	private function get_wc_post( $post ) {
		switch ( $post->post_type ) {
			case 'shop_coupon':
				return new \WC_Coupon( $post->ID );
			case 'shop_order':
				return new \WC_Order( $post->ID );
			case 'shop_order_refund':
				return new \WC_Order_Refund( $post->ID );
			case 'product':
			case 'product_variation':
				return \wc_get_product( $post->ID );
		}
	}


	/**
	 * Callback for the graphql_data_is_private filter to determine if the post should be
	 * considered private
	 *
	 * @param bool   $private    True or False value if the data should be private.
	 * @param string $model_name Name of the model for the data currently being modeled.
	 * @param mixed  $data       The Data currently being modeled.
	 *
	 * @access public
	 * @return bool
	 */
	public function is_private( $private, $model_name, $data ) {
		if ( 'PostObject' !== $model_name ) {
			return $private;
		}
		if ( ( true === $this->owner_matches_current_user() || 'publish' === $data->post_status ) && 'revision' !== $data->post_type ) {
			return false;
		}

		/**
		 * If the post_type isn't (not registered) or is not allowed in WPGraphQL,
		 * mark the post as private
		 */
		if ( empty( $this->post_type_object ) || empty( $this->post_type_object->name ) || ! in_array( $this->post_type_object->name, \WPGraphQL::$allowed_post_types, true ) ) {
			return true;
		}
		if ( 'private' === $data->post_status && ! current_user_can( $this->post_type_object->cap->read_private_posts ) ) {
			return true;
		}
		if ( 'revision' === $data->post_type || 'auto-draft' === $data->post_status ) {
			$parent               = get_post( (int) $data->post_parent );
			$parent_post_type_obj = get_post_type_object( $parent->post_type );
			if ( 'private' === $parent->post_status ) {
				$cap = $parent_post_type_obj->cap->read_private_posts;
			} else {
				$cap = $parent_post_type_obj->cap->edit_post;
			}
			if ( ! current_user_can( $cap, $parent->ID ) ) {
				return true;
			}
		}
		return $private;
	}

	/**
	 * Adds WC post-type field resolvers to $fields
	 *
	 * @param array $fields - Model field resolvers.
	 *
	 * @return array
	 */
	public function add_fields( $fields ) {
		$more_fields = array();
		if ( 'shop_coupon' === $this->post->post_type ) {
			$more_fields = array(
				'code'                          => function() {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_code() : null;
				},
				'date'                          => function() {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_date_created() : null;
				},
				'modified'                      => function() {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_date_modified() : null;
				},
				'description'                   => function() {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_description() : null;
				},
				'discountType'                  => function() {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_discount_type() : null;
				},
				'amount'                        => function() {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_amount() : null;
				},
				'dateExpiry'                    => function() {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_date_expires() : null;
				},
				'usageCount'                    => function() {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_usage_count() : null;
				},
				'individualUse'                 => function() {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_individual_use() : null;
				},
				'usageLimit'                    => function() {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_usage_limit() : null;
				},
				'usageLimitPerUser'             => function() {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_usage_limit_per_user() : null;
				},
				'limitUsageToXItems'            => function() {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_limit_usage_to_x_items() : null;
				},
				'freeShipping'                  => function() {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_free_shipping() : null;
				},
				'excludeSaleItems'              => function() {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_exclude_sale_items() : null;
				},
				'minimumAmount'                 => function() {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_minimum_amount() : null;
				},
				'maximumAmount'                 => function() {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_maximum_amount() : null;
				},
				'emailRestrictions'             => function() {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_email_restrictions() : null;
				},
				/**
				 * Connection resolvers fields
				 *
				 * These field resolvers are used in connection resolvers to define WP_Query argument
				 * Note: underscore naming style is used as a quick identifier
				 */
				'product_ids'                   => function() {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_product_ids() : null;
				},
				'excluded_product_ids'          => function() {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_excluded_product_ids() : null;
				},
				'product_category_ids'          => function() {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_product_categories() : null;
				},
				'excluded_product_category_ids' => function() {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_excluded_product_categories() : null;
				},
				'used_by_ids'                   => function() {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_used_by() : null;
				},
			);
		} elseif ( 'product' === $this->post->post_type || 'product_variation' === $this->post->post_type ) {
			$more_fields = array(
				'slug'               => function() {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_slug() : null;
				},
				'name'               => function() {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_name() : null;
				},
				'status'             => function() {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_status() : null;
				},
				'featured'           => function() {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_featured() : null;
				},
				'catalogVisibility'  => function() {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_catalog_visibility() : null;
				},
				'description'        => function() {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_description() : null;
				},
				'shortDescription'   => function() {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_short_description() : null;
				},
				'sku'                => function() {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_sku() : null;
				},
				'price'              => function() {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_price() : null;
				},
				'regularPrice'       => function() {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_regular_price() : null;
				},
				'salePrice'          => function() {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_sale_price() : null;
				},
				'dateOnSaleFrom'     => function() {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_date_on_sale_from() : null;
				},
				'dateOnSaleTo'       => function() {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_date_on_sale_to() : null;
				},
				'totalSales'         => function() {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_total_sales() : null;
				},
				'taxStatus'          => function() {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_tax_status() : null;
				},
				'taxClass'           => function() {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_tax_class() : null;
				},
				'manageStock'        => function() {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_manage_stock() : null;
				},
				'stockQuantity'      => function() {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_stock_quantity() : null;
				},
				'stockStatus'        => function() {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_stock_status() : null;
				},
				'backorders'         => function() {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_backorders() : null;
				},
				'soldIndividually'   => function() {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_sold_individually() : null;
				},
				'weight'             => function() {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_weight() : null;
				},
				'length'             => function() {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_length() : null;
				},
				'width'              => function() {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_width() : null;
				},
				'height'             => function() {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_height() : null;
				},
				'reviewsAllowed'     => function() {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_reviews_allowed() : null;
				},
				'purchaseNote'       => function() {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_purchase_note() : null;
				},
				'menuOrder'          => function() {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_menu_order() : null;
				},
				'virtual'            => function() {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_virtual() : null;
				},
				'downloadExpiry'     => function() {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_download_expiry() : null;
				},
				'downloadable'       => function() {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_downloadable() : null;
				},
				'downloadLimit'      => function() {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_download_limit() : null;
				},
				'ratingCount'        => function() {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_rating_counts() : null;
				},
				'averageRating'      => function() {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_average_rating() : null;
				},
				'reviewCount'        => function() {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_review_count() : null;
				},
				'parentId'           => function() {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_parent_id() : null;
				},
				'imageId'            => function () {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_image_id() : null;
				},
				/**
				 * Connection resolvers fields
				 *
				 * These field resolvers are used in connection resolvers to define WP_Query argument
				 * Note: underscore naming style is used as a quick identifier
				 */
				'upsell_ids'         => function() {
					switch ( true ) {
						case empty( $this->wc_post ):
						case is_a( $this->wc_post, \WC_Product_External::class ):
						case is_a( $this->wc_post, \WC_Product_Grouped::class ):
							return null;
						default:
							return $this->wc_post->get_upsell_ids();
					}
				},
				'cross_sell_ids'     => function() {
					switch ( true ) {
						case empty( $this->wc_post ):
						case is_a( $this->wc_post, \WC_Product_External::class ):
						case is_a( $this->wc_post, \WC_Product_Grouped::class ):
							return null;
						default:
							return $this->wc_post->get_cross_sell_ids();
					}
				},
				'attributes'         => function() {
					return ! empty( $this->wc_post ) ? array_values( $this->wc_post->get_attributes() ) : null;
				},
				'default_attributes' => function() {
					return ! empty( $this->wc_post ) ? array_values( $this->wc_post->get_default_attributes() ) : null;
				},
				'downloads'          => function() {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_downloads() : null;
				},
				'gallery_image_ids'  => function() {
					return ! empty( $this->wc_post ) ? $this->wc_post->get_gallery_image_ids() : null;
				},
			);
		} elseif ( 'shop_order' === $this->post->post_type ) {
			$more_fields = array(
				'orderKey'            => function() {
					return get_post_meta( $this->post->ID, '_order_key', true );
				},
				'currency'            => function() {
					return get_post_meta( $this->post->ID, '_order_currency', true );
				},
				'paymentMethod'       => function() {
					return get_post_meta( $this->post->ID, '_payment_method', true );
				},
				'paymentMethodTitle'  => function() {
					return get_post_meta( $this->post->ID, '_payment_method_title', true );
				},
				'transactionId'       => function() {
					return get_post_meta( $this->post->ID, '_transaction_id', true );
				},
				'customerIpAddress'   => function() {
					return get_post_meta( $this->post->ID, '_customer_ip_address', true );
				},
				'customerUserAgent'   => function() {
					return get_post_meta( $this->post->ID, '_customer_user_agent', true );
				},
				'createdVia'          => function() {
					return get_post_meta( $this->post->ID, '_created_via', true );
				},
				'dateCompleted'       => function() {
					return get_post_meta( $this->post->ID, '_completed_date', true );
				},
				'datePaid'            => function() {
					return get_post_meta( $this->post->ID, '_paid_date', true );
				},
				'discountTotal'       => function() {
					return get_post_meta( $this->post->ID, '_cart_discount', true );
				},
				'discountTax'         => function() {
					return get_post_meta( $this->post->ID, '_cart_discount_tax', true );
				},
				'shippingTotal'       => function() {
					return get_post_meta( $this->post->ID, '_order_shipping', true );
				},
				'shippingTax'         => function() {
					return get_post_meta( $this->post->ID, '_order_shipping_tax', true );
				},
				'cartTax'             => function() {
					return get_post_meta( $this->post->ID, '_order_tax', true );
				},
				'total'               => function() {
					return get_post_meta( $this->post->ID, '_order_total', true );
				},
				'totalTax'            => function() {
					return get_post_meta( $this->post->ID, '_order_tax', true );
				},
				'subtotal'            => function() {
					return 0;
				},
				'orderNumber'         => function() {
					return (string) apply_filters( 'woocommerce_order_number', $this->post->ID, $this );
				},
				'orderVersion'        => function() {
					return get_post_meta( $this->post->ID, '_order_version', true );
				},
				'pricesIncludeTax'    => function() {
					return get_post_meta( $this->post->ID, '_prices_include_tax', true );
				},
				'cartHash'            => function() {
					return get_post_meta( $this->post->ID, '_cart_hash', true );
				},
				'customerNote'        => function() {
					return $this->post->post_excerpt;
				},
				'isDownloadPermitted' => function() {
					return get_post_meta( $this->post->ID, '_download_permissions_granted', true );
				},
				'billing'             => function() {
					return $this->post->id;
				},
				'shipping'            => function() {
					return $this->post->id;
				},
				/**
				 * Connection resolvers fields
				 *
				 * These field resolvers are used in connection resolvers to define WP_Query argument
				 * Note: underscore naming style is used as a quick identifier
				 */
				'customer_id'        => function() {
					return get_post_meta( $this->post->ID, '_customer_user', true );
				},
			);
		}

		return array_merge( $fields, $more_fields );
	}
}
