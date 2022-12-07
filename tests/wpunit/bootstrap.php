<?php

// Turn off "QL_SESSION_HANDLER" for unit tests.
//define( 'NO_QL_SESSION_HANDLER', true );

/**
 * Remove the "extensions" payload from GraphQL results
 * so that tests can make assertions without worrying about what's in the extensions payload
 */
add_filter(
	'graphql_request_results',
	function( $response ) {
		unset( $response['extensions'] );

		return $response;
	},
	99
);
