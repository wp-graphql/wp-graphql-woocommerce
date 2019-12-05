<?php
/**
 * Adds filters that modify WPGraphQL ACF schema.
 *
 * @package \WPGraphQL\WooCommerce
 * @since   0.3.0
 */

namespace WPGraphQL\WooCommerce;

use WPGraphQL\Type\WPEnumType;

/**
 * Class ACF_Schema_Filters
 */
class ACF_Schema_Filters {

	/**
	 * Register filters
	 */
	public static function add_filters() {
		// Registers WooCommerce CPTs && taxonomies.
		add_filter( 'graphql_acf_get_root_id', array( __CLASS__, 'resolve_crud_root_id' ), 10, 2 );

		// Register ACF orderby fields.
		add_filter( 'graphql_couponsOrderByEnum_values', array( __CLASS__, 'orderby_acf_fields' ), 10 );
		add_filter( 'graphql_productsOrderByEnum_values', array( __CLASS__, 'orderby_acf_fields' ), 10 );
		add_filter( 'graphql_ordersOrderByEnum_values', array( __CLASS__, 'orderby_acf_fields' ), 10 );

		add_filter( 'graphql_coupon_connection_ordering_meta', array( __CLASS__, 'acf_ordering_meta' ) );
		add_filter( 'graphql_product_connection_ordering_meta', array( __CLASS__, 'acf_ordering_meta' ) );
		add_filter( 'graphql_order_connection_ordering_meta', array( __CLASS__, 'acf_ordering_meta' ) );
	}

	/**
	 * Resolve post object ID from CRUD object Model.
	 *
	 * @param integer|null $id    Post object database ID.
	 * @param mixed        $root  Root resolver.
	 *
	 * @return integer|null
	 */
	public static function resolve_crud_root_id( $id, $root ) {
		switch ( true ) {
			case $root instanceof \WPGraphQL\WooCommerce\Model\CRUD_CPT:
				$id = absint( $root->ID );
				break;
		}

		return $id;
	}

	/**
	 * Retrieves ACF field groups for the custom post-type related to the current hook
	 *
	 * @return false|array
	 */
	private static function get_acf_field_groups() {
		// Check captured filter and get the ACF field groups.
		switch ( current_filter() ) {
			case 'graphql_couponsOrderByEnum_values':
			case 'graphql_coupon_connection_ordering_meta':
				$field_groups = acf_get_field_groups( array( 'post_type' => 'shop_coupon' ) );
				break;
			case 'graphql_productsOrderByEnum_values':
			case 'graphql_product_connection_ordering_meta':
				$field_groups = acf_get_field_groups( array( 'post_type' => 'product' ) );
				break;
			case 'graphql_ordersOrderByEnum_values':
			case 'graphql_order_connection_ordering_meta':
				$field_groups = acf_get_field_groups( array( 'post_type' => 'shop_order' ) );
				break;
			default:
				$field_groups = false;
		}

		return $field_groups;
	}

	/**
	 * Adds ACF fields to the "OrderbyEnum" for the custom post-type related to the current hook.
	 *
	 * @param array $values  *OrderbyEnum fields.
	 *
	 * @return array.
	 */
	public static function orderby_acf_fields( $values ) {
		$field_groups = self::get_acf_field_groups();
		// If no field groups, bail.
		if ( empty( $field_groups ) || ! is_array( $field_groups ) ) {
			return $values;
		}

		foreach ( $field_groups as $field_group ) {
			// Check if should be shown in GraphQL.
			if ( empty( $field_group['show_in_graphql'] ) || false === $field_group['show_in_graphql'] ) {
				continue;
			}

			// Get the fields in the group.
			$acf_fields = ! empty( $field_group['sub_fields'] ) ? $field_group['sub_fields'] : acf_get_fields( $field_group );

			// If there are no fields, continue.
			if ( empty( $acf_fields ) || ! is_array( $acf_fields ) ) {
				continue;
			}

			// Loop over the fields and register them to the Schema.
			foreach ( $acf_fields as $acf_field ) {
				if ( empty( $acf_field['name'] ) ) {
					continue;
				}

				switch ( $acf_field['type'] ) {
					case 'text':
					case 'email':
					case 'message':
					case 'url':
					case 'number':
					case 'date_picker':
					case 'time_picker':
					case 'date_time_picker':
						$values[ WPEnumType::get_safe_name( $acf_field['name'] ) ] = array(
							'value'       => $acf_field['name'],
							'description' => sprintf(
								/* translators: %s: ACF field label */
								__( 'Order by the "%s" ACF field', 'wp-graphql-woocommerce' ),
								$acf_field['label']
							),
						);
						break;
				}
			}
		}

		return $values;
	}

	/**
	 * Adds ACF fields to the "ordering_meta" for the type related to the current hook.
	 *
	 * @param array $ordering_meta  Valid ordering meta fields.
	 *
	 * @return array.
	 */
	public static function acf_ordering_meta( $ordering_meta ) {
		$field_groups = self::get_acf_field_groups();
		// If no field groups, bail.
		if ( empty( $field_groups ) || ! is_array( $field_groups ) ) {
			return $ordering_meta;
		}

		foreach ( $field_groups as $field_group ) {
			// Check if should be shown in GraphQL.
			if ( empty( $field_group['show_in_graphql'] ) || false === $field_group['show_in_graphql'] ) {
				continue;
			}

			// Get the fields in the group.
			$acf_fields = ! empty( $field_group['sub_fields'] ) ? $field_group['sub_fields'] : acf_get_fields( $field_group );

			// If there are no fields, continue.
			if ( empty( $acf_fields ) || ! is_array( $acf_fields ) ) {
				continue;
			}

			// Loop over the fields and register them to the Schema.
			foreach ( $acf_fields as $acf_field ) {
				if ( empty( $acf_field['name'] ) ) {
					continue;
				}

				switch ( $acf_field['type'] ) {
					case 'text':
					case 'email':
					case 'message':
					case 'url':
					case 'number':
					case 'date_picker':
					case 'time_picker':
					case 'date_time_picker':
						$ordering_meta[] = $acf_field['name'];
						break;
				}
			}
		}

		return $ordering_meta;
	}
}
