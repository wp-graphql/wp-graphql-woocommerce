<?php
/**
 * WPObject Type - Product_Rating_Counter_Type
 *
 * Registers ProductRatingCounter type and queries
 *
 * @package \WPGraphQL\Extensions\WooCommerce\Type\WPObject
 * @since   0.0.1
 */

namespace WPGraphQL\Extensions\WooCommerce\Type\WPObject;

use WPGraphQL\Extensions\WooCommerce\Data\Factory;
use WPGraphQL\Data\DataSource;
use GraphQLRelay\Relay;

/**
 * Class Product_Rating_Counter_Type
 */
class Product_Rating_Counter_Type {
	/**
	 * Register ProductRatingCounter type
	 */
	public static function register() {
		register_graphql_object_type(
			'RatingCounter',
			array(
				'description' => __( 'A product rating counter', 'wp-graphql-woocommerce' ),
				'fields'      => array(
					'fiveStars'  => array(
						'type'        => 'Int',
						'description' => __( 'Product\'s number of 5-star ratings', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source ) {
							return isset( $source[5] ) ? $source[5] : 0;
						},
					),
					'fourStars'  => array(
						'type'        => 'Int',
						'description' => __( 'Product\'s number of 4-star ratings', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source ) {
							return isset( $source[4] ) ? $source[4] : 0;
						},
					),
					'threeStars' => array(
						'type'        => 'Int',
						'description' => __( 'Product\'s number of 3-star ratings', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source ) {
							return isset( $source[3] ) ? $source[3] : 0;
						},
					),
					'twoStars'   => array(
						'type'        => 'Int',
						'description' => __( 'Product\'s number of 2-star ratings', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source ) {
							return isset( $source[2] ) ? $source[2] : 0;
						},
					),
					'oneStars'   => array(
						'type'        => 'Int',
						'description' => __( 'Product\'s number of 1-star ratings', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source ) {
							return isset( $source[1] ) ? $source[1] : 0;
						},
					),
					'average'    => array(
						'type'        => 'Float',
						'description' => __( 'Product\'s rating average', 'wp-graphql-woocommerce' ),
						'resolve'     => function( $source ) {
							$count = 0;
							$total = 0;
							foreach ( $source as $grade => $num ) {
								$count += $num;
								$total += absint( $grade ) * $num;
							}
							return ! empty( $count ) ? min( 5, round( $total / $count, 2 ) ) : 0;
						},
					),
				),
			)
		);
	}
}
