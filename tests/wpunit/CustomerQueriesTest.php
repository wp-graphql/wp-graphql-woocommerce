<?php

use GraphQLRelay\Relay;
class CustomerQueriesTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {

	public function expectedCustomerData( $id ) {
		$customer = new \WC_Customer( $id );
		$wp_user  = get_user_by( 'ID', $id );

		if ( ! $customer->get_id() ) {
			throw new \Exception( 'Invalid customer ID provided.' );
		}

		return array(
			$this->expectedObject( 'customer.id', $this->toRelayId( 'customer', $id ) ),
			$this->expectedObject( 'customer.databaseId', $id ),
			$this->expectedObject( 'customer.isVatExempt', $customer->get_is_vat_exempt() ),
			$this->expectedObject( 'customer.hasCalculatedShipping', $customer->has_calculated_shipping() ),
			$this->expectedObject( 'customer.calculatedShipping', $customer->get_calculated_shipping() ),
			$this->expectedObject( 'customer.orderCount', $customer->get_order_count() ),
			$this->expectedObject( 'customer.totalSpent', (float) $customer->get_total_spent() ),
			$this->expectedObject( 'customer.username', $customer->get_username() ),
			$this->expectedObject( 'customer.email', $customer->get_email() ),
			$this->expectedObject( 'customer.firstName', $this->maybe( $customer->get_first_name() ) ),
			$this->expectedObject( 'customer.lastName', $this->maybe( $customer->get_last_name() ) ),
			$this->expectedObject( 'customer.displayName', $customer->get_display_name() ),
			$this->expectedObject( 'customer.role', $customer->get_role() ),
			$this->expectedObject( 'customer.date', (string) $customer->get_date_created() ),
			$this->expectedObject( 'customer.modified', (string) $customer->get_date_modified() ),
			$this->expectedObject( 'customer.lastOrder.databaseId', $customer->get_last_order() ? $customer->get_last_order()->get_id() : null ),
			$this->expectedObject( 'customer.billing.firstName', $this->maybe( $customer->get_billing_first_name() ) ),
			$this->expectedObject( 'customer.billing.lastName', $this->maybe( $customer->get_billing_last_name() ) ),
			$this->expectedObject( 'customer.billing.company', $this->maybe( $customer->get_billing_company() ) ),
			$this->expectedObject( 'customer.billing.address1', $this->maybe( $customer->get_billing_address_1() ) ),
			$this->expectedObject( 'customer.billing.address2', $this->maybe( $customer->get_billing_address_2() ) ),
			$this->expectedObject( 'customer.billing.city', $this->maybe( $customer->get_billing_city() ) ),
			$this->expectedObject( 'customer.billing.state', $this->maybe( $customer->get_billing_state() ) ),
			$this->expectedObject( 'customer.billing.postcode', $this->maybe( $customer->get_billing_postcode() ) ),
			$this->expectedObject( 'customer.billing.country', $this->maybe( $customer->get_billing_country() ) ),
			$this->expectedObject( 'customer.billing.email', $this->maybe( $customer->get_billing_email() ) ),
			$this->expectedObject( 'customer.billing.phone', $this->maybe( $customer->get_billing_phone() ) ),
			$this->expectedObject( 'customer.shipping.firstName', $this->maybe( $customer->get_shipping_first_name() ) ),
			$this->expectedObject( 'customer.shipping.lastName', $this->maybe( $customer->get_shipping_last_name() ) ),
			$this->expectedObject( 'customer.shipping.company', $this->maybe( $customer->get_shipping_company() ) ),
			$this->expectedObject( 'customer.shipping.address1', $this->maybe( $customer->get_shipping_address_1() ) ),
			$this->expectedObject( 'customer.shipping.address2', $this->maybe( $customer->get_shipping_address_2() ) ),
			$this->expectedObject( 'customer.shipping.city', $this->maybe( $customer->get_shipping_city() ) ),
			$this->expectedObject( 'customer.shipping.state', $this->maybe( $customer->get_shipping_state() ) ),
			$this->expectedObject( 'customer.shipping.postcode', $this->maybe( $customer->get_shipping_postcode() ) ),
			$this->expectedObject( 'customer.shipping.country', $this->maybe( $customer->get_shipping_country() ) ),
			$this->expectedObject( 'customer.isPayingCustomer', $customer->get_is_paying_customer() ),
			$this->expectedObject(
				'customer.jwtAuthToken',
				! is_wp_error( \WPGraphQL\JWT_Authentication\Auth::get_token( $wp_user ) )
					? \WPGraphQL\JWT_Authentication\Auth::get_token( $wp_user )
					: null
			),
			$this->expectedObject(
				'customer.jwtRefreshToken',
				! is_wp_error( \WPGraphQL\JWT_Authentication\Auth::get_refresh_token( $wp_user ) )
					? \WPGraphQL\JWT_Authentication\Auth::get_refresh_token( $wp_user )
					: null
			),
		);
	}

