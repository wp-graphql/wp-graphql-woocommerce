<?php
/**
 * WPObject Type - WC_Post
 *
 * Registers additional fields for WooCommerce post-types
 * Note: Overwrites any fields with identical names
 *
 * @package \WPGraphQL\Extensions\WooCommerce\Type\WPObject
 * @since   0.0.1
 */

namespace WPGraphQL\Extensions\WooCommerce\Type\WPObject;

/**
 * Class WC_Post_Object
 */
class WC_Post_Object {
	/**
	 * Deregister unnecessary or colliding fields, and
	 * registers the necessary fields
	 */
	public static function register( $post_type_object ) {
		switch ( $post_type_object->graphql_single_name ) {
			case 'coupon':
				register_graphql_fields(
					$post_type_object->graphql_single_name,
					array(
						'code'               => array(
							'type'        => 'String',
							'description' => __( 'Coupon code', 'wp-graphql-woocommerce' ),
						),
						'description'        => array(
							'type'        => 'String',
							'description' => __( 'Explanation of what the coupon does', 'wp-graphql-woocommerce' ),
						),
						'discountType'       => array(
							'type'        => 'DiscountTypeEnum',
							'description' => __( 'Type of discount', 'wp-graphql-woocommerce' ),
						),
						'amount'             => array(
							'type'        => 'Float',
							'description' => __( 'Amount off provided by the coupon', 'wp-graphql-woocommerce' ),
						),
						'dateExpiry'         => array(
							'type'        => 'String',
							'description' => __( 'Date coupon expires', 'wp-graphql-woocommerce' ),
						),
						'usageCount'         => array(
							'type'        => 'Int',
							'description' => __( 'How many times the coupon has been used', 'wp-graphql-woocommerce' ),
						),
						'individualUse'      => array(
							'type'        => 'Boolean',
							'description' => __( 'Individual use means this coupon cannot be used in conjunction with other coupons', 'wp-graphql-woocommerce' ),
						),
						'usageLimit'         => array(
							'type'        => 'Int',
							'description' => __( 'Amount of times this coupon can be used globally', 'wp-graphql-woocommerce' ),
						),
						'usageLimitPerUser'  => array(
							'type'        => 'Int',
							'description' => __( 'Amount of times this coupon can be used by a customer', 'wp-graphql-woocommerce' ),
						),
						'limitUsageToXItems' => array(
							'type'        => 'Int',
							'description' => __( 'The number of products in your cart this coupon can apply to (for product discounts)', 'wp-graphql-woocommerce' ),
						),
						'freeShipping'       => array(
							'type'        => 'Boolean',
							'description' => __( 'Does this coupon grant free shipping?', 'wp-graphql-woocommerce' ),
						),
						'excludeSaleItems'   => array(
							'type'        => 'Boolean',
							'description' => __( 'Excluding sale items mean this coupon cannot be used on items that are on sale (or carts that contain on sale items)', 'wp-graphql-woocommerce' ),
						),
						'minimumAmount'      => array(
							'type'        => 'Float',
							'description' => __( 'Minimum spend amount that must be met before this coupon can be used', 'wp-graphql-woocommerce' ),
						),
						'maximumAmount'      => array(
							'type'        => 'Float',
							'description' => __( 'Maximum spend amount that must be met before this coupon can be used ', 'wp-graphql-woocommerce' ),
						),
						'emailRestrictions'  => array(
							'type'        => array( 'list_of' => 'String' ),
							'description' => __( 'Only customers with a matching email address can use the coupon', 'wp-graphql-woocommerce' ),
						),
					)
				);
				break;

			case 'product':
			case 'productVariation':
				deregister_graphql_field( $post_type_object->graphql_single_name, 'status' );
				deregister_graphql_field( $post_type_object->graphql_single_name, 'parent' );
				register_graphql_fields(
					$post_type_object->graphql_single_name,
					array(
						'name'              => array(
							'type'        => 'String',
							'description' => __( 'Product name', 'wp-graphql-woocommerce' ),
						),
						'status'            => array(
							'type'        => 'String',
							'description' => __( 'Product status', 'wp-graphql-woocommerce' ),
						),
						'featured'          => array(
							'type'        => 'Boolean',
							'description' => __( 'If the product is featured', 'wp-graphql-woocommerce' ),
						),
						'catalogVisibility' => array(
							'type'        => 'CatalogVisibilityEnum',
							'description' => __( 'Catalog visibility', 'wp-graphql-woocommerce' ),
						),
						'description'       => array(
							'type'        => 'String',
							'description' => __( 'Product description', 'wp-graphql-woocommerce' ),
						),
						'shortDescription'  => array(
							'type'        => 'String',
							'description' => __( 'Product short description', 'wp-graphql-woocommerce' ),
						),
						'sku'               => array(
							'type'        => 'String',
							'description' => __( 'Product SKU', 'wp-graphql-woocommerce' ),
						),
						'price'             => array(
							'type'        => 'String',
							'description' => __( 'Product\'s active price', 'wp-graphql-woocommerce' ),
						),
						'regularPrice'      => array(
							'type'        => 'String',
							'description' => __( 'Product\'s regular price', 'wp-graphql-woocommerce' ),
						),
						'salePrice'         => array(
							'type'        => 'String',
							'description' => __( 'Product\'s sale price', 'wp-graphql-woocommerce' ),
						),
						'dateOnSaleFrom'    => array(
							'type'        => 'String',
							'description' => __( 'Date on sale from', 'wp-graphql-woocommerce' ),
						),
						'dateOnSaleTo'      => array(
							'type'        => 'String',
							'description' => __( 'Date on sale to', 'wp-graphql-woocommerce' ),
						),
						'totalSales'        => array(
							'type'        => 'Int',
							'description' => __( 'Number total of sales', 'wp-graphql-woocommerce' ),
						),
						'taxStatus'         => array(
							'type'        => 'TaxStatusEnum',
							'description' => __( 'Tax status', 'wp-graphql-woocommerce' ),
						),
						'taxClass'          => array(
							'type'        => 'String',
							'description' => __( 'Tax class', 'wp-graphql-woocommerce' ),
						),
						'manageStock'       => array(
							'type'        => 'Boolean',
							'description' => __( 'If product manage stock', 'wp-graphql-woocommerce' ),
						),
						'stockQuantity'     => array(
							'type'        => 'Int',
							'description' => __( 'Number of items available for sale', 'wp-graphql-woocommerce' ),
						),
						'stockStatus'       => array(
							'type'        => 'StockStatusEnum',
							'description' => __( 'Product stock status', 'wp-graphql-woocommerce' ),
						),
						'backorders'        => array(
							'type'        => 'BackordersEnum',
							'description' => __( 'Product backorders status', 'wp-graphql-woocommerce' ),
						),
						'soldIndividually'  => array(
							'type'        => 'Boolean',
							'description' => __( 'If should be sold individually', 'wp-graphql-woocommerce' ),
						),
						'weight'            => array(
							'type'        => 'String',
							'description' => __( 'Product\'s weight', 'wp-graphql-woocommerce' ),
						),
						'length'            => array(
							'type'        => 'String',
							'description' => __( 'Product\'s length', 'wp-graphql-woocommerce' ),
						),
						'width'             => array(
							'type'        => 'String',
							'description' => __( 'Product\'s width', 'wp-graphql-woocommerce' ),
						),
						'height'            => array(
							'type'        => 'String',
							'description' => __( 'Product\'s height', 'wp-graphql-woocommerce' ),
						),
						'reviewsAllowed'    => array(
							'type'        => 'Boolean',
							'description' => __( 'If reviews are allowed', 'wp-graphql-woocommerce' ),
						),
						'purchaseNote'      => array(
							'type'        => 'String',
							'description' => __( 'Purchase note', 'wp-graphql-woocommerce' ),
						),
						'menuOrder'         => array(
							'type'        => 'Int',
							'description' => __( 'Menu order', 'wp-graphql-woocommerce' ),
						),
						'virtual'           => array(
							'type'        => 'Boolean',
							'description' => __( 'Is product virtual?', 'wp-graphql-woocommerce' ),
						),
						'downloadExpiry'    => array(
							'type'        => 'Int',
							'description' => __( 'Download expiry', 'wp-graphql-woocommerce' ),
						),
						'downloadable'      => array(
							'type'        => 'Boolean',
							'description' => __( 'Is downloadable?', 'wp-graphql-woocommerce' ),
						),
						'downloadLimit'     => array(
							'type'        => 'Int',
							'description' => __( 'Download limit', 'wp-graphql-woocommerce' ),
						),
						'ratingCount'       => array(
							'type'        => array( 'list_of' => 'String' ),
							'description' => __( 'Product rating count', 'wp-graphql-woocommerce' ),
						),
						'averageRating'     => array(
							'type'        => 'Float',
							'description' => __( 'Product average count', 'wp-graphql-woocommerce' ),
						),
						'reviewCount'       => array(
							'type'        => 'Int',
							'description' => __( 'Product review count', 'wp-graphql-woocommerce' ),
						),
						'parentId'            => array(
							'type'        => 'Int',
							'description' => __( 'Parent product ID', 'wp-graphql-woocommerce' ),
						),
						'parent'            => array(
							'type'        => 'Product',
							'description' => __( 'Parent product', 'wp-graphql-woocommerce' ),
						),
						'image'             => array(
							'type'        => 'MediaItem',
							'description' => __( 'Main image', 'wp-graphql-woocommerce' ),
						),
					)
				);
				break;

			case 'order':
				register_graphql_fields(
					$post_type_object->graphql_single_name,
					array(

					) 
				);
				break;

			case 'order_refund':
				register_graphql_fields(
					$post_type_object->graphql_single_name,
					array(

					)
				);
				break;
		}
	}
}