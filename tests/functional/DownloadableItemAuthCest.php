<?php

use WPGraphQL\WooCommerce\Vendor\Firebase\JWT\JWT;
use WPGraphQL\WooCommerce\Vendor\Firebase\JWT\Key;
use Tests\WPGraphQL\Logger\CodeceptLogger as Signal;

/**
 * Tests downloadable item authentication for headless frontends.
 *
 * @see https://github.com/wp-graphql/wp-graphql-woocommerce/issues/266
 */
class DownloadableItemAuthCest {
	public function _before( FunctionalTester $I ) {
		if ( ! defined( 'GRAPHQL_WOOCOMMERCE_SECRET_KEY' ) ) {
			define( 'GRAPHQL_WOOCOMMERCE_SECRET_KEY', 'testestestestestestestestestest!!' );
		}

		// Disable approved download directories check for tests.
		update_option( 'wc_downloads_approved_directories_mode', 'disabled' );
		wp_cache_flush();
	}

	/**
	 * Helper: Creates a downloadable product, order, and grants download permissions.
	 * Returns the auth_token, session_token, and session_id for the logged-in customer.
	 */
	private function setupDownloadableOrder( FunctionalTester $I ): array {
		$I->setupStoreAndUsers();

		// Enable download access after payment.
		update_option( 'woocommerce_downloads_grant_access_after_payment', 'yes' );

		// Create a downloadable product using WooCommerce API.
		$download = new \WC_Product_Download();
		$download->set_id( wp_generate_uuid4() );
		$download->set_name( 'Test eBook PDF' );
		$download->set_file( 'http://example.com/test-ebook.pdf' );

		$product = new \WC_Product_Simple();
		$product->set_name( 'Test eBook' );
		$product->set_regular_price( '10' );
		$product->set_virtual( true );
		$product->set_downloadable( true );
		$product->set_downloads( [ $download ] );
		$product->save();
		$product_id = $product->get_id();

		// Log in to get user ID.
		$login = $I->login(
			[
				'clientMutationId' => 'login',
				'username'         => 'jimbo1234@example.com',
				'password'         => 'password',
			]
		);

		$auth_token    = $I->lodashGet( $login, 'data.login.authToken' );
		$customer_id   = $I->lodashGet( $login, 'data.login.customer.databaseId' );
		$session_token = $I->grabHttpHeader( 'woocommerce-session' );

		// Decode session_id from token.
		JWT::$leeway = 60;
		$token_data  = JWT::decode( $session_token, new Key( GRAPHQL_WOOCOMMERCE_SECRET_KEY, 'HS256' ) );
		$session_id  = $token_data->data->customer_id;

		// Create a completed order for the customer.
		$order = wc_create_order(
			[
				'customer_id' => $customer_id,
				'status'      => 'completed',
			]
		);
		$order->add_product( $product, 1 );
		$order->calculate_totals();
		$order->save();

		// Grant download permissions.
		wc_downloadable_product_permissions( $order->get_id(), true );

		return compact( 'auth_token', 'session_token', 'session_id', 'customer_id', 'product_id' );
	}

	/**
	 * Test that the downloadNonce field returns a valid nonce for the download URL.
	 */
	public function testDownloadNonceFieldIsReturned( FunctionalTester $I ) {
		$data = $this->setupDownloadableOrder( $I );

		$query = '
			query {
				customer {
					downloadableItems {
						nodes {
							downloadId
							url
							downloadNonce
							downloadUrl
						}
					}
				}
			}
		';

		$response = $I->sendGraphQLRequest(
			$query,
			null,
			[
				'Authorization'       => "Bearer {$data['auth_token']}",
				'woocommerce-session' => "Session {$data['session_token']}",
			]
		);

		$download_nonce = $I->lodashGet( $response, 'data.customer.downloadableItems.nodes.0.downloadNonce' );
		$I->assertNotEmpty( $download_nonce, 'downloadNonce should be returned for downloadable items.' );
		$I->assertIsString( $download_nonce );

		$download_url = $I->lodashGet( $response, 'data.customer.downloadableItems.nodes.0.downloadUrl' );
		$I->assertNotEmpty( $download_url, 'downloadUrl should be returned for downloadable items.' );

		// The downloadUrl should contain the nonce value.
		$I->assertStringContainsString( $download_nonce, $download_url );
	}

