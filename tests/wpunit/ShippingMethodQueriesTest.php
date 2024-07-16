<?php

use GraphQLRelay\Relay;

class ShippingMethodQueriesTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {

	public function testShippingMethodQueryAndArgs() {
		$id = Relay::toGlobalId( 'shipping_method', 'flat_rate' );

		$query = '
			query( $id: ID!, $idType: ShippingMethodIdTypeEnum ) {
				shippingMethod( id: $id, idType: $idType ) {
					id
					databaseId
					title
					description
				}
			}
		';

		/**
		 * Assertion One
		 *
		 * Confirm permission check is working
		 */
		$variables = [ 'id' => $id ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );

		$this->assertQueryError( $response );

		// Login as shop manager.
		$this->loginAsShopManager();

		/**
		 * Assertion Two
		 *
		 * Test "ID" ID type.
		 */
		$variables = [ 'id' => $id ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [ 
			$this->expectedField( 'shippingMethod.id', $id ),
			$this->expectedField( 'shippingMethod.databaseId', 'flat_rate' ),
			$this->expectedField( 'shippingMethod.title', static::NOT_NULL ),
			$this->expectedField( 'shippingMethod.description', static::NOT_NULL ),
		];

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Three
		 *
		 * Test "DATABASE_ID" ID type.
		 */
		$variables = [ 'id' => 'flat_rate', 'idType' => 'DATABASE_ID' ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testShippingMethodsQuery() {
		$query = '
			query {
				shippingMethods {
					nodes {
						id
					}
				}
			}
		';

		/**
		 * Assertion One
		 *
		 * Confirm permission check is working
		 */
		$response  = $this->graphql( compact( 'query' ) );
		$this->assertQuerySuccessful( $response, [ $this->expectedField( 'shippingMethods.nodes', static::IS_FALSY ) ] );

		// Login as shop manager.
		$this->loginAsShopManager();

		/**
		 * Assertion One
		 *
		 * Tests query
		 */
		$response = $this->graphql( compact( 'query' ) );
		$expected = array_map(
			function( $method ) {
				return $this->expectedField( 'shippingMethods.nodes.#.id', $this->toRelayId( 'shipping_method', $method->id ) );
			},
			array_values( WC_Shipping::instance()->get_shipping_methods() )
		);
		

		$this->assertQuerySuccessful( $response, $expected );
	}
}
