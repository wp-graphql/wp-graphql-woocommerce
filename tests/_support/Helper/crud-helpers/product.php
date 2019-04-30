<?php

use GraphQLRelay\Relay;

class ProductHelper extends WCG_Helper {
	private $index;

	protected function __construct() {
		$this->index     = 1;
		$this->node_type = 'product';

		parent::__construct();
	}

	public function to_relay_id( $id ) {
		return Relay::toGlobalId( 'product', $id );
	}

	public function reset_indexes() {
		$this->index = 1;
	}

	private function next_slug() {
		$slug = 'test-product-' . absint( $this->index );
		$this->index += 1;
		return $slug;
	}

	public function create_simple( $args = array() ) {
		$product       = new WC_Product_Simple();
		$name          = $this->dummy->product();
		$price         = $this->dummy->price( 15, 200 );
		$regular_price = $this->dummy->price( $price, $price + ( $price * 0.1 ) );

		$product->set_props(
			array_merge(
				array(
					'name'          => $name,
					'slug'          => $this->next_slug(),
					'regular_price' => $regular_price,
					'price'         => $price,
					'sku'           => 'DUMMY SKU '.$this->index,
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
		$product       = new WC_Product_External();
		$name          = $this->dummy->product();
		$price         = $this->dummy->price( 15, 200 );
		$product->set_props(
			array_merge(
				array(
					'name'          => $product,
					'slug'          => $this->next_slug(),
					'regular_price' => $price,
					'sku'           => 'DUMMY EXTERNAL SKU ' . $this->index,
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
					'name' => 'Dummy Grouped Product',
					'slug' => $this->next_slug(),
					'sku'  => 'DUMMY GROUPED SKU ' . $this->index,
				),
				$args
			)
		);
		$product->set_children( $children );
		return array( 'product' => $product->save(), 'children' => $children );
	}

	public function create_variable( $args = array() ) {
		$product = new WC_Product_Variable();
		$product->set_props(
			array_merge(
				array(
					'name' => 'Dummy Variable Product',
					'slug' => $this->next_slug(),
					'sku'  => 'DUMMY VARIABLE SKU ' . $this->index,
				),
				$args
			)
		);

		return $product->save();
	}

	public function print_query( $id ) {
		$data = wc_get_product( $id );

		return array(
			'id'                => $this->to_relay_id( $id ),
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
				: 'STANDARD',
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
}