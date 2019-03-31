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
use WPGraphQL\Data\DataSource;
use WPGraphQL\Model\Model;

/**
 * Class Product
 */
class Product extends Model {
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
			'userId',
			'name',
			'firstName',
			'lastName',
			'description',
			'slug',
		];

		parent::__construct( 'ProductObject', $this->product, 'list_users', $allowed_restricted_fields, $id );
		$this->init();
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
			default:
				return new \WC_Product_Simple( $id );
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
				'id'                 => function() {
					return ! empty( $this->product )
						? Relay::toGlobalId( 'product', $this->product->get_id() )
						: null;
				},
				'productId'          => function() {
					return ! empty( $this->product ) ? $this->product->get_id() : null;
				},
				'ID'                 => function() {
					return $this->product->get_id();
				},
				'type'               => function() {
					return ! empty( $this->product ) ? $this->product->get_type() : null;
				},
				'slug'               => function() {
					return ! empty( $this->product ) ? $this->product->get_slug() : null;
				},
				'name'               => function() {
					return ! empty( $this->product ) ? $this->product->get_name() : null;
				},
				'status'             => function() {
					return ! empty( $this->product ) ? $this->product->get_status() : null;
				},
				'featured'           => function() {
					return ! empty( $this->product ) ? $this->product->get_featured() : null;
				},
				'catalogVisibility'  => function() {
					return ! empty( $this->product ) ? $this->product->get_catalog_visibility() : null;
				},
				'description'        => function() {
					return ! empty( $this->product ) ? $this->product->get_description() : null;
				},
				'shortDescription'   => function() {
					return ! empty( $this->product ) ? $this->product->get_short_description() : null;
				},
				'sku'                => function() {
					return ! empty( $this->product ) ? $this->product->get_sku() : null;
				},
				'price'              => function() {
					if ( ! empty( $this->product ) ) {
						if ( 'variable' === $this->product->get_type() ) {
							return $this->product->get_variation_price( 'min' );
						}
						return $this->product->get_price();
					}
					return null;
				},
				'priceMax'           => function() {
					if ( ! empty( $this->product ) ) {
						if ( 'variable' === $this->product->get_type() ) {
							return $this->product->get_variation_price( 'max' );
						}
						return $this->product->get_price();
					}
					return null;
				},
				'regularPrice'       => function() {
					if ( ! empty( $this->product ) ) {
						if ( 'variable' === $this->product->get_type() ) {
							return $this->product->get_variation_regular_price( 'min' );
						}
						return $this->product->get_regular_price();
					}
					return null;
				},
				'regularPriceMax'    => function() {
					if ( ! empty( $this->product ) ) {
						if ( 'variable' === $this->product->get_type() ) {
							return $this->product->get_variation_regular_price( 'max' );
						}
						return $this->product->get_regular_price();
					}
					return null;
				},
				'salePrice'          => function() {
					if ( ! empty( $this->product ) ) {
						if ( 'variable' === $this->product->get_type() ) {
							return $this->product->get_variation_sale_price( 'min' );
						}
						return $this->product->get_sale_price();
					}
					return null;
				},
				'salePriceMax'       => function() {
					if ( ! empty( $this->product ) ) {
						if ( 'variable' === $this->product->get_type() ) {
							return $this->product->get_variation_sale_price( 'max' );
						}
						return $this->product->get_sale_price();
					}
					return null;
				},
				'dateOnSaleFrom'     => function() {
					return ! empty( $this->product ) ? $this->product->get_date_on_sale_from() : null;
				},
				'dateOnSaleTo'       => function() {
					return ! empty( $this->product ) ? $this->product->get_date_on_sale_to() : null;
				},
				'totalSales'         => function() {
					return ! empty( $this->product ) ? $this->product->get_total_sales() : null;
				},
				'taxStatus'          => function() {
					return ! empty( $this->product ) ? $this->product->get_tax_status() : null;
				},
				'taxClass'           => function() {
					return ! empty( $this->product ) ? $this->product->get_tax_class() : null;
				},
				'manageStock'        => function() {
					return ! empty( $this->product ) ? $this->product->get_manage_stock() : null;
				},
				'stockQuantity'      => function() {
					return ! empty( $this->product ) ? $this->product->get_stock_quantity() : null;
				},
				'stockStatus'        => function() {
					return ! empty( $this->product ) ? $this->product->get_stock_status() : null;
				},
				'backorders'         => function() {
					return ! empty( $this->product ) ? $this->product->get_backorders() : null;
				},
				'soldIndividually'   => function() {
					return ! empty( $this->product ) ? $this->product->get_sold_individually() : null;
				},
				'weight'             => function() {
					return ! empty( $this->product ) ? $this->product->get_weight() : null;
				},
				'length'             => function() {
					return ! empty( $this->product ) ? $this->product->get_length() : null;
				},
				'width'              => function() {
					return ! empty( $this->product ) ? $this->product->get_width() : null;
				},
				'height'             => function() {
					return ! empty( $this->product ) ? $this->product->get_height() : null;
				},
				'reviewsAllowed'     => function() {
					return ! empty( $this->product ) ? $this->product->get_reviews_allowed() : null;
				},
				'purchaseNote'       => function() {
					return ! empty( $this->product ) ? $this->product->get_purchase_note() : null;
				},
				'menuOrder'          => function() {
					return ! empty( $this->product ) ? $this->product->get_menu_order() : null;
				},
				'virtual'            => function() {
					return ! empty( $this->product ) ? $this->product->get_virtual() : null;
				},
				'downloadExpiry'     => function() {
					return ! empty( $this->product ) ? $this->product->get_download_expiry() : null;
				},
				'downloadable'       => function() {
					return ! empty( $this->product ) ? $this->product->get_downloadable() : null;
				},
				'downloadLimit'      => function() {
					return ! empty( $this->product ) ? $this->product->get_download_limit() : null;
				},
				'ratingCount'        => function() {
					return ! empty( $this->product ) ? $this->product->get_rating_counts() : null;
				},
				'averageRating'      => function() {
					return ! empty( $this->product ) ? $this->product->get_average_rating() : null;
				},
				'reviewCount'        => function() {
					return ! empty( $this->product ) ? $this->product->get_review_count() : null;
				},
				'parentId'           => function() {
					return ! empty( $this->product ) ? $this->product->get_parent_id() : null;
				},
				'imageId'            => function () {
					return ! empty( $this->product ) ? $this->product->get_image_id() : null;
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
							case is_a( 'external' ):
							case is_a( 'grouped' ):
								return null;
							default:
								return $this->product->get_upsell_ids();
						}
					}

					return null;
				},
				'cross_sell_ids'     => function() {
					if ( ! empty( $this->product ) ) {
						switch ( $this->product->get_type() ) {
							case is_a( 'external' ):
							case is_a( 'grouped' ):
								return null;
							default:
								return $this->product->get_cross_sell_ids();
						}
					}

					return null;
				},
				'attributes'         => function() {
					return ! empty( $this->product ) ? array_values( $this->product->get_attributes() ) : null;
				},
				'default_attributes' => function() {
					return ! empty( $this->product ) ? array_values( $this->product->get_default_attributes() ) : null;
				},
				'downloads'          => function() {
					return ! empty( $this->product ) ? $this->product->get_downloads() : null;
				},
				'gallery_image_ids'  => function() {
					return ! empty( $this->product ) ? $this->product->get_gallery_image_ids() : null;
				},
				'children_ids'       => function() {
					if ( ! empty( $this->product ) && 'variable' === $this->product->get_type() ) {
						return $this->product->get_children();
					}

					return null;
				},
			);
		}

		parent::prepare_fields();
	}
}
