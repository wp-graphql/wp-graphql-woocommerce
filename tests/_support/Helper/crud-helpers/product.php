<?php

class ProductHelper {
    public function create() {
		$product = new WC_Product();
		$product->set_name( 'Test Product' );
		$product->set_slug( 'test-product' );
		$product->set_description( 'lorem ipsum dolor' );
		$product->set_sku( 'wp-pennant' );
		$product->set_price( 11.05 );
		$product->set_weight( .2 );
		return $product->save();
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