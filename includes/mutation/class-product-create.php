<?php
/**
 * Mutation - createProduct
 *
 * Registers mutation for creating a product.
 *
 * @package WPGraphQL\WooCommerce\Mutation
 * @since TBD
 */

namespace WPGraphQL\WooCommerce\Mutation;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Data\Mutation\Product_Mutation;
use WPGraphQL\WooCommerce\Model\Product;

/**
 * Class Product_Create
 */
class Product_Create {
	/**
	 * Registers mutation
	 *
	 * @return void
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'createProduct',
			[
				'inputFields'         => self::get_input_fields(),
				'outputFields'        => self::get_output_fields(),
				'mutateAndGetPayload' => [ self::class, 'mutate_and_get_payload' ],
			]
		);
	}

    /**
	 * Defines the mutation input field configuration
	 *
	 * @return array
	 */
	public static function get_input_fields() {
		return [
            'name'              => [
                'type'        => [ 'non_null' => 'String' ],
                'description' => __( 'Name of the product.', 'wp-graphql-woocommerce' ),
            ],
            'slug'              => [
                'type'        => 'String',
                'description' => __( 'Product slug.', 'wp-graphql-woocommerce' ),
            ],
            'type'              => [
                'type'        => 'ProductTypesEnum',
                'description' => __( 'Type of the product.', 'wp-graphql-woocommerce' ),
            ],
            'status'            => [
                'type'        => 'PostStatusEnum',
                'description' => __( 'Status of the product.', 'wp-graphql-woocommerce' ),
            ],
            'featured'          => [
                'type'        => 'Boolean',
                'description' => __( 'Featured product.', 'wp-graphql-woocommerce' ),
            ],
            'catalogVisibility' => [
                'type'        => 'CatalogVisibilityEnum',
                'description' => __( 'Catalog visibility.', 'wp-graphql-woocommerce' ),
            ],
            'description'       => [
                'type'        => 'String',
                'description' => __( 'Product description.', 'wp-graphql-woocommerce' ),
            ],
            'shortDescription'  => [
                'type'        => 'String',
                'description' => __( 'Product short description.', 'wp-graphql-woocommerce' ),
            ],
            'sku'               => [
                'type'        => 'String',
                'description' => __( 'Product SKU.', 'wp-graphql-woocommerce' ),
            ],
            'regularPrice'      => [
                'type'        => 'Float',
                'description' => __( 'Product regular price.', 'wp-graphql-woocommerce' ),
            ],
            'salePrice'         => [
                'type'        => 'Float',
                'description' => __( 'Product sale price.', 'wp-graphql-woocommerce' ),
            ],
            'dateOnSaleFrom'    => [
                'type'        => 'String',
                'description' => __( 'Product sale start date.', 'wp-graphql-woocommerce' ),
            ],
            'dateOnSaleTo'      => [
                'type'        => 'String',
                'description' => __( 'Product sale end date.', 'wp-graphql-woocommerce' ),
            ],
            'virtual'           => [
                'type'        => 'Boolean',
                'description' => __( 'Product virtual.', 'wp-graphql-woocommerce' ),
            ],
            'downloadable'      => [
                'type'        => 'Boolean',
                'description' => __( 'Product downloadable.', 'wp-graphql-woocommerce' ),
            ],
            'downloads'         => [
                'type'        =>  [ 'list_of' => 'ProductDownloadInput' ],
                'description' => __( 'Product downloads.', 'wp-graphql-woocommerce' ),
            ],
            'downloadLimit'     => [
                'type'        => 'Int',
                'description' => __( 'Product download limit.', 'wp-graphql-woocommerce' ),
            ],
            'downloadExpiry'    => [
                'type'        => 'Int',
                'description' => __( 'Number of days until download access expires.', 'wp-graphql-woocommerce' ),
            ],
            'externalUrl'       => [
                'type'        => 'String',
                'description' => __( 'Product external URL. (External products only)', 'wp-graphql-woocommerce' ),
            ],
            'buttonText'        => [
                'type'        => 'String',
                'description' => __( 'Product button text. (External products only)', 'wp-graphql-woocommerce' ),
            ],
            'taxStatus'         => [
                'type'        => 'TaxStatusEnum',
                'description' => __( 'Tax status.', 'wp-graphql-woocommerce' ),
            ],
            'taxClass'          => [
                'type'        => 'String',
                'description' => __( 'Tax class.', 'wp-graphql-woocommerce' ),
            ],
            'manageStock'       => [
                'type'        => 'Boolean',
                'description' => __( 'Manage stock.', 'wp-graphql-woocommerce' ),
            ],
            'stockQuantity'     => [
                'type'        => 'Int',
                'description' => __( 'Stock quantity.', 'wp-graphql-woocommerce' ),
            ],
            'stockStatus'       => [
                'type'        => 'StockStatusEnum',
                'description' => __( 'Stock status.', 'wp-graphql-woocommerce' ),
            ],
            'backorders'        => [
                'type'        => 'BackordersEnum',
                'description' => __( 'Backorders.', 'wp-graphql-woocommerce' ),
            ],
            'soldIndividually'  => [
                'type'        => 'Boolean',
                'description' => __( 'Sold individually.', 'wp-graphql-woocommerce' ),
            ],
            'weight'            => [
                'type'        => 'String',
                'description' => __( 'Product weight.', 'wp-graphql-woocommerce' ),
            ],
            'dimensions'        => [
                'type'        => 'ProductDimensionsInput',
                'description' => __( 'Product dimensions.', 'wp-graphql-woocommerce' ),
            ],
            'shippingClass'     => [
                'type'        => 'String',
                'description' => __( 'Shipping class.', 'wp-graphql-woocommerce' ),
            ],
            'reviewsAllowed'    => [
                'type'        => 'Boolean',
                'description' => __( 'Allow reviews. Default is true', 'wp-graphql-woocommerce' ),
            ],
            'upsellIds'         => [
                'type'        => [ 'list_of' => 'Int' ],
                'description' => __( 'Upsell product IDs.', 'wp-graphql-woocommerce' ),
            ],
            'crossSellIds'      => [
                'type'        => [ 'list_of' => 'Int' ],
                'description' => __( 'Cross-sell product IDs.', 'wp-graphql-woocommerce' ),
            ],
            'parentId'          => [
                'type'        => 'Int',
                'description' => __( 'Parent product ID.', 'wp-graphql-woocommerce' ),
            ],
            'purchaseNote'      => [
                'type'        => 'String',
                'description' => __( 'Purchase note.', 'wp-graphql-woocommerce' ),
            ],
            'categories'        => [
                'type'        => [ 'list_of' => 'Int' ],
                'description' => __( 'Product categories.', 'wp-graphql-woocommerce' ),
            ],
            'tags'              => [
                'type'        => [ 'list_of' => 'Int' ],
                'description' => __( 'Product tags.', 'wp-graphql-woocommerce' ),
            ],
            'images'            => [
                'type'        => [ 'list_of' => 'ProductImageInput' ],
                'description' => __( 'Product images.', 'wp-graphql-woocommerce' ),
            ],
            'attributes'        => [
                'type'        => [ 'list_of' => 'ProductAttributesInput' ],
                'description' => __( 'Product attributes.', 'wp-graphql-woocommerce' ),
            ],
            'defaultAttributes' => [
                'type'        => [ 'list_of' => 'ProductAttributeInput' ],
                'description' => __( 'Product default attributes.', 'wp-graphql-woocommerce' ),
            ],
            'variations'        => [
                'type'        => [ 'list_of' => 'ProductVariationInput' ],
                'description' => __( 'Product variations.', 'wp-graphql-woocommerce' ),
            ],
            'groupedProducts'   => [
                'type'        => [ 'list_of' => 'Int' ],
                'description' => __( 'Grouped product IDs.', 'wp-graphql-woocommerce' ),
            ],
            'menuOrder'         => [
                'type'        => 'Int',
                'description' => __( 'Menu order.', 'wp-graphql-woocommerce' ),
            ],
            'metaData'          => [
                'type'        => [ 'list_of' => 'MetaDataInput' ],
                'description' => __( 'Meta data.', 'wp-graphql-woocommerce' ),
            ],
        ];
    }

