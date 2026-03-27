<?php

class WCSettingsQueriesTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {
	public function testWcSettingGroupsQueryAsAdmin() {
		$this->loginAsShopManager();

		$query = '
			query {
				wcSettingGroups {
					id
					label
					description
					parentId
					subGroups
				}
			}
		';

		$response = $this->graphql( compact( 'query' ) );

		$this->assertQuerySuccessful( $response, [] );

		$groups = $this->lodashGet( $response, 'data.wcSettingGroups' );
		$this->assertNotEmpty( $groups, 'Should return at least one setting group.' );

		$group_ids = array_column( $groups, 'id' );
		$this->assertContains( 'general', $group_ids, 'Should contain the "general" settings group.' );
	}

	public function testWcSettingGroupsQueryAsCustomerFails() {
		$customer_id = $this->factory->customer->create();
		$this->loginAs( $customer_id );

		$query = '
			query {
				wcSettingGroups {
					id
				}
			}
		';

		$response = $this->graphql( compact( 'query' ) );

		$this->assertQueryError(
			$response,
			[ $this->expectedErrorMessage( 'Sorry, you cannot view settings.', self::MESSAGE_EQUALS ) ]
		);
	}

	public function testWcSettingsQueryReturnsGroupSettings() {
		$this->loginAsShopManager();

		$query = '
			query ($group: String!) {
				wcSettings(group: $group) {
					id
					label
					description
					type
					tip
					placeholder
					groupId
					options
					... on WCStringSetting {
						value
						default
					}
				}
			}
		';

		$variables = [ 'group' => 'general' ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );

		$this->assertQuerySuccessful( $response, [] );

		$settings = $this->lodashGet( $response, 'data.wcSettings' );
		$this->assertNotEmpty( $settings, 'Should return settings for the "general" group.' );

		$setting_ids = array_column( $settings, 'id' );
		$this->assertContains( 'woocommerce_store_address', $setting_ids, 'Should contain the store address setting.' );
	}

	public function testWcSettingsQueryInvalidGroupFails() {
		$this->loginAsShopManager();

		$query = '
			query ($group: String!) {
				wcSettings(group: $group) {
					id
				}
			}
		';

		$variables = [ 'group' => 'nonexistent_group' ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );

		$this->assertQueryError( $response );
	}

	public function testWcSettingGroupSettingsField() {
		$this->loginAsShopManager();

		$query = '
			query {
				wcSettingGroups {
					id
					settings {
						id
						label
						type
						... on WCStringSetting {
							value
						}
					}
				}
			}
		';

		$response = $this->graphql( compact( 'query' ) );

		$this->assertQuerySuccessful( $response, [] );

		$groups = $this->lodashGet( $response, 'data.wcSettingGroups' );
		$general = null;
		foreach ( $groups as $group ) {
			if ( 'general' === $group['id'] ) {
				$general = $group;
				break;
			}
		}

		$this->assertNotNull( $general, 'Should find the "general" group.' );
		$this->assertNotEmpty( $general['settings'], 'General group should have settings.' );
	}
}
