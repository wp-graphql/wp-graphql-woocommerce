<?php
/**
 * Mutation - createProductVariation
 *
 * Registers mutation for creating a product variation.
 *
 * @package WPGraphQL\WooCommerce\Mutation
 * @since 1.0.0
 */

namespace WPGraphQL\WooCommerce\Mutation;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Data\Mutation\Product_Mutation;
use WPGraphQL\WooCommerce\Model\Product_Variation;

/**
 * Class Product_Variation_Create
 */
class Product_Variation_Create {
	/**
	 * Registers mutation
	 *
	 * @return void
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'createProductVariation',
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
			'productId'      => [
				'type'        => [ 'non_null' => 'ID' ],
				'description' => static function () {
					return __( 'Unique identifier for the product.', 'graphql-for-ecommerce' );
				},
			],
			'description'    => [
				'type'        => 'String',
				'description' => static function () {
					return __( 'Description of the product variation.', 'graphql-for-ecommerce' );
				},
			],
			'sku'            => [
				'type'        => 'String',
				'description' => static function () {
					return __( 'Unique identifier.', 'graphql-for-ecommerce' );
				},
			],
			'regularPrice'   => [
				'type'        => 'Float',
				'description' => static function () {
					return __( 'Regular price of the product variation.', 'graphql-for-ecommerce' );
				},
			],
			'salePrice'      => [
				'type'        => 'Float',
				'description' => static function () {
					return __( 'Sale price of the product variation.', 'graphql-for-ecommerce' );
				},
			],
			'dateOnSaleFrom' => [
				'type'        => 'String',
				'description' => static function () {
					return __( 'Start date of sale price.', 'graphql-for-ecommerce' );
				},
			],
			'dateOnSaleTo'   => [
				'type'        => 'String',
				'description' => static function () {
					return __( 'End date of sale price.', 'graphql-for-ecommerce' );
				},
			],
			'visible'        => [
				'type'        => 'boolean',
				'description' => static function () {
					return __( 'Is product variation public?', 'graphql-for-ecommerce' );
				},
			],
			'virtual'        => [
				'type'        => 'Boolean',
				'description' => static function () {
					return __( 'Whether the product variation is virtual.', 'graphql-for-ecommerce' );
				},
			],
			'downloadable'   => [
				'type'        => 'Boolean',
				'description' => static function () {
					return __( 'Whether the product variation is downloadable.', 'graphql-for-ecommerce' );
				},
			],
			'downloads'      => [
				'type'        => [ 'list_of' => 'ProductDownloadInput' ],
				'description' => static function () {
					return __( 'Downloadable files.', 'graphql-for-ecommerce' );
				},
			],
			'downloadLimit'  => [
				'type'        => 'Int',
				'description' => static function () {
					return __( 'Number of times downloadable files can be downloaded.', 'graphql-for-ecommerce' );
				},
			],
			'downloadExpiry' => [
				'type'        => 'Int',
				'description' => static function () {
					return __( 'Number of days until the download expires.', 'graphql-for-ecommerce' );
				},
			],
			'taxStatus'      => [
				'type'        => 'TaxStatusEnum',
				'description' => static function () {
					return __( 'Tax status of the product variation.', 'graphql-for-ecommerce' );
				},
			],
			'taxClass'       => [
				'type'        => 'String',
				'description' => static function () {
					return __( 'Tax class of the product variation.', 'graphql-for-ecommerce' );
				},
			],
			'manageStock'    => [
				'type'        => 'String',
				'description' => static function () {
					return __( 'Whether to manage stock. Either "yes", "no", or "parent".', 'graphql-for-ecommerce' );
				},
			],
			'stockQuantity'  => [
				'type'        => 'Int',
				'description' => static function () {
					return __( 'Stock quantity.', 'graphql-for-ecommerce' );
				},
			],
			'stockStatus'    => [
				'type'        => 'StockStatusEnum',
				'description' => static function () {
					return __( 'Stock status of the product variation.', 'graphql-for-ecommerce' );
				},
			],
			'backorders'     => [
				'type'        => 'BackordersEnum',
				'description' => static function () {
					return __( 'Backorder status.', 'graphql-for-ecommerce' );
				},
			],
			'weight'         => [
				'type'        => 'String',
				'description' => static function () {
					return __( 'Weight of the product variation.', 'graphql-for-ecommerce' );
				},
			],
			'dimensions'     => [
				'type'        => 'ProductDimensionsInput',
				'description' => static function () {
					return __( 'Dimensions of the product variation.', 'graphql-for-ecommerce' );
				},
			],
			'shippingClass'  => [
				'type'        => 'String',
				'description' => static function () {
					return __( 'Shipping class of the product variation.', 'graphql-for-ecommerce' );
				},
			],
			'image'          => [
				'type'        => 'ProductImageInput',
				'description' => static function () {
					return __( 'Image of the product variation.', 'graphql-for-ecommerce' );
				},
			],
			'attributes'     => [
				'type'        => [ 'list_of' => 'ProductAttributeInput' ],
				'description' => static function () {
					return __( 'Attributes of the product variation.', 'graphql-for-ecommerce' );
				},
			],
			'menuOrder'      => [
				'type'        => 'Int',
				'description' => static function () {
					return __( 'Menu order of the product variation.', 'graphql-for-ecommerce' );
				},
			],
			'metaData'       => [
				'type'        => [ 'list_of' => 'MetaDataInput' ],
				'description' => static function () {
					return __( 'Meta data of the product variation.', 'graphql-for-ecommerce' );
				},
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
			'variation' => [
				'type'    => 'ProductVariation',
				'resolve' => static function ( $payload ) {
					return new Product_Variation( $payload['id'] );
				},
			],
		];
	}

	/**
	 * Defines the mutation data modification closure.
	 *
	 * @param array                                $input    Mutation input.
	 * @param \WPGraphQL\AppContext                $context  AppContext instance.
	 * @param \GraphQL\Type\Definition\ResolveInfo $info     ResolveInfo instance. Can be
	 * use to get info about the current node in the GraphQL tree.
	 *
	 * @throws \GraphQL\Error\UserError Invalid ID provided | Lack of capabilities.
	 *
	 * @return array
	 */
	public static function mutate_and_get_payload( $input, AppContext $context, ResolveInfo $info ) {
		if ( ! empty( $input['id'] ) ) {
			/**
			 * @var \WC_Product_Variation $variation
			 */
			$variation = \wc_get_product( $input['id'] );
		} else {
			$variation = new \WC_Product_Variation();
		}

		if ( 0 === $variation->get_parent_id() ) {
			$variation->set_parent_id( $input['productId'] );
		}

		if ( isset( $input['visible'] ) ) {
			$variation->set_status( false === $input['visible'] ? 'private' : 'publish' );
		}

		if ( ! empty( $input['sku'] ) ) {
			/**
			 * @var string $sku
			 */
			$sku = wc_clean( $input['sku'] );
			$variation->set_sku( $sku );
		}

		if ( ! empty( $input['image'] ) ) {
			$image             = $input['image'];
			$image['position'] = 0;

			$variation = Product_Mutation::set_product_images( $variation, [ $image ] );
		} else {
			$variation->set_image_id( '' );
		}

		if ( isset( $input['virtual'] ) ) {
			$variation->set_virtual( $input['virtual'] );
		}

		if ( isset( $input['downloadable'] ) ) {
			$variation->set_downloadable( $input['downloadable'] );
		}

		if ( $variation->get_downloadable() ) {
			if ( ! empty( $input['downloads'] ) ) {
				$variation = Product_Mutation::save_downloadable_files( $variation, $input['downloads'] );
			}

			if ( ! empty( $input['downloadLimit'] ) ) {
				$variation->set_download_limit( $input['downloadLimit'] );
			}

			if ( ! empty( $input['downloadExpiry'] ) ) {
				$variation->set_download_expiry( $input['downloadExpiry'] );
			}
		}

		$variation = Product_Mutation::save_product_shipping_data( $variation, $input );

		if ( isset( $input['manageStock'] ) ) {
			if ( 'parent' === $input['manageStock'] ) {
				$variation->set_manage_stock( false );
			} else {
				$variation->set_manage_stock( wc_string_to_bool( $input['manageStock'] ) );
			}
		}

		if ( isset( $input['stockStatus'] ) ) {
			$variation->set_stock_status( $input['stockStatus'] );
		}

		if ( isset( $input['backorders'] ) ) {
			$variation->set_backorders( $input['backorders'] );
		}

		if ( $variation->get_manage_stock() ) {
			if ( isset( $input['stockQuantity'] ) ) {
				$variation->set_stock_quantity( $input['stockQuantity'] );
			}
		} else {
			$variation->set_backorders( 'no' );
			$variation->set_stock_quantity( null );
		}

		if ( isset( $input['regularPrice'] ) ) {
			$variation->set_regular_price( $input['regularPrice'] );
		}

		if ( isset( $input['salePrice'] ) ) {
			$variation->set_sale_price( $input['salePrice'] );
		}

		if ( isset( $input['dateOnSaleFrom'] ) ) {
			$variation->set_date_on_sale_from( $input['dateOnSaleFrom'] );
		}

		if ( isset( $input['dateOnSaleTo'] ) ) {
			$variation->set_date_on_sale_to( $input['dateOnSaleTo'] );
		}

		if ( isset( $input['taxClass'] ) ) {
			$variation->set_tax_class( $input['taxClass'] );
		}

		if ( isset( $input['description'] ) ) {
			$variation->set_description( $input['description'] );
		}

		if ( ! empty( $input['attributes'] ) ) {
			$attributes = [];
			$parent     = wc_get_product( $variation->get_parent_id() );
			if ( ! $parent ) {
				throw new UserError( __( 'Parent ID invalid', 'graphql-for-ecommerce' ) );
			}
			$parent_attributes = $parent->get_attributes();

			foreach ( $input['attributes'] as $attribute ) {
				/**
				 * Attribute ID.
				 *
				 * @var int $attribute_id
				 */
				$attribute_id = 0;
				/**
				 * Attribute name.
				 *
				 * @var string $attribute_name
				 */
				$attribute_name = '';

				// Check ID for global attributes or name for product attributes.
				$raw_attribute_name = null;
				if ( ! empty( $attribute['id'] ) ) {
					$attribute_id       = absint( $attribute['id'] );
					$raw_attribute_name = wc_attribute_taxonomy_name_by_id( $attribute_id );
				} elseif ( ! empty( $attribute['attributeName'] ) ) {
					$raw_attribute_name = sanitize_title( $attribute['attributeName'] );
				}

				if ( ! $raw_attribute_name ) {
					continue;
				}

				$attribute_name = sanitize_title( $raw_attribute_name );

				if ( ! isset( $parent_attributes[ $attribute_name ] ) || ! $parent_attributes[ $attribute_name ]->get_variation() ) {
					continue;
				}

				$attribute_key = sanitize_title( $parent_attributes[ $attribute_name ]->get_name() );
				/**
				 * @var string $attribute_value
				 */
				$attribute_value = isset( $attribute['attributeValue'] ) ? wc_clean( stripslashes( $attribute['attributeValue'] ) ) : '';

				if ( $parent_attributes[ $attribute_name ]->is_taxonomy() ) {
					// If dealing with a taxonomy, we need to get the slug from the name posted to the API.
                    $term = get_term_by( 'name', $attribute_value, $raw_attribute_name ); // @codingStandardsIgnoreLine

					if ( ! empty( $term ) ) {
						$attribute_value = $term->slug;
					} else {
						$attribute_value = sanitize_title( $attribute_value );
					}
				}

				$attributes[ $attribute_key ] = $attribute_value;
			}

			$variation->set_attributes( $attributes );
		}

		if ( ! empty( $input['menuOrder'] ) ) {
			$variation->set_menu_order( $input['menuOrder'] );
		}

		if ( ! empty( $input['metaData'] ) ) {
			foreach ( $input['metaData'] as $meta ) {
				$variation->update_meta_data( $meta['key'], $meta['value'], isset( $meta['id'] ) ? $meta['id'] : '' );
			}
		}

		$variation_id = $variation->save();

		return [ 'id' => $variation_id ];
	}
}
