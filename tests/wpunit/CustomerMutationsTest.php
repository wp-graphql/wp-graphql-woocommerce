<?php

class CustomerMutationsTest extends \Codeception\TestCase\WPTestCase {
	public function setUp(): void {
		// before
		parent::setUp();

		update_option( 'users_can_register', 1 );
		$this->helper = $this->getModule( '\Helper\Wpunit' )->customer();

		// Register Info
		$this->first_name = 'Peter';
		$this->last_name  = 'Parker';
		$this->username   = 'spidersRLoose67';
		$this->pass       = 'c@nalSt_@ll_DaY';
		$this->email      = 'peter.parker@dailybugle.com';
		$this->billing    = [
			'firstName' => 'May',
			'lastName'  => 'Parker',
			'address1'  => '20 Ingram St',
			'city'      => 'New York City',
			'state'     => 'NY',
			'postcode'  => '12345',
			'country'   => 'US',
			'email'     => 'superfreak500@gmail.com',
			'phone'     => '555-555-1234',
		];
		$this->shipping   = [
			'firstName' => 'Peter',
			'lastName'  => 'Parker',
			'address1'  => '202 Canal St',
			'address2'  => 'Apt #4',
			'city'      => 'New York City',
			'state'     => 'NY',
			'postcode'  => '12310',
			'country'   => 'US',
		];

		// Update Info
		$this->new_first_name = 'Ben';
		$this->new_last_name  = 'Wallace';
		$this->new_email      = 'we0utHere32@gmail.com';
		$this->new_billing    = [
			'firstName' => 'Jim',
			'lastName'  => 'Bean',
			'address1'  => '45 Vodka Rd',
			'city'      => 'Norfolk',
			'state'     => 'VA',
			'postcode'  => '23456',
			'country'   => 'US',
			'email'     => '4daKnock0ut@yahoo.com',
			'phone'     => '757-422-0989',
		];
		$this->new_shipping   = [
			'firstName' => 'Ben',
			'lastName'  => 'Wallace',
			'address1'  => '478 Vodka Rd',
			'city'      => 'Virginia Beach',
			'state'     => 'VA',
			'postcode'  => '23451',
			'country'   => 'US',
		];
	}

	public function tearDown(): void {
		wp_set_current_user( 0 );
		\WC()->customer = null;
		\WC()->session  = null;
		\WC()->cart     = null;

		\WC()->initialize_session();
		\WC()->initialize_cart();

		// then
		parent::tearDown();
	}

	private function empty_shipping() {
		return [
			'firstName' => null,
			'lastName'  => null,
			'company'   => null,
			'address1'  => null,
			'address2'  => null,
			'city'      => null,
			'state'     => null,
			'postcode'  => null,
			'country'   => null,
		];
	}

	private function empty_billing() {
		return array_merge(
			$this->empty_shipping(),
			[
				'email' => null,
				'phone' => null,
			]
		);
	}

