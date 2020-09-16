<?php

use GraphQLRelay\Relay;

class CustomerHelper extends WCG_Helper {
	public function __construct() {
		$this->node_type = 'customer';

		parent::__construct();
	}

	public function to_relay_id( $id ) {
		return Relay::toGlobalId( 'customer', $id );
	}

	public function create( $args = array() ) {
		$customer = new WC_Customer();

		// Create customer details
		$username   = $this->dummy->username();
		$first_name = $this->dummy->firstname();
		$last_name  = $this->dummy->lastname();
		$street     = $this->dummy->street();
		$city       = $this->dummy->city();
		$state      = $this->dummy->state();
		$postcode   = $this->dummy->zipcode();
		$country    = 'US';
		$email      = $this->dummy->email();
		$phone      = $this->dummy->telephone();

		// Set data.
		$customer->set_props(
			array_merge(
				array(
					'email'              => $email,
					'first_name'         => $first_name,
					'last_name'          => $last_name,
					'display_name'       => $username,
					'role'               => 'customer',
					'username'           => $username,
					'billing'            => array(
						'first_name'     => $first_name,
						'last_name'      => $last_name,
						'company'        => '',
						'address_1'      => $street,
						'address_2'      => '',
						'city'           => $city,
						'state'          => $state,
						'postcode'       => $postcode,
						'country'        => $country,
						'email'          => $email,
						'phone'          => $phone,
					),
					'shipping'           => array(
						'first_name'     => $first_name,
						'last_name'      => $last_name,
						'company'        => '',
						'address_1'      => $street,
						'address_2'      => '',
						'city'           => $city,
						'state'          => $state,
						'postcode'       => $postcode,
						'country'        => $country,
					),
					'is_paying_customer' => false,
				),
				$args
			)
		);

		// Set meta data.
		if ( ! empty( $args['meta_data'] ) ) {
			$customer->set_meta_data( $args['meta_data'] );
		}

		return absint( $customer->save() );
	}

