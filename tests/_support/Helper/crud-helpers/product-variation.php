<?php

use GraphQLRelay\Relay;
use WPGraphQL\Type\WPEnumType;

class ProductVariationHelper extends WCG_Helper {
	private $index;

	protected function __construct() {
		$this->index     = 1;
		$this->node_type = 'product_variation';

		parent::__construct();
	}

	public function to_relay_id( $id ) {
		return Relay::toGlobalId( 'product_variation', $id );
	}

	public function reset_indexes() {
		$this->index = 1;
	}

	private function next_slug() {
		$slug = 'test-variation-' . absint( $this->index );
		$this->index += 1;
		return $slug;
	}

	public function create( $product_id, $args = array() ) {
		// Create small size variation with download
		$variation_1 = new WC_Product_Variation();
		$variation_1->set_props(
			array(
				'parent_id'     => $product_id,
				'slug'          => $this->next_slug(),
				'sku'           => 'DUMMY SKU VARIABLE SMALL',
				'regular_price' => 10,
				'downloads'     => array( ProductHelper::createDownload() ),
			)
		);
		$variation_1->set_attributes( array( 'pa_size' => 'small' ) );
		if ( ! empty( $args['meta_data'] ) ) {
			$variation_1->set_meta_data( $args['meta_data'] );
		}

		// Create medium size variation with image
		$image_id = \wp_insert_post(
			array(
				'post_author'  => 1,
				'post_content' => '',
				'post_excerpt' => '',
				'post_status'  => 'publish',
				'post_title'   => 'Product Image',
				'post_type'    => 'attachment',
				'post_content' => 'product image',
			)
		);
		$variation_2 = new WC_Product_Variation();
		$variation_2->set_props(
			array(
				'parent_id'     => $product_id,
				'slug'          => $this->next_slug(),
				'sku'           => 'DUMMY SKU VARIABLE MEDIUM',
				'regular_price' => 15,
				'image_id'      => $image_id,
			)
		);
		$variation_2->set_attributes( array( 'pa_size' => 'medium' ) );
		if ( ! empty( $args['meta_data'] ) ) {
			$variation_2->set_meta_data( $args['meta_data'] );
		}

		// Create large size variation
		$variation_3 = new WC_Product_Variation();
		$variation_3->set_props(
			array(
				'parent_id'     => $product_id,
				'slug'          => $this->next_slug(),
				'sku'           => 'DUMMY SKU VARIABLE LARGE',
				'regular_price' => 20,
			)
		);
		$variation_3->set_attributes( array( 'pa_size' => 'large' ) );
		if ( ! empty( $args['meta_data'] ) ) {
			$variation_3->set_meta_data( $args['meta_data'] );
		}

		return array(
			'variations' => array(
				$variation_1->save(),
				$variation_2->save(),
				$variation_3->save(),
			),
			'product'    => $product_id,
		);
	}

