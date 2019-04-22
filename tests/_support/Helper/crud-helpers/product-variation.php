<?php

use GraphQLRelay\Relay;
use WPGraphQL\Type\WPEnumType;

class ProductVariationHelper extends WCG_Helper {
    private $index;

    protected function __construct() {
        $this->index = 1;

        parent::__construct();
    }

    public function reset_indexes() {
        $this->index = 1;
    }

    private function next_slug() {
        $slug = 'test-variation-' . absint( $this->index );
        $this->index += 1;
        return $slug;
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
            
			// Degister taxonomy which other tests may have created...
            unregister_taxonomy( $taxonomy_name );
            
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
				$return['term_ids'][] = $result['term_id'];
			} else {
				$return['term_ids'][] = $result['term_id'];
			}
        }

		return $return;
	}

    public function create( $product_id, $args = array() ) {
        $product = new WC_Product_Variable( $product_id );

        // Create and add size attribute.
		$attribute_data = $this->create_attribute( 'size', array( 'small', 'medium', 'large' ) ); // Create all attribute related things.
		$attribute      = new WC_Product_Attribute();
		$attribute->set_id( $attribute_data['attribute_id'] );
		$attribute->set_name( $attribute_data['attribute_taxonomy'] );
		$attribute->set_options( $attribute_data['term_ids'] );
		$attribute->set_position( 1 );
		$attribute->set_visible( true );
        $attribute->set_variation( true );
		$product->set_attributes( array( $attribute ) );
        
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
            'product'    => $product->save(),
        );
    }

    public function print_query( $id ) {
        $data = new WC_Product_Variation( $id );

        if( empty( $data ) ) {
            return null;
        }

        return array(
            'id'                => Relay::toGlobalId( 'product_variation', $id ),
            'variationId'       => $data->get_id(),
            'sku'               => $data->get_sku(),
            'weight'            => ! empty( $data->get_weight() ) ? $data->get_weight() : null,
            'length'            => ! empty( $data->get_length() ) ? $data->get_length() : null,
            'width'             => ! empty( $data->get_width() ) ? $data->get_width() : null,
            'height'            => ! empty( $data->get_height() ) ? $data->get_height() : null,
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
            'purchaseNote'      => ! empty( $data->get_purchase_note() ) ? $data->get_purchase_note() : null,
            'shippingClass'     => ! empty( $data->get_shipping_class() ) ? $data->get_shipping_class() : null,
            'catalogVisibility' => ! empty( $data->get_catalog_visibility() )
                ? WPEnumType::get_safe_name( $data->get_catalog_visibility() )
                : null,
            'hasAttributes'      => ! empty( $data->has_attributes() ) ? $data->has_attributes() : null,
            'isPurchasable'     => ! empty( $data->is_purchasable() ) ? $data->is_purchasable() : null,
            'price'             => ! empty( $data->get_price() ) ? $data->get_price() : null,
            'regularPrice'      => ! empty( $data->get_regular_price() ) ? $data->get_regular_price() : null,
            'salePrice'         => ! empty( $data->get_sale_price() ) ? $data->get_sale_price() : null,
        );
    }

    public function print_nodes( $ids, $mapper = null ) {
        if ( empty( $mapper ) ) {
            $mapper = function( $variation_id ) {
                return array( 'id' => Relay::toGlobalId( 'product_variation', $variation_id ) ); 
            };
        }

        return array_reverse( array_values( array_map( $mapper, $ids ) ) );
    }
}