	// tests
	public function testCustomerQueryAndArgs() {
		$new_customer_id = $this->factory->customer->create();

		$query = '
			query ( $id: ID, $customerId: Int ) {
				customer( id: $id, customerId: $customerId ) {
					id
					databaseId
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
						databaseId
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
					jwtAuthToken
					jwtRefreshToken
				}
			}
		';

		/**
		 * Assertion One
		 *
		 * Query should return null value due to lack of permissions.
		 */
		$this->loginAsCustomer();
		$variables = array( 'id' => $this->toRelayId( 'customer', $new_customer_id ) );
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array(
			$this->expectedErrorPath( 'customer' ),
			$this->expectedObject( 'customer', 'null' )
		);

		$this->assertQueryError( $response, $expected );


		// Clear customer cache.
		$this->clearLoaderCache( 'wc_customer' );

		/**
		 * Assertion Two
		 *
		 * Query should return requested data because user queried themselves.
		 */
		$variables = array( 'id' => $this->toRelayId( 'customer', $this->customer ) );
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = $this->expectedCustomerData( $this->customer );

		$this->assertQuerySuccessful( $response, $expected );

		// Clear customer cache.
		$this->clearLoaderCache( 'wc_customer' );

		/**
		 * Assertion Three
		 *
		 * Query should return requested data because has sufficient permissions,
		 * but should not have access to JWT fields.
		 */
		$this->loginAsShopManager();
		$variables = array( 'id' => $this->toRelayId( 'customer', $new_customer_id ) );
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array_merge(
			array(
				$this->expectedErrorPath( 'customer.jwtAuthToken' ),
				$this->expectedObject( 'customer.jwtAuthToken', 'null' ),
				$this->expectedErrorPath( 'customer.jwtRefreshToken' ),
				$this->expectedObject( 'customer.jwtRefreshToken', 'null' )
			),
			$this->expectedCustomerData( $new_customer_id )
		);

		$this->assertQueryError( $response, $expected );

		// Clear customer cache.
		$this->clearLoaderCache( 'wc_customer' );

		/**
		 * Assertion Four
		 *
		 * Query should return data corresponding with current user when no ID is provided.
		 */
		$this->loginAs( $new_customer_id );
		$response = $this->graphql( compact( 'query' ) );
		$expected = $this->expectedCustomerData( $new_customer_id );

		$this->assertQuerySuccessful( $response, $expected );

		// Clear customer cache.
		$this->clearLoaderCache( 'wc_customer' );

		/**
		 * Assertion Five
		 *
		 * Query should return requested data because user queried themselves.
		 */
		$this->loginAs( $new_customer_id );
		$variables = array( 'customerId' => $new_customer_id );
		$response  = $this->graphql( compact( 'query', 'variables' ) );

		$this->assertQuerySuccessful( $response, $expected );

		// Clear customer cache.
		$this->clearLoaderCache( 'wc_customer' );

		/**
		 * Assertion Six
		 *
		 * Query should return null value due to lack of permissions..
		 */
		$this->loginAsCustomer();
		$variables = array( 'customerId' => $new_customer_id );
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array(
			$this->expectedErrorPath( 'customer' ),
			$this->expectedObject( 'customer', 'null' )
		);

		$this->assertQueryError( $response, $expected );

		// Clear customer cache.
		$this->clearLoaderCache( 'wc_customer' );
	}