	public function print_query( $id, $session = false ) {
		$data = new WC_Customer( $id, $session );
		$wp_user = get_user_by( 'ID', $data->get_id() );

		return array(
			'id'                    => $this->to_relay_id( $id ),
			'databaseId'            => $id,
			'isVatExempt'           => $data->get_is_vat_exempt(),
			'hasCalculatedShipping' => $data->has_calculated_shipping(),
			'calculatedShipping'    => $data->get_calculated_shipping(),
			'orderCount'            => $data->get_order_count(),
			'totalSpent'            => (float) $data->get_total_spent(),
			'username'              => $data->get_username(),
			'email'                 => $data->get_email(),
			'firstName'             => ! empty( $data->get_first_name() ) ? $data->get_first_name() : null,
			'lastName'              => ! empty( $data->get_last_name() ) ? $data->get_last_name() : null,
			'displayName'           => $data->get_display_name(),
			'role'                  => $data->get_role(),
			'date'                  => $data->get_date_created()->__toString(),
			'modified'              => ! empty( $data->get_date_modified() )
				? $data->get_date_modified()->__toString()
				: null,
			'lastOrder'             => ! empty( $data->get_last_order() )
				? array(
					'id'         => Relay::toGlobalId( 'shop_order', $data->get_last_order()->get_id() ),
					'customerId' => $data->get_last_order(),
				)
				: null,
			'billing'               => array(
				'firstName' => ! empty( $data->get_billing_first_name() )
					? $data->get_billing_first_name()
					: null,
				'lastName'  => ! empty( $data->get_billing_last_name() )
					? $data->get_billing_last_name()
					: null,
				'company'   => ! empty( $data->get_billing_company() )
					? $data->get_billing_company()
					: null,
				'address1'  => ! empty( $data->get_billing_address_1() )
					? $data->get_billing_address_1()
					: null,
				'address2'  => ! empty( $data->get_billing_address_2() )
					? $data->get_billing_address_2()
					: null,
				'city'      => ! empty( $data->get_billing_city() )
					? $data->get_billing_city()
					: null,
				'state'     => ! empty( $data->get_billing_state() )
					? $data->get_billing_state()
					: null,
				'postcode'  => ! empty( $data->get_billing_postcode() )
					? $data->get_billing_postcode()
					: null,
				'country'   => ! empty( $data->get_billing_country() )
					? $data->get_billing_country()
					: null,
				'email'     => ! empty( $data->get_billing_email() )
					? $data->get_billing_email()
					: null,
				'phone'     => ! empty( $data->get_billing_phone() )
					? $data->get_billing_phone()
					: null,
			),
			'shipping'              => array(
				'firstName' => ! empty( $data->get_shipping_first_name() )
					? $data->get_shipping_first_name()
					: null,
				'lastName'  => ! empty( $data->get_shipping_last_name() )
					? $data->get_shipping_last_name()
					: null,
				'company'   => ! empty( $data->get_shipping_company() )
					? $data->get_shipping_company()
					: null,
				'address1'  => ! empty( $data->get_shipping_address_1() )
					? $data->get_shipping_address_1()
					: null,
				'address2'  => ! empty( $data->get_shipping_address_2() )
					? $data->get_shipping_address_2()
					: null,
				'city'      => ! empty( $data->get_shipping_city() )
					? $data->get_shipping_city()
					: null,
				'state'     => ! empty( $data->get_shipping_state() )
					? $data->get_shipping_state()
					: null,
				'postcode'  => ! empty( $data->get_shipping_postcode() )
					? $data->get_shipping_postcode()
					: null,
				'country'   => ! empty( $data->get_shipping_country() )
					? $data->get_shipping_country()
					: null,
			),
			'isPayingCustomer'      => $data->get_is_paying_customer(),
			'jwtAuthToken'          => ! is_wp_error( \WPGraphQL\JWT_Authentication\Auth::get_token( $wp_user ) )
				? \WPGraphQL\JWT_Authentication\Auth::get_token( $wp_user )
				: null,
			'jwtRefreshToken'       => ! is_wp_error( \WPGraphQL\JWT_Authentication\Auth::get_refresh_token( $wp_user ) )
				? \WPGraphQL\JWT_Authentication\Auth::get_refresh_token( $wp_user )
				: null,
		);
	}

	public function print_failed_query( $id ) {
		$data = new WC_Customer( $id );

		return array(
			'id'                    => $this->to_relay_id( $id ),
			'databaseId'            => $id,
			'isVatExempt'           => null,
			'hasCalculatedShipping' => null,
			'calculatedShipping'    => null,
			'orderCount'            => null,
			'totalSpent'            => null,
			'username'              => null,
			'email'                 => null,
			'firstName'             => null,
			'lastName'              => null,
			'displayName'           => $data->get_display_name(),
			'role'                  => null,
			'date'                  => null,
			'modified'              => null,
			'lastOrder'             => null,
			'billing'               => null,
			'shipping'              => null,
			'isPayingCustomer'      => null,
			'jwtAuthToken'          => null,
			'jwtRefreshToken'       => null,
		);
	}

	public function print_downloadables( $id ) {
		$items = wc_get_customer_available_downloads( $id );

		if ( empty( $items ) ) {
			return array();
		}

		$nodes = array();
		foreach ( $items as $item ) {
			$nodes[] = array(
				'url'                => $item['download_url'],
				'accessExpires'      => $item['access_expires'],
				'downloadId'         => $item['download_id'],
				'downloadsRemaining' => isset( $item['downloads_remaining'] ) && 'integer' === gettype( $item['downloads_remaining'] )
					? $item['downloads_remaining']
					: null,
				'name'               => $item['download_name'],
				'product'            => array( 'databaseId' => $item['product_id'] ),
				'download'           => array( 'downloadId' => $item['download_id'] ),
			);
		}

		return $nodes;
	}
}
