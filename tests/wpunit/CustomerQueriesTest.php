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

		$this->shop_manager  = $this->factory->user->create( array( 'role' => 'shop_manager', 'user_login' => 'shopManager25' ) );
		$this->customer      = $this->factory->user->create( array( 'role' => 'customer', 'user_login' => 'customer43' ) );
		$this->helper        = $this->getModule('\Helper\Wpunit')->customer();
		$this->new_customer  = $this->helper->create();
	}

	public function tearDown() {
		// your tear down methods here
		// then
		parent::tearDown();
	}

	// tests
	public function testCustomerQueryAndArgs() {
		$query = '
			query customerQuery( $id: ID ) {
				customer( id: $id ) {
					id
					customerId
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

		// Clear customer cache.
		$this->getModule('\Helper\Wpunit')->clear_loader_cache( 'wc_customer' );

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

		// Clear customer cache.
		$this->getModule('\Helper\Wpunit')->clear_loader_cache( 'wc_customer' );

		/**
		 * Assertion Four
		 * 
		 * Query should return data corresponding with current user when no ID is provided.
		 */
		wp_set_current_user( $this->new_customer );
		$actual    = do_graphql_request( $query, 'customerQuery' );
		$expected = array( 'data' => array( 'customer' => $this->helper->print_query( $this->new_customer ) ) );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEqualSets( $expected, $actual );
	}

	public function testCustomerByQuery() {
		$query = '
			query customerByQuery( $id: Int! ) {
				customerBy( customerId: $id ) {
					id
					customerId
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

	public function testCustomersQueryAndWhereArgs() {
		$user_id = $this->helper->create( array( 'email' => 'gotcha@example.com', 'username' => 'megaman8080' ) );
		$users = get_users(
			array (
				'count_total' => false,
				'order'       => 'ASC',
				'fields'      => 'ID'
			)
		);

		$query = '
			query customersQuery(
				$search: String,
				$include: [Int],
				$exclude: [Int],
				$email: String,
				$role: UserRoleEnum,
				$roleIn: [UserRoleEnum],
				$roleNotIn: [UserRoleEnum],
				$orderby: CustomerConnectionOrderbyEnum,
				$order: OrderEnum
			) {
				customers( where: {
					search: $search,
					include: $include,
					exclude: $exclude,
					email: $email,
					role: $role,
					roleIn: $roleIn,
					roleNotIn: $roleNotIn,
					orderby: $orderby,
					order: $order
				} ) {
					nodes{
						id
					}
				}
			}
		';

		/**
		 * Assertion One
		 * 
		 * Query should return null value due to lack of capabilities...
		 */
		wp_set_current_user( $this->customer );
		$actual   = do_graphql_request( $query, 'customersQuery' );
		$expected = array( 'data' => array( 'customers' => array( 'nodes' => array() ) ) );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEqualSets( $expected, $actual );

		// Clear customer cache.
		$this->getModule('\Helper\Wpunit')->clear_loader_cache( 'wc_customer' );

		/**
		 * Assertion Two
		 * 
		 * Query should return requested data because user has proper capabilities.
		 */
		wp_set_current_user( $this->shop_manager );
		$actual   = do_graphql_request( $query, 'customersQuery' );
		$expected = array(
			'data' => array(
				'customers' => array(
					'nodes' => $this->helper->print_nodes( $users ),
				),
			),
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEqualSets( $expected, $actual );

		/**
		 * Assertion Three
		 * 
		 * Tests "search" where argument.
		 */
		$variables = array( 'search' => 'megaman8080' );
		$actual    = do_graphql_request( $query, 'customersQuery', $variables );
		$expected = array(
			'data' => array(
				'customers' => array(
					'nodes' => $this->helper->print_nodes(
						$users,
						array(
							'filter' => function( $id ) {
								$customer = new \WC_Customer( $id );
								return 'megaman8080' === $customer->get_username();
							}
						)
					),
				),
			),
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEqualSets( $expected, $actual );

		/**
		 * Assertion Four
		 * 
		 * Tests "include" where argument.
		 */
		$variables = array( 'include' => array( $user_id ) );
		$actual    = do_graphql_request( $query, 'customersQuery', $variables );
		$expected = array(
			'data' => array(
				'customers' => array(
					'nodes' => $this->helper->print_nodes(
						$users,
						array(
							'filter' => function( $id ) use ( $user_id ) {
								return absint( $id ) === $user_id;
							}
						)
					),
				),
			),
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEqualSets( $expected, $actual );

		/**
		 * Assertion Five
		 * 
		 * Tests "exclude" where argument.
		 */
		$variables = array( 'exclude' => array( $user_id ) );
		$actual    = do_graphql_request( $query, 'customersQuery', $variables );
		$expected = array(
			'data' => array(
				'customers' => array(
					'nodes' => $this->helper->print_nodes(
						$users,
						array(
							'filter' => function( $id ) use( $user_id ) {
								return absint( $id ) !== $user_id;
							}
						)
					),
				),
			),
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEqualSets( $expected, $actual );

		/**
		 * Assertion Six
		 * 
		 * Tests "email" where argument.
		 */
		$variables = array( 'email' => 'gotcha@example.com' );
		$actual    = do_graphql_request( $query, 'customersQuery', $variables );
		$expected = array(
			'data' => array(
				'customers' => array(
					'nodes' => $this->helper->print_nodes(
						$users,
						array(
							'filter' => function( $id ) {
								$customer = new \WC_Customer( $id );
								return 'gotcha@example.com' === $customer->get_email();
							}
						)
					),
				),
			),
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEqualSets( $expected, $actual );

		/**
		 * Assertion Seven
		 * 
		 * Tests "role" where argument.
		 */
		$variables = array( 'role' => 'SHOP_MANAGER' );
		$actual    = do_graphql_request( $query, 'customersQuery', $variables );
		$expected = array(
			'data' => array(
				'customers' => array(
					'nodes' => $this->helper->print_nodes(
						$users,
						array(
							'filter' => function( $id ) {
								$customer = new \WC_Customer( $id );
								return 'shop_manager' === $customer->get_role();
							}
						)
					),
				),
			),
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEqualSets( $expected, $actual );

		/**
		 * Assertion Eight
		 * 
		 * Tests "roleIn" where argument.
		 */
		$variables = array( 'roleIn' => array( 'SHOP_MANAGER' ) );
		$actual    = do_graphql_request( $query, 'customersQuery', $variables );
		$expected = array(
			'data' => array(
				'customers' => array(
					'nodes' => $this->helper->print_nodes(
						$users,
						array(
							'filter' => function( $id ) {
								$customer = new \WC_Customer( $id );
								return 'shop_manager' === $customer->get_role();
							}
						)
					),
				),
			),
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEqualSets( $expected, $actual );

		/**
		 * Assertion Nine
		 * 
		 * Tests "roleNotIn" where argument.
		 */
		$variables = array( 'roleNotIn' => array( 'SHOP_MANAGER' ) );
		$actual    = do_graphql_request( $query, 'customersQuery', $variables );
		$expected = array(
			'data' => array(
				'customers' => array(
					'nodes' => $this->helper->print_nodes(
						$users,
						array(
							'filter' => function( $id ) {
								$customer = new \WC_Customer( $id );
								return 'shop_manager' !== $customer->get_role();
							}
						)
					),
				),
			),
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEqualSets( $expected, $actual );

		/**
		 * Assertion Ten
		 * 
		 * Tests "orderby" and "order" where arguments.
		 */
		$variables = array( 'orderby' => 'USERNAME', 'order' => 'ASC' );
		$actual    = do_graphql_request( $query, 'customersQuery', $variables );
		$expected = array(
			'data' => array(
				'customers' => array(
					'nodes' => $this->helper->print_nodes(
						$users,
						array(
							'sorter' => function( $id_a, $id_b ) {
								$data = new \WC_Customer( $id_a );
								$username_a = $data->get_username();
								$data = new \WC_Customer( $id_b );
								$username_b = $data->get_username();

								codecept_debug( array( $username_a, $username_b ) );

								return strnatcmp( $username_a, $username_b );
							}
						)
					),
				),
			),
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEqualSets( $expected, $actual );
	}
}
