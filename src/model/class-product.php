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
	 * Stores the instance of WC_Product_External|WC_Product_Simple|WC_Product_Grouped|WC_Product_Variable
	 *
	 * @var \WC_Product_External|\WC_Product_Simple|\WC_Product_Grouped|\WC_Product_Variable $product
	 * @access protected
	 */
	protected $product;

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
		$this->product             = $this->get_object( $id );
		$allowed_restricted_fields = [
			'isRestricted',
			'isPrivate',
			'isPublic',
			'id',
			'type',
			'name',
			'slug',
		];

		parent::__construct(
			'ProductObject',
			$this->product,
			$allowed_restricted_fields,
			'product',
			$id
		);
	}

	/**
	 * Retrieve the cap to check if the data should be restricted for the coupon
	 *
	 * @access protected
	 * @return string
	 */
	protected function get_restricted_cap() {
		if ( post_password_required( $this->product->get_id() ) ) {
			return $this->post_type_object->cap->edit_others_posts;
		}
		switch ( get_post_status( $this->product->get_id() ) ) {
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
	 * Initializes the Product field resolvers
	 *
	 * @access public
	 */
	public function init() {
		if ( 'private' === $this->get_visibility() || is_null( $this->product ) ) {
			return null;
		}

		if ( empty( $this->fields ) ) {
			$this->fields = array(
				'ID'                 => function() {
					return $this->product->get_id();
				},
				'id'                 => function() {
					return ! empty( $this->product->get_id() )
						? Relay::toGlobalId( 'product', $this->product->get_id() )
						: null;
				},
				'productId'          => function() {
					return ! empty( $this->product->get_id() ) ? $this->product->get_id() : null;
				},
				'type'               => function() {
					return ! empty( $this->product->get_type() ) ? $this->product->get_type() : null;
				},
				'slug'               => function() {
					return ! empty( $this->product->get_slug() ) ? $this->product->get_slug() : null;
				},
				'name'               => function() {
					return ! empty( $this->product->get_name() ) ? $this->product->get_name() : null;
				},
				'status'             => function() {
					return ! empty( $this->product->get_status() ) ? $this->product->get_status() : null;
				},
				'featured'           => function() {
					return ! empty( $this->product->get_featured() ) ? $this->product->get_featured() : null;
				},
				'catalogVisibility'  => function() {
					return ! empty( $this->product->get_catalog_visibility() ) ? $this->product->get_catalog_visibility() : null;
				},
				'description'        => function() {
					return ! empty( $this->product->get_description() ) ? $this->product->get_description() : null;
				},
				'shortDescription'   => function() {
					return ! empty( $this->product->get_short_description() ) ? $this->product->get_short_description() : null;
				},
				'sku'                => function() {
					return ! empty( $this->product->get_sku() ) ? $this->product->get_sku() : null;
				},
				'price'              => function() {
					if ( ! empty( $this->product ) ) {
						if ( 'variable' === $this->product->get_type() ) {
							return ! empty( $this->product->get_variation_price( 'min' ) )
								? $this->product->get_variation_price( 'min' )
								: null;
						}
						return ! empty( $this->product->get_price() )
							? $this->product->get_price()
							: null;
					}
					return null;
				},
				'priceMax'           => function() {
					if ( ! empty( $this->product ) ) {
						if ( 'variable' === $this->product->get_type() ) {
							return ! empty( $this->product->get_variation_price( 'max' ) )
								? $this->product->get_variation_price( 'max' )
								: null;
						}
						return ! empty( $this->product->get_price() )
							? $this->product->get_price()
							: null;
					}
					return null;
				},
				'regularPrice'       => function() {
					if ( ! empty( $this->product ) ) {
						if ( 'variable' === $this->product->get_type() ) {
							return ! empty( $this->product->get_variation_regular_price( 'min' ) )
								? $this->product->get_variation_regular_price( 'min' )
								: null;
						}
						return ! empty( $this->product->get_regular_price() )
							? $this->product->get_regular_price()
							: null;
					}
					return null;
				},
				'regularPriceMax'    => function() {
					if ( ! empty( $this->product ) ) {
						if ( 'variable' === $this->product->get_type() ) {
							return ! empty( $this->product->get_variation_regular_price( 'max' ) )
								? $this->product->get_variation_regular_price( 'max' )
								: null;
						}
						return ! empty( $this->product->get_regular_price() )
							? $this->product->get_regular_price()
							: null;
					}
					return null;
				},
				'salePrice'          => function() {
					if ( ! empty( $this->product ) ) {
						if ( 'variable' === $this->product->get_type() ) {
							return ! empty( $this->product->get_variation_sale_price( 'min' ) )
							? $this->product->get_variation_sale_price( 'min' )
							: null;
						}
						return ! empty( $this->product->get_sale_price() )
							? $this->product->get_sale_price()
							: null;
					}
					return null;
				},
				'salePriceMax'       => function() {
					if ( ! empty( $this->product ) ) {
						if ( 'variable' === $this->product->get_type() ) {
							return ! empty( $this->product->get_variation_sale_price( 'max' ) )
								? $this->product->get_variation_sale_price( 'max' )
								: null;
						}
						return ! empty( $this->product->get_sale_price() )
							? $this->product->get_sale_price()
							: null;
					}
					return null;
				},
				'dateOnSaleFrom'     => function() {
					return ! empty( $this->product->get_date_on_sale_from() ) ? $this->product->get_date_on_sale_from() : null;
				},
				'dateOnSaleTo'       => function() {
					return ! empty( $this->product->get_date_on_sale_to() ) ? $this->product->get_date_on_sale_to() : null;
				},
				'totalSales'         => function() {
					return ! empty( $this->product->get_total_sales() ) ? $this->product->get_total_sales() : null;
				},
				'taxStatus'          => function() {
					return ! empty( $this->product->get_tax_status() ) ? $this->product->get_tax_status() : null;
				},
				'taxClass'           => function() {
					return ! empty( $this->product->get_tax_class() ) ? $this->product->get_tax_class() : null;
				},
				'manageStock'        => function() {
					return ! empty( $this->product->get_manage_stock() ) ? $this->product->get_manage_stock() : null;
				},
				'stockQuantity'      => function() {
					return ! empty( $this->product->get_stock_quantity() ) ? $this->product->get_stock_quantity() : null;
				},
				'stockStatus'        => function() {
					return ! empty( $this->product->get_stock_status() ) ? $this->product->get_stock_status() : null;
				},
				'backorders'         => function() {
					return ! empty( $this->product->get_backorders() ) ? $this->product->get_backorders() : null;
				},
				'soldIndividually'   => function() {
					return ! empty( $this->product->get_sold_individually() ) ? $this->product->get_sold_individually() : null;
				},
				'weight'             => function() {
					return ! empty( $this->product->get_weight() ) ? $this->product->get_weight() : null;
				},
				'length'             => function() {
					return ! empty( $this->product->get_length() ) ? $this->product->get_length() : null;
				},
				'width'              => function() {
					return ! empty( $this->product->get_width() ) ? $this->product->get_width() : null;
				},
				'height'             => function() {
					return ! empty( $this->product->get_height() ) ? $this->product->get_height() : null;
				},
				'reviewsAllowed'     => function() {
					return ! empty( $this->product->get_reviews_allowed() ) ? $this->product->get_reviews_allowed() : null;
				},
				'purchaseNote'       => function() {
					return ! empty( $this->product->get_purchase_note() ) ? $this->product->get_purchase_note() : null;
				},
				'menuOrder'          => function() {
					return ! empty( $this->product->get_menu_order() ) ? $this->product->get_menu_order() : null;
				},
				'virtual'            => function() {
					return ! empty( $this->product->get_virtual() ) ? $this->product->get_virtual() : null;
				},
				'downloadExpiry'     => function() {
					return ! empty( $this->product->get_download_expiry() ) ? $this->product->get_download_expiry() : null;
				},
				'downloadable'       => function() {
					return ! empty( $this->product->get_downloadable() ) ? $this->product->get_downloadable() : null;
				},
				'downloadLimit'      => function() {
					return ! empty( $this->product->get_download_limit() ) ? $this->product->get_download_limit() : null;
				},
				'ratingCount'        => function() {
					return ! empty( $this->product->get_rating_counts() ) ? $this->product->get_rating_counts() : null;
				},
				'averageRating'      => function() {
					return ! empty( $this->product->get_average_rating() ) ? $this->product->get_average_rating() : null;
				},
				'reviewCount'        => function() {
					return ! empty( $this->product->get_review_count() ) ? $this->product->get_review_count() : null;
				},
				'parentId'           => function() {
					return ! empty( $this->product->get_parent_id() ) ? $this->product->get_parent_id() : null;
				},
				'imageId'            => function () {
					return ! empty( $this->product->get_image_id() ) ? $this->product->get_image_id() : null;
				},
				/**
				 * Connection resolvers fields
				 *
				 * These field resolvers are used in connection resolvers to define WP_Query argument
				 * Note: underscore naming style is used as a quick identifier
				 */
				'upsell_ids'         => function() {
					if ( ! empty( $this->product ) ) {
						switch ( $this->product->get_type() ) {
							case 'external':
							case 'grouped':
								return null;
							default:
								return ! empty( $this->product->get_upsell_ids() )
									? $this->product->get_upsell_ids()
									: array( '0' );
						}
					}

					return array( '0' );
				},
				'cross_sell_ids'     => function() {
					if ( ! empty( $this->product ) ) {
						switch ( $this->product->get_type() ) {
							case 'external':
							case 'grouped':
								return null;
							default:
								return ! empty( $this->product->get_cross_sell_ids() )
									? $this->product->get_cross_sell_ids()
									: array( '0' );
						}
					}

					return array( '0' );
				},
				'attributes'         => function() {
					return ! empty( $this->product->get_attributes() ) ? array_values( $this->product->get_attributes() ) : array( '0' );
				},
				'default_attributes' => function() {
					return ! empty( $this->product->get_default_attributes() ) ? array_values( $this->product->get_default_attributes() ) : array( '0' );
				},
				'downloads'          => function() {
					return ! empty( $this->product->get_downloads() ) ? $this->product->get_downloads() : array( '0' );
				},
				'gallery_image_ids'  => function() {
					return ! empty( $this->product->get_gallery_image_ids() ) ? $this->product->get_gallery_image_ids() : array( '0' );
				},
				'children_ids'       => function() {
					if ( ! empty( $this->product ) && 'variable' === $this->product->get_type() ) {
						return ! empty( $this->product->get_children )
							? $this->product->get_children()
							: null;
					}

					return array( '0' );
				},
				'category_ids'       => function() {
					return ! empty( $this->product->get_category_ids() ) ? $this->product->get_category_ids() : array( '0' );
				},
				'tag_ids'            => function() {
					return ! empty( $this->product->get_tag_ids() ) ? $this->product->get_tag_ids() : array( '0' );
				},
			);
		}

		parent::prepare_fields();
	}
}
