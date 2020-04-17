<?php
/**
 * Mutation - updateReview
 *
 * Registers mutation for update an existing product review.
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
use WPGraphQL\Mutation\CommentUpdate;

/**
 * Class Review_Update
 */
class Review_Update {

	/**
	 * Registers mutation
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'updateReview',
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
        return array_merge(
			Review_Write::get_input_fields(),
			array(
				'id' => array(
					'type'        => array( 'non_null' => 'ID' ),
					'description' => __( 'The ID of the review being updated.', 'wp-graphql-woocommerce' ),
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
		return Review_Write::get_output_fields();
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

            $resolver = CommentUpdate::mutate_and_get_payload();

            $payload = $resolver( $input, $context, $info );

            // Check if product rating needs updating.
            if ( ! empty( $payload['id'] ) && isset( $input['rating'] ) ) {
				$success = update_comment_meta( $payload['id'], 'rating', $input['rating'] );
				
				if ( ! $success ) {
					throw UserError( __( 'Failed to update review rating', 'wp-graphql-woocommerce' ) );
				}
            }

			return $payload;
		};
	}
}
