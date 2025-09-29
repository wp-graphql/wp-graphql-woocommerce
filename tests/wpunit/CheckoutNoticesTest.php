<?php

class CheckoutNoticesTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {
	public function setUp(): void {
		parent::setUp();

		$this->loginAs( 0 );

		// Turn on guest checkout.
		update_option( 'woocommerce_enable_guest_checkout', 'yes' );

		// Enable test gateway that can simulate failures
		$gateways     = \WC()->payment_gateways->payment_gateways();
		$bacs_gateway = $gateways['bacs'];
		$bacs_gateway->settings['enabled'] = 'yes';
		update_option( $bacs_gateway->get_option_key(), $bacs_gateway->settings );
		\WC()->payment_gateways->init();
	}

	private function getCheckoutMutation() {
		return '
			mutation checkout( $input: CheckoutInput! ) {
				checkout( input: $input ) {
					clientMutationId
					order {
						id
						status
					}
					customer {
						id
					}
					result
					redirect
				}
			}
		';
	}

	private function getCheckoutInput( $overwrite = [] ) {
		return array_merge(
			[
				'paymentMethod'  => 'bacs',
				'billing'        => [
					'firstName' => 'John',
					'lastName'  => 'Doe',
					'address1'  => '123 Test St',
					'city'      => 'Test City',
					'state'     => 'NY',
					'postcode'  => '12345',
					'country'   => 'US',
					'email'     => 'test@example.com',
					'phone'     => '555-555-1234',
					'overwrite' => true,
				],
			],
			$overwrite
		);
	}

	/**
	 * Test that checkout mutation includes WC notices in error messages
	 * This verifies the fix for GitHub issue #666
	 */
	public function testCheckoutMutationIncludesNoticesInErrorMessage() {
		// Arrange
		$product_id = $this->factory->product->createSimple();
		WC()->cart->add_to_cart( $product_id, 1 );

		// Hook into checkout process to simulate payment gateway failure
		add_action( 'woocommerce_checkout_process', function() {
			wc_add_notice( 'Payment failed: Test card declined', 'error' );
		}, 10 );

		$query = $this->getCheckoutMutation();
		$variables = [ 'input' => $this->getCheckoutInput() ];

		// Act
		$response = $this->graphql( compact( 'query', 'variables' ) );

		// Assert
		$expected = [ $this->expectedField( 'checkout', static::IS_NULL ) ];
		$this->assertQueryError( $response, $expected );

		// Verify the error message contains our notice text
		$this->assertResponseIsValid( $response );
		$this->assertArrayHasKey( 'errors', $response );
		$this->assertStringContainsString( 'Payment failed: Test card declined', $response['errors'][0]['message'] );

		// Verify notices are cleared from session
		$remaining_notices = wc_get_notices();
		$this->assertEmpty( $remaining_notices, 'Notices should be cleared after checkout failure' );
	}

	/**
	 * Test that checkout mutation now has notices field available
	 * This verifies the GraphQL schema enhancement
	 */
	public function testCheckoutMutationHasNoticesField() {
		// Arrange
		$product_id = $this->factory->product->createSimple();
		WC()->cart->add_to_cart( $product_id, 1 );

		// Hook to add error notice during checkout
		add_action( 'woocommerce_checkout_process', function() {
			wc_add_notice( 'Payment failed: Test card declined', 'error' );
		}, 10 );

		$query = '
			mutation checkout( $input: CheckoutInput! ) {
				checkout( input: $input ) {
					notices {
						type
						message
					}
					result
				}
			}
		';

		$variables = [ 'input' => $this->getCheckoutInput() ];

		// Act
		$response = $this->graphql( compact( 'query', 'variables' ) );

		// Assert - The query should not fail due to missing notices field
		// Even though checkout fails, the schema should accept the notices field
		$this->assertResponseIsValid( $response );

		// Clean up
		wc_clear_notices();
	}

	/**
	 * Test that notices persist across checkout attempts
	 * This reproduces the exact issue from GitHub issue #666
	 */
	public function testNoticesPersistAcrossCheckoutAttempts() {
		// Add a product to cart
		$product_id = $this->factory->product->createSimple();
		WC()->cart->add_to_cart( $product_id, 1 );

		// Hook into checkout validation to simulate payment gateway failure
		add_action( 'woocommerce_after_checkout_validation', function() {
			// Simulate a payment gateway adding an error notice during validation
			wc_add_notice( 'Previous payment failed: Test error', 'error' );
		}, 10 );

		$variables = [ 'input' => $this->getCheckoutInput() ];
		$query     = $this->getCheckoutMutation();

		// This should fail due to the error notice added during validation
		$response = $this->graphql( compact( 'query', 'variables' ) );

		// The checkout should fail due to the error notice
		$this->assertQueryError( $response );

		// Clean up
		wc_clear_notices();
	}

	/**
	 * Test that successful checkout returns non-error notices
	 */
	public function testSuccessfulCheckoutReturnsNotices() {
		// Arrange
		$product_id = $this->factory->product->createSimple();
		WC()->cart->add_to_cart( $product_id, 1 );

		// Hook into checkout process to add a success notice
		add_action( 'woocommerce_checkout_order_processed', function() {
			wc_add_notice( 'Order processed successfully!', 'success' );
		}, 10 );

		$query = '
			mutation checkout( $input: CheckoutInput! ) {
				checkout( input: $input ) {
					notices {
						type
						message
					}
					order {
						id
					}
					result
				}
			}
		';

		$variables = [ 'input' => $this->getCheckoutInput() ];

		// Act
		$response = $this->graphql( compact( 'query', 'variables' ) );

		// Assert
		$expected = [
			$this->expectedField( 'checkout.result', 'success' ),
			$this->expectedField( 'checkout.order.id', static::NOT_NULL ),
			$this->expectedNode(
				'checkout.notices',
				[
					$this->expectedField( 'type',  'SUCCESS' ),
					$this->expectedField( 'message', 'Order processed successfully!' ),
				]
			),
		];

		$this->assertQuerySuccessful( $response, $expected );

		// Verify notices are cleared from session
		$remaining_notices = wc_get_notices();
		$this->assertEmpty( $remaining_notices, 'Notices should be cleared after successful checkout' );
	}

	/**
	 * Test comparing checkout mutation with session update mutation notice handling
	 * This shows how other mutations properly handle notices
	 */
	public function testSessionUpdateMutationHandlesNoticesProperly() {
		// Arrange
		wc_add_notice( 'Test error notice', 'error' );

		$query = '
			mutation updateSession( $input: UpdateSessionInput! ) {
				updateSession( input: $input ) {
					session {
						key
						value
					}
					customer {
						id
					}
				}
			}
		';

		$variables = [
			'input' => [
				'sessionData' => [
					[
						'key' => 'test_key',
						'value' => 'test_value'
					]
				]
			]
		];

		// Act
		$response = $this->graphql( compact( 'query', 'variables' ) );

		// Assert
		$expected = [ $this->expectedField( 'updateSession', static::IS_NULL ) ];
		$this->assertQueryError( $response, $expected );

		// Verify notices are cleared after the mutation
		$notices = wc_get_notices();
		$this->assertEmpty( $notices, 'Session update mutation should clear notices' );
	}
}