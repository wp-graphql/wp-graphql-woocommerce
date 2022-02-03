<?php

use GraphQLRelay\Relay;
class CustomerQueriesTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {

	public function expectedCustomerData( $id ) {
		$customer = new \WC_Customer( $id );
		$customer->read_meta_data( true );

		$wp_user = get_user_by( 'ID', $id );

		if ( ! $customer->get_id() ) {
			throw new \Exception( 'Invalid customer ID provided.' );
		}

		$billing  = $customer->get_billing();
		$shipping = $customer->get_shipping();

		return array(
			$this->expectedObject(
				'customer',
				array(
					$this->expectedField( 'id', $this->toRelayId( 'customer', $id ) ),
					$this->expectedField( 'databaseId', $id ),
					$this->expectedField( 'isVatExempt', $customer->get_is_vat_exempt() ),
					$this->expectedField( 'hasCalculatedShipping', $customer->has_calculated_shipping() ),
					$this->expectedField( 'calculatedShipping', $customer->get_calculated_shipping() ),
					$this->expectedField( 'orderCount', $customer->get_order_count() ),
					$this->expectedField( 'totalSpent', (float) $customer->get_total_spent() ),
					$this->expectedField( 'username', $customer->get_username() ),
					$this->expectedField( 'email', $customer->get_email() ),
					$this->expectedField( 'firstName', $this->maybe( $customer->get_first_name() ) ),
					$this->expectedField( 'lastName', $this->maybe( $customer->get_last_name() ) ),
					$this->expectedField( 'displayName', $customer->get_display_name() ),
					$this->expectedField( 'role', $customer->get_role() ),
					$this->expectedField( 'date', (string) $customer->get_date_created() ),
					$this->expectedField( 'modified', (string) $customer->get_date_modified() ),
					$this->expectedField(
						'lastOrder.databaseId',
						$customer->get_last_order()
							? $customer->get_last_order()->get_id()
							: self::IS_NULL
					),
					$this->expectedObject(
						'billing',
						array(
							$this->expectedField( 'firstName', $this->maybe( $billing['first_name'] ) ),
							$this->expectedField( 'lastName', $this->maybe( $billing['last_name'] ) ),
							$this->expectedField( 'company', $this->maybe( $billing['company'] ) ),
							$this->expectedField( 'address1', $this->maybe( $billing['address_1'] ) ),
							$this->expectedField( 'address2', $this->maybe( $billing['address_2'] ) ),
							$this->expectedField( 'city', $this->maybe( $billing['city'] ) ),
							$this->expectedField( 'postcode', $this->maybe( $billing['postcode'] ) ),
							$this->expectedField( 'email', $this->maybe( $billing['email'] ) ),
							$this->expectedField( 'phone', $this->maybe( $billing['phone'] ) ),
						)
					),
					$this->expectedObject(
						'shipping',
						array(
							$this->expectedField( 'firstName', $this->maybe( $shipping['first_name'] ) ),
							$this->expectedField( 'lastName', $this->maybe( $shipping['last_name'] ) ),
							$this->expectedField( 'company', $this->maybe( $shipping['company'] ) ),
							$this->expectedField( 'address1', $this->maybe( $shipping['address_1'] ) ),
							$this->expectedField( 'address2', $this->maybe( $shipping['address_2'] ) ),
							$this->expectedField( 'city', $this->maybe( $shipping['city'] ) ),
							$this->expectedField( 'postcode', $this->maybe( $shipping['postcode'] ) ),
						)
					),
					$this->expectedField( 'isPayingCustomer', $customer->get_is_paying_customer() ),
					$this->expectedField(
						'jwtAuthToken',
						! is_wp_error( \WPGraphQL\JWT_Authentication\Auth::get_token( $wp_user ) )
							? \WPGraphQL\JWT_Authentication\Auth::get_token( $wp_user )
							: null
					),
					$this->expectedField(
						'jwtRefreshToken',
						! is_wp_error( \WPGraphQL\JWT_Authentication\Auth::get_refresh_token( $wp_user ) )
							? \WPGraphQL\JWT_Authentication\Auth::get_refresh_token( $wp_user )
							: null
					),
				)
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
						postcode
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
						postcode
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
			$this->expectedField( 'customer', self::IS_NULL ),
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
				$this->expectedField( 'customer.jwtAuthToken', self::IS_NULL ),
				$this->expectedErrorPath( 'customer.jwtRefreshToken' ),
				$this->expectedField( 'customer.jwtRefreshToken', self::IS_NULL ),
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
			$this->expectedField( 'customer', self::IS_NULL ),
		);

		$this->assertQueryError( $response, $expected );

		// Clear customer cache.
		$this->clearLoaderCache( 'wc_customer' );
	}

	public function testCustomersQueryAndWhereArgs() {
		$users = array(
			$this->factory->customer->create(
				array(
					'email'    => 'gotcha@example.com',
					'username' => 'megaman8080',
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
		$expected = array( $this->expectedField( 'customers.nodes', array() ) );

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Two
		 *
		 * Query should return requested data because user has proper capabilities.
		 */
		$this->loginAsShopManager();
		$response = $this->graphql( compact( 'query' ) );
		$expected = array(
			$this->expectedField( 'customers.nodes.#.databaseId', $users[0] ),
			$this->expectedField( 'customers.nodes.#.databaseId', $users[1] ),
			$this->expectedField( 'customers.nodes.#.databaseId', $users[2] ),
			$this->expectedField( 'customers.nodes.#.databaseId', $users[3] ),
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
			$this->expectedField( 'customers.nodes.0.databaseId', $users[0] ),
		);

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Four
		 *
		 * Tests "include" where argument.
		 */
		$variables = array( 'include' => array( $users[2] ) );
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = array(
			$this->expectedField( 'customers.nodes.0.databaseId', $users[2] ),
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
			$this->expectedField( 'customers.nodes.#.databaseId', $users[0] ),
			$this->expectedField( 'customers.nodes.#.databaseId', $users[1] ),
			$this->expectedField( 'customers.nodes.#.databaseId', $users[3] ),
			$this->not()->expectedField( 'customers.nodes.#.databaseId', $users[2] ),
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
			$this->expectedField( 'customers.nodes.0.databaseId', $users[0] ),
			$this->not()->expectedField( 'customers.nodes.#.databaseId', $users[1] ),
			$this->not()->expectedField( 'customers.nodes.#.databaseId', $users[2] ),
			$this->not()->expectedField( 'customers.nodes.#.databaseId', $users[3] ),
		);

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Seven
		 *
		 * Tests "orderby" and "order" where arguments.
		 */
		$variables = array(
			'orderby' => 'USERNAME',
			'order'   => 'ASC',
		);
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
			$expected[] = $this->expectedField(
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
			$this->expectedField( 'customer.orders.nodes.#.databaseId', $order_1 ),
		);

		$this->assertQuerySuccessful( $response, $expected );
	}
}
