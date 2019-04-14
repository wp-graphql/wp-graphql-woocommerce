<?php

class ProductHelper {
    private $index;
    private $variation_index;

    public function __construct() {
        $this->index = 1;
        $this->variation_index = 1;
    }

    public function reset_indexes() {
        $this->index = 1;
        $this->variation_index = 1;
    }

    private function next_slug() {
        $slug = 'test-product-' . absint( $this->index );
        $this->index += 1;
        return $slug;
    }

    private function next_variation_slug() {
        $slug = 'test-product-variation-' . absint( $this->variation_index );
        $this->variation_index += 1;
        return $slug;
    }

    public function create_simple( $args = array() ) {
        $product = new WC_Product_Simple();
        codecept_debug( $this->index );
        $product->set_props(
            array_merge(
                array(
                    'name'          => 'Dummy Product',
                    'slug'          => $this->next_slug(),
                    'regular_price' => 20,
                    'price'         => 20,
                    'sku'           => 'DUMMY SKU',
                    'manage_stock'  => false,
                    'tax_status'    => 'taxable',
                    'downloadable'  => false,
                    'virtual'       => false,
                    'stock_status'  => 'instock',
                    'weight'        => '1.1',
                ),
                $args
            )
        );
		return $product->save();
    }

    public function create_external( $args = array() ) {
        $product = new WC_Product_External();
        $product->set_props(
            array_merge(
                array(
                    'name'          => 'Dummy External Product',
                    'slug'          => $this->next_slug(),
                    'regular_price' => 10,
                    'sku'           => 'DUMMY EXTERNAL SKU',
                    'product_url'   => 'http://woocommerce.com',
                    'button_text'   => 'Buy external product',
                ),
                $args
            )
        );
		return $product->save();
    }

	public function create_grouped( $args = array() ) {
        $children = array(
            $this->create_simple(),
            $this->create_simple(),
        );
		$product          = new WC_Product_Grouped();
		$product->set_props(
            array_merge(
                array(
                    'name'          => 'Dummy Grouped Product',
                    'slug'          => $this->next_slug(),
                    'sku'           => 'DUMMY GROUPED SKU',
                ),
                $args
            )
		);
		$product->set_children( $children );
		return array( 'product' => $product->save(), 'children' => $children );
    }
    
    public function create_variation() {
		$product = new WC_Product_Variable();
		$product->set_props(
			array(
                'name' => 'Dummy Variable Product',
                'slug' => $this->next_slug(),
				'sku'  => 'DUMMY VARIABLE SKU',
			)
        );

		$attribute_data = self::create_attribute( 'size', array( 'small', 'large' ) ); // Create all attribute related things.
		$attributes     = array();
		$attribute      = new WC_Product_Attribute();
		$attribute->set_id( $attribute_data['attribute_id'] );
		$attribute->set_name( $attribute_data['attribute_taxonomy'] );
		$attribute->set_options( $attribute_data['term_ids'] );
		$attribute->set_position( 1 );
		$attribute->set_visible( true );
		$attribute->set_variation( true );
		$attributes[] = $attribute;
		$product->set_attributes( $attributes );
        $product_id = $product->save();
        
		$variation_1 = new WC_Product_Variation();
		$variation_1->set_props(
			array(
                'parent_id'     => $product_id,
                'slug'          => $this->next_variation_slug(),
				'sku'           => 'DUMMY SKU VARIABLE SMALL',
				'regular_price' => 10,
			)
		);
        $variation_1->set_attributes( array( 'pa_size' => 'small' ) );
        
		$variation_2 = new WC_Product_Variation();
		$variation_2->set_props(
			array(
                'parent_id'     => $product_id,
                'slug'          => $this->next_variation_slug(),
				'sku'           => 'DUMMY SKU VARIABLE LARGE',
				'regular_price' => 15,
			)
		);
		$variation_2->set_attributes( array( 'pa_size' => 'large' ) );

		return array( 'product' => $product->save(), 'variations' => array( $variation_1->save(), $variation_2->save() ) );
	}

    public function get_query_data( $id ) {
        $data = wc_get_product( $id );

        return array(
            'productId'         => $data->get_id(),
            'name'              => $data->get_name(),
            'slug'              => $data->get_slug(),
            'date'              => $data->get_date_created()->__toString(),
            'modified'          => $data->get_date_modified()->__toString(),
            'status'            => $data->get_status(),
            'featured'          => $data->get_featured(),
            'catalogVisibility' => strtoupper( $data->get_catalog_visibility() ),
            'description'       => ! empty( $data->get_description() )
                ? $data->get_description()
                : null,
            'shortDescription'  => ! empty( $data->get_short_description() )
            ? $data->get_short_description()
            : null,
            'sku'               => $data->get_sku(),
            'price'             => ! empty( $data->get_price() )
                ? $data->get_price()
                : null,
            'regularPrice'      => ! empty( $data->get_regular_price() )
                ? $data->get_regular_price()
                : null,
            'salePrice'         => ! empty( $data->get_sale_price() )
                ? $data->get_sale_price()
                : null,
            'dateOnSaleFrom'    => $data->get_date_on_sale_from(),
            'dateOnSaleTo'      => $data->get_date_on_sale_to(),
            'totalSales'        => $data->get_total_sales(),
            'taxStatus'         => strtoupper( $data->get_tax_status() ),
            'taxClass'          => ! empty( $data->get_tax_class() )
                ? $data->get_tax_class()
                : null,
            'manageStock'       => $data->get_manage_stock(),
            'stockQuantity'     => $data->get_stock_quantity(),
            'soldIndividually'  => $data->get_sold_individually(),
            'weight'            => $data->get_weight(),
            'length'            => $data->get_length(),
            'width'             => $data->get_width(),
            'height'            => $data->get_height(),
            'reviewsAllowed'    => $data->get_reviews_allowed(),
            'purchaseNote'      => ! empty( $data->get_purchase_note() )
                ? $data->get_purchase_note()
                : null,
            'menuOrder'         => $data->get_menu_order(),
            'virtual'           => $data->get_virtual(),
            'downloadable'      => $data->get_downloadable(),
            'downloadLimit'     => $data->get_download_limit(),
            'downloadExpiry'    => $data->get_download_expiry(),
            'averageRating'     => (float) $data->get_average_rating(),
            'reviewCount'       => $data->get_review_count(),
        );
    }

    public function get_all_query_data( $ids ) {
        
    }
}