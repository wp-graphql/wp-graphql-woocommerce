<?php

class IntrospectionQueryTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {
	public function setUp(): void {
		// before
		parent::setUp();

		$settings = get_option( 'graphql_general_settings' );
		if ( ! $settings ) {
			$settings = [];
		}
		$settings['public_introspection_enabled'] = 'on';
		update_option( 'graphql_general_settings', $settings );
		\WPGraphQL::clear_schema();
	}

	// Validate schema.
	public function testSchema() {
		try {
			new \WPGraphQL\Request();

			$schema = WPGraphQL::get_schema();
			$schema->assertValid();

			// Assert true upon success.
			$this->assertTrue( true, 'Schema is valid.' );
		} catch ( \GraphQL\Error\InvariantViolation $e ) {
			// use --debug flag to view.
			$this->logData( $e->getMessage() );

			// Fail upon throwing
			$this->assertTrue( false, $e->getMessage() );
		}
	}

	// Test introspection query.
	public function testIntrospectionQuery() {
		$query   = \GraphQL\Type\Introspection::getIntrospectionQuery();
		$results = graphql( [ 'query' => $query ] );

		$this->assertArrayNotHasKey( 'errors', $results );
	}
}
