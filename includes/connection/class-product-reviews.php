<?php
/**
 * Connection type - Comments
 *
 * Registers connections to product reviews
 *
 * @package WPGraphQL\WooCommerce\Connection
 * @since 0.3.2
 */

namespace WPGraphQL\WooCommerce\Connection;

use WPGraphQL\Connection\Comments;

/**
 * Class - Product_Reviews
 */
class Product_Reviews extends Comments {

	/**
	 * Registers connection.
	 */
	public static function register_connections() {
		// From Products.
		register_graphql_connection(
			self::get_connection_config(
				array(
					'fromType'         => 'Product',
					'toType'           => 'Comment',
					'fromFieldName'    => 'reviews',
					'connectionFields' => array(
						'averageRating' => array(
							'type'        => 'Float',
							'description' => __( 'Average review rating for this product.', 'wp-graphql-woocommerce' ),
							'resolve'     => function( $source ) {
								if ( empty( $source['edges'] ) ) {
									return 0;
								}
								$product = $source['edges'][0]['source'];
								return $product->averageRating; // @codingStandardsIgnoreLine
							},
						),
					),
					'edgeFields'       => array(
						'rating' => array(
							'type'        => 'Float',
							'description' => __( 'Review rating', 'wp-graphql-woocommerce' ),
							'resolve'     => function( $source ) {
								$review = $source['node'];
								$rating = get_comment_meta( $review->commentId, 'rating', true );
								return $rating ? $rating : 0;
							},
						),
					),
				)
			)
		);
	}
}
