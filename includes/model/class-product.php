<?php
/**
 * Model - Product
 *
 * Resolves product crud object model
 *
 * @package WPGraphQL\WooCommerce\Model
 * @since 0.0.1
 */

namespace WPGraphQL\WooCommerce\Model;

use GraphQLRelay\Relay;
use WC_Product_Factory;
use WC_Product_External;
use WC_Product_Grouped;
use WC_Product_Variable;
use WC_Product_Simple;

/**
 * Class Product
 */
class Product extends WC_Post {

	/**
	 * Stores the product type: external, grouped, simple, variable.
	 *
	 * @var string
	 */
	protected $product_type;

	/**
	 * Stores product factory.
	 *
	 * @var WC_Product_Factory|null
	 */
	protected static $product_factory = null;

	/**
	 * Product constructor.
	 *
	 * @param int $id - product post-type ID.
	 */
	public function __construct( $id ) {
		// Get product type.
		$this->product_type = $this->product_factory()->get_product_type( $id );

		// Get WC_Product object.
		$data = $this->get_object( $id );

		parent::__construct( $data );
	}

	/**
	 * Returns product factory instance.
	 *
	 * @return WC_Product_Factory
	 */
	public function product_factory() {
		if ( null === self::$product_factory ) {
			self::$product_factory = new WC_Product_Factory();
		}

		return self::$product_factory;
	}

