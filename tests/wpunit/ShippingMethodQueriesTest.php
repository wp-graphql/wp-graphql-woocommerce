<?php

use GraphQLRelay\Relay;

class ShippingMethodQueriesTest extends \Codeception\TestCase\WPTestCase {
	private $shop_manager;
	private $customer;
	private $method;
	private $helper;

	public function setUp() {
		parent::setUp();

		$this->shop_manager = $this->factory->user->create( array( 'role' => 'shop_manager' ) );
		$this->customer     = $this->factory->user->create( array( 'role' => 'customer' ) );
		$this->helper       = $this->getModule('\Helper\Wpunit')->shipping_method();
		$this->method       = 'flat_rate';
	}

	public function tearDown() {
		// your tear down methods here
		// then
		parent::tearDown();
	}

	// tests
	public function testShippingMethodQueryAndArgs() {
		$id = Relay::toGlobalId( 'shipping_method', $this->method );

		$query = '
			query shippingMethodQuery( $id: ID, $methodId: ID ) {
				shippingMethod( id: $id, methodId: $methodId ) {
					id
					methodId
					title
					description
				}
			}
		';

		/**
		 * Assertion One
		 * 
		 * test query and "id" query argument
		 */
		$variables = array( 'id' => $id );
		$actual = do_graphql_request( $query, 'shippingMethodQuery', $variables );
		$expected = array( 'data' => array( 'shippingMethod' => $this->helper->print_query( $this->method ) ) );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEqualSets( $expected, $actual );

		/**
		 * Assertion Two
		 * 
		 * test query and "methodId" query argument
		 */
		$variables = array( 'methodId' => $this->method );
		$actual = do_graphql_request( $query, 'shippingMethodQuery', $variables );
		$expected = array( 'data' => array( 'shippingMethod' => $this->helper->print_query( $this->method ) ) );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEqualSets( $expected, $actual );

	}

	public function testShippingMethodsQuery() {
		$methods = array(
			'flat_rate',
			'free_shipping',
			'local_pickup',
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
		 * tests query
		 */
		$actual = do_graphql_request( $query, 'shippingMethodQuery' );
		$expected = array(
			'data' => array(
				'shippingMethods' => array(
					'nodes' => array_map(
						function( $id ) {
							return array( 'id' => Relay::toGlobalId( 'shipping_method', $id ) );
						},
						array_values( $methods )
					)
				),
			),
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEqualSets( $expected, $actual );
	}
}