    /**
	 * Defines the mutation output field configuration
	 *
	 * @return array
	 */
	public static function get_output_fields() {
		return [
			'product'   => [
				'type'    => 'Product',
				'resolve' => static function ( $payload ) {
					return new Product( $payload['id'] );
				},
			],
			'productId' => [
				'type'    => 'Int',
				'resolve' => static function ( $payload ) {
					return $payload['id'];
				},
			],
		];
	}

    /**
	 * Defines the mutation data modification closure.
	 *
	 * @return callable
	 */
	public static function mutate_and_get_payload( $input, AppContext $context, ResolveInfo $info ) {        
        $product_id = ! empty( $input['id'] ) ? $input['id'] : 0;
        $type       = ! empty( $input['type'] ) ? $input['type'] : 'simple';

        if ( 0 !== $product_id ) {
            $product = \wc_get_product( $product_id );
            if ( $product && ! wc_rest_check_post_permissions( 'product', 'edit', $product->get_id() ) ) {
                throw new UserError( __( 'You do not have permission to edit this product', 'wp-graphql-woocommerce' ) );
            }
        } else {
            $classname = \WC_Product_Factory::get_classname_from_product_type( $type );
            if ( ! class_exists( $classname ) ) {
                $classname = '\WC_Product_Simple';
            }

            $product = new $classname( $product_id );

            $post_type_object = get_post_type_object( 'product' );
            if ( ! current_user_can( $post_type_object->cap->edit_posts ) ) {
                throw new UserError( __( 'You do not have permission to create products', 'wp-graphql-woocommerce' ) );
            }
        }

        if ( ! empty( $input['name'] ) ) {
            $product->set_name( wp_filter_post_kses( $input['name'] ) );
        }

        if ( ! empty( $input['description'] ) ) {
            $product->set_description( wp_filter_post_kses( $input['description'] ) );
        }

        if ( ! empty( $input['shortDescription'] ) ) {
            $product->set_short_description( wp_filter_post_kses( $input['shortDescription'] ) );
        }

        if ( ! empty( $input['status'] ) ) {
            $product->set_status( get_post_status_object( $request['status'] ) ? $request['status'] : 'draft' );
        }

        if ( ! empty( $input['slug'] ) ) {
            $product->set_slug( $input['slug'] );
        }

        if ( ! empty( $input['menuOrder'] ) ) {
            $product->set_menu_order( $input['menuOrder'] );
        }

        if ( isset( $input['reviewsAllowed'] ) ) {
            $product->set_reviews_allowed( $input['reviewsAllowed'] );
        }

        if ( isset( $input['virtual'] ) ) {
            $product->set_virtual( $input['virtual'] );
        }

        if ( ! empty( $input['taxStatus'] ) ) {
            $product->set_tax_status( $input['taxStatus'] );
        }

        if ( ! empty( $input['taxClass'] ) ) {
            $product->set_tax_class( $input['taxClass'] );
        }

        if ( ! empty( $input['catalogVisibility'] ) ) {
            $product->set_catalog_visibility( $input['catalogVisibility'] );
        }

        if ( ! empty( $input['purchaseNote'] ) ) {
            $product->set_purchase_note( wp_filter_post_kses( $input['purchaseNote'] ) );
        }

        if ( isset( $input['featured'] ) ) {
            $product->set_featured( $input['featured'] );
        }

        $product = Product_Mutation::save_product_shipping_data( $product, $input );

        if ( ! empty( $input['sku'] ) ) {
            $product->set_sku( wc_clean( $input['sku'] ) );
        }

        if ( ! empty( $input['attributes'] ) ) {
            $attributes = [];

            foreach ( $input['attributes'] as $attribute ) {
                $attribute_object = Product_Mutation::prepare_attribute( $attribute );
                if ( $attribute_object ) {
                    $attributes[] = $attribute_object;
                }
            }

            $product->set_attributes( $attributes );
        }

        if ( in_array( $type, [ 'variable', 'grouped' ], true ) ) {
            $product->set_regular_price( '' );
            $product->set_sale_price( '' );
            $product->set_date_on_sale_to( '' );
            $product->set_date_on_sale_from( '' );
            $product->set_price( '' );
        } else {
            if ( ! empty( $input['regularPrice'] ) ) {
                $product->set_regular_price( $input['regularPrice'] );
            }

            if ( ! empty( $input['salePrice'] ) ) {
                $product->set_sale_price( $input['salePrice'] );
            }

            if ( ! empty( $input['dateOnSaleFrom'] ) ) {
                $product->set_date_on_sale_from( $input['dateOnSaleFrom'] );
            }

            if ( ! empty( $input['dateOnSaleTo'] ) ) {
                $product->set_date_on_sale_to( $input['dateOnSaleTo'] );
            }
        }

        if ( ! empty( $input['parentId'] ) ) {
            $product->set_parent_id( $input['parentId'] );
        }

        if ( isset( $input['soldIndividually'] ) ) {
            $product->set_sold_individually( $input['soldIndividually'] );
        }

        if ( isset( $input['stockStatus'] ) ) {
            $stock_status = wc_clean( $input['stockStatus'] );
        } else {
            $stock_status = $product->get_stock_status();
        }

        if ( 'yes' === get_option( 'woocommerce_manage_stock' ) ) {
            if ( isset( $input['manageStock'] ) ) {
                $product->set_manage_stock( $input['manageStock'] );
            }


            if ( isset( $input['backorders'] ) ) {
                $product->set_backorders( $input['backorders'] );
            }

            if ( $product->is_type( 'grouped' ) ) {
                $product->set_manage_stock( 'no' );
                $product->set_backorders( 'no' );
                $product->set_stock_quantity( '' );
                $product->set_stock_status( $stock_status );
            } elseif ( $product->is_type( 'external' ) ) {
                $product->set_manage_stock( 'no' );
                $product->set_backorders( 'no' );
                $product->set_stock_quantity( '' );
                $product->set_stock_status( 'instock' );
            } elseif ( $product->get_manage_stock() ) {
                // Stock status is always determined by children so sync later.
                if ( ! $product->is_type( 'variable' ) ) {
                    $product->set_stock_status( $stock_status );
                }

                // Stock quantity.
                if ( isset( $input['stockQuantity'] ) ) {
                    $product->set_stock_quantity( wc_stock_amount( $input['stockQuantity'] ) );
                }
            } else {
                // Don't manage stock.
                $product->set_manage_stock( 'no' );
                $product->set_stock_quantity( '' );
                $product->set_stock_status( $stock_status );
            }
        } elseif ( $product->is_type( 'variable' ) ) {
            $product->set_stock_status( $stock_status );
        }

        if ( ! empty( $input['upsellIds'] ) ) {
            $product->set_upsell_ids( $input['upsellIds'] );
        }

        if ( ! empty( $input['crossSellIds'] ) ) {
            $product->set_cross_sell_ids( $input['crossSellIds'] );
        }

        if ( ! empty( $input['categories'] ) ) {
            $product = Product_Mutation::save_taxonomy_terms( $product, $input['categories'] );
        }

        if ( ! empty( $input['tags'] ) ) {
            $product = Product_Mutation::save_taxonomy_terms( $product, $input['tags'], 'tag' );
        }

        if ( isset( $input['downloadable'] ) ) {
            $product->set_downloadable( $input['downloadable'] );
        }

        if ( $product->get_downloadable() ) {
            if ( ! empty( $input['downloads'] ) ) {
                $product = Product_Mutation::save_downloadable_files( $product, $input['downloads'] );
            }

            if ( isset( $input['downloadLimit'] ) ) {
                $product->set_download_limit( $input['downloadLimit'] );
            }

            if ( isset( $input['downloadExpiry'] ) ) {
                $product->set_download_expiry( $input['downloadExpiry'] );
            }
        }

        if ( $product->is_type( 'external' ) ) {
            if ( ! empty( $input['externalUrl'] ) ) {
                $product->set_product_url( $input['externalUrl'] );
            }

            if ( ! empty( $input['buttonText'] ) ) {
                $product->set_button_text( $input['buttonText'] );
            }
        }

        if ( $product->is_type( 'variable' ) ) {
            $product = Product_Mutation::save_default_attributes( $product, $input );
        }

        if ( $product->is_type( 'grouped' ) && isset( $input['groupedProducts'] ) ) {
            $product->set_children( $input['groupedProducts'] );
        }

        if ( ! empty( $input['images'] ) ) {
            $product = Product_Mutation::set_product_images( $product, $input['images'] );
        }

        if ( ! empty( $input['metaData'] ) ) {
            foreach( $input['metaData'] as $meta ) {
                $product->update_meta_data( $meta['key'], $meta['value'], isset( $meta['id'] ) ? $meta['id'] : '' );
            }
        }

        /**
         * Filters an object before it is inserted via the GraphQL API.
         *
         * The dynamic portion of the hook name, `$this->post_type`,
         * refers to the object type slug.
         *
         * @param WC_Data  $product   Object object.
         * @param array    $input     GraphQL input object.
         * @param bool     $creating  If is creating a new object.
         */
        $product = apply_filters( 'graphql_woocommerce_pre_insert_product_object', $product, $input, true ); 

        $product_id = $product->save();

        return [ 'id' => $product_id ];
    }
}
