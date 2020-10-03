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
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;
use WPGraphQL\Data\DataSource;
use WPGraphQL\Model\Comment;
use WPGraphQL\Mutation\CommentCreate;

/**
 * Class Review_Write
 */
class Review_Write {

	/**
	 * Registers mutation
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'writeReview',
			array(
				'inputFields'         => self::get_input_fields(),
				'outputFields'        => self::get_output_fields(),
				'mutateAndGetPayload' => self::mutate_and_get_payload(),
			)
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
			array(
				'rating'         => array(
					'type'        => array( 'non_null' => 'Int' ),
					'description' => __( 'Product rating', 'wp-graphql-woocommerce' ),
				),
			)
		);
	}

	/**
	 * Defines the mutation output field configuration
	 *
	 * @return array
	 */
	public static function get_output_fields() {
		return array(
            'rating' => array(
                'type'        => 'Float',
                'description' => __( 'The product rating of the review that was created', 'wp-graphql-woocommerce' ),
                'resolve'     => function( $payload ) {
					if ( ! isset( $payload['id'] ) || ! absint( $payload['id'] ) ) {
						return null;
                    }
                    return (float) get_comment_meta( $payload['id'], 'rating', true );
                }
            ),
			'review' => array(
                'type'        => 'Comment',
                'description' => __( 'The product review that was created', 'wp-graphql-woocommerce' ),
				'resolve'     => function( $payload, $args, AppContext $context ) {
					if ( ! isset( $payload['id'] ) || ! absint( $payload['id'] ) ) {
						return null;
                    }
                    $comment = get_comment( $payload['id'] );
					return new Comment( $comment );
				},
			),
		);
	}

	/**
	 * Defines the mutation data modification closure.
	 *
	 * @return callable
	 */
	public static function mutate_and_get_payload() {
		return function( $input, AppContext $context, ResolveInfo $info ) {
            // Set comment type to "review".
            $input['type'] = 'review';

            $resolver = CommentCreate::mutate_and_get_payload();

            $payload = $resolver( $input, $context, $info );

            // Set product rating upon successful creation of the review.
            if ( $payload['success'] ) {
                add_comment_meta( $payload['id'], 'rating', $input['rating'] );
            }

			return $payload;
		};
	}
}
