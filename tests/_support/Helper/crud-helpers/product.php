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

	public function create_attribute( $raw_name = 'size', $terms = array( 'small' ) ) {
		global $wpdb, $wc_product_attributes;

		// Make sure caches are clean.
		delete_transient( 'wc_attribute_taxonomies' );
		WC_Cache_Helper::incr_cache_prefix( 'woocommerce-attributes' );

		// These are exported as labels, so convert the label to a name if possible first.
		$attribute_labels = wp_list_pluck( wc_get_attribute_taxonomies(), 'attribute_label', 'attribute_name' );
		$attribute_name   = array_search( $raw_name, $attribute_labels, true );

		if ( ! $attribute_name ) {
			$attribute_name = wc_sanitize_taxonomy_name( $raw_name );
		}

		$attribute_id = wc_attribute_taxonomy_id_by_name( $attribute_name );

		if ( ! $attribute_id ) {
			$taxonomy_name = wc_attribute_taxonomy_name( $attribute_name );

			$attribute_id = wc_create_attribute(
				array(
					'name'         => $raw_name,
					'slug'         => $attribute_name,
					'type'         => 'select',
					'order_by'     => 'menu_order',
					'has_archives' => 0,
				)
			);

			// Register as taxonomy.
			register_taxonomy(
				$taxonomy_name,
				apply_filters( 'woocommerce_taxonomy_objects_' . $taxonomy_name, array( 'product' ) ),
				apply_filters(
					'woocommerce_taxonomy_args_' . $taxonomy_name,
					array(
						'labels'       => array(
							'name' => $raw_name,
						),
						'hierarchical' => false,
						'show_ui'      => false,
						'query_var'    => true,
						'rewrite'      => false,
					)
				)
			);

			// Set product attributes global.
			$wc_product_attributes = array();

			foreach ( wc_get_attribute_taxonomies() as $taxonomy ) {
				$wc_product_attributes[ wc_attribute_taxonomy_name( $taxonomy->attribute_name ) ] = $taxonomy;
			}
		}

		$attribute = wc_get_attribute( $attribute_id );
		$return    = array(
			'attribute_name'     => $attribute->name,
			'attribute_taxonomy' => $attribute->slug,
			'attribute_id'       => $attribute_id,
			'term_ids'           => array(),
		);

		foreach ( $terms as $term ) {
			$result = term_exists( $term, $attribute->slug );

			if ( ! $result ) {
				$result = wp_insert_term( $term, $attribute->slug );
				$return['term_ids'][] = absint( $result['term_id'] );
			} else {
				$return['term_ids'][] = absint( $result['term_id'] );
			}
		}

		return $return;
	}

	public function create_variable( $args = array() ) {
		$product = new WC_Product_Variable();
		$product->set_props(
			array_merge(
				array(
					'name' => $this->dummy->product(),
					'slug' => $this->next_slug(),
					'sku'  => 'DUMMY VARIABLE SKU ' . $this->index,
				),
				$args
			)
		);

		// Create and add size attribute.
		$attribute_data = $this->create_attribute( 'size', array( 'small', 'medium', 'large' ) ); // Create all attribute related things.
		$attribute_1    = new WC_Product_Attribute();
		$attribute_1->set_id( $attribute_data['attribute_id'] );
		$attribute_1->set_name( $attribute_data['attribute_taxonomy'] );
		$attribute_1->set_options( $attribute_data['term_ids'] );
		$attribute_1->set_position( 1 );
		$attribute_1->set_visible( true );
		$attribute_1->set_variation( true );

		$attribute_data = $this->create_attribute( 'color', array( 'red', 'blue', 'green' ) );
		$attribute_2    = new WC_Product_Attribute();
		$attribute_2->set_id( $attribute_data['attribute_id'] );
		$attribute_2->set_name( $attribute_data['attribute_taxonomy'] );
		$attribute_2->set_options( $attribute_data['term_ids'] );
		$attribute_2->set_position( 2 );
		$attribute_2->set_visible( true );
		$attribute_2->set_variation( true );

		$product->set_attributes( array( $attribute_1, $attribute_2 ) );
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

	public function print_attributes( $id ) {
		$product    = wc_get_product( $id );
		$attributes = array_values( $product->get_attributes() );

		$results = array();

		foreach( $attributes as $attribute ) {
			$results[] = array(
				'attributeId' => $attribute->get_id(),
				'name'        => $attribute->get_name(),
				'options'     => $attribute->get_slugs(),
				'position'    => $attribute->get_position(),
				'visible'     => $attribute->get_visible(),
				'variation'   => $attribute->get_variation(),
			);
		}

		return ! empty ( $results ) ? array( 'nodes' => $results ) : null;
	}
}