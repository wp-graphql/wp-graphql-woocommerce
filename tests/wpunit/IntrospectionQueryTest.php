<?php

class IntrospectionQueryTest extends \Codeception\TestCase\WPTestCase {

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
			$request = new \WPGraphQL\Request();
			$request->schema->assertValid();

			// Assert true upon success.
			$this->assertTrue( true );
		} catch ( \GraphQL\Error\InvariantViolation $e ) {
			// use --debug flag to view.
			codecept_debug( $e->getMessage() );

			// Fail upon throwing
			$this->assertTrue( false );
		}
	}

	// Test introspection query.
	public function testIntrospectionQuery() {
		$query   = \GraphQL\Type\Introspection::getIntrospectionQuery();
		$results = graphql( [ 'query' => $query ] );

		$this->assertArrayNotHasKey( 'errors', $results );
	}
}
