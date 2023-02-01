<?php

class CustomerMutationsTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {
	public function setUp(): void {
		// before
		parent::setUp();

		update_option( 'users_can_register', 1 );

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

	private function executeRegisterCustomerMutation( $input ) {
		$query = '
			mutation( $input: RegisterCustomerInput! ) {
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
		$response  = $this->graphql( compact( 'query', 'variables' ) );

		return $response;
	}

	private function executeUpdateCustomerMutation( $input ) {
		$query = '
			mutation( $input: UpdateCustomerInput! ) {
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
		$response  = $this->graphql( compact( 'query', 'variables' ) );

		return $response;
	}

	public function testRegisterMutationWithoutCustomerInfo() {
		/**
		 * Assertion One
		 *
		 * Tests mutation without a providing WooCommerce specific customer information.
		 */
		$response = $this->executeRegisterCustomerMutation(
			[
				'clientMutationId' => 'someId',
				'username'         => $this->username,
				'password'         => $this->pass,
				'email'            => $this->email,
				'firstName'        => $this->first_name,
				'lastName'         => $this->last_name,
			]
		);

		// Assert user created.
		$user = get_user_by( 'email', 'peter.parker@dailybugle.com' );
		$this->assertTrue( is_a( $user, WP_User::class ) );

		$expected = [
			$this->expectedField( 'registerCustomer.authToken', \WPGraphQL\JWT_Authentication\Auth::get_token( $user ) ),
			$this->expectedField( 'registerCustomer.refreshToken', \WPGraphQL\JWT_Authentication\Auth::get_refresh_token( $user ) ),
			$this->expectedObject(
				'registerCustomer.customer',
				[
					$this->expectedField( 'databaseId', $user->ID ),
					$this->expectedField( 'email', $this->email ),
					$this->expectedField( 'username', $this->username ),
					$this->expectedField( 'firstName', $this->first_name ),
					$this->expectedField( 'lastName', $this->last_name ),
					$this->expectedField( 'billing', $this->empty_billing() ),
					$this->expectedField( 'shipping', $this->empty_shipping() ),
				]
			),
			$this->expectedField( 'registerCustomer.viewer.userId', $user->ID ),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testRegisterMutationWithBillingInfo() {
		/**
		 * Assertion One
		 *
		 * Tests mutation with customer billing information.
		 */
		$response = $this->executeRegisterCustomerMutation(
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

		$user = get_user_by( 'email', 'peter.parker@dailybugle.com' );
		$this->assertTrue( is_a( $user, WP_User::class ) );

		$expected = [
			$this->expectedField( 'registerCustomer.authToken', \WPGraphQL\JWT_Authentication\Auth::get_token( $user ) ),
			$this->expectedField( 'registerCustomer.refreshToken', \WPGraphQL\JWT_Authentication\Auth::get_refresh_token( $user ) ),
			$this->expectedObject(
				'registerCustomer.customer',
				[
					$this->expectedField( 'databaseId', $user->ID ),
					$this->expectedField( 'email', $this->email ),
					$this->expectedField( 'username', $this->username ),
					$this->expectedField( 'firstName', $this->first_name ),
					$this->expectedField( 'lastName', $this->last_name ),
					$this->expectedField( 'billing', array_merge( $this->empty_billing(), $this->billing ) ),
					$this->expectedField( 'shipping', $this->empty_shipping() ),
				]
			),
			$this->expectedField( 'registerCustomer.viewer.userId', $user->ID ),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testRegisterMutationWithShippingSameAsBillingInfo() {
		/**
		 * Assertion One
		 *
		 * Tests mutation using "shippingSameAsBilling" field.
		 */
		$response = $this->executeRegisterCustomerMutation(
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

		$user = get_user_by( 'email', 'peter.parker@dailybugle.com' );
		$this->assertTrue( is_a( $user, WP_User::class ) );

		$expected = [
			$this->expectedField( 'registerCustomer.authToken', \WPGraphQL\JWT_Authentication\Auth::get_token( $user ) ),
			$this->expectedField( 'registerCustomer.refreshToken', \WPGraphQL\JWT_Authentication\Auth::get_refresh_token( $user ) ),
			$this->expectedObject(
				'registerCustomer.customer',
				[
					$this->expectedField( 'databaseId', $user->ID ),
					$this->expectedField( 'email', $this->email ),
					$this->expectedField( 'username', $this->username ),
					$this->expectedField( 'firstName', $this->first_name ),
					$this->expectedField( 'lastName', $this->last_name ),
					$this->expectedField( 'billing', array_merge( $this->empty_billing(), $this->billing ) ),
					$this->expectedField(
						'shipping',
						array_merge(
							$this->empty_shipping(),
							array_intersect_key( $this->billing, $this->empty_shipping() )
						)
					),
				]
			),
			$this->expectedField( 'registerCustomer.viewer.userId', $user->ID ),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testRegisterMutationWithBillingAndShippingInfo() {
		/**
		 * Assertion One
		 *
		 * Tests mutation with customer shipping information.
		 */
		$response = $this->executeRegisterCustomerMutation(
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

		$expected = [
			$this->expectedField( 'registerCustomer.authToken', \WPGraphQL\JWT_Authentication\Auth::get_token( $user ) ),
			$this->expectedField( 'registerCustomer.refreshToken', \WPGraphQL\JWT_Authentication\Auth::get_refresh_token( $user ) ),
			$this->expectedObject(
				'registerCustomer.customer',
				[
					$this->expectedField( 'databaseId', $user->ID ),
					$this->expectedField( 'email', $this->email ),
					$this->expectedField( 'username', $this->username ),
					$this->expectedField( 'firstName', $this->first_name ),
					$this->expectedField( 'lastName', $this->last_name ),
					$this->expectedField( 'billing', array_merge( $this->empty_billing(), $this->billing ) ),
					$this->expectedField( 'shipping', array_merge( $this->empty_shipping(), $this->shipping ) ),
				]
			),
			$this->expectedField( 'registerCustomer.viewer.userId', $user->ID ),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testUpdateMutation() {
		/**
		 * Assertion One
		 *
		 * Tests mutation without a providing WooCommerce specific customer information.
		 */
		$this->executeRegisterCustomerMutation(
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

		$response = $this->executeUpdateCustomerMutation(
			[
				'clientMutationId' => 'someId',
				'id'               => $this->toRelayId( 'customer', $user->ID ),
				'email'            => $this->new_email,
				'firstName'        => $this->new_first_name,
				'lastName'         => $this->new_last_name,
				'billing'          => array_merge( $this->new_billing, [ 'overwrite' => true ] ),
				'shipping'         => array_merge( $this->new_shipping, [ 'overwrite' => true ] ),
			]
		);

		$expected = [
			$this->expectedObject(
				'updateCustomer.customer',
				[
					$this->expectedField( 'databaseId', $user->ID ),
					$this->expectedField( 'email', $this->new_email ),
					$this->expectedField( 'username', $this->username ),
					$this->expectedField( 'firstName', $this->new_first_name ),
					$this->expectedField( 'lastName', $this->new_last_name ),
					$this->expectedField( 'billing', array_merge( $this->empty_billing(), $this->new_billing ) ),
					$this->expectedField( 'shipping', array_merge( $this->empty_shipping(), $this->new_shipping ) ),
				]
			),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testUpdateMutationWithShippingSameAsBilling() {
		/**
		 * Assertion One
		 *
		 * Tests mutation without a providing WooCommerce specific customer information.
		 */
		$this->executeRegisterCustomerMutation(
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

		$response = $this->executeUpdateCustomerMutation(
			[
				'clientMutationId'      => 'someId',
				'id'                    => $this->toRelayId( 'customer', $user->ID ),
				'shippingSameAsBilling' => true,
			]
		);

		$expected = [
			$this->expectedObject(
				'updateCustomer.customer',
				[
					$this->expectedField( 'databaseId', $user->ID ),
					$this->expectedField( 'email', $this->email ),
					$this->expectedField( 'username', $this->username ),
					$this->expectedField( 'firstName', $this->first_name ),
					$this->expectedField( 'lastName', $this->last_name ),
					$this->expectedField( 'billing', array_merge( $this->empty_billing(), $this->billing ) ),
					$this->expectedField(
						'shipping',
						array_merge(
							$this->empty_shipping(),
							array_intersect_key( $this->billing, $this->empty_shipping() )
						)
					),
				]
			),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testRegisterMutationWithoutAnyInfo() {
		/**
		 * Assertion One
		 *
		 * Tests mutation without a providing an username and password.
		 */
		$response = $this->executeRegisterCustomerMutation(
			[
				'clientMutationId' => 'someId',
				'email'            => $this->email,
				'firstName'        => $this->first_name,
				'lastName'         => $this->last_name,
			]
		);

		$user = get_user_by( 'email', 'peter.parker@dailybugle.com' );
		$this->assertTrue( is_a( $user, WP_User::class ) );

		$expected = [
			$this->expectedObject(
				'registerCustomer.customer',
				[
					$this->expectedField( 'databaseId', $user->ID ),
					$this->expectedField( 'email', $this->email ),
					$this->expectedField( 'firstName', $this->first_name ),
					$this->expectedField( 'lastName', $this->last_name ),
					$this->expectedField( 'billing', $this->empty_billing() ),
					$this->expectedField( 'shipping', $this->empty_shipping() ),
				]
			),
		];

		$this->assertQuerySuccessful( $response, $expected );
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

		$response = $this->graphql( compact( 'query', 'variables' ) );

		$user = get_user_by( 'email', 'user@woographql.test' );
		$this->assertTrue( is_a( $user, WP_User::class ) );

		$expected = [
			$this->expectedObject(
				'registerCustomer.customer',
				[
					$this->expectedField( 'databaseId', $user->ID ),
					$this->expectedField( 'email', 'user@woographql.test' ),
					$this->expectedNode(
						'metaData',
						[
							$this->expectedField( 'key', 'test_meta_key' ),
							$this->expectedField( 'value', 'test_meta_value' ),
						]
					),
				]
			),
		];

		$this->assertQuerySuccessful( $response, $expected );

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
				'id'               => $this->toRelayId( 'customer', $user->ID ),
				'metaData'         => [
					[
						'key'   => 'test_meta_key',
						'value' => 'new_meta_value',
					],
				],
			],
		];

		$response = $this->graphql( compact( 'query', 'variables' ) );
		$expected = [
			$this->expectedObject(
				'updateCustomer.customer',
				[
					$this->expectedField( 'databaseId', $user->ID ),
					$this->expectedField( 'email', 'user@woographql.test' ),
					$this->expectedNode(
						'metaData',
						[
							$this->expectedField( 'key', 'test_meta_key' ),
							$this->expectedField( 'value', 'new_meta_value' ),
						]
					),
				]
			),
		];

		$this->assertQuerySuccessful( $response, $expected );

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

		$response = $this->graphql( compact( 'query', 'variables' ) );
		$expected = [
			$this->expectedObject(
				'updateCustomer.customer',
				[
					$this->expectedField( 'id', 'guest' ),
					$this->expectedNode(
						'metaData',
						[
							$this->expectedField( 'key', 'test_meta_key' ),
							$this->expectedField( 'value', 'test_meta_value' ),
						]
					),
				]
			),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}
}
