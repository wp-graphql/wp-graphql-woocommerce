<?php
/**
 * Model - Product
 *
 * Resolves product crud object model
 *
 * @package WPGraphQL\Extensions\WooCommerce\Model
 * @since 0.0.1
 */

namespace WPGraphQL\Extensions\WooCommerce\Model;

use GraphQLRelay\Relay;

/**
 * Class Product
 */
class Product extends Crud_CPT {
	/**
	 * Stores the product type: external, grouped, simple, variable
	 *
	 * @var string $product_type
	 * @access protected
	 */
	protected $product_type;

	/**
	 * Product constructor
	 *
	 * @param int $id - product post-type ID.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct( $id ) {
		$this->product_type        = \WC()->product_factory->get_product_type( $id );
		$this->data                = $this->get_object( $id );
		$allowed_restricted_fields = [
			'isRestricted',
			'isPrivate',
			'isPublic',
			'id',
			'productId',
		];

		parent::__construct( $allowed_restricted_fields, 'product', $id );
	}

	/**
	 * Retrieve the cap to check if the data should be restricted for the coupon
	 *
	 * @access protected
	 * @return string
	 */
	protected function get_restricted_cap() {
		if ( post_password_required( $this->data->get_id() ) ) {
			return $this->post_type_object->cap->edit_others_posts;
		}
		switch ( get_post_status( $this->data->get_id() ) ) {
			case 'trash':
				$cap = $this->post_type_object->cap->edit_posts;
				break;
			case 'draft':
				$cap = $this->post_type_object->cap->edit_others_posts;
				break;
			default:
				$cap = '';
				break;
		}
		return $cap;
	}

	/**
	 * Returns an instance of: WC_Product_External, WC_Product_Simple, WC_Product_Grouped,
	 * or WC_Product_Variable; based upon the product type
	 *
	 * @param int $id - ID of the product.
	 * @return \WC_Product_External|\WC_Product_Simple|\WC_Product_Grouped|\WC_Product_Variable
	 */
	private function get_object( $id ) {
		switch ( $this->product_type ) {
			case 'external':
				return new \WC_Product_External( $id );
			case 'grouped':
				return new \WC_Product_Grouped( $id );
			case 'variable':
				return new \WC_Product_Variable( $id );
			case 'simple':
				return new \WC_Product_Simple( $id );
			default:
				return \wc_get_product( $id );
		}
	}

	/**
	 * Returns string of variation price range.
	 *
	 * @param string  $pricing_type - Range selected pricing type.
	 * @param boolean $raw          - Whether to return raw value.
	 *
	 * @return string|null
	 */
	private function get_variation_price( $pricing_type = '', $raw = false ) {
		$prices = $this->data->get_variation_prices( true );

		if ( empty( $prices['price'] ) || ( 'sale' === $pricing_type && ! $this->data->is_on_sale() ) ) {
			return null;
		} else {
			$min_price     = current( $prices['price'] );
			$max_price     = end( $prices['price'] );
			$min_reg_price = current( $prices['regular_price'] );
			$max_reg_price = end( $prices['regular_price'] );

			if ( $min_price !== $max_price ) {
				$price = ! $raw ? \wc_graphql_price_range( $min_price, $max_price ) : implode( ', ', $prices['price'] );
			} elseif ( 'regular' !== $pricing_type && $this->data->is_on_sale() && $min_reg_price === $max_reg_price ) {
				$price = ! $raw ? \wc_graphql_price_range( $min_price, $max_reg_price ) : implode( ', ', $prices['price'] );
			} else {
				$price = ! $raw ? \wc_graphql_price( $min_price ) : $min_price;
			}
		}

		return apply_filters( 'graphql_get_variation_price', $price, $this );
	}

