<?php

class WCSettingsMutationsTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {
	public function testUpdateWCSettingText() {
		$this->loginAsShopManager();

		$original = get_option( 'woocommerce_store_address', '' );

		$query = '
			mutation ($input: UpdateWCSettingInput!) {
				updateWCSetting(input: $input) {
					setting {
						id
						... on WCStringSetting {
							value
						}
					}
				}
			}
		';

		$variables = [
			'input' => [
				'group' => 'general',
				'id'    => 'woocommerce_store_address',
				'value' => '123 Test Street',
			],
		];

		$response = $this->graphql( compact( 'query', 'variables' ) );

		$this->assertQuerySuccessful(
			$response,
			[
				$this->expectedField( 'updateWCSetting.setting.id', 'woocommerce_store_address' ),
				$this->expectedField( 'updateWCSetting.setting.value', '123 Test Street' ),
			]
		);

		$this->assertSame( '123 Test Street', get_option( 'woocommerce_store_address' ) );

		update_option( 'woocommerce_store_address', $original );
	}

	public function testUpdateWCSettingSelect() {
		$this->loginAsShopManager();

		$original = get_option( 'woocommerce_currency', 'USD' );

		$query = '
			mutation ($input: UpdateWCSettingInput!) {
				updateWCSetting(input: $input) {
					setting {
						id
						... on WCStringSetting {
							value
						}
					}
				}
			}
		';

		$variables = [
			'input' => [
				'group' => 'general',
				'id'    => 'woocommerce_currency',
				'value' => 'EUR',
			],
		];

		$response = $this->graphql( compact( 'query', 'variables' ) );

		$this->assertQuerySuccessful(
			$response,
			[
				$this->expectedField( 'updateWCSetting.setting.id', 'woocommerce_currency' ),
				$this->expectedField( 'updateWCSetting.setting.value', 'EUR' ),
			]
		);

		update_option( 'woocommerce_currency', $original );
	}

	public function testUpdateWCSettingCheckbox() {
		$this->loginAsShopManager();

		$original = get_option( 'woocommerce_calc_taxes', 'no' );

		$query = '
			mutation ($input: UpdateWCSettingInput!) {
				updateWCSetting(input: $input) {
					setting {
						id
						... on WCStringSetting {
							value
						}
					}
				}
			}
		';

		$variables = [
			'input' => [
				'group' => 'general',
				'id'    => 'woocommerce_calc_taxes',
				'value' => 'yes',
			],
		];

		$response = $this->graphql( compact( 'query', 'variables' ) );

		$this->assertQuerySuccessful(
			$response,
			[
				$this->expectedField( 'updateWCSetting.setting.id', 'woocommerce_calc_taxes' ),
				$this->expectedField( 'updateWCSetting.setting.value', 'yes' ),
			]
		);

		update_option( 'woocommerce_calc_taxes', $original );
	}

	public function testUpdateWCSettingNumber() {
		$this->loginAsShopManager();

		$original = get_option( 'woocommerce_price_num_decimals', '2' );

		$query = '
			mutation ($input: UpdateWCSettingInput!) {
				updateWCSetting(input: $input) {
					setting {
						id
						... on WCStringSetting {
							value
						}
					}
				}
			}
		';

		$variables = [
			'input' => [
				'group' => 'general',
				'id'    => 'woocommerce_price_num_decimals',
				'value' => '4',
			],
		];

		$response = $this->graphql( compact( 'query', 'variables' ) );

		$this->assertQuerySuccessful(
			$response,
			[
				$this->expectedField( 'updateWCSetting.setting.id', 'woocommerce_price_num_decimals' ),
				$this->expectedField( 'updateWCSetting.setting.value', '4' ),
			]
		);

		update_option( 'woocommerce_price_num_decimals', $original );
	}

	public function testUpdateWCSettingTextarea() {
		$this->loginAsShopManager();

		$original = get_option( 'woocommerce_registration_privacy_policy_text', '' );

		$query = '
			mutation ($input: UpdateWCSettingInput!) {
				updateWCSetting(input: $input) {
					setting {
						id
						... on WCStringSetting {
							value
						}
					}
				}
			}
		';

		$variables = [
			'input' => [
				'group' => 'account',
				'id'    => 'woocommerce_registration_privacy_policy_text',
				'value' => 'Updated privacy policy text for testing.',
			],
		];

		$response = $this->graphql( compact( 'query', 'variables' ) );

		$this->assertQuerySuccessful(
			$response,
			[
				$this->expectedField( 'updateWCSetting.setting.id', 'woocommerce_registration_privacy_policy_text' ),
				$this->expectedField( 'updateWCSetting.setting.value', 'Updated privacy policy text for testing.' ),
			]
		);

		update_option( 'woocommerce_registration_privacy_policy_text', $original );
	}

