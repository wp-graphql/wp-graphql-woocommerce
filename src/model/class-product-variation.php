<?php
/**
 * Model - Product_Variation
 *
 * Resolves product variation crud object model
 *
 * @package WPGraphQL\Extensions\WooCommerce\Model
 * @since 0.0.1
 */

namespace WPGraphQL\Extensions\WooCommerce\Model;

use GraphQLRelay\Relay;
use WPGraphQL\Data\DataSource;
use WPGraphQL\Model\Model;

/**
 * Class Product_Variation
 */
class Product_Variation extends Model {
	/**
	 * Stores the instance of WC_Product_Variation
	 *
	 * @var \WC_Product_Variation $variation
	 * @access protected
	 */
	protected $variation;

	/**
	 * Product_Variation constructor
	 *
	 * @param int $id - product_variation post-type ID.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct( $id ) {
		$this->variation           = new \WC_Product_Variation( $id );
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

		parent::__construct( 'ProductVariationObject', $this->variation, 'list_users', $allowed_restricted_fields, $id );
		$this->init();
	}

	/**
	 * Initializes the ProductVariation field resolvers
	 *
	 * @access public
	 */
	public function init() {
		if ( 'private' === $this->get_visibility() || is_null( $this->variation ) ) {
			return null;
		}

		if ( empty( $this->fields ) ) {
			$this->fields = array(
				'ID'                 => function() {
					return $this->variation->get_id();
				},
				'id'                 => function() {
					return ! empty( $this->variation ) ? Relay::toGlobalId( 'product_variation', $this->variation->get_id() ) : null;
				},
				'variationId'        => function() {
					return ! empty( $this->variation ) ? $this->variation->get_id() : null;
				},
				'sku'                => function() {
					return ! empty( $this->variation ) ? $this->variation->get_sku() : null;
				},
				'weight'             => function() {
					return ! empty( $this->variation ) ? $this->variation->get_weight() : null;
				},
				'length'             => function() {
					return ! empty( $this->variation ) ? $this->variation->get_length() : null;
				},
				'width'              => function() {
					return ! empty( $this->variation ) ? $this->variation->get_width() : null;
				},
				'height'             => function() {
					return ! empty( $this->variation ) ? $this->variation->get_height() : null;
				},
				'taxClass'           => function() {
					return ! empty( $this->variation ) ? $this->variation->get_tax_class() : null;
				},
				'manageStock'        => function() {
					return ! empty( $this->variation ) ? $this->variation->get_manage_stock() : null;
				},
				'stockQuantity'      => function() {
					return ! empty( $this->variation ) ? $this->variation->get_stock_quantity() : null;
				},
				'backorders'         => function() {
					return ! empty( $this->variation ) ? $this->variation->get_backorders() : null;
				},
				'purchaseNote'       => function() {
					return ! empty( $this->variation ) ? $this->variation->get_purchase_note() : null;
				},
				'catalogVisibility'  => function() {
					return ! empty( $this->variation ) ? $this->variation->get_catalog_visibility() : null;
				},
				'hasAttributes'      => function() {
					return ! empty( $this->variation ) ? $this->variation->has_attributes() : null;
				},
				'isPurchasable'      => function() {
					return ! empty( $this->variation ) ? $this->variation->is_purchasable() : null;
				},
				'price'              => function() {
					return ! empty( $this->variation ) ? $this->variation->get_price() : null;
				},
				'salePrice'          => function() {
					return ! empty( $this->variation ) ? $this->variation->get_sale_price() : null;
				},
				'regularPrice'       => function() {
					return ! empty( $this->variation ) ? $this->variation->get_regular_price() : null;
				},
				/**
				 * Connection resolvers fields
				 *
				 * These field resolvers are used in connection resolvers to define WP_Query argument
				 * Note: underscore naming style is used as a quick identifier
				 */
				'parent_id'          => function() {
					return ! empty( $this->variation ) ? $this->variation->get_parent_id() : null;
				},
				'shipping_class_id'  => function() {
					return ! empty( $this->variation ) ? $this->variation->get_shipping_class_id() : null;
				},
				'image_id'           => function() {
					return ! empty( $this->variation ) ? $this->variation->get_image_id() : null;
				},
				'attributes'         => function() {
					return ! empty( $this->variation ) ? array_values( $this->variation->get_attributes() ) : null;
				},
				'default_attributes' => function() {
					return ! empty( $this->variation ) ? array_values( $this->variation->get_default_attributes() ) : null;
				},
			);
		}

		parent::prepare_fields();
	}
}
