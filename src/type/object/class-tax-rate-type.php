<?php
/**
 * WPObject Type - Tax_Rate_Type
 *
 * Registers TaxRate WPObject type and queries
 *
 * @package \WPGraphQL\Extensions\WooCommerce\Type\WPObject
 * @since   0.0.2
 */

namespace WPGraphQL\Extensions\WooCommerce\Type\WPObject;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;
use WPGraphQL\Type\WPObjectType;
use WPGraphQL\Extensions\WooCommerce\Data\Factory;
use WPGraphQL\Extensions\WooCommerce\Model\Tax_Rate;

/**
 * Class Tax_Rate_Type
 */
class Tax_Rate_Type {
	/**
	 * Registers tax rate type
	 */
	public static function register() {
		wc_register_graphql_object_type(
			'TaxRate',
			array(
				'description'       => __( 'A Tax rate object', 'wp-graphql-woocommercer' ),
				'interfaces'        => [ WPObjectType::node_interface() ],
				'fields'            => array(
					'id'       => array(
						'type'        => array( 'non_null' => 'ID' ),
						'description' => __( 'The globally unique identifier for the tax rate.', 'wp-graphql-woocommerce' ),
					),
					'rateId'   => array(
						'type'        => 'Int',
						'description' => __( 'The ID of the tax rate.', 'wp-graphql-woocommerce' ),
					),
					'country'  => array(
						'type'        => 'String',
						'description' => __( 'Country ISO 3166 code.', 'wp-graphql-woocommerce' ),
					),
					'state'    => array(
						'type'        => 'String',
						'description' => __( 'State code.', 'wp-graphql-woocommerce' ),
					),
					'postcode' => array(
						'type'        => array( 'list_of' => 'String' ),
						'description' => __( 'Postcode/ZIP.', 'wp-graphql-woocommerce' ),
					),
					'city'     => array(
						'type'        => array( 'list_of' => 'String' ),
						'description' => __( 'City name.', 'wp-graphql-woocommerce' ),
					),
					'rate'     => array(
						'type'        => 'String',
						'description' => __( 'Tax rate.', 'wp-graphql-woocommerce' ),
					),
					'name'     => array(
						'type'        => 'String',
						'description' => __( 'Tax rate name.', 'wp-graphql-woocommerce' ),
					),
					'priority' => array(
						'type'        => 'Int',
						'description' => __( 'Tax priority.', 'wp-graphql-woocommerce' ),
					),
					'compound' => array(
						'type'        => 'Boolean',
						'description' => __( 'Whether or not this is a compound rate.', 'wp-graphql-woocommerce' ),
					),
					'shipping' => array(
						'type'        => 'Boolean',
						'description' => __( 'Whether or not this tax rate also gets applied to shipping.', 'wp-graphql-woocommerce' ),
					),
					'order'    => array(
						'type'        => 'Int',
						'description' => __( 'Indicates the order that will appear in queries.', 'wp-graphql-woocommerce' ),
					),
					'class'    => array(
						'type'        => 'TaxClassEnum',
						'description' => __( 'Tax class. Default is standard.', 'wp-graphql-woocommerce' ),
					),
				),
				'resolve_node'      => function( $node, $id, $type, AppContext $context ) {
					if ( 'tax_rate' === $type ) {
						$node = Factory::resolve_tax_rate( $id );
					}

					return $node;
				},
				'resolve_node_type' => function( $type, $node ) {
					if ( is_a( $node, Tax_Rate::class ) ) {
						$type = 'TaxRate';
					}

					return $type;
				},
			)
		);

		register_graphql_field(
			'RootQuery',
			'taxRate',
			array(
				'type'        => 'TaxRate',
				'description' => __( 'A tax rate object', 'wp-graphql-woocommerce' ),
				'args'        => array(
					'id'     => array(
						'type'        => 'ID',
						'description' => __( 'Get the tax rate by its global ID', 'wp-graphql-woocommerce' ),
					),
					'rateId' => array(
						'type'        => 'Int',
						'description' => __( 'Get the tax rate by its database ID', 'wp-graphql-woocommerce' ),
					),
				),
				'resolve'     => function ( $source, array $args ) {
					$rate_id = 0;
					if ( ! empty( $args['id'] ) ) {
						$id_components = Relay::fromGlobalId( $args['id'] );
						if ( empty( $id_components['id'] ) || empty( $id_components['type'] ) ) {
							throw new UserError( __( 'The "id" is invalid', 'wp-graphql-woocommerce' ) );
						}

						$arg          = 'ID';
						$rate_id = absint( $id_components['id'] );
					} elseif ( ! empty( $args['rateId'] ) ) {
						$arg          = 'database ID';
						$rate_id = absint( $args['rateId'] );
					}

					return Factory::resolve_tax_rate( $rate_id );
				},
			)
		);
	}
}