	public function testCustomersQueryAndWhereArgs() {
		$users = array(
			$this->factory->customer->create(
				array(
					'email' => 'gotcha@example.com',
					'username' => 'megaman8080'
				)
			),
			$this->factory->customer->create(),
			$this->factory->customer->create(),
			$this->factory->customer->create(),
		);

		$query = '
			query (
				$search: String,
				$include: [Int],
				$exclude: [Int],
				$email: String,
				$orderby: CustomerConnectionOrderbyEnum,
				$order: OrderEnum
			) {
				customers( where: {
					search: $search,
					include: $include,
					exclude: $exclude,
					email: $email,
					orderby: $orderby,
					order: $order
				} ) {
					nodes{
						databaseId
					}
				}
			}
		';

		/**
		 * Assertion One
		 *
		 * Query should return null value due to lack of capabilities...
		 */
		$this->loginAs( $users[0] );
		$response = $this->graphql( compact( 'query' ) );
		$expected = array( $this->expectedObject( 'customers.nodes', array() ) );

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Two
		 *
		 * Query should return requested data because user has proper capabilities.
		 */
		$this->loginAsShopManager();
		$response = $this->graphql( compact( 'query' ) );
		$expected = array(
			$this->expectedObject( 'customers.nodes.#.databaseId', $users[0] ),
			$this->expectedObject( 'customers.nodes.#.databaseId', $users[1] ),
			$this->expectedObject( 'customers.nodes.#.databaseId', $users[2] ),
			$this->expectedObject( 'customers.nodes.#.databaseId', $users[3] ),
		);

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Three
		 *
		 * Tests "search" where argument.
		 */
		$variables = array( 'search' => 'megaman8080' );
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array(
			$this->expectedObject( 'customers.nodes.0.databaseId', $users[0] ),
		);

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Four
		 *
		 * Tests "include" where argument.
		 */
		$variables = array( 'include' => array( $users[2]) );
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array(
			$this->expectedObject( 'customers.nodes.0.databaseId', $users[2] ),
		);

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Five
		 *
		 * Tests "exclude" where argument.
		 */
		$variables = array( 'exclude' => array( $users[2] ) );
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array(
			$this->expectedObject( 'customers.nodes.#.databaseId', $users[0] ),
			$this->expectedObject( 'customers.nodes.#.databaseId', $users[1] ),
			$this->expectedObject( 'customers.nodes.#.databaseId', $users[3] ),
			$this->not()->expectedObject( 'customers.nodes.#.databaseId', $users[2] ),
		);

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Six
		 *
		 * Tests "email" where argument.
		 */
		$variables = array( 'email' => 'gotcha@example.com' );
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array(
			$this->expectedObject( 'customers.nodes.0.databaseId', $users[0] ),
			$this->not()->expectedObject( 'customers.nodes.#.databaseId', $users[1] ),
			$this->not()->expectedObject( 'customers.nodes.#.databaseId', $users[2] ),
			$this->not()->expectedObject( 'customers.nodes.#.databaseId', $users[3] ),
		);

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Seven
		 *
		 * Tests "orderby" and "order" where arguments.
		 */
		$variables = array( 'orderby' => 'USERNAME', 'order' => 'ASC' );
		$response  = $this->graphql( compact( 'query', 'variables' ) );

		$all_users = get_users(
			array(
				'fields'  => 'ID',
				'role'    => 'customer',
				'orderby' => 'username',
				'order'   => 'ASC',
			)
		);
		$expected  = array();
		foreach ( $all_users as $index => $user_id ) {
			$expected[] = $this->expectedObject(
				"customers.nodes.{$index}.databaseId",
				absint( $user_id )
			);
		}

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testCustomerToOrdersConnection() {
		$new_customer_id = $this->factory->customer->create();
		$order_1         = $this->factory->order->createNew(
			array( 'customer_id' => $this->customer )
		);
		$order_2         = $this->factory->order->createNew(
			array( 'customer_id' => $new_customer_id )
		);

		$query = '
			query {
				customer {
					orders {
						nodes {
							databaseId
						}
					}
				}
			}
		';

		/**
		 * Assertion One
		 *
		 * Query for authenticated customer's orders.
		 */
		$this->loginAsCustomer();
		$response = $this->graphql( compact( 'query' ) );
		$expected = array(
			$this->expectedObject( 'customer.orders.nodes.#.databaseId', $order_1 ),
		);

		$this->assertQuerySuccessful( $response, $expected );
	}
}
