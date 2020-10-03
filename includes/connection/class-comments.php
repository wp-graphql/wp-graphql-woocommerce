<?php
/**
 * Connection type - Comments
 *
 * Registers connections to comments from woocommerce post-types
 *
 * @package WPGraphQL\WooCommerce\Connection
 * @since 0.3.2
 */

namespace WPGraphQL\WooCommerce\Connection;

use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;

/**
 * Class - Comments
 */
class Comments extends \WPGraphQL\Connection\Comments {

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

		// From Orders.
		register_graphql_connection(
			self::get_connection_config(
				array(
					'fromType'         => 'Order',
					'toType'           => 'Comment',
					'fromFieldName'    => 'orderNotes',
					'edgeFields'       => array(
						'isCustomerNote' => array(
							'type'        => 'Boolean',
							'description' => __( 'Is this a customer note?', 'wp-graphql-woocommerce' ),
							'resolve'     => function( $source ) {
								$note = $source['node'];
								return get_comment_meta( $note->commentId, 'is_customer_note', true );
							},
						),
					),
					'resolve'  => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
						$resolver = new \WPGraphQL\Data\Connection\CommentConnectionResolver( $source, $args, $context, $info );

						$resolver->set_query_arg( 'post_id', $source->ID );
						$resolver->set_query_arg( 'approve', 'approve' );
						$resolver->set_query_arg( 'type', '' );

						if ( ! current_user_can( 'edit_shop_orders', $source->ID ) ) {
							$resolver->set_query_arg( 'meta_key', 'is_customer_note' );
							$resolver->set_query_arg( 'meta_value', true );
						}

						remove_filter( 'comments_clauses', array( 'WC_Comments', 'exclude_order_comments' ) );

						$connection = $resolver->get_connection();

						add_filter( 'comments_clauses', array( 'WC_Comments', 'exclude_order_comments' ) );

						return $connection;
					},
				)
			)
		);
	}
}
