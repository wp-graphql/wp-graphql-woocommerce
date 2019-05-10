<?php

class CustomerMutationsTest extends \Codeception\TestCase\WPTestCase {
	public function setUp() {
		// before
		parent::setUp();

		update_option( 'users_can_register', 1 );
		$this->helper = $this->getModule('\Helper\Wpunit')->customer();
		$this->first_name = 'Peter';
		$this->last_name  = 'Parker';
		$this->username   = 'spidersRLoose67';
		$this->pass       = 'c@nalSt_@ll_DaY';
		$this->email      = 'peter.parker@dailybugle.com';
		$this->billing    = array(
			'firstName' => 'May',
			'lastName'  => 'Parker',
			'address1'  => '20 Ingram St',
			'city'      => 'New York City',
			'state'     => 'NY',
			'postcode'  => '12345',
			'country'   => 'US',
			'email'     => 'superfreak500@gmail.com',
			'phone'     => '555-555-1234',
		);
		$this->shipping   = array(
			'firstName' => 'Peter',
			'lastName'  => 'Parker',
			'address1'  => '202 Canal St',
			'address2'  => 'Apt #4',
			'city'      => 'New York City',
			'state'     => 'NY',
			'postcode'  => '12310',
			'country'   => 'US',
		);
	}

	public function tearDown() {
		// your tear down methods here

		// then
		parent::tearDown();
	}

	private function empty_shipping() {
		return array(
			'firstName' => null,
            'lastName'  => null,
            'company'   => null,
            'address1'  => null,
            'address2'  => null,
            'city'      => null,
            'state'     => null,
            'postcode'  => null,
            'country'   => null,
		);
	}

	private function empty_billing() {
		return array_merge(
			$this->empty_shipping(),
			array( 'email' => null, 'phone' => null )
		);
	}

	private function registerUser( $input ) {
		$mutation   = '
			mutation register( $input: RegisterCustomerInput! ) {
				registerCustomer( input: $input ) {
					clientMutationId
					customer {
						customerId
						username
						email
						firstName
						lastName
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
					}
				}
			}
		';

		$variables = array( 'input' => $input );
		$actual    = do_graphql_request( $mutation, 'register', $variables );

		return $actual;
	}

	// tests
	public function testRegisterWithoutCustomerInfo() {
		/**
		 * Assertion One
		 * 
		 * Tests mutation without a providing WooCommerce specific customer information.
		 */
		$actual = $this->registerUser(
			array(
				'clientMutationId' => 'someId',
				'username'         => $this->username,
				'password'         => $this->pass,
				'email'            => $this->email,
				'firstName'        => $this->first_name,
				'lastName'         => $this->last_name,
			)
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$user = get_user_by( 'email', 'peter.parker@dailybugle.com' );
		$this->assertTrue( is_a( $user, WP_User::class ) );

		$expected = array(
			'data' => array(
				'registerCustomer' => array(
					'clientMutationId' => 'someId',
					'customer'         => array(
						'customerId' => $user->ID,
						'email'      => $this->email,
						'username'   => $this->username,
						'firstName'  => $this->first_name,
						'lastName'   => $this->last_name,
						'billing'    => $this->empty_billing(),
						'shipping'   => $this->empty_shipping(),
					),
				),
			),
		);

		$this->assertEqualSets( $expected, $actual );
	}

	public function testRegisterWithBillingInfo() {
		/**
		 * Assertion One
		 * 
		 * Tests mutation with customer billing information.
		 */
		$actual = $this->registerUser(
			array(
				'clientMutationId' => 'someId',
				'username'         => $this->username,
				'password'         => $this->pass,
				'email'            => $this->email,
				'firstName'        => $this->first_name,
				'lastName'         => $this->last_name,
				'billing'          => $this->billing,
			)
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$user = get_user_by( 'email', 'peter.parker@dailybugle.com' );
		$this->assertTrue( is_a( $user, WP_User::class ) );

		$expected = array(
			'data' => array(
				'registerCustomer' => array(
					'clientMutationId' => 'someId',
					'customer'         => array(
						'customerId' => $user->ID,
						'email'      => $this->email,
						'username'   => $this->username,
						'firstName'  => $this->first_name,
						'lastName'   => $this->last_name,
						'billing'    => array_merge( $this->empty_billing(), $this->billing ),
						'shipping'   => $this->empty_shipping(),
					),
				),
			),
		);

		$this->assertEqualSets( $expected, $actual );
	}

	public function testRegisterWithShippingSameAsBillingInfo() {
		/**
		 * Assertion One
		 * 
		 * Tests mutation using "shippingSameAsBilling" field.
		 */
		$actual = $this->registerUser(
			array(
				'clientMutationId'      => 'someId',
				'username'              => $this->username,
				'password'              => $this->pass,
				'email'                 => $this->email,
				'firstName'             => $this->first_name,
				'lastName'              => $this->last_name,
				'billing'               => $this->billing,
				'shippingSameAsBilling' => true,
			)
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$user = get_user_by( 'email', 'peter.parker@dailybugle.com' );
		$this->assertTrue( is_a( $user, WP_User::class ) );

		$expected = array(
			'data' => array(
				'registerCustomer' => array(
					'clientMutationId' => 'someId',
					'customer'         => array(
						'customerId' => $user->ID,
						'email'      => $this->email,
						'username'   => $this->username,
						'firstName'  => $this->first_name,
						'lastName'   => $this->last_name,
						'billing'    => array_merge( $this->empty_billing(), $this->billing ),
						'shipping'   => array_merge(
							$this->empty_shipping(),
							array_intersect_key( $this->billing, $this->empty_shipping() )
						),
					),
				),
			),
		);

		$this->assertEqualSets( $expected, $actual );
	}

	public function testRegisterWithBillingAndShippingInfo() {
		/**
		 * Assertion Four
		 * 
		 * Tests mutation with customer shipping information.
		 */
		$actual = $this->registerUser(
			array(
				'clientMutationId' => 'someId',
				'username'         => $this->username,
				'password'         => $this->pass,
				'email'            => $this->email,
				'firstName'        => $this->first_name,
				'lastName'         => $this->last_name,
				'billing'          => $this->billing,
				'shipping'         => $this->shipping,
			)
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$user = get_user_by( 'email', 'peter.parker@dailybugle.com' );
		$this->assertTrue( is_a( $user, WP_User::class ) );

		$expected = array(
			'data' => array(
				'registerCustomer' => array(
					'clientMutationId' => 'someId',
					'customer'         => array(
						'customerId' => $user->ID,
						'email'      => $this->email,
						'username'   => $this->username,
						'firstName'  => $this->first_name,
						'lastName'   => $this->last_name,
						'billing'    => array_merge( $this->empty_billing(), $this->billing ),
						'shipping'   => array_merge( $this->empty_shipping(), $this->shipping ),
					),
				),
			),
		);

		$this->assertEqualSets( $expected, $actual );
	}

}