	private function registerCustomer( $input ) {
		$mutation = '
			mutation register( $input: RegisterCustomerInput! ) {
				registerCustomer( input: $input ) {
					clientMutationId
					authToken
					refreshToken
					customer {
						databaseId
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
					viewer {
						userId
					}
				}
			}
		';

		$variables = [ 'input' => $input ];
		$actual    = graphql(
			[
				'query'          => $mutation,
				'operation_name' => 'register',
				'variables'      => $variables,
			]
		);

		return $actual;
	}

	private function updateCustomer( $input ) {
		$mutation = '
			mutation update( $input: UpdateCustomerInput! ) {
				updateCustomer( input: $input ) {
					clientMutationId
					authToken
					refreshToken
					customer {
						databaseId
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

		$variables = [ 'input' => $input ];
		$actual    = graphql(
			[
				'query'          => $mutation,
				'operation_name' => 'update',
				'variables'      => $variables,
			]
		);

		return $actual;
	}

	public function testRegisterMutationWithoutCustomerInfo() {
		/**
		 * Assertion One
		 *
		 * Tests mutation without a providing WooCommerce specific customer information.
		 */
		$actual = $this->registerCustomer(
			[
				'clientMutationId' => 'someId',
				'username'         => $this->username,
				'password'         => $this->pass,
				'email'            => $this->email,
				'firstName'        => $this->first_name,
				'lastName'         => $this->last_name,
			]
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$user = get_user_by( 'email', 'peter.parker@dailybugle.com' );
		$this->assertTrue( is_a( $user, WP_User::class ) );

		$expected = [
			'data' => [
				'registerCustomer' => [
					'clientMutationId' => 'someId',
					'authToken'        => \WPGraphQL\JWT_Authentication\Auth::get_token( $user ),
					'refreshToken'     => \WPGraphQL\JWT_Authentication\Auth::get_refresh_token( $user ),
					'customer'         => [
						'databaseId' => $user->ID,
						'email'      => $this->email,
						'username'   => $this->username,
						'firstName'  => $this->first_name,
						'lastName'   => $this->last_name,
						'billing'    => $this->empty_billing(),
						'shipping'   => $this->empty_shipping(),
					],
					'viewer'           => [
						'userId' => $user->ID,
					],
				],
			],
		];

		$this->assertEquals( $expected, $actual );
	}

	public function testRegisterMutationWithBillingInfo() {
		/**
		 * Assertion One
		 *
		 * Tests mutation with customer billing information.
		 */
		$actual = $this->registerCustomer(
			[
				'clientMutationId' => 'someId',
				'username'         => $this->username,
				'password'         => $this->pass,
				'email'            => $this->email,
				'firstName'        => $this->first_name,
				'lastName'         => $this->last_name,
				'billing'          => $this->billing,
			]
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$user = get_user_by( 'email', 'peter.parker@dailybugle.com' );
		$this->assertTrue( is_a( $user, WP_User::class ) );

		$expected = [
			'data' => [
				'registerCustomer' => [
					'clientMutationId' => 'someId',
					'authToken'        => \WPGraphQL\JWT_Authentication\Auth::get_token( $user ),
					'refreshToken'     => \WPGraphQL\JWT_Authentication\Auth::get_refresh_token( $user ),
					'customer'         => [
						'databaseId' => $user->ID,
						'email'      => $this->email,
						'username'   => $this->username,
						'firstName'  => $this->first_name,
						'lastName'   => $this->last_name,
						'billing'    => array_merge( $this->empty_billing(), $this->billing ),
						'shipping'   => $this->empty_shipping(),
					],
					'viewer'           => [
						'userId' => $user->ID,
					],
				],
			],
		];

		$this->assertEquals( $expected, $actual );
	}

	public function testRegisterMutationWithShippingSameAsBillingInfo() {
		/**
		 * Assertion One
		 *
		 * Tests mutation using "shippingSameAsBilling" field.
		 */
		$actual = $this->registerCustomer(
			[
				'clientMutationId'      => 'someId',
				'username'              => $this->username,
				'password'              => $this->pass,
				'email'                 => $this->email,
				'firstName'             => $this->first_name,
				'lastName'              => $this->last_name,
				'billing'               => $this->billing,
				'shippingSameAsBilling' => true,
			]
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$user = get_user_by( 'email', 'peter.parker@dailybugle.com' );
		$this->assertTrue( is_a( $user, WP_User::class ) );

		$expected = [
			'data' => [
				'registerCustomer' => [
					'clientMutationId' => 'someId',
					'authToken'        => \WPGraphQL\JWT_Authentication\Auth::get_token( $user ),
					'refreshToken'     => \WPGraphQL\JWT_Authentication\Auth::get_refresh_token( $user ),
					'customer'         => [
						'databaseId' => $user->ID,
						'email'      => $this->email,
						'username'   => $this->username,
						'firstName'  => $this->first_name,
						'lastName'   => $this->last_name,
						'billing'    => array_merge( $this->empty_billing(), $this->billing ),
						'shipping'   => array_merge(
							$this->empty_shipping(),
							array_intersect_key( $this->billing, $this->empty_shipping() )
						),
					],
					'viewer'           => [
						'userId' => $user->ID,
					],
				],
			],
		];

		$this->assertEquals( $expected, $actual );
	}

	public function testRegisterMutationWithBillingAndShippingInfo() {
		/**
		 * Assertion One
		 *
		 * Tests mutation with customer shipping information.
		 */
		$actual = $this->registerCustomer(
			[
				'clientMutationId' => 'someId',
				'username'         => $this->username,
				'password'         => $this->pass,
				'email'            => $this->email,
				'firstName'        => $this->first_name,
				'lastName'         => $this->last_name,
				'billing'          => $this->billing,
				'shipping'         => $this->shipping,
			]
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$user = get_user_by( 'email', 'peter.parker@dailybugle.com' );
		$this->assertTrue( is_a( $user, WP_User::class ) );

		$expected = [
			'data' => [
				'registerCustomer' => [
					'clientMutationId' => 'someId',
					'authToken'        => \WPGraphQL\JWT_Authentication\Auth::get_token( $user ),
					'refreshToken'     => \WPGraphQL\JWT_Authentication\Auth::get_refresh_token( $user ),
					'customer'         => [
						'databaseId' => $user->ID,
						'email'      => $this->email,
						'username'   => $this->username,
						'firstName'  => $this->first_name,
						'lastName'   => $this->last_name,
						'billing'    => array_merge( $this->empty_billing(), $this->billing ),
						'shipping'   => array_merge( $this->empty_shipping(), $this->shipping ),
					],
					'viewer'           => [
						'userId' => $user->ID,
					],
				],
			],
		];

		$this->assertEquals( $expected, $actual );
	}

	public function testUpdateMutation() {
		/**
		 * Assertion One
		 *
		 * Tests mutation without a providing WooCommerce specific customer information.
		 */
		$this->registerCustomer(
			[
				'clientMutationId' => 'someId',
				'username'         => $this->username,
				'password'         => $this->pass,
				'email'            => $this->email,
				'firstName'        => $this->first_name,
				'lastName'         => $this->last_name,
				'billing'          => $this->billing,
				'shipping'         => $this->shipping,
			]
		);

		$user = get_user_by( 'email', 'peter.parker@dailybugle.com' );
		$this->assertTrue( is_a( $user, WP_User::class ) );

		$actual = $this->updateCustomer(
			[
				'clientMutationId' => 'someId',
				'id'               => $this->helper->to_relay_id( $user->ID ),
				'email'            => $this->new_email,
				'firstName'        => $this->new_first_name,
				'lastName'         => $this->new_last_name,
				'billing'          => array_merge( $this->new_billing, [ 'overwrite' => true ] ),
				'shipping'         => array_merge( $this->new_shipping, [ 'overwrite' => true ] ),
			]
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$expected = [
			'data' => [
				'updateCustomer' => [
					'clientMutationId' => 'someId',
					'authToken'        => \WPGraphQL\JWT_Authentication\Auth::get_token( $user ),
					'refreshToken'     => \WPGraphQL\JWT_Authentication\Auth::get_refresh_token( $user ),
					'customer'         => [
						'databaseId' => $user->ID,
						'email'      => $this->new_email,
						'username'   => $this->username,
						'firstName'  => $this->new_first_name,
						'lastName'   => $this->new_last_name,
						'billing'    => array_merge( $this->empty_billing(), $this->new_billing ),
						'shipping'   => array_merge( $this->empty_shipping(), $this->new_shipping ),
					],
				],
			],
		];

		$this->assertEquals( $expected, $actual );
	}

	public function testUpdateMutationWithShippingSameAsBilling() {
		/**
		 * Assertion One
		 *
		 * Tests mutation without a providing WooCommerce specific customer information.
		 */
		$this->registerCustomer(
			[
				'clientMutationId' => 'someId',
				'username'         => $this->username,
				'password'         => $this->pass,
				'email'            => $this->email,
				'firstName'        => $this->first_name,
				'lastName'         => $this->last_name,
				'billing'          => $this->billing,
				'shipping'         => $this->shipping,
			]
		);

		$user = get_user_by( 'email', 'peter.parker@dailybugle.com' );
		$this->assertTrue( is_a( $user, WP_User::class ) );

		$actual = $this->updateCustomer(
			[
				'clientMutationId'      => 'someId',
				'id'                    => $this->helper->to_relay_id( $user->ID ),
				'shippingSameAsBilling' => true,
			]
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$expected = [
			'data' => [
				'updateCustomer' => [
					'clientMutationId' => 'someId',
					'authToken'        => \WPGraphQL\JWT_Authentication\Auth::get_token( $user ),
					'refreshToken'     => \WPGraphQL\JWT_Authentication\Auth::get_refresh_token( $user ),
					'customer'         => [
						'databaseId' => $user->ID,
						'email'      => $this->email,
						'username'   => $this->username,
						'firstName'  => $this->first_name,
						'lastName'   => $this->last_name,
						'billing'    => array_merge( $this->empty_billing(), $this->billing ),
						'shipping'   => array_merge(
							$this->empty_shipping(),
							array_intersect_key( $this->billing, $this->empty_shipping() )
						),
					],
				],
			],
		];

		$this->assertEquals( $expected, $actual );
	}

	public function testRegisterMutationWithoutAnyInfo() {
		/**
		 * Assertion One
		 *
		 * Tests mutation without a providing an username and password.
		 */
		$actual = $this->registerCustomer(
			[
				'clientMutationId' => 'someId',
				'email'            => $this->email,
				'firstName'        => $this->first_name,
				'lastName'         => $this->last_name,
			]
		);

		// use --debug flag to view.
		codecept_debug( $actual );

		$user = get_user_by( 'email', 'peter.parker@dailybugle.com' );
		$this->assertTrue( is_a( $user, WP_User::class ) );

		$expected = [
			'data' => [
				'registerCustomer' => [
					'clientMutationId' => 'someId',
					'authToken'        => \WPGraphQL\JWT_Authentication\Auth::get_token( $user ),
					'refreshToken'     => \WPGraphQL\JWT_Authentication\Auth::get_refresh_token( $user ),
					'customer'         => [
						'databaseId' => $user->ID,
						'email'      => $this->email,
						'username'   => $user->user_login,
						'firstName'  => $this->first_name,
						'lastName'   => $this->last_name,
						'billing'    => $this->empty_billing(),
						'shipping'   => $this->empty_shipping(),
					],
					'viewer'           => [
						'userId' => $user->ID,
					],
				],
			],
		];

		$this->assertEquals( $expected, $actual );
	}

	public function testCustomerMutationsWithMeta() {
		/**
		 * Assertion One
		 *
		 * Test "metaData" input field with "registerCustomer" mutation.
		 */
		$query     = '
			mutation( $input: RegisterCustomerInput! ) {
				registerCustomer( input: $input ) {
					customer {
						databaseId
						email
						metaData {
							key
							value
						}
					}
				}
			}
		';
		$variables = [
			'input' => [
				'clientMutationId' => 'some_id',
				'email'            => 'user@woographql.test',
				'metaData'         => [
					[
						'key'   => 'test_meta_key',
						'value' => 'test_meta_value',
					],
				],
			],
		];

		$actual = graphql( compact( 'query', 'variables' ) );
		codecept_debug( $actual );

		$user = get_user_by( 'email', 'user@woographql.test' );
		$this->assertTrue( is_a( $user, WP_User::class ) );

		$expected = [
			'data' => [
				'registerCustomer' => [
					'customer' => [
						'databaseId' => $user->ID,
						'email'      => 'user@woographql.test',
						'metaData'   => [
							[
								'key'   => 'test_meta_key',
								'value' => 'test_meta_value',
							],
						],
					],
				],
			],
		];

		$this->assertEquals( $expected, $actual );

		/**
		 * Assertion Two
		 *
		 * Test "metaData" input field with "updateCustomer" mutation.
		 */
		$query     = '
			mutation( $input: UpdateCustomerInput! ) {
				updateCustomer( input: $input ) {
					customer {
						databaseId
						email
						metaData {
							key
							value
						}
					}
				}
			}
		';
		$variables = [
			'input' => [
				'clientMutationId' => 'some_id',
				'id'               => \GraphQLRelay\Relay::toGlobalId( 'customer', $user->ID ),
				'metaData'         => [
					[
						'key'   => 'test_meta_key',
						'value' => 'new_meta_value',
					],
				],
			],
		];

		$actual = graphql( compact( 'query', 'variables' ) );
		codecept_debug( $actual );

		$expected = [
			'data' => [
				'updateCustomer' => [
					'customer' => [
						'databaseId' => $user->ID,
						'email'      => 'user@woographql.test',
						'metaData'   => [
							[
								'key'   => 'test_meta_key',
								'value' => 'new_meta_value',
							],
						],
					],
				],
			],
		];

		$this->assertEquals( $expected, $actual );

		/**
		 * Assertion Three
		 *
		 * Test "metaData" input field with "updateCustomer" mutation on the session user.
		 */
		$query     = '
			mutation( $input: UpdateCustomerInput! ) {
				updateCustomer( input: $input ) {
					customer {
						id
						metaData {
							key
							value
						}
					}
				}
			}
		';
		$variables = [
			'input' => [
				'clientMutationId' => 'some_id',
				'metaData'         => [
					[
						'key'   => 'test_meta_key',
						'value' => 'test_meta_value',
					],
				],
			],
		];

		$actual = graphql( compact( 'query', 'variables' ) );
		codecept_debug( $actual );

		$expected = [
			'data' => [
				'updateCustomer' => [
					'customer' => [
						'id'       => 'guest',
						'metaData' => [
							[
								'key'   => 'test_meta_key',
								'value' => 'test_meta_value',
							],
						],
					],
				],
			],
		];

		$this->assertEquals( $expected, $actual );
	}
}