	public function testUpdateWCSettingAsCustomerFails() {
		$customer_id = $this->factory->customer->create();
		$this->loginAs( $customer_id );

		$query = '
			mutation ($input: UpdateWCSettingInput!) {
				updateWCSetting(input: $input) {
					setting {
						id
					}
				}
			}
		';

		$variables = [
			'input' => [
				'group' => 'general',
				'id'    => 'woocommerce_store_address',
				'value' => 'Unauthorized Street',
			],
		];

		$response = $this->graphql( compact( 'query', 'variables' ) );

		$this->assertQueryError(
			$response,
			[ $this->expectedErrorMessage( 'Sorry, you cannot update settings.', self::MESSAGE_EQUALS ) ]
		);
	}

	public function testUpdateWCSettingInvalidSettingFails() {
		$this->loginAsShopManager();

		$query = '
			mutation ($input: UpdateWCSettingInput!) {
				updateWCSetting(input: $input) {
					setting {
						id
					}
				}
			}
		';

		$variables = [
			'input' => [
				'group' => 'general',
				'id'    => 'nonexistent_setting',
				'value' => 'test',
			],
		];

		$response = $this->graphql( compact( 'query', 'variables' ) );

		$this->assertQueryError(
			$response,
			[ $this->expectedErrorMessage( 'Invalid setting', self::MESSAGE_CONTAINS ) ]
		);
	}

	public function testUpdateWCSettingsBatchUpdate() {
		$this->loginAsShopManager();

		$original_address = get_option( 'woocommerce_store_address', '' );
		$original_city    = get_option( 'woocommerce_store_city', '' );

		$query = '
			mutation ($input: UpdateWCSettingsInput!) {
				updateWCSettings(input: $input) {
					settings {
						id
						... on WCStringSetting {
							value
						}
					}
				}
			}
		';

		$variables = [
			'input' => [
				'group'    => 'general',
				'settings' => [
					[ 'id' => 'woocommerce_store_address', 'value' => '456 Batch Ave' ],
					[ 'id' => 'woocommerce_store_city', 'value' => 'Batchville' ],
				],
			],
		];

		$response = $this->graphql( compact( 'query', 'variables' ) );

		$this->assertQuerySuccessful(
			$response,
			[
				$this->expectedField( 'updateWCSettings.settings.#.id', 'woocommerce_store_address' ),
				$this->expectedField( 'updateWCSettings.settings.#.value', '456 Batch Ave' ),
				$this->expectedField( 'updateWCSettings.settings.#.id', 'woocommerce_store_city' ),
				$this->expectedField( 'updateWCSettings.settings.#.value', 'Batchville' ),
			]
		);

		$this->assertSame( '456 Batch Ave', get_option( 'woocommerce_store_address' ) );
		$this->assertSame( 'Batchville', get_option( 'woocommerce_store_city' ) );

		update_option( 'woocommerce_store_address', $original_address );
		update_option( 'woocommerce_store_city', $original_city );
	}

	public function testQuerySettingsByTypeVariant() {
		$this->loginAsShopManager();

		$query = '
			query {
				wcSettings(group: "account") {
					id
					type
					... on WCStringSetting {
						stringValue: value
					}
					... on WCArraySetting {
						arrayValue: value
					}
					... on WCRelativeDateSetting {
						relativeDateValue: value {
							number
							unit
						}
					}
				}
			}
		';

		$response = $this->graphql( compact( 'query' ) );

		$this->assertQuerySuccessful( $response, [] );

		$settings = $this->lodashGet( $response, 'data.wcSettings' );
		$this->assertNotEmpty( $settings );

		// Verify relative_date_selector settings resolve correctly.
		$relative_date_setting = null;
		foreach ( $settings as $setting ) {
			if ( 'RELATIVE_DATE_SELECTOR' === $setting['type'] ) {
				$relative_date_setting = $setting;
				break;
			}
		}

		$this->assertNotNull( $relative_date_setting, 'Should find a relative_date_selector setting in the account group.' );
		$this->assertArrayHasKey( 'relativeDateValue', $relative_date_setting, 'Relative date setting should have a relativeDateValue field.' );
		$this->assertArrayHasKey( 'unit', $relative_date_setting['relativeDateValue'], 'Relative date value should have a unit field.' );
	}
}
