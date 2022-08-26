<?php

use GraphQLRelay\Relay;

class ShippingMethodQueriesTest extends \Codeception\TestCase\WPTestCase {
	private $shop_manager;
	private $customer;
	private $method;
	private $helper;

	public function setUp(): void {
		parent::setUp();

		$this->shop_manager = $this->factory->user->create( [ 'role' => 'shop_manager' ] );
		$this->customer     = $this->factory->user->create( [ 'role' => 'customer' ] );
		$this->helper       = $this->getModule( '\Helper\Wpunit' )->shipping_method();
		$this->method       = 'flat_rate';
	}

	// tests
	public function testShippingMethodQueryAndArgs() {
		$id = Relay::toGlobalId( 'shipping_method', $this->method );

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
		 * Test "ID" ID type.
		 */
		$variables = [
			'id'     => $id,
			'idType' => 'ID',
		];
		$actual    = graphql(
			[
				'query'     => $query,
				'variables' => $variables,
			]
		);
		$expected  = [ 'data' => [ 'shippingMethod' => $this->helper->print_query( $this->method ) ] ];

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );

		/**
		 * Assertion Two
		 *
		 * Test "DATABASE_ID" ID type.
		 */
		$variables = [
			'id'     => $this->method,
			'idType' => 'DATABASE_ID',
		];
		$actual    = graphql(
			[
				'query'     => $query,
				'variables' => $variables,
			]
		);
		$expected  = [ 'data' => [ 'shippingMethod' => $this->helper->print_query( $this->method ) ] ];

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );
	}

	public function testShippingMethodsQuery() {
		$wc_shipping = WC_Shipping::instance();
		$methods     = array_values(
			array_map(
				function( $method ) {
					return [ 'id' => Relay::toGlobalId( 'shipping_method', $method->id ) ];
				},
				$wc_shipping->get_shipping_methods()
			)
		);

		$query = '
			query shippingMethodsQuery {
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
		 * Tests query
		 */
		$actual   = do_graphql_request( $query, 'shippingMethodQuery' );
		$expected = [ 'data' => [ 'shippingMethods' => [ 'nodes' => $methods ] ] ];

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );
	}
}
