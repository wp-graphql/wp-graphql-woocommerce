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

/**
 * Class Product
 *
 * @property \WC_Product   $wc_data
 * @property \WP_Post_Type $post_type_object
 *
 * @property int           $ID
 * @property string        $id
 * @property string        $type
 * @property string        $slug
 * @property string        $name
 * @property string        $date
 * @property string        $modified
 * @property string        $status
 * @property bool          $featured
 * @property string        $description
 * @property string        $descriptionRaw
 * @property string        $shortDescription
 * @property string        $shortDescriptionRaw
 * @property string        $sku
 * @property string        $dateOnSaleFrom
 * @property string        $dateOnSaleTo
 * @property bool          $reviewsAllowed
 * @property string        $purchaseNote
 * @property int           $menuOrder
 * @property float         $averageRating
 * @property int           $reviewCount
 * @property bool          $onSale
 * @property bool          $purchasable
 * @property string        $catalogVisibility
 * @property int           $totalSales
 *
 * @property array         $upsell_ids
 * @property array         $attributes
 * @property array         $default_attributes
 * @property int           $image_id
 * @property int[]         $gallery_image_ids
 * @property int[]         $category_ids
 * @property int[]         $tag_ids
 * @property int           $parent_id
 *
 * @property bool          $manageStock
 * @property int           $stockQuantity
 * @property string        $backorders
 * @property bool          $backordersAllowed
 * @property bool          $soldIndividually
 * @property float         $weight
 * @property float         $length
 * @property float         $width
 * @property float         $height
 * @property int           $shippingClassId
 * @property bool          $shippingRequired
 * @property bool          $shippingTaxable
 * @property array         $cross_sell_ids
 * @property string        $stockStatus
 *
 * @property bool          $virtual
 * @property int           $downloadExpiry
 * @property bool          $downloadable
 * @property int           $downloadLimit
 * @property array         $downloads
 *
 * @property string        $price
 * @property float|float[] $priceRaw
 * @property string        $regularPrice
 * @property float|float[] $regularPriceRaw
 * @property string        $salePrice
 * @property float|float[] $salePriceRaw
 * @property string        $taxStatus
 * @property string        $taxClass
 *
 * @property string        $externalUrl
 * @property string        $buttonText
 *
 * @property string        $addToCartText
 * @property string        $addToCartDescription
 * @property int[]         $grouped_ids
 *
 * @property int[]         $variation_ids
 *
 * @package WPGraphQL\WooCommerce\Model
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
	 * @var \WC_Product_Factory|null
	 */
	protected static $product_factory = null;

	/**
	 * Product constructor.
	 *
	 * @param int|\WC_Data $id - product post-type ID.
	 *
	 * @throws \Exception  Failed to retrieve product data source.
	 */
	public function __construct( $id ) {
		// Get WC_Product object.
		$data = \wc_get_product( $id );

		// Check if product is valid.
		if ( ! is_object( $data ) ) {
			throw new \Exception( __( 'Failed to retrieve product data source', 'wp-graphql-woocommerce' ) );
		}

		parent::__construct( $data );
	}

	/**
	 * Returns the product type.
	 *
	 * @return string
	 */
	public function get_type() {
		return $this->wc_data->get_type();
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
		/**
		 * Variable product data source.
		 *
		 * @var \WC_Product_Variable $data
		 */
		$data = $this->wc_data;

		$prices = $data->get_variation_prices( true );

		if ( empty( $prices['price'] ) || ( 'sale' === $pricing_type && ! $this->wc_data->is_on_sale() ) ) {
			return null;
		}

		switch ( $pricing_type ) {
			case 'sale':
				$prices = array_values( array_diff( $prices['sale_price'], $prices['regular_price'] ) );
				break;
			case 'regular':
				$prices = $prices['regular_price'];
				break;
			default:
				$prices = $prices['price'];
				break;
		}

		sort( $prices, SORT_NUMERIC );

		if ( $raw ) {
			return implode( ', ', $prices );
		}

		return wc_graphql_price_range( current( $prices ), end( $prices ) );
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
			$fields = [
				'ID'                  => function () {
					return ! empty( $this->wc_data->get_id() ) ? $this->wc_data->get_id() : null;
				},
				'id'                  => function () {
					return ! empty( $this->ID ) ? Relay::toGlobalId( 'product', "{$this->ID}" ) : null;
				},
				'type'                => function () {
					return ! empty( $this->wc_data->get_type() ) ? $this->wc_data->get_type() : null;
				},
				'slug'                => function () {
					return ! empty( $this->wc_data->get_slug() ) ? $this->wc_data->get_slug() : null;
				},
				'name'                => function () {
					return ! empty( $this->wc_data->get_name() ) ? html_entity_decode( $this->wc_data->get_name() ) : null;
				},
				'date'                => function () {
					return ! empty( $this->wc_data ) ? $this->wc_data->get_date_created() : null;
				},
				'modified'            => function () {
					return ! empty( $this->wc_data ) ? $this->wc_data->get_date_modified() : null;
				},
				'status'              => function () {
					return ! empty( $this->wc_data->get_status() ) ? $this->wc_data->get_status() : null;
				},
				'featured'            => function () {
					return $this->wc_data->get_featured();
				},
				'description'         => function () {
					return ! empty( $this->wc_data->get_description() )
						// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
						? apply_filters( 'the_content', $this->wc_data->get_description() )
						: null;
				},
				'descriptionRaw'      => function () {
					return ! empty( $this->wc_data->get_description() ) ? $this->wc_data->get_description() : null;
				},
				'shortDescription'    => function () {
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
				'shortDescriptionRaw' => function () {
					return ! empty( $this->wc_data->get_short_description() ) ? $this->wc_data->get_short_description() : null;
				},
				'sku'                 => function () {
					return ! empty( $this->wc_data->get_sku() ) ? $this->wc_data->get_sku() : null;
				},
				'dateOnSaleFrom'      => function () {
					return ! empty( $this->wc_data->get_date_on_sale_from() ) ? $this->wc_data->get_date_on_sale_from() : null;
				},
				'dateOnSaleTo'        => function () {
					return ! empty( $this->wc_data->get_date_on_sale_to() ) ? $this->wc_data->get_date_on_sale_to() : null;
				},
				'reviewsAllowed'      => function () {
					return ! empty( $this->wc_data->get_reviews_allowed() ) ? $this->wc_data->get_reviews_allowed() : null;
				},
				'purchaseNote'        => function () {
					return ! empty( $this->wc_data->get_purchase_note() ) ? $this->wc_data->get_purchase_note() : null;
				},
				'menuOrder'           => function () {
					return $this->wc_data->get_menu_order();
				},
				'averageRating'       => function () {
					return $this->wc_data->get_average_rating();
				},
				'reviewCount'         => function () {
					return $this->wc_data->get_review_count();
				},
				'onSale'              => function () {
					return $this->wc_data->is_on_sale();
				},
				'purchasable'         => function () {
					return $this->wc_data->is_purchasable();
				},

				/**
				 * Editor/Shop Manager only fields
				 */
				'catalogVisibility'   => [
					'callback'   => function () {
						return ! empty( $this->wc_data->get_catalog_visibility() ) ? $this->wc_data->get_catalog_visibility() : null;
					},
					'capability' => $this->post_type_object->cap->edit_posts,
				],
				'totalSales'          => [
					'callback'   => function () {
						return $this->wc_data->get_total_sales();
					},
					'capability' => $this->post_type_object->cap->edit_posts,
				],

				/**
				 * Connection resolvers fields
				 *
				 * These field resolvers are used in connection resolvers to define WP_Query argument
				 * Note: underscore naming style is used as a quick identifier
				 */
				'upsell_ids'          => function () {
					return ! empty( $this->wc_data->get_upsell_ids() )
						? array_map( 'absint', $this->wc_data->get_upsell_ids() )
						: [ '0' ];
				},
				'attributes'          => function () {
					return ! empty( $this->wc_data->get_attributes() ) ? $this->wc_data->get_attributes() : [];
				},
				'default_attributes'  => function () {
					return ! empty( $this->wc_data->get_default_attributes() ) ? $this->wc_data->get_default_attributes() : [ '0' ];
				},
				'image_id'            => function () {
					return ! empty( $this->wc_data->get_image_id() ) ? $this->wc_data->get_image_id() : null;
				},
				'gallery_image_ids'   => function () {
					return ! empty( $this->wc_data->get_gallery_image_ids() ) ? $this->wc_data->get_gallery_image_ids() : [ '0' ];
				},
				'category_ids'        => function () {
					return ! empty( $this->wc_data->get_category_ids() ) ? $this->wc_data->get_category_ids() : [ '0' ];
				},
				'tag_ids'             => function () {
					return ! empty( $this->wc_data->get_tag_ids() ) ? $this->wc_data->get_tag_ids() : [ '0' ];
				},
				'parent_id'           => function () {
					return ! empty( $this->wc_data->get_parent_id() ) ? $this->wc_data->get_parent_id() : null;
				},
				'post'                => function () {
					return ! empty( $this->wc_data->post ) ? $this->wc_data->post : null;
				},
			];

			if (
				apply_filters(
					"graphql_{$type}_product_model_use_pricing_and_tax_fields",
					'grouped' !== $this->wc_data->get_type()
				)
			) {
				$fields += [
					'price'           => function () {
						return ! empty( $this->wc_data->get_price() )
							? wc_graphql_price( \wc_get_price_to_display( $this->wc_data ) )
							: null;
					},
					'priceRaw'        => function () {
						return ! empty( $this->wc_data->get_price() ) ? $this->wc_data->get_price() : null;
					},
					'regularPrice'    => function () {
						return ! empty( $this->wc_data->get_regular_price() )
							? wc_graphql_price( \wc_get_price_to_display( $this->wc_data, [ 'price' => $this->wc_data->get_regular_price() ] ) )
							: null;
					},
					'regularPriceRaw' => function () {
						return ! empty( $this->wc_data->get_regular_price() ) ? $this->wc_data->get_regular_price() : null;
					},
					'salePrice'       => function () {
						return ! empty( $this->wc_data->get_sale_price() )
							? wc_graphql_price(
								\wc_get_price_to_display(
									$this->wc_data,
									[
										'price' => $this->wc_data->get_sale_price(),
									]
								)
							)
							: null;
					},
					'salePriceRaw'    => function () {
						return ! empty( $this->wc_data->get_sale_price() ) ? $this->wc_data->get_sale_price() : null;
					},
					'taxStatus'       => function () {
						return ! empty( $this->wc_data->get_tax_status() ) ? $this->wc_data->get_tax_status() : null;
					},
					'taxClass'        => function () {
						return $this->wc_data->get_tax_class();
					},
				];
			}//end if

			if (
				apply_filters(
					"graphql_{$type}_product_model_use_inventory_fields",
					'simple' === $type || 'variable' === $type
				)
			) {
				$fields += [
					'manageStock'       => function () {
						return ! empty( $this->wc_data->get_manage_stock() ) ? $this->wc_data->get_manage_stock() : null;
					},
					'stockQuantity'     => function () {
						return ! empty( $this->wc_data->get_stock_quantity() ) ? $this->wc_data->get_stock_quantity() : null;
					},
					'backorders'        => function () {
						return ! empty( $this->wc_data->get_backorders() ) ? $this->wc_data->get_backorders() : null;
					},
					'backordersAllowed' => function () {
						return $this->wc_data->backorders_allowed();
					},
					'soldIndividually'  => function () {
						return $this->wc_data->is_sold_individually();
					},
					'lowStockAmount'    => function () {
						return ! empty( $this->wc_data->get_low_stock_amount() ) ? $this->wc_data->get_low_stock_amount() : null;
					},
					'weight'            => function () {
						return ! empty( $this->wc_data->get_weight() ) ? $this->wc_data->get_weight() : null;
					},
					'length'            => function () {
						return ! empty( $this->wc_data->get_length() ) ? $this->wc_data->get_length() : null;
					},
					'width'             => function () {
						return ! empty( $this->wc_data->get_width() ) ? $this->wc_data->get_width() : null;
					},
					'height'            => function () {
						return ! empty( $this->wc_data->get_height() ) ? $this->wc_data->get_height() : null;
					},
					'shippingClassId'   => function () {
						return ! empty( $this->wc_data->get_image_id() ) ? $this->wc_data->get_shipping_class_id() : null;
					},
					'shippingRequired'  => function () {
						return $this->wc_data->needs_shipping();
					},
					'shippingTaxable'   => function () {
						return $this->wc_data->is_shipping_taxable();
					},
					'cross_sell_ids'    => function () {
						return ! empty( $this->wc_data->get_cross_sell_ids() )
							? array_map( 'absint', $this->wc_data->get_cross_sell_ids() )
							: [ '0' ];
					},
					'stockStatus'       => function () {
						return ! empty( $this->wc_data->get_stock_status() ) ? $this->wc_data->get_stock_status() : null;
					},
				];
			}//end if

			switch ( true ) {
				case apply_filters( "graphql_{$type}_product_model_use_virtual_data_fields", 'simple' === $type ):
					$fields += [
						'virtual'        => function () {
							return $this->wc_data->is_virtual();
						},
						'downloadExpiry' => function () {
							return $this->wc_data->get_download_expiry();
						},
						'downloadable'   => function () {
							return $this->wc_data->is_downloadable();
						},
						'downloadLimit'  => function () {
							return ! empty( $this->wc_data->get_download_limit() ) ? $this->wc_data->get_download_limit() : null;
						},
						'downloads'      => function () {
							return ! empty( $this->wc_data->get_downloads() ) ? $this->wc_data->get_downloads() : null;
						},
					];
					break;
				case apply_filters( "graphql_{$type}_product_model_use_variation_pricing_fields", 'variable' === $type ):
					$fields = [
						'price'           => function () {
							return $this->get_variation_price();
						},
						'regularPrice'    => function () {
							return $this->get_variation_price( 'regular' );
						},
						'salePrice'       => function () {
							return $this->get_variation_price( 'sale' );
						},
						'variation_ids'   => function () {
							return ! empty( $this->wc_data->get_children() )
								? array_map( 'absint', $this->wc_data->get_children() )
								: [ '0' ];
						},
						'priceRaw'        => function () {
							return $this->get_variation_price( '', true );
						},
						'regularPriceRaw' => function () {
							return $this->get_variation_price( 'regular', true );
						},
						'salePriceRaw'    => function () {
							return $this->get_variation_price( 'sale', true );
						},
					] + $fields;
					break;
				case apply_filters( "graphql_{$type}_product_model_use_external_fields", 'external' === $type ):
					$fields += [
						'externalUrl' => function () {
							/**
							 * External product
							 *
							 * @var \WC_Product_External $data
							 */
							$data = $this->wc_data;
							return ! empty( $data->get_product_url() ) ? $data->get_product_url() : null;
						},
						'buttonText'  => function () {
							/**
							 * External product
							 *
							 * @var \WC_Product_External $data
							 */
							$data = $this->wc_data;
							return ! empty( $data->get_button_text() ) ? $data->get_button_text() : null;
						},
					];
					break;
				case apply_filters( "graphql_{$type}_product_model_use_grouped_fields", 'grouped' === $type ):
					$fields += [
						'addToCartText'        => function () {
							return ! empty( $this->wc_data->add_to_cart_text() ) ? $this->wc_data->add_to_cart_text() : null;
						},
						'addToCartDescription' => function () {
							return ! empty( $this->wc_data->add_to_cart_description() )
								? $this->wc_data->add_to_cart_description()
								: null;
						},
						'grouped_ids'          => function () {
							return ! empty( $this->wc_data->get_children() )
								? array_map( 'absint', $this->wc_data->get_children() )
								: [ '0' ];
						},
					];
					break;
			}//end switch

			/**
			 * Defines aliased fields
			 *
			 * These fields are used primarily by WPGraphQL core Node* interfaces
			 * and some fields act as aliases/decorator for existing fields.
			 */
			$fields += [
				'commentCount'  => function () {
					// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
					return ! empty( $this->reviewCount ) ? $this->reviewCount : null;
				},
				'commentStatus' => function () {
					// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
					return isset( $this->reviewsAllowed ) && $this->reviewsAllowed ? 'open' : 'closed';
				},
			];

			$this->fields = array_merge( $this->fields, $fields );
		}//end if
	}
}
