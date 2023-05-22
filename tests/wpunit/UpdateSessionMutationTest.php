<?php

class UpdateSessionMutationTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {

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
			$this->expectedField( 'updateSession.customer.id', $this->toRelayId( 'customer', $registered ) ),
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
}
