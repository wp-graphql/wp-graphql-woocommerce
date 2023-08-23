<?php
use Faker\Factory;

class CustomerMutationsTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {
	/**
	 * Faker instance.
	 *
	 * @var Faker\Factory
	 */
	private $faker;

	public function setUp(): void {
		// before
		parent::setUp();

		$this->faker = Factory::create();
		update_option( 'users_can_register', 1 );
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

	public function generateCustomerInput() {
		$first_name = $this->faker->firstName();
		$last_name  = $this->faker->lastName();
		$username   = $this->faker->userName();
		$password   = $this->faker->password();
		$email      = $this->faker->email();
		$phone	    = $this->faker->phoneNumber();
		$address    = $this->faker->streetAddress();
		$city       = $this->faker->city();
		$state      = $this->faker->state();
		$postcode   = $this->faker->postcode();
		$country    = 'US';

		return [
			'firstName' => $first_name,
			'lastName'  => $last_name,
			'username'  => $username,
			'password'  => $password,
			'email'     => $email,
			'billing'   => [
				'firstName' => $first_name,
				'lastName'  => $last_name,
				'address1'  => $address,
				'city'      => $city,
				'state'     => $state,
				'postcode'  => $postcode,
				'country'   => $country,
				'email'     => $email,
				'phone'     => $phone,
			],
			'shipping'  => [
				'firstName' => $first_name,
				'lastName'  => $last_name,
				'address1'  => $address,
				'city'      => $city,
				'state'     => $state,
				'postcode'  => $postcode,
				'country'   => $country,
				'phone'     => $phone,
			],
		];
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
			'phone'		=> null,
		];
	}

	private function empty_billing() {
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
			'email'     => null,
			'phone'		=> null,
		];
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
							phone
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
							phone
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
		 * Get customer input.
		 */
		$customer_input = $this->generateCustomerInput();

		/**
		 * Assertion One
		 *
		 * Tests mutation without a providing WooCommerce specific customer information.
		 */
		$response = $this->executeRegisterCustomerMutation(
			array_merge(
				$customer_input,
				[
					'billing'  => [],
					'shipping' => [],
				]
			)
		);

		// Assert user created.
		$user = get_user_by( 'email', $customer_input['email'] );
		$this->assertTrue( is_a( $user, WP_User::class ) );