	/**
	 * Initializes the Product field resolvers
	 *
	 * @access protected
	 */
	protected function init() {
		if ( empty( $this->fields ) ) {
			$this->fields = array(
				'ID'                   => function() {
					return $this->data->get_id();
				},
				'id'                   => function() {
					return ! empty( $this->data->get_id() )
						? Relay::toGlobalId( 'product', $this->data->get_id() )
						: null;
				},
				'productId'            => function() {
					return ! empty( $this->data->get_id() ) ? $this->data->get_id() : null;
				},
				'type'                 => function() {
					return ! empty( $this->data->get_type() ) ? $this->data->get_type() : null;
				},
				'slug'                 => function() {
					return ! empty( $this->data->get_slug() ) ? $this->data->get_slug() : null;
				},
				'name'                 => function() {
					return ! empty( $this->data->get_name() ) ? $this->data->get_name() : null;
				},
				'date'                 => function() {
					return ! empty( $this->data ) ? $this->data->get_date_created() : null;
				},
				'modified'             => function() {
					return ! empty( $this->data ) ? $this->data->get_date_modified() : null;
				},
				'status'               => function() {
					return ! empty( $this->data->get_status() ) ? $this->data->get_status() : null;
				},
				'featured'             => function() {
					return ! is_null( $this->data->get_featured() ) ? $this->data->get_featured() : null;
				},
				'catalogVisibility'    => function() {
					return ! empty( $this->data->get_catalog_visibility() ) ? $this->data->get_catalog_visibility() : null;
				},
				'description'          => function() {
					return ! empty( $this->data->get_description() ) ? $this->data->get_description() : null;
				},
				'shortDescription'     => function() {
					return ! empty( $this->data->get_short_description() ) ? $this->data->get_short_description() : null;
				},
				'sku'                  => function() {
					return ! empty( $this->data->get_sku() ) ? $this->data->get_sku() : null;
				},
				'price'                => function() {
					if ( 'variable' === $this->data->get_type() ) {
						return $this->get_variation_price();
					}

					return ! empty( $this->data->get_price() )
						? \wc_graphql_price( $this->data->get_price() )
						: null;
				},
				'priceRaw'             => array(
					'callback'   => function() {
						if ( 'variable' === $this->data->get_type() ) {
							return $this->get_variation_price( '', true );
						}

						return ! empty( $this->data->get_price() ) ? $this->data->get_price() : null;
					},
					'capability' => $this->post_type_object->cap->edit_posts,
				),
				'regularPrice'         => function() {
					if ( 'variable' === $this->data->get_type() ) {
						return $this->get_variation_price( 'regular' );
					}

					return ! empty( $this->data->get_regular_price() )
						? \wc_graphql_price( $this->data->get_regular_price() )
						: null;
				},
				'regularPriceRaw'      => array(
					'callback'   => function() {
						if ( 'variable' === $this->data->get_type() ) {
							return $this->get_variation_price( 'regular', true );
						}

						return ! empty( $this->data->get_regular_price() ) ? $this->data->get_regular_price() : null;
					},
					'capability' => $this->post_type_object->cap->edit_posts,
				),
				'salePrice'            => function() {
					if ( 'variable' === $this->data->get_type() ) {
						return $this->get_variation_price( 'sale' );
					}

					return ! empty( $this->data->get_sale_price() )
						? \wc_graphql_price( $this->data->get_sale_price() )
						: null;
				},
				'salePriceRaw'         => array(
					'callback'   => function() {
						if ( 'variable' === $this->data->get_type() ) {
							return $this->get_variation_price( 'sale', true );
						}

						return ! empty( $this->data->get_sale_price() ) ? $this->data->get_sale_price() : null;
					},
					'capability' => $this->post_type_object->cap->edit_posts,
				),
				'dateOnSaleFrom'       => function() {
					return ! empty( $this->data->get_date_on_sale_from() ) ? $this->data->get_date_on_sale_from() : null;
				},
				'dateOnSaleTo'         => function() {
					return ! empty( $this->data->get_date_on_sale_to() ) ? $this->data->get_date_on_sale_to() : null;
				},
				'totalSales'           => function() {
					return ! is_null( $this->data->get_total_sales() ) ? $this->data->get_total_sales() : null;
				},
				'taxStatus'            => function() {
					return ! empty( $this->data->get_tax_status() ) ? $this->data->get_tax_status() : null;
				},
				'taxClass'             => function() {
					return ! empty( $this->data->get_tax_class() ) ? $this->data->get_tax_class() : 'standard';
				},
				'manageStock'          => function() {
					return ! is_null( $this->data->get_manage_stock() ) ? $this->data->get_manage_stock() : null;
				},
				'stockQuantity'        => function() {
					return ! empty( $this->data->get_stock_quantity() ) ? $this->data->get_stock_quantity() : null;
				},
				'stockStatus'          => function() {
					return ! empty( $this->data->get_stock_status() ) ? $this->data->get_stock_status() : null;
				},
				'backorders'           => function() {
					return ! empty( $this->data->get_backorders() ) ? $this->data->get_backorders() : null;
				},
				'soldIndividually'     => function() {
					return ! is_null( $this->data->is_sold_individually() ) ? $this->data->is_sold_individually() : null;
				},
				'weight'               => function() {
					return ! is_null( $this->data->get_weight() ) ? $this->data->get_weight() : null;
				},
				'length'               => function() {
					return ! is_null( $this->data->get_length() ) ? $this->data->get_length() : null;
				},
				'width'                => function() {
					return ! is_null( $this->data->get_width() ) ? $this->data->get_width() : null;
				},
				'height'               => function() {
					return ! is_null( $this->data->get_height() ) ? $this->data->get_height() : null;
				},
				'reviewsAllowed'       => function() {
					return ! empty( $this->data->get_reviews_allowed() ) ? $this->data->get_reviews_allowed() : null;
				},
				'purchaseNote'         => function() {
					return ! empty( $this->data->get_purchase_note() ) ? $this->data->get_purchase_note() : null;
				},
				'menuOrder'            => function() {
					return ! is_null( $this->data->get_menu_order() ) ? $this->data->get_menu_order() : null;
				},
				'virtual'              => function() {
					return ! is_null( $this->data->is_virtual() ) ? $this->data->is_virtual() : null;
				},
				'downloadExpiry'       => function() {
					return ! is_null( $this->data->get_download_expiry() ) ? $this->data->get_download_expiry() : null;
				},
				'downloadable'         => function() {
					return ! is_null( $this->data->is_downloadable() ) ? $this->data->is_downloadable() : null;
				},
				'downloadLimit'        => function() {
					return ! is_null( $this->data->get_download_limit() ) ? $this->data->get_download_limit() : null;
				},
				'averageRating'        => function() {
					return ! is_null( $this->data->get_average_rating() ) ? $this->data->get_average_rating() : null;
				},
				'reviewCount'          => function() {
					return ! is_null( $this->data->get_review_count() ) ? $this->data->get_review_count() : null;
				},
				'shippingClassId'      => function () {
					return ! empty( $this->data->get_image_id() ) ? $this->data->get_shipping_class_id() : null;
				},
				'downloads'            => function() {
					return ! empty( $this->data->get_downloads() ) ? $this->data->get_downloads() : null;
				},
				'onSale'               => function () {
					return ! is_null( $this->data->is_on_sale() ) ? $this->data->is_on_sale() : null;
				},
				'purchasable'          => function () {
					return ! is_null( $this->data->is_purchasable() ) ? $this->data->is_purchasable() : null;
				},
				'externalUrl'          => function() {
					if ( 'external' === $this->data->get_type() ) {
						return ! empty( $this->data->get_product_url() ) ? $this->data->get_product_url() : null;
					}
					return null;
				},
				'buttonText'           => function() {
					if ( 'external' === $this->data->get_type() ) {
						return ! empty( $this->data->get_button_text() ) ? $this->data->get_button_text() : null;
					}
					return null;
				},
				'addToCartText'        => function() {
					if ( 'grouped' === $this->data->get_type() || 'external' === $this->data->get_type() ) {
						return ! empty( $this->data->add_to_cart_text() ) ? $this->data->add_to_cart_text() : null;
					}
					return null;
				},
				'addToCartDescription' => function() {
					if ( 'grouped' === $this->data->get_type() || 'external' === $this->data->get_type() ) {
						return ! empty( $this->data->add_to_cart_description() ) ? $this->data->add_to_cart_description() : null;
					}
					return null;
				},
				'backordersAllowed'    => function() {
					return ! empty( $this->data->backorders_allowed() ) ? $this->data->backorders_allowed() : null;
				},
				'shippingRequired'     => function() {
					return ! is_null( $this->data->needs_shipping() ) ? $this->data->needs_shipping() : null;
				},
				'shippingTaxable'      => function() {
					return ! is_null( $this->data->is_shipping_taxable() ) ? $this->data->is_shipping_taxable() : null;
				},
				/**
				 * Connection resolvers fields
				 *
				 * These field resolvers are used in connection resolvers to define WP_Query argument
				 * Note: underscore naming style is used as a quick identifier
				 */
				'related_ids'          => function() {
					$related_ids = array_map( 'absint', array_values( wc_get_related_products( $this->data->get_id() ) ) );
					return ! empty( $related_ids ) ? $related_ids : array( '0' );
				},
				'upsell_ids'           => function() {
					if ( ! empty( $this->data ) ) {
						switch ( $this->data->get_type() ) {
							case 'external':
							case 'grouped':
								return null;
							default:
								$upsell_ids = array_map( 'absint', $this->data->get_upsell_ids() );
								return ! empty( $upsell_ids ) ? $upsell_ids : array( '0' );
						}
					}

					return array( '0' );
				},
				'cross_sell_ids'       => function() {
					if ( ! empty( $this->data ) ) {
						switch ( $this->data->get_type() ) {
							case 'external':
							case 'grouped':
								return null;
							default:
								$cross_sell_ids = array_map( 'absint', $this->data->get_cross_sell_ids() );
								return ! empty( $cross_sell_ids ) ? $cross_sell_ids : array( '0' );
						}
					}

					return array( '0' );
				},
				'grouped_ids'          => function() {
					if ( ! empty( $this->data ) && 'grouped' === $this->data->get_type() ) {
						$grouped = array_map( 'absint', $this->data->get_children() );
						return ! empty( $grouped ) ? $grouped : array( '0' );
					}
					return array( '0' );
				},
				'variation_ids'        => function() {
					if ( ! empty( $this->data ) && 'variable' === $this->data->get_type() ) {
						$variations = array_map( 'absint', $this->data->get_children() );
						return ! empty( $variations ) ? $variations : array( '0' );
					}
					return array( '0' );
				},
				'attributes'           => function() {
					return ! empty( $this->data->get_attributes() ) ? array_values( $this->data->get_attributes() ) : array( '0' );
				},
				'default_attributes'   => function() {
					return ! empty( $this->data->get_default_attributes() ) ? $this->data->get_default_attributes() : array( '0' );
				},
				'image_id'             => function () {
					return ! empty( $this->data->get_image_id() ) ? $this->data->get_image_id() : null;
				},
				'gallery_image_ids'    => function() {
					return ! empty( $this->data->get_gallery_image_ids() ) ? $this->data->get_gallery_image_ids() : array( '0' );
				},
				'category_ids'         => function() {
					return ! empty( $this->data->get_category_ids() ) ? $this->data->get_category_ids() : array( '0' );
				},
				'tag_ids'              => function() {
					return ! empty( $this->data->get_tag_ids() ) ? $this->data->get_tag_ids() : array( '0' );
				},
				'parent_id'            => function() {
					return ! empty( $this->data->get_parent_id() ) ? $this->data->get_parent_id() : null;
				},
			);
		}

		parent::prepare_fields();
	}
}
