<?php
/**
 * Defines helper functions for executing mutations related to products.
 *
 * @package WPGraphQL\WooCommerce\Data\Mutation
 * @since TBD
 */

namespace WPGraphQL\WooCommerce\Data\Mutation;

use GraphQL\Error\UserError;

/**
 * Class - Product_Mutation
 */
class Product_Mutation {
    /**
     * Save product shipping data
     *
     * @param \WC_Product $product  Product object.
     * @param array       $input    Mutation input.
     * 
     * @return \WC_Product
     */
    public static function save_product_shipping_data( $product, $input ) {
        // Virtual
        if ( isset( $input['virtual'] ) && true === $input['virtual'] ) {
            $product->set_weight( '' );
			$product->set_height( '' );
			$product->set_length( '' );
			$product->set_width( '' );
        } else {
            if ( isset( $input['weight'] ) ) {
                $product->set_weight( $input['weight'] );
            }

            if ( isset( $input['dimensions']['height'] ) ) {
                $product->set_height( $input['dimensions']['height'] );
            }

            if ( isset( $input['dimensions']['width'] ) ) {
                $product->set_width( $input['dimensions']['width'] );
            }

            if ( isset( $input['dimensions']['length'] ) ) {
                $product->set_length( $input['dimensions']['length'] );
            }
        }

        if ( isset( $input['shippingClass'] ) ) {
            $data_store        = $product->get_data_store();
			$shipping_class_id = $data_store->get_shipping_class_id_by_slug( wc_clean( $input['shippingClass'] ) );
			$product->set_shipping_class_id( $shipping_class_id );
        }

        return $product;
    }

    /**
     * Prepare product attribute
     *
     * @param array $attribute  Product attribute data.
     * 
     * @return \WC_Product_Attribute|null
     */
    public static function prepare_attribute( $attribute ) {
        $attribute_id   = 0;
        $attribute_name = '';

        if ( ! empty( $attribute['id'] ) ) {
            $attribute_id   = absint( $attribute['id'] );
            $attribute_name = wc_attribute_taxonomy_name_by_id( $attribute_id );
        } elseif ( ! empty( $attribute['name'] ) ) {
            $attribute_name = wc_clean( $attribute['name'] );
        }

        if ( ! $attribute_id && ! $attribute_name ) {
            return null;
        }

        if ( $attribute_id ) {
            if ( isset( $attribute['options'] ) ) {
                $options = $attribute['options'];

                if ( ! is_array( $options ) ) {
                    $options = explode( WC_DELIMITER, $options );
                }

                $values = array_map( 'wc_sanitize_term_text_based', $options );
                $values = array_filter( $values, 'strlen' );
            } else {
                $values = [];
            }

            if ( ! empty( $values ) ) {
                $attribute_object = new \WC_Product_Attribute();
                $attribute_object->set_id( $attribute_id );
                $attribute_object->set_name( $attribute_name );
                $attribute_object->set_options( $values );
                $attribute_object->set_position( isset( $attribute['position'] ) ? (string) absint( $attribute['position'] ) : '0' );
                $attribute_object->set_visible( ( isset( $attribute['visible'] ) && $attribute['visible'] ) ? 1 : 0 );
                $attribute_object->set_variation( ( isset( $attribute['variation'] ) && $attribute['variation'] ) ? 1 : 0 );

                return $attribute_object;
            }
        } elseif ( isset( $attribute['options'] ) ) {
            if ( is_array( $attribute['options'] ) ) {
                $values = $attribute['options'];
            } else {
                $values = explode( WC_DELIMITER, $attribute['options'] );
            }

            $attribute_object = new \WC_Product_Attribute();
            $attribute_object->set_name( $attribute_name );
            $attribute_object->set_options( $values );
            $attribute_object->set_position( isset( $attribute['position'] ) ? (string) absint( $attribute['position'] ) : '0' );
            $attribute_object->set_visible( ( isset( $attribute['visible'] ) && $attribute['visible'] ) ? 1 : 0 );
            $attribute_object->set_variation( ( isset( $attribute['variation'] ) && $attribute['variation'] ) ? 1 : 0 );

            return $attribute_object;
        }

        return null;
    }

    /**
     * Save product attributes
     *
	 * @param WC_Data $product  Product instance.
	 * @param array   $terms    Terms data.
	 * @param string  $taxonomy Taxonomy name.
     * 
     * @return \WC_Data
     */
    public static function save_taxonomy_terms( $product, $terms, $taxonomy = 'cat' ) {
        $term_ids = wp_list_pluck( $terms, 'id' );

		if ( 'cat' === $taxonomy ) {
			$product->set_category_ids( $term_ids );
		} elseif ( 'tag' === $taxonomy ) {
			$product->set_tag_ids( $term_ids );
		}
    }

    /**
     * Save product downloadable files
     *
	 * @param WC_Data $product    Product instance.
	 * @param array      $downloads  Downloads data.
     * 
     * @return \WC_Data
     */
    public static function save_downloadable_files( $product, $downloads ) {
        $files = array();
		foreach ( $downloads as $key => $file ) {
			if ( empty( $file['file'] ) ) {
				continue;
			}

			$download = new WC_Product_Download();
			$download->set_id( ! empty( $file['id'] ) ? $file['id'] : wp_generate_uuid4() );
			$download->set_name( $file['name'] ? $file['name'] : wc_get_filename_from_url( $file['file'] ) );
			$download->set_file( apply_filters( 'woocommerce_file_download_path', $file['file'], $product, $key ) );
			$files[] = $download;
		}
		$product->set_downloads( $files );

        return $product;
    }

