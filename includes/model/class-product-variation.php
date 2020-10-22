<?php
/**
 * Model - Product_Variation
 *
 * Resolves product variation crud object model
 *
 * @package WPGraphQL\WooCommerce\Model
 * @since 0.0.1
 */

namespace WPGraphQL\WooCommerce\Model;

use GraphQLRelay\Relay;
use WC_Product_Variation;

/**
 * Class Product_Variation
 */
class Product_Variation extends WC_Post {

	/**
	 * Product_Variation constructor
	 *
	 * @param int $id - product_variation post-type ID.
	 */
	public function __construct( $id ) {
		$data = \wc_get_product( $id );
		parent::__construct( $data );
	}

	/**
	 * Initializes the ProductVariation field resolvers.
	 */
	protected function init() {
		if ( empty( $this->fields ) ) {
			parent::init();

			$fields = array(
				'id'                => function() {
					return ! empty( $this->wc_data->get_id() ) ? Relay::toGlobalId( 'product_variation', $this->wc_data->get_id() ) : null;
				},
				'name'              => function() {
					return ! empty( $this->wc_data->get_name() ) ? $this->wc_data->get_name() : null;
				},
				'date'              => function() {
					return ! empty( $this->wc_data ) ? $this->wc_data->get_date_created() : null;
				},
				'modified'          => function() {
					return ! empty( $this->wc_data ) ? $this->wc_data->get_date_modified() : null;
				},
				'description'       => function() {
					return ! empty( $this->wc_data->get_description() ) ? $this->wc_data->get_description() : null;
				},
				'sku'               => function() {
					return ! empty( $this->wc_data->get_sku() ) ? $this->wc_data->get_sku() : null;
				},
				'price'             => function() {
					return ! empty( $this->wc_data->get_price() )
						? \wc_graphql_price( $this->wc_data->get_price() )
						: null;
				},
				'regularPrice'      => function() {
					return ! empty( $this->wc_data->get_regular_price() ) ?
						\wc_graphql_price( $this->wc_data->get_regular_price() )
						: null;
				},
				'salePrice'         => function() {
					return ! empty( $this->wc_data->get_sale_price() )
						? \wc_graphql_price( $this->wc_data->get_sale_price() )
						: null;
				},
				'dateOnSaleFrom'    => function() {
					return ! empty( $this->wc_data->get_date_on_sale_from() ) ? $this->wc_data->get_date_on_sale_from() : null;
				},
				'dateOnSaleTo'      => function() {
					return ! empty( $this->wc_data->get_date_on_sale_to() ) ? $this->wc_data->get_date_on_sale_to() : null;
				},
				'onSale'            => function () {
					return ! is_null( $this->wc_data->is_on_sale() ) ? $this->wc_data->is_on_sale() : null;
				},
				'status'            => function() {
					return ! empty( $this->wc_data->get_status() ) ? $this->wc_data->get_status() : null;
				},
				'purchasable'       => function() {
					return ! empty( $this->wc_data->is_purchasable() ) ? $this->wc_data->is_purchasable() : null;
				},
				'virtual'           => function() {
					return ! is_null( $this->wc_data->is_virtual() ) ? $this->wc_data->is_virtual() : null;
				},
				'downloadable'      => function() {
					return ! is_null( $this->wc_data->is_downloadable() ) ? $this->wc_data->is_downloadable() : null;
				},
				'downloads'         => function() {
					return ! empty( $this->wc_data->get_downloads() ) ? $this->wc_data->get_downloads() : null;
				},
				'downloadLimit'     => function() {
					return ! is_null( $this->wc_data->get_download_limit() ) ? $this->wc_data->get_download_limit() : null;
				},
				'downloadExpiry'    => function() {
					return ! is_null( $this->wc_data->get_download_expiry() ) ? $this->wc_data->get_download_expiry() : null;
				},
				'taxStatus'         => function() {
					return ! empty( $this->wc_data->get_tax_status() ) ? $this->wc_data->get_tax_status() : null;
				},
				'taxClass'          => function() {
					return ! is_null( $this->wc_data->get_tax_class() ) ? $this->wc_data->get_tax_class() : '';
				},
				'manageStock'       => function() {
					return ! empty( $this->wc_data->get_manage_stock() ) ? $this->wc_data->get_manage_stock() : null;
				},
				'stockQuantity'     => function() {
					return ! empty( $this->wc_data->get_stock_quantity() ) ? $this->wc_data->get_stock_quantity() : null;
				},
				'stockStatus'       => function() {
					return ! empty( $this->wc_data->get_stock_status() ) ? $this->wc_data->get_stock_status() : null;
				},
				'backorders'        => function() {
					return ! empty( $this->wc_data->get_backorders() ) ? $this->wc_data->get_backorders() : null;
				},
				'backordersAllowed' => function() {
					return ! is_null( $this->wc_data->backorders_allowed() ) ? $this->wc_data->backorders_allowed() : null;
				},
				'weight'            => function() {
					return ! empty( $this->wc_data->get_weight() ) ? $this->wc_data->get_weight() : null;
				},
				'length'            => function() {
					return ! empty( $this->wc_data->get_length() ) ? $this->wc_data->get_length() : null;
				},
				'width'             => function() {
					return ! empty( $this->wc_data->get_width() ) ? $this->wc_data->get_width() : null;
				},
				'height'            => function() {
					return ! empty( $this->wc_data->get_height() ) ? $this->wc_data->get_height() : null;
				},
				'menuOrder'         => function() {
					return ! is_null( $this->wc_data->get_menu_order() ) ? $this->wc_data->get_menu_order() : null;
				},
				'purchaseNote'      => function() {
					return ! empty( $this->wc_data->get_purchase_note() ) ? $this->wc_data->get_purchase_note() : null;
				},
				'catalogVisibility' => function() {
					return ! empty( $this->wc_data->get_catalog_visibility() ) ? $this->wc_data->get_catalog_visibility() : null;
				},
				'hasAttributes'     => function() {
					return ! empty( $this->wc_data->has_attributes() ) ? $this->wc_data->has_attributes() : null;
				},
				'type'              => function() {
					return ! empty( $this->wc_data->get_type() ) ? $this->wc_data->get_type() : null;
				},

				/**
				 * Editor/Shop Manager only fields
				 */
				'priceRaw'          => array(
					'callback'   => function() {
						return ! empty( $this->wc_data->get_price() ) ? $this->wc_data->get_price() : null;
					},
					'capability' => $this->post_type_object->cap->edit_posts,
				),
				'regularPriceRaw'   => array(
					'callback'   => function() {
						return ! empty( $this->wc_data->get_regular_price() ) ? $this->wc_data->get_regular_price() : null;
					},
					'capability' => $this->post_type_object->cap->edit_posts,
				),
				'salePriceRaw'      => array(
					'callback'   => function() {
						return ! empty( $this->wc_data->get_sale_price() ) ? $this->wc_data->get_sale_price() : null;
					},
					'capability' => $this->post_type_object->cap->edit_posts,
				),

				/**
				 * Connection resolvers fields
				 *
				 * These field resolvers are used in connection resolvers to define WP_Query argument.
				 * Note: underscore naming style is used as a quick identifier
				 */
				'parent_id'         => function() {
					return ! empty( $this->wc_data->get_parent_id() ) ? $this->wc_data->get_parent_id() : null;
				},
				'parentId'          => function() {
					return ! empty( $this->wc_data->get_parent_id() ) ? Relay::toGlobalId( 'product', $this->wc_data->get_parent_id() ) : null;
				},
				'shipping_class_id' => function() {
					return ! empty( $this->wc_data->get_shipping_class_id() ) ? $this->wc_data->get_shipping_class_id() : null;
				},
				'image_id'          => function() {
					return ! empty( $this->wc_data->get_image_id() ) ? $this->wc_data->get_image_id() : null;
				},
				'attributes'        => function() {
					return ! empty( $this->wc_data->get_attributes() ) ? $this->wc_data->get_attributes() : null;
				},
			);

			$this->fields = array_merge( $this->fields, $fields );
		}
	}
}
