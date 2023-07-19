<?php
/**
 * Mutation - writeReview
 *
 * Registers mutation for creating a new product review.
 *
 * @package WPGraphQL\WooCommerce\Mutation
 * @since 0.5.1
 */

namespace WPGraphQL\WooCommerce\Mutation;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\Model\Comment;
use WPGraphQL\Mutation\CommentCreate;

/**
 * Class Review_Write
 */
class Review_Write {
	/**
	 * Registers mutation
	 *
	 * @return void
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'writeReview',
			[
				'inputFields'         => self::get_input_fields(),
				'outputFields'        => self::get_output_fields(),
				'mutateAndGetPayload' => self::mutate_and_get_payload(),
			]
		);
	}

	/**
	 * Defines the mutation input field configuration
	 *
	 * @return array
	 */
	public static function get_input_fields() {
		$comment_input_fields = CommentCreate::get_input_fields();
		unset( $comment_input_fields['type'] );

		return array_merge(
			$comment_input_fields,
			[
				'rating' => [
					'type'        => [ 'non_null' => 'Int' ],
					'description' => __( 'Product rating', 'wp-graphql-woocommerce' ),
				],
			]
		);
	}

	/**
	 * Defines the mutation output field configuration
	 *
	 * @return array
	 */
	public static function get_output_fields() {
		return [
			'rating' => [
				'type'        => 'Float',
				'description' => __( 'The product rating of the review that was created', 'wp-graphql-woocommerce' ),
				'resolve'     => static function ( $payload ) {
					if ( ! isset( $payload['id'] ) || ! absint( $payload['id'] ) ) {
						return null;
					}
					return (float) get_comment_meta( $payload['id'], 'rating', true );
				},
			],
			'review' => [
				'type'        => 'Comment',
				'description' => __( 'The product review that was created', 'wp-graphql-woocommerce' ),
				'resolve'     => static function ( $payload, $args, AppContext $context ) {
					if ( ! isset( $payload['id'] ) || ! absint( $payload['id'] ) ) {
						return null;
					}
					$comment = get_comment( $payload['id'] );

					if ( null === $comment ) {
						return null;
					}

					return new Comment( $comment );
				},
			],
		];
	}

	/**
	 * Defines the mutation data modification closure.
	 *
	 * @return callable
	 */
	public static function mutate_and_get_payload() {
		return static function ( $input, AppContext $context, ResolveInfo $info ) {
			// Set comment type to "review".
			$input['type'] = 'review';

			$resolver = CommentCreate::mutate_and_get_payload();

			$payload = $resolver( $input, $context, $info );
			if ( is_a( $payload, UserError::class ) ) {
				throw $payload;
			}

			// Set product rating upon successful creation of the review.
			if ( $payload['success'] ) {
				add_comment_meta( $payload['id'], 'rating', $input['rating'] );
			}

			return $payload;
		};
	}
}
