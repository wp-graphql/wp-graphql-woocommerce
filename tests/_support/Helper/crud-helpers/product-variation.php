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
		// Create small size variation
		$variation_1 = new WC_Product_Variation();
		$variation_1->set_props(
			array(
				'parent_id'     => $product_id,
				'slug'          => $this->next_slug(),
				'sku'           => 'DUMMY SKU VARIABLE SMALL',
				'regular_price' => 10,
			)
		);
		$variation_1->set_attributes( array( 'pa_size' => 'small' ) );

		// Create medium size variation
		$variation_2 = new WC_Product_Variation();
		$variation_2->set_props(
			array(
				'parent_id'     => $product_id,
				'slug'          => $this->next_slug(),
				'sku'           => 'DUMMY SKU VARIABLE MEDIUM',
				'regular_price' => 15,
			)
		);
		$variation_2->set_attributes( array( 'pa_size' => 'medium' ) );

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
			'variationId'       => $data->get_id(),
			'name'              => $data->get_name(),
			'date'              => $data->get_date_created()->__toString(),
			'modified'          => ! empty( $data->get_date_created() )
				? $data->get_date_created()->__toString()
				: null,
			'description'       => ! empty( $data->get_description() ) ? $data->get_description() : null,
			'sku'               => $data->get_sku(),
			'price'             => ! empty( $data->get_price() ) ? $data->get_price() : null,
			'regularPrice'      => ! empty( $data->get_regular_price() ) ? $data->get_regular_price() : null,
			'salePrice'         => ! empty( $data->get_sale_price() ) ? $data->get_sale_price() : null,
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
				'id'    => ! empty( $term ) ? $term->term_id : 0,
				'name'  => $name,
				'value' => $value,
			);
		}

		return ! empty ( $results ) ? array( 'nodes' => $results ) : null;
	}
}