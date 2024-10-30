<?php
/**
 * Mutation - createTaxRate
 *
 * Registers mutation for creating a tax rate.
 *
 * @package WPGraphQL\WooCommerce\Mutation
 * @since 0.20.0
 */

namespace WPGraphQL\WooCommerce\Mutation;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\Utils\Utils;

/**
 * Class - Tax_Rate_Create
 */
class Tax_Rate_Create {
	/**
	 * Registers mutation
	 *
	 * @return void
	 */
	public static function register_mutation() {
		register_graphql_mutation(
			'createTaxRate',
			[
				'inputFields'         => self::get_input_fields(),
				'outputFields'        => self::get_output_fields(),
				'mutateAndGetPayload' => [ self::class, 'mutate_and_get_payload' ],
			]
		);
	}

	/**
	 * Defines the mutation input field configuration
	 *
	 * @return array
	 */
	public static function get_input_fields() {
		return [
			'country'   => [
				'type'        => 'String',
				'description' => __( 'Country code for the tax rate.', 'wp-graphql-woocommerce' ),
			],
			'state'     => [
				'type'        => 'String',
				'description' => __( 'State code for the tax rate.', 'wp-graphql-woocommerce' ),
			],
			'postcodes' => [
				'type'        => [ 'list_of' => 'String' ],
				'description' => __( 'Postcodes for the tax rate.', 'wp-graphql-woocommerce' ),
			],
			'cities'    => [
				'type'        => [ 'list_of' => 'String' ],
				'description' => __( 'Cities for the tax rate.', 'wp-graphql-woocommerce' ),
			],
			'rate'      => [
				'type'        => 'String',
				'description' => __( 'Tax rate.', 'wp-graphql-woocommerce' ),
			],
			'name'      => [
				'type'        => 'String',
				'description' => __( 'Tax rate name.', 'wp-graphql-woocommerce' ),
			],
			'priority'  => [
				'type'        => 'Int',
				'description' => __( 'Tax rate priority.', 'wp-graphql-woocommerce' ),
			],
			'compound'  => [
				'type'        => 'Boolean',
				'description' => __( 'Whether the tax rate is compound.', 'wp-graphql-woocommerce' ),
			],
			'shipping'  => [
				'type'        => 'Boolean',
				'description' => __( 'Whether the tax rate is applied to shipping.', 'wp-graphql-woocommerce' ),
			],
			'order'     => [
				'type'        => 'Int',
				'description' => __( 'Tax rate order.', 'wp-graphql-woocommerce' ),
			],
			'class'     => [
				'type'        => 'TaxClassEnum',
				'description' => __( 'Tax rate class.', 'wp-graphql-woocommerce' ),
			],
		];
	}

	/**
	 * Defines the mutation output field configuration
	 *
	 * @return array
	 */
	public static function get_output_fields() {
		return [
			'taxRate' => [
				'type'    => 'TaxRate',
				'resolve' => static function ( array $payload, array $args, AppContext $context ) {
					return $context->get_loader( 'tax_rate' )->load( $payload['tax_rate_id'] );
				},
			],
		];
	}

	/**
	 * Defines the mutation data modification closure.
	 *
	 * @param array                                $input    Mutation input.
	 * @param \WPGraphQL\AppContext                $context  AppContext instance.
	 * @param \GraphQL\Type\Definition\ResolveInfo $info     ResolveInfo instance. Can be
	 * use to get info about the current node in the GraphQL tree.
	 *
	 * @throws \GraphQL\Error\UserError Invalid ID provided | Lack of capabilities.
	 *
	 * @return array
	 */
	public static function mutate_and_get_payload( $input, AppContext $context, ResolveInfo $info ) {
		$id = ! empty( $input['id'] ) ? Utils::get_database_id_from_id( $input['id'] ) : null;
		if ( false === $id ) {
			throw new UserError( __( 'Invalid ID provided.', 'wp-graphql-woocommerce' ) );
		}
		$action     = ! $id ? 'create' : 'update';
		$permission = ! $id ? 'create' : 'edit';
		if ( ! \wc_rest_check_manager_permissions( 'settings', $permission ) ) {
			throw new UserError(
				sprintf(
					/* translators: %s: permission */
					__( 'Sorry, you are not allowed to %s tax rates.', 'wp-graphql-woocommerce' ),
					$permission
				),
				\rest_authorization_required_code()
			);
		}

		$current = null;
		if ( ! empty( $id ) ) {
			/**
			 * @var object{
			 *  tax_rate_id: int,
			 *  tax_rate_class: string,
			 *  tax_rate_country: string,
			 *  tax_rate_state: string,
			 *  tax_rate: string,
			 *  tax_rate_name: string,
			 *  tax_rate_priority: int,
			 *  tax_rate_compound: bool,
			 *  tax_rate_shipping: bool,
			 *  tax_rate_order: int,
			 *  tax_rate_city: string,
			 *  tax_rate_postcode: string,
			 *  tax_rate_postcodes: string,
			 *  tax_rate_cities: string
			 *  } $current
			 */
			$current = \WC_Tax::_get_tax_rate( $id, OBJECT );
		}

		$data   = [];
		$fields = [
			'country'  => 'tax_rate_country',
			'state'    => 'tax_rate_state',
			'rate'     => 'tax_rate',
			'name'     => 'tax_rate_name',
			'priority' => 'tax_rate_priority',
			'compound' => 'tax_rate_compound',
			'shipping' => 'tax_rate_shipping',
			'order'    => 'tax_rate_order',
			'class'    => 'tax_rate_class',
		];

		foreach ( $fields as $key => $field ) {
			if ( ! isset( $input[ $key ] ) ) {
				continue;
			}

			if ( $current && $current->$field === $input[ $key ] ) {
				continue;
			}

			switch ( $field ) {
				case 'tax_rate_priority':
				case 'tax_rate_compound':
				case 'tax_rate_shipping':
				case 'tax_rate_order':
					$data[ $field ] = $input[ $key ];
					break;
				case 'tax_rate_class':
					$data[ $field ] = 'standard' !== $input[ $key ] ? $input[ $key ] : '';
					break;
				default:
					$data[ $field ] = $input[ $key ];
					break;
			}
		}

		/**
		 * Filter tax rate data before creating/updating.
		 *
		 * @param array $data  The tax rate data.
		 * @param array $input Request input.
		 */
		$data = apply_filters( "graphql_woocommerce_before_tax_rate_{$action}_data", $data, $input );

		if ( ! $id ) {
			$id = \WC_Tax::_insert_tax_rate( $data );
		} else {
			\WC_Tax::_update_tax_rate( $id, $data );
		}

		if ( isset( $input['cities'] ) ) {
			\WC_Tax::_update_tax_rate_cities( $id, join( ';', $input['cities'] ) );
		}

		if ( isset( $input['postcodes'] ) ) {
			\WC_Tax::_update_tax_rate_postcodes( $id, join( ';', $input['postcodes'] ) );
		}

		/**
		 * Filter tax rate object before responding.
		 *
		 * @param int    $tax_rate_id  The shipping method object.
		 * @param array  $input        Request input.
		 */
		do_action( "graphql_woocommerce_tax_rate_{$action}", $id, $input );

		return [ 'tax_rate_id' => $id ];
	}
}
