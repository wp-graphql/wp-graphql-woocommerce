<?php
/**
 * Mutation - deleteReview
 *
 * Registers mutations for trashing/deleting/restore a product review.
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

/**
 * Class Review_Delete_Restore
 */
class Review_Delete_Restore {

	/**
	 * Registers mutation
	 */
	public static function register_mutation() {
		// Trash/Delete mutation.
		register_graphql_mutation(
			'deleteReview',
			array(
				'inputFields'         => self::get_input_fields( true ),
				'outputFields'        => self::get_output_fields(),
				'mutateAndGetPayload' => self::mutate_and_get_payload(),
			)
		);

		// Restore mutation.
		register_graphql_mutation(
			'restoreReview',
			array(
				'inputFields'         => self::get_input_fields(),
				'outputFields'        => self::get_output_fields( true ),
				'mutateAndGetPayload' => self::mutate_and_get_payload(),
			)
		);
	}
	
	/**
	 * Defines the mutation input field configuration.
	 *
	 * @return array
	 */
	public static function get_input_fields( $delete = false ) {
		$fields =  array(
			'id' => array(
				'type'        => array(
					'non_null' => 'ID',
				),
				'description' => __( 'The ID of the target product review', 'wp-graphql-woocommerce' ),
			),
		);

		if ( $delete ) {
			$fields['forceDelete'] = array(
				'type'        => 'Boolean',
				'description' => __( 'Whether the product review should be force deleted instead of being moved to the trash', 'wp-graphql-woocommerce' ),
			);
		}

		return $fields;
	}

	/**
	 * Defines the mutation output field configuration.
	 *
	 * @return array
	 */
	public static function get_output_fields( $restore = false ) {
		return array(
			'rating'     => array(
				'type'        => 'Float',
                'description' => __( 'The product rating of the affected product review', 'wp-graphql-woocommerce' ),
                'resolve'     => function( $payload ) {
                    if ( ! isset( $payload['rating'] ) ) {
						return null;
                    }
                    
                    return floatval( $payload['rating'] );
                }
			),
			'affectedId' => array(
				'type'        => 'Id',
				'description' => __( 'The affected product review ID', 'wp-graphql-woocommerce' ),
				'resolve'     => function( $payload ) {
					$deleted = (object) $payload['commentObject'];

					return ! empty( $deleted->comment_ID ) ? Relay::toGlobalId( 'comment', absint( $deleted->comment_ID ) ) : null;
				},
			),
			'review'     => array(
				'type'        => 'Comment',
				'description' => __( 'The affected product review', 'wp-graphql-woocommerce' ),
				'resolve'     => function( $payload, $args, AppContext $context, ResolveInfo $info ) use ( $restore ) {
					if ( empty( $payload['commentObject'] ) ) {
						return null;
					}

					if ( $restore ) {
						return ! empty( $payload['commentObject']->comment_ID )
							? DataSource::resolve_comment( absint( $payload['commentObject']->comment_ID ), $context )
							: null;
					}

					return $payload['commentObject'];
				},
			)
		);
	}

	/**
	 * Defines the mutation data modification closure.
	 *
	 * @return callable
	 */
	public static function mutate_and_get_payload() {
		return function( $input, AppContext $context, ResolveInfo $info ) {
			// Retrieve the product review rating for the payload.
			$id_parts = Relay::fromGlobalId( $input['id'] );
			if ( empty( $id_parts['id'] ) ) {
				throw new UserError( __( 'Invalid Product Review ID provided', 'wp-graphql-woocommerce' ) );
			}

			$rating = get_comment_meta( absint( $id_parts['id'] ), 'rating' );

			// @codingStandardsIgnoreLine
			switch ( $info->fieldName ) {
				case 'deleteReview':
					$classname = '\WPGraphQL\Mutation\CommentDelete';
					break;
				case 'restoreReview':
					$classname = '\WPGraphQL\Mutation\CommentRestore';
					break;
			}

			// Get the comment mutation resolver
			$resolver = $classname::mutate_and_get_payload();

			// Execute and retrieve payload.
			$payload  = $resolver( $input, $context, $info );

			// Add rating to payload.
			$payload['rating'] = $rating;

			return $payload;
		};
	}
}