		$expected = [
			$this->expectedField( 'registerCustomer.authToken', \WPGraphQL\JWT_Authentication\Auth::get_token( $user ) ),
			$this->expectedField( 'registerCustomer.refreshToken', \WPGraphQL\JWT_Authentication\Auth::get_refresh_token( $user ) ),
			$this->expectedObject(
				'registerCustomer.customer',
				[
					$this->expectedField( 'databaseId', $user->ID ),
					$this->expectedField( 'email', $customer_input['email'] ),
					$this->expectedField( 'username', $customer_input['username'] ),
					$this->expectedField( 'firstName', $customer_input['firstName'] ),
					$this->expectedField( 'lastName', $customer_input['lastName'] ),
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
		 * Get customer input.
		 */
		$customer_input = $this->generateCustomerInput();

		/**
		 * Assertion One
		 *
		 * Tests mutation with customer billing information.
		 */
		$response = $this->executeRegisterCustomerMutation(
			array_merge(
				$customer_input,
				[
					'shipping' => [],
				]
			)
		);

		$user = get_user_by( 'email', $customer_input['email'] );
		$this->assertTrue( is_a( $user, WP_User::class ) );

		$expected = [
			$this->expectedField( 'registerCustomer.authToken', \WPGraphQL\JWT_Authentication\Auth::get_token( $user ) ),
			$this->expectedField( 'registerCustomer.refreshToken', \WPGraphQL\JWT_Authentication\Auth::get_refresh_token( $user ) ),
			$this->expectedObject(
				'registerCustomer.customer',
				[
					$this->expectedField( 'databaseId', $user->ID ),
					$this->expectedField( 'email', $customer_input['email'] ),
					$this->expectedField( 'username', $customer_input['username'] ),
					$this->expectedField( 'firstName', $customer_input['firstName'] ),
					$this->expectedField( 'lastName', $customer_input['lastName'] ),
					$this->expectedField( 'billing', array_merge( $this->empty_billing(), $customer_input['billing'] ) ),
					$this->expectedField( 'shipping', $this->empty_shipping() ),
				]
			),
			$this->expectedField( 'registerCustomer.viewer.userId', $user->ID ),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testRegisterMutationWithShippingSameAsBillingInfo() {
		/**
		 * Get customer input.
		 */
		$customer_input = $this->generateCustomerInput();

		/**
		 * Assertion One
		 *
		 * Tests mutation using "shippingSameAsBilling" field.
		 */
		$response = $this->executeRegisterCustomerMutation(
			array_merge(
				$customer_input,
				[
					'shipping'              => [],
					'shippingSameAsBilling' => true,
				]
			)
		);

		$user = get_user_by( 'email', $customer_input['email'] );
		$this->assertTrue( is_a( $user, WP_User::class ) );

		$expected = [
			$this->expectedField( 'registerCustomer.authToken', \WPGraphQL\JWT_Authentication\Auth::get_token( $user ) ),
			$this->expectedField( 'registerCustomer.refreshToken', \WPGraphQL\JWT_Authentication\Auth::get_refresh_token( $user ) ),
			$this->expectedObject(
				'registerCustomer.customer',
				[
					$this->expectedField( 'databaseId', $user->ID ),
					$this->expectedField( 'email', $customer_input['email'] ),
					$this->expectedField( 'username', $customer_input['username'] ),
					$this->expectedField( 'firstName', $customer_input['firstName'] ),
					$this->expectedField( 'lastName', $customer_input['lastName'] ),
					$this->expectedField( 'billing', array_merge( $this->empty_billing(), $customer_input['billing'] ) ),
					$this->expectedField(
						'shipping',
						array_merge(
							$this->empty_shipping(),
							array_intersect_key( $customer_input['billing'], $this->empty_shipping() )
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
		 * Get customer input.
		 */
		$customer_input = $this->generateCustomerInput();

		/**
		 * Assertion One
		 *
		 * Tests mutation with customer shipping information.
		 */
		$response = $this->executeRegisterCustomerMutation( $customer_input );

		$user = get_user_by( 'email', $customer_input['email'] );
		$this->assertTrue( is_a( $user, WP_User::class ) );

		$expected = [
			$this->expectedField( 'registerCustomer.authToken', \WPGraphQL\JWT_Authentication\Auth::get_token( $user ) ),
			$this->expectedField( 'registerCustomer.refreshToken', \WPGraphQL\JWT_Authentication\Auth::get_refresh_token( $user ) ),
			$this->expectedObject(
				'registerCustomer.customer',
				[
					$this->expectedField( 'databaseId', $user->ID ),
					$this->expectedField( 'email', $customer_input['email'] ),
					$this->expectedField( 'username', $customer_input['username'] ),
					$this->expectedField( 'firstName', $customer_input['firstName'] ),
					$this->expectedField( 'lastName', $customer_input['lastName'] ),
					$this->expectedField( 'billing', array_merge( $this->empty_billing(), $customer_input['billing'] ) ),
					$this->expectedField( 'shipping', array_merge( $this->empty_shipping(), $customer_input['shipping'] ) ),
				]
			),
			$this->expectedField( 'registerCustomer.viewer.userId', $user->ID ),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testUpdateMutation() {
		/**
		 * Get customer input.
		 */
		$initial_customer_input = $this->generateCustomerInput();

		/**
		 * Assertion One
		 *
		 * Tests mutation without a providing WooCommerce specific customer information.
		 */
		$response = $this->executeRegisterCustomerMutation( $initial_customer_input );

		$user = get_user_by( 'email', $initial_customer_input['email'] );
		$this->assertTrue( is_a( $user, WP_User::class ) );

		$expected = [
			$this->expectedObject(
				'registerCustomer.customer',
				[
					$this->expectedField( 'databaseId', $user->ID ),
					$this->expectedField( 'email', $initial_customer_input['email'] ),
					$this->expectedField( 'username', $initial_customer_input['username'] ),
					$this->expectedField( 'firstName', $initial_customer_input['firstName'] ),
					$this->expectedField( 'lastName', $initial_customer_input['lastName'] ),
					$this->expectedField( 'billing', array_merge( $this->empty_billing(), $initial_customer_input['billing'] ) ),
					$this->expectedField( 'shipping', array_merge( $this->empty_shipping(), $initial_customer_input['shipping'] ) ),
				]
			),
		];

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Get customer input.
		 */
		$customer_input = $this->generateCustomerInput();
		unset( $customer_input['username'] );
		$response       = $this->executeUpdateCustomerMutation(
			array_merge(
				$customer_input,
				[
					'id' => $this->toRelayId( 'customer', $user->ID ),
				]
			)
		);

		$expected = [
			$this->expectedObject(
				'updateCustomer.customer',
				[
					$this->expectedField( 'databaseId', $user->ID ),
					$this->expectedField( 'email', $customer_input['email'] ),
					$this->expectedField( 'username', $initial_customer_input['username'] ),
					$this->expectedField( 'firstName', $customer_input['firstName'] ),
					$this->expectedField( 'lastName', $customer_input['lastName'] ),
					$this->expectedField( 'billing', array_merge( $this->empty_billing(), $customer_input['billing'] ) ),
					$this->expectedField( 'shipping', array_merge( $this->empty_shipping(), $customer_input['shipping'] ) ),
				]
			),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testUpdateMutationWithoutID() {
		/**
		 * Get customer input.
		 */
		$initial_customer_input = $this->generateCustomerInput();

		/**
		 * Assertion One
		 *
		 * Tests mutation without a providing WooCommerce specific customer information.
		 */
		$response = $this->executeRegisterCustomerMutation( $initial_customer_input );

		$user = get_user_by( 'email', $initial_customer_input['email'] );
		$this->assertTrue( is_a( $user, WP_User::class ) );

		$expected = [
			$this->expectedObject(
				'registerCustomer.customer',
				[
					$this->expectedField( 'databaseId', $user->ID ),
					$this->expectedField( 'email', $initial_customer_input['email'] ),
					$this->expectedField( 'username', $initial_customer_input['username'] ),
					$this->expectedField( 'firstName', $initial_customer_input['firstName'] ),
					$this->expectedField( 'lastName', $initial_customer_input['lastName'] ),
					$this->expectedField( 'billing', array_merge( $this->empty_billing(), $initial_customer_input['billing'] ) ),
					$this->expectedField( 'shipping', array_merge( $this->empty_shipping(), $initial_customer_input['shipping'] ) ),
				]
			),
		];

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Get customer input.
		 */
		$this->loginAs( $user->ID );
		\WC()->initialize_session();
		$customer_input = $this->generateCustomerInput();
		unset( $customer_input['username'] );
		$response       = $this->executeUpdateCustomerMutation( $customer_input );

		$expected = [
			$this->expectedObject(
				'updateCustomer.customer',
				[
					$this->expectedField( 'databaseId', $user->ID ),
					$this->expectedField( 'email', $customer_input['email'] ),
					$this->expectedField( 'username', $initial_customer_input['username'] ),
					$this->expectedField( 'firstName', $customer_input['firstName'] ),
					$this->expectedField( 'lastName', $customer_input['lastName'] ),
					$this->expectedField( 'billing', array_merge( $this->empty_billing(), $customer_input['billing'] ) ),
					$this->expectedField( 'shipping', array_merge( $this->empty_shipping(), $customer_input['shipping'] ) ),
				]
			),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testUpdateMutationWithShippingSameAsBilling() {
		/**
		 * Get customer input.
		 */
		$customer_input = $this->generateCustomerInput();

		/**
		 * Assertion One
		 *
		 * Tests mutation without a providing WooCommerce specific customer information.
		 */
		$this->executeRegisterCustomerMutation(
			array_merge(
				$customer_input,
				[
					'shipping' => [],
				]
			)
		);

		$user = get_user_by( 'email', $customer_input['email'] );
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
					$this->expectedField( 'email', $customer_input['email'] ),
					$this->expectedField( 'username', $customer_input['username'] ),
					$this->expectedField( 'firstName', $customer_input['firstName'] ),
					$this->expectedField( 'lastName', $customer_input['lastName'] ),
					$this->expectedField( 'billing', array_merge( $this->empty_billing(), $customer_input['billing'] ) ),
					$this->expectedField(
						'shipping',
						array_merge(
							$this->empty_shipping(),
							array_intersect_key( $customer_input['billing'], $this->empty_shipping() )
						)
					),
				]
			),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testRegisterMutationWithoutAnyInfo() {
		/**
		 * Get customer input.
		 */
		$customer_input = $this->generateCustomerInput();

		/**
		 * Assertion One
		 *
		 * Tests mutation without a providing an username and password.
		 */
		$response = $this->executeRegisterCustomerMutation(
			[
				'email'     => $customer_input['email'],
				'firstName' => $customer_input['firstName'],
				'lastName'  => $customer_input['lastName'],
			]
		);

		$user = get_user_by( 'email', $customer_input['email'] );
		$this->assertTrue( is_a( $user, WP_User::class ) );

		$expected = [
			$this->expectedObject(
				'registerCustomer.customer',
				[
					$this->expectedField( 'databaseId', $user->ID ),
					$this->expectedField( 'email', $customer_input['email'] ),
					$this->expectedField( 'firstName', $customer_input['firstName'] ),
					$this->expectedField( 'lastName', $customer_input['lastName'] ),
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
		$this->loginAs( $user->ID );
		\WC()->initialize_session();
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
		$this->loginAs( 0 );
		\WC()->initialize_session();
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