    /**
     * Save variable product default attributes
     *
     * @param \WC_Data $product  Product object.
     * @param array       $input    Mutation input.
     * 
     * @return \WC_Data
     */
    public static function save_default_attributes( $product, $input ) {
        if ( ! empty( $input['defaultAttributes'] ) ) {

            $attributes         = $product->get_attributes();
            $default_attributes = [];

            foreach ( $input['defaultAttributes'] as $attribute ) {
                $attribue_id    = 0;
                $attribute_name = '';

                if ( ! empty( $attribute['id'] ) ) {
                    $attribute_id   = absint( $attribute['id'] );
                    $attribute_name = wc_attribute_taxonomy_name_by_id( $attribute_id );
                } elseif ( ! empty( $attribute['attributeName'] ) ) {
                    $attribute_name = sanitize_title( $attribute['attributeName'] );
                }

                if ( ! $attribute_id && ! $attribute_name ) {
                    continue;
                }

                if ( isset( $attributes[ $attribute_name ] ) ) {
                    $_attribute = $attributes[ $attribute_name ];

                    if ( $_attribute['is_variation'] ) {
                        $value = isset( $attribute['attributeValue'] ) ? wc_clean( stripslashes( $attribute['attributeValue'] ) ) : ''; 

                        if ( ! empty( $_attribute['is_taxonomy'] ) ) {
                            $term = get_term_by( 'name', $value, $attribute_name );

                            if ( $term && ! is_wp_error( $term ) ) {
                                $value = $term->slug;
                            } else {
                                $value = $sanitize_title( $value );
                            }
                        }

                        if ( $value ) {
                            $default_attributes[ $attribute_name ] = $value;
                        }
                    }
                }
            }

            $product->set_default_attributes( $default_attributes );
        }

        return $product;
    }

    /**
     * Set product images
     *
	 * @param WC_Product $product Product instance.
	 * @param array      $images  Images data.
     * 
     * @return \WC_Product
     */
    public static function set_product_images( $product, $images ) {
        $images = is_array( $images ) ? array_filter( $images ) : [];

        if ( ! empty( $images ) ) {
            $gallery_positions = [];

            foreach ( $images as $index => $image ) {
                $attachment_id = isset( $image['id'] ) ? absint( $image['id'] ) : 0;

                if ( 0 === $attachment_id && isset( $image['src'] ) ) {
                    $upload = \wc_rest_upload_image_from_url( $image['src'] );

                    if ( is_wp_error( $upload ) ) {
                        if ( ! apply_filters( 'woocommerce_rest_suppress_image_upload_error', false, $upload, $product->get_id(), $images ) ) {
                            throw new UserError( $upload->get_error_message() );
                        } else {
                            continue;
                        }
                    }

                    $attachment_id = \wc_rest_set_uploaded_image_as_attachment( $upload, $product->get_id() );
                }

                if ( ! wp_attachment_is_image( $attachment_id ) ) {
                    throw new UserError( 
                        sprintf( __( '#%s is an invalid image ID.', 'wp-graphql-woocommerce' ), $attachment_id )
                    );
                }

                $gallery_positions[ $attachment_id ] = absint( isset( $image['position'] ) ? $image['position'] : $index );

                // Set the image alt if present.
				if ( ! empty( $image['altText'] ) ) {
					update_post_meta( $attachment_id, '_wp_attachment_image_alt', wc_clean( $image['alt'] ) );
				}

				// Set the image name if present.
				if ( ! empty( $image['name'] ) ) {
					wp_update_post(
						[
							'ID'         => $attachment_id,
							'post_title' => $image['name'],
                        ]
					);
				}

				// Set the image source if present, for future reference.
				if ( ! empty( $image['src'] ) ) {
					update_post_meta( $attachment_id, '_wc_attachment_source', esc_url_raw( $image['src'] ) );
				}
            }

            // Sort images and get IDs in correct order.
			asort( $gallery_positions );

			// Get gallery in correct order.
			$gallery = array_keys( $gallery_positions );

			// Featured image is in position 0.
			$image_id = array_shift( $gallery );

			// Set images.
			$product->set_image_id( $image_id );
			$product->set_gallery_image_ids( $gallery );
        } else {
            $product->set_image_id( '' );
			$product->set_gallery_image_ids( [] );
        }

        return $product;
    }

    public static function get_attribute( $id ) {
        global $wpdb;

		$attribute = $wpdb->get_row(
			$wpdb->prepare(
				"
			SELECT *
			FROM {$wpdb->prefix}woocommerce_attribute_taxonomies
			WHERE attribute_id = %d
		 ",
				$id
			)
		);

		if ( is_wp_error( $attribute ) || is_null( $attribute ) ) {
			throw new UserError( __( 'Invalid attribute ID.', 'wp-graphql-woocommerce' ) );
		}

		return $attribute;
    }
}