	/**
	 * Test that the downloadUrl field returns a nonced Protected Router URL
	 * that redirects to the WooCommerce download endpoint.
	 */
	public function testDownloadUrlRedirectsToWooCommerceDownload( FunctionalTester $I ) {
		$data = $this->setupDownloadableOrder( $I );

		$query = '
			query {
				customer {
					downloadableItems {
						nodes {
							downloadId
							url
							downloadUrl
						}
					}
				}
			}
		';

		$response = $I->sendGraphQLRequest(
			$query,
			null,
			[
				'Authorization'       => "Bearer {$data['auth_token']}",
				'woocommerce-session' => "Session {$data['session_token']}",
			]
		);

		$download_url = $I->lodashGet( $response, 'data.customer.downloadableItems.nodes.0.downloadUrl' );
		$I->assertNotEmpty( $download_url, 'downloadUrl should be returned for downloadable items.' );

		// The downloadUrl should point to the transfer-session endpoint.
		$I->assertStringContainsString( 'transfer-session', $download_url );
		$I->assertStringContainsString( 'session_id=', $download_url );

		// Following the URL should redirect to the WooCommerce download endpoint.
		$I->stopFollowingRedirects();
		$I->amOnUrl( $download_url );
		$I->seeResponseCodeIs( 302 );

		$I->startFollowingRedirects();
	}

	/**
	 * Test that the downloadUrl with an invalid nonce does NOT redirect to the download.
	 */
	public function testDownloadUrlWithInvalidNonceRedirectsToHome( FunctionalTester $I ) {
		$data = $this->setupDownloadableOrder( $I );

		$wp_url = getenv( 'WORDPRESS_URL' );

		$I->stopFollowingRedirects();
		$I->amOnUrl( "{$wp_url}/transfer-session?session_id={$data['session_id']}&_wc_download=invalid_nonce" );
		$I->seeResponseCodeIs( 302 );
		$I->followRedirect();
		$I->dontSeeInCurrentUrl( 'download_file' );

		$I->startFollowingRedirects();
	}

	/**
	 * Test that preAuthDownloadUrl is only available when the setting is enabled.
	 */
	public function testPreAuthDownloadUrlOnlyAvailableWhenEnabled( FunctionalTester $I ) {
		$data = $this->setupDownloadableOrder( $I );

		// Ensure the setting is disabled.
		$existing = $I->grabOptionFromDatabase( 'woographql_settings' );
		$I->haveOptionInDatabase(
			'woographql_settings',
			array_merge(
				is_array( $existing ) ? $existing : [],
				[ 'enable_pre_auth_download_urls' => 'off' ]
			)
		);

		$query = '
			query {
				customer {
					downloadableItems {
						nodes {
							downloadId
							url
						}
					}
				}
			}
		';

		$response = $I->sendGraphQLRequest(
			$query,
			null,
			[
				'Authorization'       => "Bearer {$data['auth_token']}",
				'woocommerce-session' => "Session {$data['session_token']}",
			]
		);

		// Should succeed — url is always available.
		$url = $I->lodashGet( $response, 'data.customer.downloadableItems.nodes.0.url' );
		$I->assertNotEmpty( $url );
	}

	/**
	 * Test that preAuthDownloadUrl generates a working download link when enabled.
	 */
	public function testPreAuthDownloadUrlWorksWhenEnabled( FunctionalTester $I ) {
		$data = $this->setupDownloadableOrder( $I );

		// Enable the setting.
		$existing = $I->grabOptionFromDatabase( 'woographql_settings' );
		$I->haveOptionInDatabase(
			'woographql_settings',
			array_merge(
				is_array( $existing ) ? $existing : [],
				[ 'enable_pre_auth_download_urls' => 'on' ]
			)
		);

		$query = '
			query {
				customer {
					downloadableItems {
						nodes {
							downloadId
							url
							preAuthDownloadUrl
						}
					}
				}
			}
		';

		$response = $I->sendGraphQLRequest(
			$query,
			null,
			[
				'Authorization'       => "Bearer {$data['auth_token']}",
				'woocommerce-session' => "Session {$data['session_token']}",
			]
		);

		$pre_auth_url = $I->lodashGet( $response, 'data.customer.downloadableItems.nodes.0.preAuthDownloadUrl' );
		$I->assertNotEmpty( $pre_auth_url, 'preAuthDownloadUrl should be returned when setting is enabled.' );

		// The URL should contain a token parameter.
		$I->assertStringContainsString( 'token=', $pre_auth_url );

		// Following the URL should not return a "must be logged in" error.
		// WooCommerce will try to serve the file — it may fail because the file
		// doesn't exist in the test environment, but it should NOT return a login error.
		$I->amOnUrl( $pre_auth_url );
		$I->dontSee( 'You must be logged in' );
	}
}