	/**
	 * Returns an instance of: WC_Product_External, WC_Product_Simple, WC_Product_Grouped,
	 * or WC_Product_Variable; based upon the product type
	 *
	 * @param int $id - ID of the product.
	 * @return WC_Product_External|WC_Product_Simple|WC_Product_Grouped|WC_Product_Variable
	 */
	private function get_object( $id ) {
		switch ( $this->product_type ) {
			case 'external':
				return new WC_Product_External( $id );
			case 'grouped':
				return new WC_Product_Grouped( $id );
			case 'variable':
				return new WC_Product_Variable( $id );
			case 'simple':
				return new WC_Product_Simple( $id );
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
		$prices = $this->wc_data->get_variation_prices( true );

		if ( empty( $prices['price'] ) || ( 'sale' === $pricing_type && ! $this->wc_data->is_on_sale() ) ) {
			return null;
		} else {
			$min_price     = current( $prices['price'] );
			$max_price     = end( $prices['price'] );
			$min_reg_price = current( $prices['regular_price'] );
			$max_reg_price = end( $prices['regular_price'] );

			if ( $min_price !== $max_price ) {
				$price = ! $raw ? \wc_graphql_price_range( $min_price, $max_price ) : implode( ', ', $prices['price'] );
			} elseif ( 'regular' !== $pricing_type && $this->wc_data->is_on_sale() && $min_reg_price === $max_reg_price ) {
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
			parent::init();

			$type   = $this->wc_data->get_type();
			$fields = array(
				'id'                  => function() {
					return ! empty( $this->wc_data->get_id() ) ? Relay::toGlobalId( 'product', $this->wc_data->get_id() ) : null;
				},
				'type'                => function() {
					return ! empty( $this->wc_data->get_type() ) ? $this->wc_data->get_type() : null;
				},
				'slug'                => function() {
					return ! empty( $this->wc_data->get_slug() ) ? $this->wc_data->get_slug() : null;
				},
				'name'                => function() {
					return ! empty( $this->wc_data->get_name() ) ? $this->wc_data->get_name() : null;
				},
				'date'                => function() {
					return ! empty( $this->wc_data ) ? $this->wc_data->get_date_created() : null;
				},
				'modified'            => function() {
					return ! empty( $this->wc_data ) ? $this->wc_data->get_date_modified() : null;
				},
				'status'              => function() {
					return ! empty( $this->wc_data->get_status() ) ? $this->wc_data->get_status() : null;
				},
				'featured'            => function() {
					return ! is_null( $this->wc_data->get_featured() ) ? $this->wc_data->get_featured() : null;
				},
				'description'         => function() {
					return ! empty( $this->wc_data->get_description() )
						// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
						? apply_filters( 'the_content', $this->wc_data->get_description() )
						: null;
				},
				'descriptionRaw'      => function() {
					return ! empty( $this->wc_data->get_description() ) ? $this->wc_data->get_description() : null;
				},
				'shortDescription'    => function() {
					$short_description = ! empty( $this->wc_data->get_short_description() )
					? apply_filters(
						// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
						'get_the_excerpt',
						$this->wc_data->get_short_description(),
						get_post( $this->wc_data->get_id() )
					)
					: null;

					// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
					return apply_filters( 'the_excerpt', $short_description );
				},
				'shortDescriptionRaw' => function() {
					return ! empty( $this->wc_data->get_short_description() ) ? $this->wc_data->get_short_description() : null;
				},
				'sku'                 => function() {
					return ! empty( $this->wc_data->get_sku() ) ? $this->wc_data->get_sku() : null;
				},
				'dateOnSaleFrom'      => function() {
					return ! empty( $this->wc_data->get_date_on_sale_from() ) ? $this->wc_data->get_date_on_sale_from() : null;
				},
				'dateOnSaleTo'        => function() {
					return ! empty( $this->wc_data->get_date_on_sale_to() ) ? $this->wc_data->get_date_on_sale_to() : null;
				},
				'reviewsAllowed'      => function() {
					return ! empty( $this->wc_data->get_reviews_allowed() ) ? $this->wc_data->get_reviews_allowed() : null;
				},
				'purchaseNote'        => function() {
					return ! empty( $this->wc_data->get_purchase_note() ) ? $this->wc_data->get_purchase_note() : null;
				},
				'menuOrder'           => function() {
					return ! is_null( $this->wc_data->get_menu_order() ) ? $this->wc_data->get_menu_order() : null;
				},
				'averageRating'       => function() {
					return ! is_null( $this->wc_data->get_average_rating() ) ? $this->wc_data->get_average_rating() : null;
				},
				'reviewCount'         => function() {
					return ! is_null( $this->wc_data->get_review_count() ) ? $this->wc_data->get_review_count() : null;
				},
				'onSale'              => function () {
					return ! is_null( $this->wc_data->is_on_sale() ) ? $this->wc_data->is_on_sale() : null;
				},
				'purchasable'         => function () {
					return ! is_null( $this->wc_data->is_purchasable() ) ? $this->wc_data->is_purchasable() : null;
				},

				/**
				 * Editor/Shop Manager only fields
				 */
				'catalogVisibility'   => array(
					'callback'   => function() {
						return ! empty( $this->wc_data->get_catalog_visibility() ) ? $this->wc_data->get_catalog_visibility() : null;
					},
					'capability' => $this->post_type_object->cap->edit_posts,
				),
				'totalSales'          => array(
					'callback'   => function() {
						return ! is_null( $this->wc_data->get_total_sales() ) ? $this->wc_data->get_total_sales() : null;
					},
					'capability' => $this->post_type_object->cap->edit_posts,
				),

				/**
				 * Connection resolvers fields
				 *
				 * These field resolvers are used in connection resolvers to define WP_Query argument
				 * Note: underscore naming style is used as a quick identifier
				 */
				'upsell_ids'          => function() {
					return ! empty( $this->wc_data->get_upsell_ids() )
						? array_map( 'absint', $this->wc_data->get_upsell_ids() )
						: array( '0' );
				},
				'attributes'          => function() {
					return ! empty( $this->wc_data->get_attributes() ) ? $this->wc_data->get_attributes() : array();
				},
				'default_attributes'  => function() {
					return ! empty( $this->wc_data->get_default_attributes() ) ? $this->wc_data->get_default_attributes() : array( '0' );
				},
				'image_id'            => function () {
					return ! empty( $this->wc_data->get_image_id() ) ? $this->wc_data->get_image_id() : null;
				},
				'gallery_image_ids'   => function() {
					return ! empty( $this->wc_data->get_gallery_image_ids() ) ? $this->wc_data->get_gallery_image_ids() : array( '0' );
				},
				'category_ids'        => function() {
					return ! empty( $this->wc_data->get_category_ids() ) ? $this->wc_data->get_category_ids() : array( '0' );
				},
				'tag_ids'             => function() {
					return ! empty( $this->wc_data->get_tag_ids() ) ? $this->wc_data->get_tag_ids() : array( '0' );
				},
				'parent_id'           => function() {
					return ! empty( $this->wc_data->get_parent_id() ) ? $this->wc_data->get_parent_id() : null;
				},
				'post'                => function() {
					return ! empty( $this->wc_data->post ) ? $this->wc_data->post : null;
				},
			);

			if (
				apply_filters(
					"graphql_{$type}_product_model_use_pricing_and_tax_fields",
					'grouped' !== $this->wc_data->get_type()
				)
			) {
				$fields += array(
					'price'           => function() {
						return ! empty( $this->wc_data->get_price() )
							? \wc_graphql_price( $this->wc_data->get_price() )
							: null;
					},
					'priceRaw'        => function() {
						return ! empty( $this->wc_data->get_price() ) ? $this->wc_data->get_price() : null;
					},
					'regularPrice'    => function() {
						return ! empty( $this->wc_data->get_regular_price() )
							? \wc_graphql_price( $this->wc_data->get_regular_price() )
							: null;
					},
					'regularPriceRaw' => function() {
						return ! empty( $this->wc_data->get_regular_price() ) ? $this->wc_data->get_regular_price() : null;
					},
					'salePrice'       => function() {
						return ! empty( $this->wc_data->get_sale_price() )
							? \wc_graphql_price( $this->wc_data->get_sale_price() )
							: null;
					},
					'salePriceRaw'    => function() {
						return ! empty( $this->wc_data->get_sale_price() ) ? $this->wc_data->get_sale_price() : null;
					},
					'taxStatus'       => function() {
						return ! empty( $this->wc_data->get_tax_status() ) ? $this->wc_data->get_tax_status() : null;
					},
					'taxClass'        => function() {
						return ! is_null( $this->wc_data->get_tax_class() ) ? $this->wc_data->get_tax_class() : '';
					},
				);
			}

			if (
				apply_filters(
					"graphql_{$type}_product_model_use_inventory_fields",
					'simple' === $type || 'variable' === $type
				)
			) {
				$fields += array(
					'manageStock'       => function() {
						return ! is_null( $this->wc_data->get_manage_stock() ) ? $this->wc_data->get_manage_stock() : null;
					},
					'stockQuantity'     => function() {
						return ! empty( $this->wc_data->get_stock_quantity() ) ? $this->wc_data->get_stock_quantity() : null;
					},
					'backorders'        => function() {
						return ! empty( $this->wc_data->get_backorders() ) ? $this->wc_data->get_backorders() : null;
					},
					'backordersAllowed' => function() {
						return ! empty( $this->wc_data->backorders_allowed() ) ? $this->wc_data->backorders_allowed() : null;
					},
					'soldIndividually'  => function() {
						return ! is_null( $this->wc_data->is_sold_individually() ) ? $this->wc_data->is_sold_individually() : null;
					},
					'weight'            => function() {
						return ! is_null( $this->wc_data->get_weight() ) ? $this->wc_data->get_weight() : null;
					},
					'length'            => function() {
						return ! is_null( $this->wc_data->get_length() ) ? $this->wc_data->get_length() : null;
					},
					'width'             => function() {
						return ! is_null( $this->wc_data->get_width() ) ? $this->wc_data->get_width() : null;
					},
					'height'            => function() {
						return ! is_null( $this->wc_data->get_height() ) ? $this->wc_data->get_height() : null;
					},
					'shippingClassId'   => function () {
						return ! empty( $this->wc_data->get_image_id() ) ? $this->wc_data->get_shipping_class_id() : null;
					},
					'shippingRequired'  => function() {
						return ! is_null( $this->wc_data->needs_shipping() ) ? $this->wc_data->needs_shipping() : null;
					},
					'shippingTaxable'   => function() {
						return ! is_null( $this->wc_data->is_shipping_taxable() ) ? $this->wc_data->is_shipping_taxable() : null;
					},
					'cross_sell_ids'    => function() {
						return ! empty( $this->wc_data->get_cross_sell_ids() )
							? array_map( 'absint', $this->wc_data->get_cross_sell_ids() )
							: array( '0' );
					},
					'stockStatus'    => function() {
						return ! empty( $this->wc_data->get_stock_status() ) ? $this->wc_data->get_stock_status() : null;
					},
				);
			}

			switch ( true ) {
				case apply_filters( "graphql_{$type}_product_model_use_virtual_data_fields", 'simple' === $type ):
					$fields += array(
						'virtual'        => function() {
							return ! is_null( $this->wc_data->is_virtual() ) ? $this->wc_data->is_virtual() : null;
						},
						'downloadExpiry' => function() {
							return ! is_null( $this->wc_data->get_download_expiry() ) ? $this->wc_data->get_download_expiry() : null;
						},
						'downloadable'   => function() {
							return ! is_null( $this->wc_data->is_downloadable() ) ? $this->wc_data->is_downloadable() : null;
						},
						'downloadLimit'  => function() {
							return ! is_null( $this->wc_data->get_download_limit() ) ? $this->wc_data->get_download_limit() : null;
						},
						'downloads'      => function() {
							return ! empty( $this->wc_data->get_downloads() ) ? $this->wc_data->get_downloads() : null;
						},
					);
					break;
				case apply_filters( "graphql_{$type}_product_model_use_variation_pricing_fields", 'variable' === $type ):
					$fields = array(
						'price'           => function() {
							return $this->get_variation_price();
						},
						'regularPrice'    => function() {
							return $this->get_variation_price( 'regular' );
						},
						'salePrice'       => function() {
							return $this->get_variation_price( 'sale' );
						},
						'variation_ids'   => function() {
							return ! empty( $this->wc_data->get_children() )
								? array_map( 'absint', $this->wc_data->get_children() )
								: array( '0' );
						},
						'priceRaw'        => function() {
							return $this->get_variation_price( '', true );
						},
						'regularPriceRaw' => function() {
							return $this->get_variation_price( 'regular', true );
						},
						'salePriceRaw'    => function() {
							return $this->get_variation_price( 'sale', true );
						},
					) + $fields;
					break;
				case apply_filters( "graphql_{$type}_product_model_use_external_fields", 'external' === $type ):
					$fields += array(
						'externalUrl' => function() {
							return ! empty( $this->wc_data->get_product_url() ) ? $this->wc_data->get_product_url() : null;
						},
						'buttonText'  => function() {
							return ! empty( $this->wc_data->get_button_text() ) ? $this->wc_data->get_button_text() : null;
						},
					);
					break;
				case apply_filters( "graphql_{$type}_product_model_use_grouped_fields", 'grouped' === $type	):
					$fields += array(
						'addToCartText'        => function() {
							return ! empty( $this->wc_data->add_to_cart_text() ) ? $this->wc_data->add_to_cart_text() : null;
						},
						'addToCartDescription' => function() {
							return ! empty( $this->wc_data->add_to_cart_description() )
								? $this->wc_data->add_to_cart_description()
								: null;
						},
						'grouped_ids'          => function() {
							return ! empty( $this->wc_data->get_children() )
								? array_map( 'absint', $this->wc_data->get_children() )
								: array( '0' );
						},
					);
					break;
			}

			/**
			 * Defines aliased fields
			 *
			 * These fields are used primarily by WPGraphQL core Node* interfaces
			 * and some fields act as aliases/decorator for existing fields.
			 */
			$fields += array(
				'commentCount'    => function() {
					return $this->reviewCount;
				},
				'commentStatus'   => function() {
					return $this->reviewsAllowed ? 'open': 'closed';
				},
			);

			$this->fields = array_merge( $this->fields, $fields );
		}
	}
}
