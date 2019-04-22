<?php

use GraphQLRelay\Relay;
class CustomerQueriesTest extends \Codeception\TestCase\WPTestCase {
	private $shop_manager;
	private $customer;
	private $helper;
	private $new_customer;

	public function setUp() {
		// before
		parent::setUp();

		$this->shop_manager  = $this->factory->user->create( array( 'role' => 'shop_manager' ) );
		$this->customer      = $this->factory->user->create( array( 'role' => 'customer' ) );
		$this->helper        = $this->getModule('\Helper\Wpunit')->customer();
		$this->new_customer  = $this->helper->create();
	}

	public function tearDown() {
		// your tear down methods here
		// then
		parent::tearDown();
	}

	// tests
	public function testCustomerQuery() {
		$query = '
			query customerQuery( $id: ID! ) {
				customer( id: $id ) {
					isVatExempt
					hasCalculatedShipping
					calculatedShipping
					orderCount
					totalSpent
					username
					email
					firstName
					lastName
					displayName
					role
					date
					modified
					lastOrder {
						id
						orderId
					}
					billing {
						firstName
						lastName
						company
						address1
						address2
						city
						state
						postcode
						country
						email
						phone
					}
					shipping {
						firstName
						lastName
						company
						address1
						address2
						city
						state
						postcode
						country
					}
					isPayingCustomer
				}
			}
		';

		/**
		 * Assertion One
		 * 
		 * Query should return null value due to lack of permissions.
		 */
		wp_set_current_user( $this->customer );
		$variables = array( 'id' => Relay::toGlobalId( 'customer', $this->new_customer ) );
		$actual    = do_graphql_request( $query, 'customerQuery', $variables );
		$expected = array( 'data' => array( 'customer' => $this->helper->print_failed_query( $this->new_customer ) ) );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEqualSets( $expected, $actual );

		/**
		 * Assertion Two
		 * 
		 * Query should return requested data because user queried themselves.
		 */
		$variables = array( 'id' => Relay::toGlobalId( 'customer', $this->customer ) );
		$actual    = do_graphql_request( $query, 'customerQuery', $variables );
		$expected = array( 'data' => array( 'customer' => $this->helper->print_query( $this->customer ) ) );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEqualSets( $expected, $actual );

		// Clear customer cache.
		$this->getModule('\Helper\Wpunit')->clear_loader_cache( 'wc_customer' );

		/**
		 * Assertion Three
		 * 
		 * Query should return requested data because has sufficient permissions.
		 */
		wp_set_current_user( $this->shop_manager );
		$variables = array( 'id' => Relay::toGlobalId( 'customer', $this->new_customer ) );
		$actual    = do_graphql_request( $query, 'customerQuery', $variables );
		$expected = array( 'data' => array( 'customer' => $this->helper->print_query( $this->new_customer ) ) );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEqualSets( $expected, $actual );		
	}

	public function testCustomerByQuery() {
		$query = '
			query customerByQuery( $id: Int! ) {
				customerBy( customerId: $id ) {
					isVatExempt
					hasCalculatedShipping
					calculatedShipping
					orderCount
					totalSpent
					username
					email
					firstName
					lastName
					displayName
					role
					date
					modified
					lastOrder {
						id
						orderId
					}
					billing {
						firstName
						lastName
						company
						address1
						address2
						city
						state
						postcode
						country
						email
						phone
					}
					shipping {
						firstName
						lastName
						company
						address1
						address2
						city
						state
						postcode
						country
					}
					isPayingCustomer
				}
			}
		';

		/**
		 * Assertion One
		 * 
		 * Query should return requested data because user queried themselves.
		 */
		wp_set_current_user( $this->new_customer );
		$variables = array( 'id' => $this->new_customer );
		$actual    = do_graphql_request( $query, 'customerByQuery', $variables );
		$expected  = array( 'data' => array( 'customerBy' => $this->helper->print_query( $this->new_customer ) ) );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEqualSets( $expected, $actual );

		// Clear customer cache.
		$this->getModule('\Helper\Wpunit')->clear_loader_cache( 'wc_customer' );

		/**
		 * Assertion Two
		 * 
		 * Query should return null value due to lack of permissions..
		 */
		wp_set_current_user( $this->customer );
		$variables = array( 'id' => $this->new_customer );
		$actual    = do_graphql_request( $query, 'customerByQuery', $variables );
		$expected  = array( 'data' => array( 'customerBy' => $this->helper->print_failed_query( $this->new_customer ) ) );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEqualSets( $expected, $actual );
	}

	public function testCustomersQuery() {
		$query = '
			query {
				customers {
					nodes{
						isVatExempt
						hasCalculatedShipping
						calculatedShipping
						orderCount
						totalSpent
						username
						email
						firstName
						lastName
						displayName
						role
						date
						modified
						lastOrder {
							id
							orderId
						}
						billing {
							firstName
							lastName
							company
							address1
							address2
							city
							state
							postcode
							country
							email
							phone
						}
						shipping {
							firstName
							lastName
							company
							address1
							address2
							city
							state
							postcode
							country
						}
						isPayingCustomer
					}
				}
			}
		';

		/**
		 * Assertion One
		 * 
		 * Query should return requested data because user queried themselves.
		 */
		wp_set_current_user( $this->shop_manager );
		$actual    = do_graphql_request( $query );
		$users = get_users(
			array (
				'count_total' => false,
				'order'       => 'DESC',
				'fields'      => 'ids'
			)
		);
		$expected  = array(
			'data' => array(
				'customers' => $this->helper->print_nodes( $users ),
			),
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEqualSets( $expected, $actual );

		// Clear customer cache.
		$this->getModule('\Helper\Wpunit')->clear_loader_cache( 'wc_customer' );

		/**
		 * Assertion Two
		 * 
		 * Query should return null value due to lack of permissions..
		 */
		wp_set_current_user( $this->customer );
		$variables = array( 'id' => $this->new_customer );
		$actual    = do_graphql_request( $query, 'customerByQuery', $variables );
		$expected  = array( 'data' => array( 'customers' => array( 'nodes' => array() ) ) );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEqualSets( $expected, $actual );
	}
}
