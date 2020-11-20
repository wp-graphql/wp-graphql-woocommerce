<?php

// Turn off "QL_SESSION_HANDLER" for unit tests.
define( 'NO_QL_SESSION_HANDLER', true );

add_filter(
	'graphql_request_results',
	function( $response ) {
		unset( $response['extensions'] );

		return $response;
	}
);
