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
				'ID'                 => function() {
					return $this->data->get_id();
				},
				'id'                 => function() {
					return ! empty( $this->data->get_id() ) ? Relay::toGlobalId( 'product_variation', $this->data->get_id() ) : null;
				},
				'variationId'        => function() {
					return ! empty( $this->data->get_id() ) ? $this->data->get_id() : null;
				},
				'sku'                => function() {
					return ! empty( $this->data->get_sku() ) ? $this->data->get_sku() : null;
				},
				'weight'             => function() {
					return ! empty( $this->data->get_weight() ) ? $this->data->get_weight() : null;
				},
				'length'             => function() {
					return ! empty( $this->data->get_length() ) ? $this->data->get_length() : null;
				},
				'width'              => function() {
					return ! empty( $this->data->get_width() ) ? $this->data->get_width() : null;
				},
				'height'             => function() {
					return ! empty( $this->data->get_height() ) ? $this->data->get_height() : null;
				},
				'taxClass'           => function() {
					return ! empty( $this->data->get_tax_class() ) ? $this->data->get_tax_class() : 'standard';
				},
				'manageStock'        => function() {
					return ! empty( $this->data->get_manage_stock() ) ? $this->data->get_manage_stock() : null;
				},
				'stockQuantity'      => function() {
					return ! empty( $this->data->get_stock_quantity() ) ? $this->data->get_stock_quantity() : null;
				},
				'backorders'         => function() {
					return ! empty( $this->data->get_backorders() ) ? $this->data->get_backorders() : null;
				},
				'purchaseNote'       => function() {
					return ! empty( $this->data->get_purchase_note() ) ? $this->data->get_purchase_note() : null;
				},
				'catalogVisibility'  => function() {
					return ! empty( $this->data->get_catalog_visibility() ) ? $this->data->get_catalog_visibility() : null;
				},
				'hasAttributes'      => function() {
					return ! empty( $this->data->has_attributes() ) ? $this->data->has_attributes() : null;
				},
				'isPurchasable'      => function() {
					return ! empty( $this->data->is_purchasable() ) ? $this->data->is_purchasable() : null;
				},
				'price'              => function() {
					return ! empty( $this->data->get_price() ) ? $this->data->get_price() : null;
				},
				'salePrice'          => function() {
					return ! empty( $this->data->get_sale_price() ) ? $this->data->get_sale_price() : null;
				},
				'regularPrice'       => function() {
					return ! empty( $this->data->get_regular_price() ) ? $this->data->get_regular_price() : null;
				},
				/**
				 * Connection resolvers fields
				 *
				 * These field resolvers are used in connection resolvers to define WP_Query argument
				 * Note: underscore naming style is used as a quick identifier
				 */
				'parent_id'          => function() {
					return ! empty( $this->data->get_parent_id() ) ? $this->data->get_parent_id() : null;
				},
				'shipping_class_id'  => function() {
					return ! empty( $this->data->get_shipping_class_id() ) ? $this->data->get_shipping_class_id() : null;
				},
				'image_id'           => function() {
					return ! empty( $this->data->get_image_id() ) ? $this->data->get_image_id() : null;
				},
				'attributes'         => function() {
					return ! empty( $this->data->get_attributes() ) ? array_values( $this->data->get_attributes() ) : null;
				},
				'default_attributes' => function() {
					return ! empty( $this->data->get_default_attributes() ) ? array_values( $this->data->get_default_attributes() ) : null;
				},
			);
		}

		parent::prepare_fields();
	}
}
