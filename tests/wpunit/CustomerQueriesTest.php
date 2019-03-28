<?php

class CustomerQueriesTest extends \Codeception\TestCase\WPTestCase {

	public function setUp() {
		// before
		parent::setUp();

		// your set up methods here
	}

	public function tearDown() {
		// your tear down methods here
		// then
		parent::tearDown();
	}

	private function create_customer( $username = 'testcustomer', $password = 'hunter2', $email = 'test@woo.local' ) {
		$customer = new WC_Customer();
		$customer->set_billing_country( 'US' );
		$customer->set_first_name( 'Justin' );
		$customer->set_billing_state( 'PA' );
		$customer->set_billing_postcode( '19123' );
		$customer->set_billing_city( 'Philadelphia' );
		$customer->set_billing_address( '123 South Street' );
		$customer->set_billing_address_2( 'Apt 1' );
		$customer->set_shipping_country( 'US' );
		$customer->set_shipping_state( 'PA' );
		$customer->set_shipping_postcode( '19123' );
		$customer->set_shipping_city( 'Philadelphia' );
		$customer->set_shipping_address( '123 South Street' );
		$customer->set_shipping_address_2( 'Apt 1' );
		$customer->set_username( $username );
		$customer->set_password( $password );
		$customer->set_email( $email );
		$customer->save();
		return $customer;
	}

	// tests
	public function testCustomerQuery() {
		$customer_1 = $this->create_customer();

		$query = "
			query {
				customerBy(customerId: \"$customer_1->get_id() \") {
					isVatExempt
					hasCalculatedShipping
					calculatedShipping
					lastOrder
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
						email
						phone
					}
					isPayingCustomer
				}
			}
		";

		$actual = do_graphql_request( $query );

		/**
		 * use --debug flag to view
		 */
		\Codeception\Util\Debug::debug( $actual );

		$expected = [
			'data' => [
				'customer' => [
					'isVatExempt'           => $customer_1->get_is_vat_exempt(),
					'hasCalculatedShipping' => $customer_1->has_calculated_shipping(),
					'calculatedShipping'    => $customer_1->get_calculated_shipping(),
					'lastOrder'             => $customer_1->get_last_order(),
					'orderCount'            => $customer_1->get_order_count(),
					'totalSpent'            => $customer_1->get_total_spent(),
					'username'              => $customer_1->get_username(),
					'email'                 => $customer_1->get_email(),
					'firstName'             => $customer_1->get_first_name(),
					'lastName'              => $customer_1->get_last_name(),
					'displayName'           => $customer_1->get_display_name(),
					'role'                  => $customer_1->get_role(),
					'date'                  => $customer_1->get_date_created(),
					'modified'              => $customer_1->get_date_modified(),
					'billing'               => [
						'firstName' => $customer_1->get_billing_first_name(),
						'lastName'  => $customer_1->get_billing_last_name(),
						'company'   => $customer_1->get_billing_company(),
						'address1'  => $customer_1->get_billing_address_1(),
						'address2'  => $customer_1->get_billing_address_2(),
						'city'      => $customer_1->get_billing_city(),
						'state'     => $customer_1->get_billing_state(),
						'postcode'  => $customer_1->get_billing_postcode(),
						'country'   => $customer_1->get_billing_country(),
						'email'     => $customer_1->get_billing_email(),
						'phone'     => $customer_1->get_billing_phone(),
					],
					'shipping'              => [
						'firstName' => $customer_1->get_shipping_first_name(),
						'lastName'  => $customer_1->get_shipping_last_name(),
						'company'   => $customer_1->get_shipping_company(),
						'address1'  => $customer_1->get_shipping_address_1(),
						'address2'  => $customer_1->get_shipping_address_2(),
						'city'      => $customer_1->get_shipping_city(),
						'state'     => $customer_1->get_shipping_state(),
						'postcode'  => $customer_1->get_shipping_postcode(),
						'country'   => $customer_1->get_shipping_country(),
					],
					'isPayingCustomer'      => $customer_1->get_is_paying_customer(),
				],
			],
		];

		$this->assertEquals( $expected, $actual );
	}
}
