<?php

class PaymentMethodMutationsTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {

	// Tests
	public function testSetDefaultPaymentMethodMutation() {
		// Create customer.
		$customer_id = $this->factory->customer->create();

		// Create tokens.
		$expiry_month = gmdate( 's', strtotime( 'now' ) );
		$expiry_year  = gmdate( 'Y', strtotime( '+1 year' ) );
		$token_cc     = $this->factory->payment_token->createCCToken(
			$customer_id,
			[
				'last4'        => 1234,
				'expiry_month' => $expiry_month,
				'expiry_year'  => $expiry_year,
				'card_type'    => 'visa',
				'token'        => time(),
			]
		);
		$token_ec     = $this->factory->payment_token->createECheckToken(
			$customer_id,
			[
				'last4' => 4567,
				'token' => time(),
			]
		);

		// Create query and variables.
		$query     = '
			mutation($tokenId: Int!) {
				setDefaultPaymentMethod(input: { tokenId: $tokenId }) {
					status
                    token {
                        id
                        isDefault
                    }
				}
			}
		';
		$variables = [ 'tokenId' => $token_ec->get_id() ];

		/**
		 * Assert default payment method can't be set by guests or admin.
		 */

		$response = $this->graphql( compact( 'query', 'variables' ) );
		$expected = [ $this->expectedField( 'setDefaultPaymentMethod', self::IS_NULL ) ];

		$this->assertQueryError( $response, $expected );

		// Again, as admin.
		$this->loginAsShopManager();
		$response = $this->graphql( compact( 'query', 'variables' ) );
		$expected = [ $this->expectedField( 'setDefaultPaymentMethod', self::IS_NULL ) ];

		$this->assertQueryError( $response, $expected );

		/**
		 * Assert customer can set default payment method
		 */
		$this->loginAs( $customer_id );
		$response = $this->graphql( compact( 'query', 'variables' ) );
		$expected = [
			$this->expectedField( 'setDefaultPaymentMethod.status', 'SUCCESS' ),
			$this->expectedObject(
				'setDefaultPaymentMethod.token',
				[
					$this->expectedField( 'id', $this->toRelayId( 'token', $token_ec->get_id() ) ),
					$this->expectedField( 'isDefault', true ),
				]
			),
		];

		$this->assertQuerySuccessful( $response, $expected );

		// Change default payment method back to credit card token.
		$variables = [ 'tokenId' => $token_cc->get_id() ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedField( 'setDefaultPaymentMethod.status', 'SUCCESS' ),
			$this->expectedObject(
				'setDefaultPaymentMethod.token',
				[
					$this->expectedField( 'id', $this->toRelayId( 'token', $token_cc->get_id() ) ),
					$this->expectedField( 'isDefault', true ),
				]
			),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testDeletePaymentMethodMutation() {
		// Create customer.
		$customer_id = $this->factory->customer->create();

		// Create tokens.
		$expiry_month = gmdate( 's', strtotime( 'now' ) );
		$expiry_year  = gmdate( 'Y', strtotime( '+1 year' ) );
		$token_cc     = $this->factory->payment_token->createCCToken(
			$customer_id,
			[
				'last4'        => 1234,
				'expiry_month' => $expiry_month,
				'expiry_year'  => $expiry_year,
				'card_type'    => 'visa',
				'token'        => time(),
			]
		);
		$token_ec     = $this->factory->payment_token->createECheckToken(
			$customer_id,
			[
				'last4' => 4567,
				'token' => time(),
			]
		);

		// Create query and variables.
		$query     = '
			mutation($tokenId: Int!) {
				deletePaymentMethod(input: { tokenId: $tokenId }) {
					status
				}
			}
		';
		$variables = [ 'tokenId' => $token_cc->get_id() ];

		/**
		 * Assert payment method can't be deleted by guests or admin.
		 */

		$response = $this->graphql( compact( 'query', 'variables' ) );
		$expected = [ $this->expectedField( 'deletePaymentMethod', self::IS_NULL ) ];

		$this->assertQueryError( $response, $expected );

		// Again, as admin.
		$this->loginAsShopManager();
		$response = $this->graphql( compact( 'query', 'variables' ) );
		$expected = [ $this->expectedField( 'deletePaymentMethod', self::IS_NULL ) ];

		$this->assertQueryError( $response, $expected );

		/**
		 * Assert customer can delete payment method
		 */
		$this->loginAs( $customer_id );
		$response = $this->graphql( compact( 'query', 'variables' ) );
		$expected = [ $this->expectedField( 'deletePaymentMethod.status', 'SUCCESS' ) ];

		$this->assertQuerySuccessful( $response, $expected );
	}
}
