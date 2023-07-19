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
	 *
	 * @return void
	 */
	public static function register_mutation() {
		// Trash/Delete mutation.
		register_graphql_mutation(
			'deleteReview',
			[
				'inputFields'         => self::get_input_fields( true ),
				'outputFields'        => self::get_output_fields(),
				'mutateAndGetPayload' => self::mutate_and_get_payload(),
			]
		);

		// Restore mutation.
		register_graphql_mutation(
			'restoreReview',
			[
				'inputFields'         => self::get_input_fields(),
				'outputFields'        => self::get_output_fields( true ),
				'mutateAndGetPayload' => self::mutate_and_get_payload(),
			]
		);
	}

	/**
	 * Defines the mutation input field configuration.
	 *
	 * @param bool $delete Whether the `forceDelete` flag should be included.
	 *
	 * @return array
	 */
	public static function get_input_fields( $delete = false ) {
		$fields = [
			'id' => [
				'type'        => [
					'non_null' => 'ID',
				],
				'description' => __( 'The ID of the target product review', 'wp-graphql-woocommerce' ),
			],
		];

		if ( $delete ) {
			$fields['forceDelete'] = [
				'type'        => 'Boolean',
				'description' => __( 'Whether the product review should be force deleted instead of being moved to the trash', 'wp-graphql-woocommerce' ),
			];
		}

		return $fields;
	}

	/**
	 * Defines the mutation output field configuration.
	 *
	 * @param bool $restore  Whether the restored review should be resolved.
	 *
	 * @return array
	 */
	public static function get_output_fields( $restore = false ) {
		return [
			'rating'     => [
				'type'        => 'Float',
				'description' => __( 'The product rating of the affected product review', 'wp-graphql-woocommerce' ),
				'resolve'     => static function ( $payload ) {
					if ( ! isset( $payload['rating'] ) ) {
						return null;
					}

					return floatval( $payload['rating'] );
				},
			],
			'affectedId' => [
				'type'        => 'Id',
				'description' => __( 'The affected product review ID', 'wp-graphql-woocommerce' ),
				'resolve'     => static function ( $payload ) {
					$deleted = (object) $payload['commentObject'];

					return ! empty( $deleted->comment_ID ) ? Relay::toGlobalId( 'comment', (string) $deleted->comment_ID ) : null;
				},
			],
			'review'     => [
				'type'        => 'Comment',
				'description' => __( 'The affected product review', 'wp-graphql-woocommerce' ),
				'resolve'     => static function ( $payload, $args, AppContext $context, ResolveInfo $info ) use ( $restore ) {
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

			if ( empty( $classname ) || ! class_exists( $classname ) ) {
				throw new UserError( __( 'Failed to find mutation resolver. Please contact site adminstrator', 'wp-graphql-woocommerce' ) );
			}

			// Get the comment mutation resolver.
			$resolver = $classname::mutate_and_get_payload();

			// Execute and retrieve payload.
			$payload = $resolver( $input, $context, $info );

			// Add rating to payload.
			$payload['rating'] = $rating;

			return $payload;
		};
	}
}
