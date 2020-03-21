<?php
/**
 * WPObject Type - Tax_Rate_Type
 *
 * Registers TaxRate WPObject type and queries
 *
 * @package WPGraphQL\WooCommerce\Type\WPObject
 * @since   0.0.2
 */

namespace WPGraphQL\WooCommerce\Type\WPObject;

use GraphQL\Error\UserError;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Data\Factory;

/**
 * Class Tax_Rate_Type
 */
class Tax_Rate_Type {

	/**
	 * Registers tax rate type
	 */
	public static function register() {
		register_graphql_object_type(
			'TaxRate',
			array(
				'description' => __( 'A Tax rate object', 'wp-graphql-woocommerce' ),
				'interfaces'  => array( 'Node' ),
				'fields'      => array(
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
						'description' => __( 'The ID for identifying the tax rate', 'wp-graphql-woocommerce' ),
					),
					'idType' => array(
						'type'        => 'TaxRateIdTypeEnum',
						'description' => __( 'Type of ID being used identify tax rate', 'wp-graphql-woocommerce' ),
					),
					'rateId' => array(
						'type'              => 'Int',
						'description'       => __( 'Get the tax rate by its database ID', 'wp-graphql-woocommerce' ),
						'isDeprecated'      => true,
						'deprecationReason' => __(
							'This argument has been deprecation, and will be removed in v0.5.x. Please use "taxRate(id: value, idType: DATABASE_ID)" instead.',
							'wp-graphql-woocommerce'
						),
					),
				),
				'resolve'     => function ( $source, array $args, AppContext $context ) {
					$id = isset( $args['id'] ) ? $args['id'] : null;
					$id_type = isset( $args['idType'] ) ? $args['idType'] : 'global_id';

					/**
					 * Process deprecated arguments
					 *
					 * Will be removed in v0.5.x.
					 */
					if ( ! empty( $args['rateId'] ) ) {
						$id = $args['rateId'];
						$id_type = 'database_id';
					}

					$rate_id = null;
					switch ( $id_type ) {
						case 'database_id':
							$rate_id = absint( $id );
							break;
						case 'global_id':
						default:
							$id_components = Relay::fromGlobalId( $id );
							if ( empty( $id_components['id'] ) || empty( $id_components['type'] ) ) {
								throw new UserError( __( 'The "id" is invalid', 'wp-graphql-woocommerce' ) );
							}
							$rate_id = absint( $id_components['id'] );
							break;
					}

					return Factory::resolve_tax_rate( $rate_id, $context );
				},
			)
		);
	}
}
