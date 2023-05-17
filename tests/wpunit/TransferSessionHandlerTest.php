<?php

use function WPGraphQL\WooCommerce\get_includes_directory;

class TransferSessionHandlerTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {

	public function testGenerateCustomerId() {
		require_once get_includes_directory() . 'utils/class-transfer-session-handler.php';

		$session = new \WPGraphQL\WooCommerce\Utils\Transfer_Session_Handler();

		$_REQUEST['_wc_cart']   = 'test';
		$_REQUEST['session_id'] = 'test-session-id';

		$session->init_session_cookie();
		$this->assertEquals( 'test-session-id', $session->get_customer_id() );
	}
}
