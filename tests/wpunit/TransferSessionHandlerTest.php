<?php

use function WPGraphQL\WooCommerce\get_includes_directory;
use WPGraphQL\WooCommerce\Utils\Transfer_Session_Handler;
class TransferSessionHandlerTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {
	/**
	 * Session handler instance.
	 *
	 * @var Transfer_Session_Handler
	 */
	private $session;

	public function setUp(): void {
		// before
		parent::setUp();

		require_once get_includes_directory() . 'utils/class-transfer-session-handler.php';

		$this->session = new Transfer_Session_Handler();
	}

	public function testGenerateCustomerId() {
		// Assert random customer ID is generated when invalid creds are provided.
		$this->session->init_session_cookie();
		$this->assertNotEquals( 'test-session-id', $this->session->get_customer_id() );

		$_REQUEST['_wc_cart']   = 'test';
		$this->session->init_session_cookie();
		$this->assertNotEquals( 'test-session-id', $this->session->get_customer_id() );
		
		unset( $_REQUEST['_wc_cart'] );
		$_REQUEST['session_id'] = 'test-session-id';
		$this->session->init_session_cookie();
		$this->assertNotEquals( 'test-session-id', $this->session->get_customer_id() );

		// Assert "session_id" is returned, if proper creds are provided.
		$_REQUEST['_wc_cart']   = 'test';
		$_REQUEST['session_id'] = 'test-session-id';
		$this->session->init_session_cookie();
		$this->assertEquals( 'test-session-id', $this->session->get_customer_id() );
	}

	public function testGetClientSessionId() {
		// Assert an empty string is return, when invalid creds are provided.
		$this->session->init_session_cookie();
		$this->assertEquals( '', $this->session->get_client_session_id());

		$this->session->init_session_cookie();
		$_REQUEST['_wc_cart']   = 'test';
		$this->assertEquals( '', $this->session->get_client_session_id() );

		$this->session->init_session_cookie();
		unset( $_REQUEST['_wc_cart'] );
		$_REQUEST['session_id'] = $this->session->get_client_session_id();
		$this->assertEquals( '', $this->session->get_client_session_id() );

		// Assert "test-client-session-id" is returned, if proper creds are provided.
		$this->session->init_session_cookie();
		$this->session->set( 'client_session_id', 'test-client-session-id' );
		$this->session->set( 'client_session_id_expiration', ( time() + 3600 ) );
		$_REQUEST['_wc_cart']   = 'test';
		$_REQUEST['session_id'] = $this->session->get_customer_id();
		$this->assertEquals( 'test-client-session-id', $this->session->get_client_session_id() );

		// Assert an empty string is returned, because "client_session_id_expiration" is expired.
		$this->session->init_session_cookie();
		$this->session->set( 'client_session_id', 'test-client-session-id-2' );
		$this->session->set( 'client_session_id_expiration', '1' );
		$_REQUEST['_wc_cart']   = 'test';
		$_REQUEST['session_id'] = $this->session->get_customer_id();
		$this->assertEquals( '', $this->session->get_client_session_id() );
	}
}
