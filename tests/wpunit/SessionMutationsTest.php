<?php

class SessionMutationsTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {
	public function testUpdateSessionMutation() {
		// Create registered customer.
		$registered = $this->factory->customer->create();
		$this->loginAs( $registered );

		// Create query.
		$query = '
            mutation($input: UpdateSessionInput!) {
                updateSession(input: $input) {
                    session {
                        id
                        key
                        value
                    }
                    customer {
                        id
                        session {
                            id
                            key
                            value
                        }
                    }
                }
            }
        ';

		$variables = [
			'input' => [
				'sessionData' => [
					[
						'key'   => 'test-2',
						'value' => 'test-value',
					],
				],
			],
		];

		/**
		 * Assert working.
		 */
		$response = $this->graphql( compact( 'query', 'variables' ) );
		$expected = [
			$this->expectedObject(
				'updateSession.session.#',
				[
					$this->expectedField( 'key', 'test-2' ),
					$this->expectedField( 'value', 'test-value' ),
				]
			),
			$this->expectedField( 'updateSession.customer.id', $this->toRelayId( 'user', $registered ) ),
			$this->expectedObject(
				'updateSession.customer.session.#',
				[
					$this->expectedField( 'key', 'test-2' ),
					$this->expectedField( 'value', 'test-value' ),
				]
			),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testForgetSessionMutation() {
		$this->markTestSkipped( 'This test has not been implemented yet.' );

		// Create registered customer.
		$registered = $this->factory->customer->create();
		$this->loginAs( $registered );

		// Add products to cart.
		$this->factory->cart->add(
			[
				'product_id' => $this->factory->product->createSimple(),
				'quantity'   => 2,
			],
			[
				'product_id' => $this->factory->product->createSimple(),
				'quantity'   => 1,
			]
		);

		// Save session.
		\WC()->session->save_data();

		// Reinitialize session.
		\WC()->session->init();

		// Confirm cart has items.
		$cart_query = '
			query {
				cart {
					contents {
						nodes {
							key
						}
					}
				}
			}
		';

		$response = $this->graphql( [ 'query' => $cart_query ] );
		$this->assertQuerySuccessful(
			$response,
			[ $this->expectedField( 'cart.contents.nodes', static::NOT_FALSY ) ]
		);

		// Forget session.
		$query = 'mutation {
			forgetSession(input: {}) {
				session {
					id
					key
					value
				}
			}
		}';

		$response = $this->graphql( compact( 'query' ) );
		$this->assertQuerySuccessful( $response );

		// Reinitialize session.
		\WC()->session->init();

		// Confirm cart is empty.
		$response = $this->graphql( [ 'query' => $cart_query ] );
		$this->assertQuerySuccessful(
			$response,
			[ $this->expectedField( 'cart.contents.nodes', static::IS_FALSY ) ]
		);
	}
}