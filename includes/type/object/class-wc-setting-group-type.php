<?php
/**
 * WPObject Type - WC_Setting_Group_Type
 *
 * Registers WCSettingGroup WPObject type
 *
 * @package WPGraphQL\WooCommerce\Type\WPObject
 * @since   TBD
 */

namespace WPGraphQL\WooCommerce\Type\WPObject;

/**
 * Class WC_Setting_Group_Type
 */
class WC_Setting_Group_Type {
	/**
	 * Registers WC setting group type
	 *
	 * @return void
	 */
	public static function register() {
		register_graphql_object_type(
			'WCSettingGroup',
			[
				'eagerlyLoadType' => true,
				'description'     => static function () {
					return __( 'A WooCommerce settings group', 'wp-graphql-woocommerce' );
				},
				'fields'          => [
					'id'          => [
						'type'        => [ 'non_null' => 'String' ],
						'description' => static function () {
					return __( 'A unique identifier that can be used to link settings together.', 'wp-graphql-woocommerce' );
				},
					],
					'label'       => [
						'type'        => 'String',
						'description' => static function () {
					return __( 'A human readable label for the setting group used in interfaces.', 'wp-graphql-woocommerce' );
				},
					],
					'description' => [
						'type'        => 'String',
						'description' => static function () {
					return __( 'A human readable description for the setting group used in interfaces.', 'wp-graphql-woocommerce' );
				},
					],
					'parentId'    => [
						'type'        => 'String',
						'description' => static function () {
					return __( 'ID of parent grouping.', 'wp-graphql-woocommerce' );
				},
						'resolve'     => static function ( $source ) {
							return ! empty( $source['parent_id'] ) ? $source['parent_id'] : null;
						},
					],
					'subGroups'   => [
						'type'        => [ 'list_of' => 'String' ],
						'description' => static function () {
					return __( 'IDs for settings sub groups.', 'wp-graphql-woocommerce' );
				},
						'resolve'     => static function ( $source ) {
							return ! empty( $source['sub_groups'] ) ? $source['sub_groups'] : [];
						},
					],
					'settings'    => [
						'type'        => [ 'list_of' => 'WCSetting' ],
						'description' => static function () {
					return __( 'The settings belonging to this group.', 'wp-graphql-woocommerce' );
				},
						'resolve'     => static function ( $source ) {
							$controller = new \WC_REST_Setting_Options_Controller();
							$settings   = $controller->get_group_settings( $source['id'] );

							return is_wp_error( $settings ) ? [] : $settings;
						},
					],
				],
			]
		);
	}
}
