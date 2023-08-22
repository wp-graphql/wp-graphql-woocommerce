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

/**
 * Class Product_Variation
 *
 * @property \WC_Product_Variation $wc_data
 *
 * @property int    $ID
 * @property string $id
 * @property string $name
 * @property string $date
 * @property string $modified
 * @property string $description
 * @property string $sku
 * @property string $price
 * @property float  $priceRaw
 * @property string $regularPrice
 * @property float  $regularPriceRaw
 * @property string $salePrice
 * @property float  $salePriceRaw
 * @property string $dateOnSaleFrom
 * @property string $dateOnSaleTo
 * @property bool   $onSale
 * @property string $status
 * @property bool   $purchasable
 * @property bool   $virtual
 * @property bool   $downloadable
 * @property array  $downloads
 * @property int    $downloadLimit
 * @property int    $downloadExpiry
 * @property string $taxStatus
 * @property string $taxClass
 * @property bool   $manageStock
 * @property int    $stockQuantity
 * @property string $stockStatus
 * @property string $backorders
 * @property bool   $backordersAllowed
 * @property float  $weight
 * @property float  $length
 * @property float  $width
 * @property float  $height
 * @property int    $menuOrder
 * @property string $purchaseNote
 * @property string $catalogVisibility
 * @property bool   $hasAttributes
 * @property string $type
 * @property int    $parent_id
 * @property string $parentId
 * @property int    $shipping_class_id
 * @property int    $image_id
 * @property array  $attributes
 *
 * @package WPGraphQL\WooCommerce\Model
 */
class Product_Variation extends WC_Post {
	/**
	 * Product_Variation constructor
	 *
	 * @param int|\WC_Data $id - product_variation post-type ID.
	 *
	 * @throws \Exception  If product variation cannot be retrieved.
	 */
	public function __construct( $id ) {
		$data = \wc_get_product( $id );

		// Check if product variation is valid.
		if ( ! is_object( $data ) ) {
			throw new \Exception( __( 'Failed to retrieve product variation data source', 'wp-graphql-woocommerce' ) );
		}

		parent::__construct( $data );
	}

