<?php
/**
 * Model - Product_Variation
 *
 * Resolves product variation crud object model
 *
 * @package WPGraphQL\Extensions\WooCommerce\Model
 * @since 0.0.1
 */

namespace WPGraphQL\Extensions\WooCommerce\Model;

use GraphQLRelay\Relay;
use WPGraphQL\Data\DataSource;

/**
 * Class Product_Variation
 */
class Product_Variation extends Crud_CPT {
	/**
	 * Product_Variation constructor
	 *
	 * @param int $id - product_variation post-type ID.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct( $id ) {
		$this->data                = new \WC_Product_Variation( $id );
		$allowed_restricted_fields = [
			'isRestricted',
			'isPrivate',
			'isPublic',
			'id',
			'variationId',
		];

		parent::__construct( $allowed_restricted_fields, 'product_variation', $id );
	}

	/**
	 * Retrieve the cap to check if the data should be restricted for the coupon
	 *
	 * @access protected
	 * @return string
	 */
	protected function get_restricted_cap() {
		if ( post_password_required( $this->data->get_parent_id() ) ) {
			return $this->post_type_object->cap->edit_others_posts;
		}
		switch ( get_post_status( $this->data->get_parent_id() ) ) {
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
	 * Initializes the ProductVariation field resolvers
	 *
	 * @access protected
	 */
	protected function init() {
		if ( empty( $this->fields ) ) {
			$this->fields = array(
				'ID'                => function() {
					return $this->data->get_id();
				},
				'id'                => function() {
					return ! empty( $this->data->get_id() ) ? Relay::toGlobalId( 'product_variation', $this->data->get_id() ) : null;
				},
				'variationId'       => function() {
					return ! empty( $this->data->get_id() ) ? $this->data->get_id() : null;
				},
				'name'              => function() {
					return ! empty( $this->data->get_name() ) ? $this->data->get_name() : null;
				},
				'date'              => function() {
					return ! empty( $this->data ) ? $this->data->get_date_created() : null;
				},
				'modified'          => function() {
					return ! empty( $this->data ) ? $this->data->get_date_modified() : null;
				},
				'description'       => function() {
					return ! empty( $this->data->get_description() ) ? $this->data->get_description() : null;
				},
				'sku'               => function() {
					return ! empty( $this->data->get_sku() ) ? $this->data->get_sku() : null;
				},
				'price'             => function() {
					return ! empty( $this->data->get_price() ) ? $this->data->get_price() : null;
				},
				'salePrice'         => function() {
					return ! empty( $this->data->get_sale_price() ) ? $this->data->get_sale_price() : null;
				},
				'regularPrice'      => function() {
					return ! empty( $this->data->get_regular_price() ) ? $this->data->get_regular_price() : null;
				},
				'dateOnSaleFrom'    => function() {
					return ! empty( $this->data->get_date_on_sale_from() ) ? $this->data->get_date_on_sale_from() : null;
				},
				'dateOnSaleTo'      => function() {
					return ! empty( $this->data->get_date_on_sale_to() ) ? $this->data->get_date_on_sale_to() : null;
				},
				'onSale'            => function () {
					return ! is_null( $this->data->is_on_sale() ) ? $this->data->is_on_sale() : null;
				},
				'status'            => function() {
					return ! empty( $this->data->get_status() ) ? $this->data->get_status() : null;
				},
				'purchasable'       => function() {
					return ! empty( $this->data->is_purchasable() ) ? $this->data->is_purchasable() : null;
				},
				'virtual'           => function() {
					return ! is_null( $this->data->is_virtual() ) ? $this->data->is_virtual() : null;
				},
				'downloadable'      => function() {
					return ! is_null( $this->data->is_downloadable() ) ? $this->data->is_downloadable() : null;
				},
				'downloads'         => function() {
					return ! empty( $this->data->get_downloads() ) ? $this->data->get_downloads() : null;
				},
				'downloadLimit'     => function() {
					return ! is_null( $this->data->get_download_limit() ) ? $this->data->get_download_limit() : null;
				},
				'downloadExpiry'    => function() {
					return ! is_null( $this->data->get_download_expiry() ) ? $this->data->get_download_expiry() : null;
				},
				'taxStatus'         => function() {
					return ! empty( $this->data->get_tax_status() ) ? $this->data->get_tax_status() : null;
				},
				'taxClass'          => function() {
					return ! empty( $this->data->get_tax_class() ) ? $this->data->get_tax_class() : 'standard';
				},
				'manageStock'       => function() {
					return ! empty( $this->data->get_manage_stock() ) ? $this->data->get_manage_stock() : null;
				},
				'stockQuantity'     => function() {
					return ! empty( $this->data->get_stock_quantity() ) ? $this->data->get_stock_quantity() : null;
				},
				'stockStatus'       => function() {
					return ! empty( $this->data->get_stock_status() ) ? $this->data->get_stock_status() : null;
				},
				'backorders'        => function() {
					return ! empty( $this->data->get_backorders() ) ? $this->data->get_backorders() : null;
				},
				'backordersAllowed' => function() {
					return ! is_null( $this->data->backorders_allowed() ) ? $this->data->backorders_allowed() : null;
				},
				'weight'            => function() {
					return ! empty( $this->data->get_weight() ) ? $this->data->get_weight() : null;
				},
				'length'            => function() {
					return ! empty( $this->data->get_length() ) ? $this->data->get_length() : null;
				},
				'width'             => function() {
					return ! empty( $this->data->get_width() ) ? $this->data->get_width() : null;
				},
				'height'            => function() {
					return ! empty( $this->data->get_height() ) ? $this->data->get_height() : null;
				},
				'menuOrder'         => function() {
					return ! is_null( $this->data->get_menu_order() ) ? $this->data->get_menu_order() : null;
				},
				'purchaseNote'      => function() {
					return ! empty( $this->data->get_purchase_note() ) ? $this->data->get_purchase_note() : null;
				},
				'catalogVisibility' => function() {
					return ! empty( $this->data->get_catalog_visibility() ) ? $this->data->get_catalog_visibility() : null;
				},
				'hasAttributes'     => function() {
					return ! empty( $this->data->has_attributes() ) ? $this->data->has_attributes() : null;
				},
				'type'              => function() {
					return ! empty( $this->data->get_type() ) ? $this->data->get_type() : null;
				},
				/**
				 * Connection resolvers fields
				 *
				 * These field resolvers are used in connection resolvers to define WP_Query argument
				 * Note: underscore naming style is used as a quick identifier
				 */
				'parent_id'         => function() {
					return ! empty( $this->data->get_parent_id() ) ? $this->data->get_parent_id() : null;
				},
				'shipping_class_id' => function() {
					return ! empty( $this->data->get_shipping_class_id() ) ? $this->data->get_shipping_class_id() : null;
				},
				'image_id'          => function() {
					return ! empty( $this->data->get_image_id() ) ? $this->data->get_image_id() : null;
				},
				'attributes'        => function() {
					return ! empty( $this->data->get_attributes() ) ? $this->data->get_attributes() : null;
				},
			);
		}

		parent::prepare_fields();
	}
}
