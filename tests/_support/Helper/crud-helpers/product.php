<?php

use GraphQLRelay\Relay;
use WPGraphQL\Type\WPEnumType;

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

	public static function get_stock_status_enum( $status ) {
		$statuses = array(
			'instock'     => 'IN_STOCK',
			'outofstock'  => 'OUT_OF_STOCK',
			'onbackorder' => 'ON_BACKORDER',
		);

		if ( in_array( $status, array_keys( $statuses ) ) ) {
			return $statuses[$status];
		}

		return null;
	}

	public function create_product_tag( $term ) {
		if ( term_exists( $term, 'product_tag' ) ) {
			$term = get_term( $term, 'product_tag', ARRAY_A );
		} else {
			$term = wp_insert_term( $term, 'product_tag' );
		}

		return ! empty( $term['term_id'] ) ? $term['term_id'] : null;
	}

	public function create_product_category( $term, $parent_id = 0 ) {
		if ( term_exists( $term, 'product_cat' ) ) {
			$term = get_term( $term, 'product_cat', ARRAY_A );
		} else {
			$args = [];
			if ( $parent_id ) {
				$args['parent'] = $parent_id;
			}
			$term = wp_insert_term( $term, 'product_cat', $args );
		}

		return ! empty( $term['term_id'] ) ? $term['term_id'] : null;
	}

	public function create_simple( $args = array() ) {
		$product       = new WC_Product_Simple();
		$name          = $this->dummy->product();
		$price         = $this->dummy->price( 15, 200 );
		$regular_price = $this->dummy->price( $price, $price + ( $price * 0.1 ) );

		$props = array_merge(
			array(
				'name'              => $name,
				'slug'              => $this->next_slug(),
				'regular_price'     => $regular_price,
				'price'             => $price,
				'sku'               => uniqid(),
				'manage_stock'      => false,
				'tax_status'        => 'taxable',
				'downloadable'      => false,
				'virtual'           => false,
				'stock_status'      => 'instock',
				'weight'            => '1.1',
				'description'       => '[shortcode_test]',
				'short_description' => $this->dummy->sentence(),
			),
			$args
		);

		foreach ( $props as $key => $value ) {
			if ( is_callable( array( $product, "set_{$key}" ) ) ) {
				$product->{"set_{$key}"}( $value );
			}
		}

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

	public function create_grouped( $args = array(), $children = array() ) {
		if ( empty( $children ) ) {
			$children = array( $this->create_simple() );
		}

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
		return array( 'parent' => $product->save(), 'children' => $children );
	}

	public function create_variable( $args = array() ) {
		$product = new WC_Product_Variable();
		$props = array_merge(
			array(
				'name' => $this->dummy->product(),
				'slug' => $this->next_slug(),
				'sku'  => 'DUMMY VARIABLE SKU ' . $this->index,
			),
			$args
		);

		foreach ( $props as $key => $value ) {
			if ( is_callable( array( $product, "set_{$key}" ) ) ) {
				$product->{"set_{$key}"}( $value );
			}
		}

		if ( ! empty( $args['meta_data'] ) ) {
			$product->set_meta_data( $args['meta_data'] );
		}

		// Create and add size attribute.
		$attribute_data = $this->create_attribute( 'size', array( 'small', 'medium', 'large' ) ); // Create all attribute related things.
		$attribute_1    = new WC_Product_Attribute();
		$attribute_1->set_id( $attribute_data['attribute_id'] );
		$attribute_1->set_name( $attribute_data['attribute_taxonomy'] );
		$attribute_1->set_options( $attribute_data['term_ids'] );
		$attribute_1->set_position( 1 );
		$attribute_1->set_visible( true );
		$attribute_1->set_variation( true );

		$attribute_data = $this->create_attribute( 'color', array( 'red' ) );
		$attribute_2    = new WC_Product_Attribute();
		$attribute_2->set_id( $attribute_data['attribute_id'] );
		$attribute_2->set_name( $attribute_data['attribute_taxonomy'] );
		$attribute_2->set_options( $attribute_data['term_ids'] );
		$attribute_2->set_position( 2 );
		$attribute_2->set_visible( true );
		$attribute_2->set_variation( false );

		$product->set_attributes( array( $attribute_1, $attribute_2 ) );
		$product->set_default_attributes( array( 'size' => 'small' ) );
		return $product->save();
	}

	public function create_related( $args = array() ) {
		$cross_sell_ids     = array(
			$this->create_simple(),
			$this->create_simple(),
		);
		$upsell_ids         = array(
			$this->create_simple(),
			$this->create_simple(),
		);
		$tag_ids            = array( $this->create_product_tag( 'related' ) );
		$related_product_id = $this->create_simple( array( 'tag_ids' => $tag_ids ) );

		return array(
			'product'    => $this->create_simple(
				array(
					'tag_ids'        => $tag_ids,
					'cross_sell_ids' => $cross_sell_ids,
					'upsell_ids'     => $upsell_ids,
				)
			),
			'related'   => array( $related_product_id ),
			'cross_sell' => $cross_sell_ids,
			'upsell'     => $upsell_ids,
		);
	}

	public function create_attribute( $raw_name = 'size', $terms = array( 'small' ) ) {
		global $wpdb, $wc_product_attributes;

		// Make sure caches are clean.
		delete_transient( 'wc_attribute_taxonomies' );
		WC_Cache_Helper::invalidate_cache_group( 'woocommerce-attributes' );

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

	public function create_download() {
		return self::createDownload( ...func_get_args() );
	}

	public static function createDownload( $id = 0 ) {
		$download = new WC_Product_Download();
		$download->set_id( wp_generate_uuid4() );
		$download->set_name( 'Test Name' );
		$download->set_file( 'http://example.com/file.jpg' );

		if ( $id ) {
			$product = \wc_get_product( $id );
			$product->set_downloads( array($download) );
			$product->save();
		}

		return $download;
	}

	public function print_query( $id, $raw = false ) {
		$data = wc_get_product( $id );
		$is_shop_manager = false;
		$user = wp_get_current_user();
		if ( $user && in_array( 'shop_manager', (array) $user->roles ) ) {
			$is_shop_manager = true;
		}

		return array(
			'id'                => $this->to_relay_id( $id ),
			'databaseId'        => $data->get_id(),
			'name'              => $data->get_name(),
			'slug'              => $data->get_slug(),
			'date'              => $data->get_date_created()->__toString(),
			'modified'          => $data->get_date_modified()->__toString(),
			'status'            => $data->get_status(),
			'featured'          => $data->get_featured(),
			'description'       => ! empty( $data->get_description() )
				? $raw
					? $data->get_description()
					: apply_filters( 'the_content', $data->get_description() )
				: null,
			'shortDescription'  => ! empty( $data->get_short_description() )
				? $raw
					? $data->get_short_description()
					: apply_filters(
						'get_the_excerpt',
						apply_filters( 'the_excerpt', $data->get_short_description() )
					)
				: null,
			'sku'               => $data->get_sku(),
			'price'             => ! empty( $data->get_price() )
				? \wc_graphql_price( $data->get_price() )
				: null,
			'regularPrice'      => ! empty( $data->get_regular_price() )
				? \wc_graphql_price( $data->get_regular_price() )
				: null,
			'salePrice'         => ! empty( $data->get_sale_price() )
				? \wc_graphql_price( $data->get_sale_price() )
				: null,
			'dateOnSaleFrom'    => $data->get_date_on_sale_from(),
			'dateOnSaleTo'      => $data->get_date_on_sale_to(),
			'taxStatus'         => strtoupper( $data->get_tax_status() ),
			'taxClass'          => ! empty( $data->get_tax_class() )
				? $data->get_tax_class()
				: 'STANDARD',
			'manageStock'       => $data->get_manage_stock(),
			'stockQuantity'     => $data->get_stock_quantity(),
			'stockStatus'       => self::get_stock_status_enum( $data->get_stock_status() ),
			'backorders'        => WPEnumType::get_safe_name( $data->get_backorders() ),
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
			'backordersAllowed' => $data->backorders_allowed(),
			'onSale'            => $data->is_on_sale(),
			'purchasable'       => $data->is_purchasable(),
			'shippingRequired'  => $data->needs_shipping(),
			'shippingTaxable'   => $data->is_shipping_taxable(),
			'link'              => get_post_permalink( $id ),
			'totalSales'        => $is_shop_manager ? $data->get_total_sales() : null,
			'catalogVisibility' => $is_shop_manager ? strtoupper( $data->get_catalog_visibility() ) :null,
		);
	}

	public function print_attributes( $id ) {
		$product    = wc_get_product( $id );
		$attributes = $product->get_attributes();

		$results = array();

		foreach( $attributes as $attribute_name => $attribute ) {
			$results[] = array(
				'id'          => base64_encode( $attribute_name . ':' . $id . ':' . $attribute->get_name() ),
				'attributeId' => $attribute->get_id(),
				'name'        => str_replace( 'pa_', '', $attribute->get_name() ),
				'label'       => $attribute->is_taxonomy()
					? ucwords( get_taxonomy( $attribute->get_name() )->labels->singular_name )
					: null,
				'options'     => $attribute->get_slugs(),
				'position'    => $attribute->get_position(),
				'visible'     => $attribute->get_visible(),
				'variation'   => $attribute->get_variation(),
			);
		}

		return ! empty ( $results ) ? array( 'nodes' => $results ) : null;
	}

	public function print_downloads( $id ) {
		$product    = wc_get_product( $id );
		$downloads  = (array) $product->get_downloads();
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

	public function print_grouped( $id ) {
		$data = wc_get_product( $id );
		$children = array( 'nodes' => array() );
		foreach ( $data->get_children() as $child ) {
			$parent_id = $this->field( $child, 'parent_id' );
			$children['nodes'][] = array(
				'id'     => $this->to_relay_id( $child ),
				'parent' => ! empty( $parent_id ) ? array( 'id' => $this->to_relay_id( $parent_id ) ) : null,
			);
		}

		return array(
			'addToCartText'        => ! empty( $data->add_to_cart_text() ) ? $data->add_to_cart_text() : null,
			'addToCartDescription' => ! empty( $data->add_to_cart_description() ) ? $data->add_to_cart_description() : null,
			'products'             => $children,
		);
	}

	public function print_external( $id ) {
		$data = wc_get_product( $id );

		return array(
			'id'          => $this->to_relay_id( $id ),
			'buttonText'  => ! empty( $data->get_button_text() ) ? $data->get_button_text() : null,
			'externalUrl' => ! empty( $data->get_product_url() ) ? $data->get_product_url() : null,
		);
	}

	public function field( $id, $field_name = 'id', $args = array() ) {
		$get = 'get_' . $field_name;
		$product = wc_get_product( $id );
		if ( ! empty( $product ) ) {
			return $product->{$get}( ...$args );
		}

		return null;
	}

	public function create_review( $product_id, $args = array() ) {
		$firstName = $this->dummy->firstname();
		$data = array_merge(
			array(
				'comment_post_ID'      => $product_id,
				'comment_author'       => $firstName,
				'comment_author_email' => "{$firstName}@example.com",
				'comment_author_url'   => '',
				'comment_content'      => $this->dummy->text(),
				'comment_approved'     => 1,
				'comment_type'         => 'review',
			),
			$args
		);

		$comment_id = wp_insert_comment( $data );

		$rating = ! empty( $args['rating'] ) ? $args['rating'] : $this->dummy->number( 0, 5 );
		update_comment_meta( $comment_id, 'rating', $rating );

		return $comment_id;
	}

	public function print_review_edges( $ids ) {
		if ( empty( $ids ) ) {
			return array();
		}

		$reviews = array();
		foreach ( $ids as $review_id ) {
			$reviews[] = array(
				'rating' => floatval( get_comment_meta( $review_id, 'rating', true ) ),
				'node'   => array(
					'id' => Relay::toGlobalId( 'comment', $review_id )
				),
			);
		}

		return $reviews;
	}
}