	/**
	 * Initializes the ProductVariation field resolvers.
	 */
	protected function init() {
		if ( empty( $this->fields ) ) {
			parent::init();

			$fields = [
				'ID'                => function () {
					return ! empty( $this->wc_data->get_id() ) ? $this->wc_data->get_id() : null;
				},
				'id'                => function () {
					return ! empty( $this->ID ) ? Relay::toGlobalId( 'product_variation', "{$this->ID}" ) : null;
				},
				'name'              => function () {
					return ! empty( $this->wc_data->get_name() ) ? $this->wc_data->get_name() : null;
				},
				'date'              => function () {
					return ! empty( $this->wc_data ) ? $this->wc_data->get_date_created() : null;
				},
				'modified'          => function () {
					return ! empty( $this->wc_data ) ? $this->wc_data->get_date_modified() : null;
				},
				'description'       => function () {
					return ! empty( $this->wc_data->get_description() ) ? $this->wc_data->get_description() : null;
				},
				'sku'               => function () {
					return ! empty( $this->wc_data->get_sku() ) ? $this->wc_data->get_sku() : null;
				},
				'price'             => function () {
					return ! empty( $this->wc_data->get_price() )
						? wc_graphql_price( \wc_get_price_to_display( $this->wc_data, [ 'price' => $this->wc_data->get_price() ] ) )
						: null;
				},
				'regularPrice'      => function () {
					return ! empty( $this->wc_data->get_regular_price() )
						? wc_graphql_price( \wc_get_price_to_display( $this->wc_data, [ 'price' => $this->wc_data->get_regular_price() ] ) )
						: null;
				},
				'salePrice'         => function () {
					return ! empty( $this->wc_data->get_sale_price() )
						? wc_graphql_price( \wc_get_price_to_display( $this->wc_data, [ 'price' => $this->wc_data->get_sale_price() ] ) )
						: null;
				},
				'dateOnSaleFrom'    => function () {
					return ! empty( $this->wc_data->get_date_on_sale_from() ) ? $this->wc_data->get_date_on_sale_from() : null;
				},
				'dateOnSaleTo'      => function () {
					return ! empty( $this->wc_data->get_date_on_sale_to() ) ? $this->wc_data->get_date_on_sale_to() : null;
				},
				'onSale'            => function () {
					return $this->wc_data->is_on_sale();
				},
				'status'            => function () {
					return ! empty( $this->wc_data->get_status() ) ? $this->wc_data->get_status() : null;
				},
				'purchasable'       => function () {
					return ! empty( $this->wc_data->is_purchasable() ) ? $this->wc_data->is_purchasable() : null;
				},
				'virtual'           => function () {
					return $this->wc_data->is_virtual();
				},
				'downloadable'      => function () {
					return $this->wc_data->is_downloadable();
				},
				'downloads'         => function () {
					return ! empty( $this->wc_data->get_downloads() ) ? $this->wc_data->get_downloads() : null;
				},
				'downloadLimit'     => function () {
					return ! empty( $this->wc_data->get_download_limit() ) ? $this->wc_data->get_download_limit() : null;
				},
				'downloadExpiry'    => function () {
					return ! empty( $this->wc_data->get_download_expiry() ) ? $this->wc_data->get_download_expiry() : null;
				},
				'taxStatus'         => function () {
					return ! empty( $this->wc_data->get_tax_status() ) ? $this->wc_data->get_tax_status() : null;
				},
				'taxClass'          => function () {
					return $this->wc_data->get_tax_class();
				},
				'manageStock'       => function () {
					return ! empty( $this->wc_data->get_manage_stock() ) ? $this->wc_data->get_manage_stock() : null;
				},
				'stockQuantity'     => function () {
					return ! empty( $this->wc_data->get_stock_quantity() ) ? $this->wc_data->get_stock_quantity() : null;
				},
				'stockStatus'       => function () {
					return ! empty( $this->wc_data->get_stock_status() ) ? $this->wc_data->get_stock_status() : null;
				},
				'backorders'        => function () {
					return ! empty( $this->wc_data->get_backorders() ) ? $this->wc_data->get_backorders() : null;
				},
				'backordersAllowed' => function () {
					return $this->wc_data->backorders_allowed();
				},
				'weight'            => function () {
					return ! empty( $this->wc_data->get_weight() ) ? $this->wc_data->get_weight() : null;
				},
				'length'            => function () {
					return ! empty( $this->wc_data->get_length() ) ? $this->wc_data->get_length() : null;
				},
				'width'             => function () {
					return ! empty( $this->wc_data->get_width() ) ? $this->wc_data->get_width() : null;
				},
				'height'            => function () {
					return ! empty( $this->wc_data->get_height() ) ? $this->wc_data->get_height() : null;
				},
				'menuOrder'         => function () {
					return $this->wc_data->get_menu_order();
				},
				'purchaseNote'      => function () {
					return ! empty( $this->wc_data->get_purchase_note() ) ? $this->wc_data->get_purchase_note() : null;
				},
				'catalogVisibility' => function () {
					return ! empty( $this->wc_data->get_catalog_visibility() ) ? $this->wc_data->get_catalog_visibility() : null;
				},
				'hasAttributes'     => function () {
					return ! empty( $this->wc_data->has_attributes() ) ? $this->wc_data->has_attributes() : null;
				},
				'type'              => function () {
					return ! empty( $this->wc_data->get_type() ) ? $this->wc_data->get_type() : null;
				},
				'priceRaw'          => function () {
					return ! empty( $this->wc_data->get_price() ) ? $this->wc_data->get_price() : null;
				},
				'regularPriceRaw'   => function () {
					return ! empty( $this->wc_data->get_regular_price() ) ? $this->wc_data->get_regular_price() : null;
				},

				'salePriceRaw'      => function () {
					return ! empty( $this->wc_data->get_sale_price() ) ? $this->wc_data->get_sale_price() : null;
				},

				/**
				 * Connection resolvers fields
				 *
				 * These field resolvers are used in connection resolvers to define WP_Query argument.
				 * Note: underscore naming style is used as a quick identifier
				 */
				'parent_id'         => function () {
					return ! empty( $this->wc_data->get_parent_id() ) ? $this->wc_data->get_parent_id() : null;
				},
				'parentId'          => function () {
					return ! empty( $this->wc_data->get_parent_id() ) ? Relay::toGlobalId( 'product', (string) $this->wc_data->get_parent_id() ) : null;
				},
				'shipping_class_id' => function () {
					return ! empty( $this->wc_data->get_shipping_class_id() ) ? $this->wc_data->get_shipping_class_id() : null;
				},
				'image_id'          => function () {
					return ! empty( $this->wc_data->get_image_id() ) ? $this->wc_data->get_image_id() : null;
				},
				'attributes'        => function () {
					return ! empty( $this->wc_data->get_attributes() ) ? $this->wc_data->get_attributes() : null;
				},
			];

			$this->fields = array_merge( $this->fields, $fields );
		}//end if
	}
}
