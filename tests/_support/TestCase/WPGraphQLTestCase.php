<?php
/**
 * WPGraphQL test case
 *
 * For testing WPGraphQL responses.
 * @since 0.6.0
 * @package Tests\WPGraphQL\TestCase
 */
namespace Tests\WPGraphQL\TestCase;

class WPGraphQLTestCase extends \Codeception\TestCase\WPTestCase {

	/**
	 * Wrapper for the "graphql()" function.
	 *
	 * @return array
	 */
	public function graphql() {
		$results = graphql( ...func_get_args() );

		// use --debug flag to view.
		$this->log_data( $results );

		return $results;
	}

	/**
	 * Wrapper for the "GraphQLRelay\Relay::toGlobalId()" function.
	 *
	 * @return string
	 */
	public function toRelayId() {
		return \GraphQLRelay\Relay::toGlobalId( ...func_get_args() );
	}

	/**
	 * Console logging function.
	 *
	 * Use --debug flag to view in console.
	 */
	public function log_data( $data ) {
		if ( is_array( $data) || is_object( $data ) ) {
			\codecept_debug( json_encode( $data, JSON_PRETTY_PRINT ) );
			return;
		}

		\codecept_debug( $data );
	}

	/**
	 * Returns an expected "Object" type data object.
	 *
	 * @param string     $path            Path to the data being tested.
	 * @param mixed|null $expected_value  Expected value of the object being evaluted.
	 * @return array
	 */
	public function expectedObject( string $path, $expected_value ) {
		$type = 'OBJECT';
		return compact( 'type', 'path', 'expected_value' );
	}

	/**
	 * Returns an expected "Node" type data object.
	 *
	 * @param string       $path            Path to the data being tested.
	 * @param mixed|null   $expected_value  Expected value of the node being evaluted.
	 * @param integer|null $expected_index  Expected index of the node being evaluted.
	 * @return array
	 */
	public function expectedNode( string $path, $expected_value = null, $expected_index = null ) {
		$type = 'NODE';
		return compact( 'type', 'path', 'expected_value', 'expected_index' );
	}

	/**
	 * Returns an expected "Edge" type data object.
	 *
	 * @param string       $path            Path to the data being tested.
	 * @param mixed|null   $expected_value  Expected value of the edge being evaluted.
	 * @param integer|null $expected_index  Expected index of the edge being evaluted.
	 * @return array
	 */
	public function expectedEdge( string $path, $expected_value = null, $expected_index = null ) {
		$type = 'EDGE';
		return compact( 'type', 'path', 'expected_value', 'expected_index' );
	}

	/**
	 * Assert the valid of a GraphQL response object.
	 *
	 * @param array $response  GraphQL query response object.
	 */
	public function assertIsValidQueryResponse( $response ) {
		$this->assertIsArray( $response, 'The GraphQL query response must be provided as an associative array.' );
		$this->assertNotEmpty( $response, 'GraphQL query response is empty.' );
		$this->assertTrue(
			in_array( 'data', array_keys( $response ), true ) || in_array( 'errors', array_keys( $response ), true ),
			'A valid GraphQL query response must contain a "data" or "errors" object.'
		);
	}

	/**
	 * Assert that expected data object
	 *
	 * @param array $response       GraphQL query response object
	 * @param array $expected_data  Expected data object to be evaluated.
	 */
	public function assertExpectedDataFound( array $response, array $expected_data ) {
		// Throw if "$expected_data" invalid.
		if ( empty( $expected_data['type'] ) ) {
			\codecept_debug( array( 'INVALID_DATA_OBJECT' => $expected_data ) );
			throw new \Exception( 'Invalid data object provided for evaluation.' );
		}

		// Deconstruct $expected_data.
		extract( $expected_data );

		// Get flags.
		$check_order = isset( $expected_index ) && ! is_null( $expected_index );

		$actual_path = $check_order ? "data.{$path}.{$expected_index}" : "data.$path";
		$actual_data = $this->lodashGet( $response, $actual_path );

		// Only check data existence, if no "$expected_value" provided.
		if ( is_null( $expected_value ) ) {
			$this->assertNotNull( $actual_data, sprintf( 'No data found at path "%s"', $actual_path ) );
			if ( is_array( $actual_data ) ) {
				$this->assertNotEmpty( $actual_data, sprintf( 'Data object found at path "%s" empty.', $actual_path ) );
			}
			return;
		}

		// Evaluate expected data.
		switch( $type ) {
			case 'OBJECT':
				// Log assertion.
				$actual_log_type = is_array( $actual_data ) ? 'ACTUAL_DATA_OBJECT' : 'ACTUAL_DATA';
				$assertion_log = array(
					'EXPECTED_VALUE' => $expected_value,
					$actual_log_type => $actual_data,
				);
				$this->log_data( $assertion_log );

				// Execute assertion.
				$this->assertSame(
					$expected_value,
					$actual_data,
					sprintf( 'Data found at path "%s" doesn\'t match the provided value', $actual_path )
				);
				break;
			case 'NODE':
			case 'EDGE':
				if ( $check_order ) {
					// Log assertion.
					$actual_log_type = is_array( $actual_data ) ? 'ACTUAL_DATA_OBJECT' : 'ACTUAL_DATA';
					$assertion_log = array(
						"EXPECTED_{$type}_VALUE"   => $expected_value,
						$actual_log_type           => $actual_data,
					);
					$this->log_data( $assertion_log );

					// Execute assertion
					$this->assertSame(
						$expected_value,
						$actual_data,
						sprintf( 'Data found at path "%s" doesn\'t match the provided value', $actual_path )
					);
					break;
				}
				// Log data objects before the coming assertion.
				$assertion_log = array(
					"NEEDLE_NODE"    => $expected_value,
					"HAYSTACK_NODES" => $actual_data,
				);
				$this->log_data( $assertion_log );

				foreach ( $actual_data as $actual_node ) {
					// If match found, Assert true.
					if ( $expected_value === $actual_node ) {
						$this->assertTrue( true );
						break 2;
					}
				}
				$this->assertTrue(
					false,
					sprintf( 'Expected data not found in the %1$s list at path "%2$s"', strtolower( $type ), $actual_path )
				);
				break;
			default:
				throw new \Exception( 'Invalid data object provided for evaluation.' );
		}
	}

	/**
	 * Assert where a query was successful, by evaluating the query response
	 * against an provided list of expected data objects.
	 *
	 * @param array $response  GraphQL query response.
	 * @param array $expected  List of expected data objects.
	 */
	public function assertQuerySuccessful( array $response, array $expected ) {
		$this->assertIsValidQueryResponse( $response );
		foreach( $expected as $expected_data ) {
			$this->assertExpectedDataFound( $response, $expected_data );
		}
	}

	public function assertQueryFailure( $response, $expected = array() ) {

	}

	/**
	 * The value returned for undefined resolved values.
	 *
	 * Clone of the "get" function from the Lodash JS libra
	 *
	 * @param array  $object   The object to query.
	 * @param string $path     The path of the property to get.
	 * @param mixed  $default  The value returned for undefined resolved values.
	 * @return void
	 */
	protected function lodashGet( array $data, string $string, $default = null ) {
        $arrStr = explode( '.', $string );
        if ( ! is_array( $arrStr ) ) {
			$arrStr = [ $arrStr ];
		}

        $result = $data;
        foreach ( $arrStr as $lvl ) {
			if ( ! is_null( $lvl ) && isset( $result[ $lvl ] ) ) {
				$result = $result[ $lvl ];
			} else {
				$result = $default;
			}
		}

        return $result;
	}
}