	public function print_query( $id ) {
		$data = new WC_Product_Variation( $id );

		if( empty( $data ) ) {
			return null;
		}

		return array(
			'id'                => $this->to_relay_id( $id ),
			'databaseId'        => $data->get_id(),
			'name'              => $data->get_name(),
			'date'              => $data->get_date_created()->__toString(),
			'modified'          => ! empty( $data->get_date_created() )
				? $data->get_date_created()->__toString()
				: null,
			'description'       => ! empty( $data->get_description() ) ? $data->get_description() : null,
			'sku'               => $data->get_sku(),
			'price'             => ! empty( $data->get_price() ) ? \wc_graphql_price( $data->get_price() ) : null,
			'regularPrice'      => ! empty( $data->get_regular_price() ) ? \wc_graphql_price( $data->get_regular_price() ) : null,
			'salePrice'         => ! empty( $data->get_sale_price() ) ? \wc_graphql_price( $data->get_sale_price() ) : null,
			'dateOnSaleFrom'    => ! empty( $data->get_date_on_sale_from() )
				? $data->get_date_on_sale_from()
				: null,
			'dateOnSaleTo'      => ! empty( $data->get_date_on_sale_to() )
				? $data->get_date_on_sale_to()
				: null,
			'onSale'            => $data->is_on_sale(),
			'status'            => $data->get_status(),
			'purchasable'       => ! empty( $data->is_purchasable() ) ? $data->is_purchasable() : null,
			'virtual'           => $data->is_virtual(),
			'downloadable'      => $data->is_downloadable(),
			'downloadLimit'     => ! empty( $data->get_download_limit() ) ? $data->get_download_limit() : null,
			'downloadExpiry'    => ! empty( $data->get_download_expiry() ) ? $data->get_download_expiry() : null,
			'taxStatus'         => strtoupper( $data->get_tax_status() ),
			'taxClass'          => ! empty( $data->get_tax_class() )
				? WPEnumType::get_safe_name( $data->get_tax_class() )
				: 'STANDARD',
			'manageStock'       => ! empty( $data->get_manage_stock() )
				? WPEnumType::get_safe_name( $data->get_manage_stock() )
				: null,
			'stockQuantity'     => ! empty( $data->get_stock_quantity() ) ? $data->get_stock_quantity() : null,
			'stockStatus'       => ProductHelper::get_stock_status_enum( $data->get_stock_status() ),
			'backorders'        => ! empty( $data->get_backorders() )
				? WPEnumType::get_safe_name( $data->get_backorders() )
				: null,
			'backordersAllowed' => $data->backorders_allowed(),
			'weight'            => ! empty( $data->get_weight() ) ? $data->get_weight() : null,
			'length'            => ! empty( $data->get_length() ) ? $data->get_length() : null,
			'width'             => ! empty( $data->get_width() ) ? $data->get_width() : null,
			'height'            => ! empty( $data->get_height() ) ? $data->get_height() : null,
			'menuOrder'         => $data->get_menu_order(),
			'purchaseNote'      => ! empty( $data->get_purchase_note() ) ? $data->get_purchase_note() : null,
			'shippingClass'     => ! empty( $data->get_shipping_class() ) ? $data->get_shipping_class() : null,
			'catalogVisibility' => ! empty( $data->get_catalog_visibility() )
				? WPEnumType::get_safe_name( $data->get_catalog_visibility() )
				: null,
			'hasAttributes'     => ! empty( $data->has_attributes() ) ? $data->has_attributes() : null,
			'type'              => WPEnumType::get_safe_name( $data->get_type() ),
			'parent'            => array (
				'node' => array(
					'id' => Relay::toGlobalId( 'product', $data->get_parent_id() ),
				),
			),
		);
	}

	public function print_attributes( $id, $from = 'VARIATION' ) {
		if ( 'PRODUCT' === $from ) {
			$product    = wc_get_product( $id );
			$attributes = $product->get_default_attributes();
		} else {
			$product    = new \WC_Product_Variation( $id );
			$attributes = $product->get_attributes();
		}

		$results = array();

		foreach( $attributes as $name => $value ) {
			$term   = get_term_by( 'slug', $value, $name );
			$results[] = array(
				'id'          => base64_encode( $product->get_id() . '||' . $name . '||' . $value ),
				'attributeId' => ! empty( $term ) ? $term->term_id : 0,
				'name'        => $name,
				'value'       => $value,
			);
		}

		return ! empty ( $results ) ? array( 'nodes' => $results ) : null;
	}

	public function field( $id, $field_name = 'id' ) {
		$get = 'get_' . $field_name;
		$variation = new WC_Product_Variation( $id );
		if ( ! empty( $variation ) ) {
			return $variation->{$get}();
		}

		return null;
	}

	public function print_downloads( $id ) {
		$variation = new WC_Product_Variation( $id );
		$downloads = (array) $variation->get_downloads();
		if ( empty( $downloads ) ) {
			return null;
		}

		$results = array();
		foreach ( $downloads as $download ) {
			$results[] = array(
				'name'            => $download->get_name(),
				'downloadId'      => $download->get_id(),
				'filePathType'    => $download->get_type_of_file_path(),
				'fileType'        => $download->get_file_type(),
				'fileExt'         => $download->get_file_extension(),
				'allowedFileType' => $download->is_allowed_filetype(),
				'fileExists'      => $download->file_exists(),
				'file'            => $download->get_file(),
			);
		}

		return $results;
	}
}
