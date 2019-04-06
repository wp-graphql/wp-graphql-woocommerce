<?php

class CustomerHelper {
    public function create( $username = 'testcustomer', $password = 'hunter2', $email = 'test@woo.local' ) {
        $customer = new WC_Customer();
		$customer->set_billing_country( 'US' );
		$customer->set_first_name( 'Justin' );
		$customer->set_billing_state( 'PA' );
		$customer->set_billing_postcode( '19123' );
		$customer->set_billing_city( 'Philadelphia' );
		$customer->set_billing_address( '123 South Street' );
		$customer->set_billing_address_2( 'Apt 1' );
		$customer->set_shipping_country( 'US' );
		$customer->set_shipping_state( 'PA' );
		$customer->set_shipping_postcode( '19123' );
		$customer->set_shipping_city( 'Philadelphia' );
		$customer->set_shipping_address( '123 South Street' );
		$customer->set_shipping_address_2( 'Apt 1' );
		$customer->set_username( $username );
		$customer->set_password( $password );
		$customer->set_email( $email );
		return $customer->save();
    }

    /**
	 * Formats customer response data
	 * 
	 * @param int $id - customer ID
	 * 
	 * @return array
	 */
    public function get_query_data( $id ) {
		$data = new WC_Customer( $id );

		return array(
			'isVatExempt'           => $data->get_is_vat_exempt(),
			'hasCalculatedShipping' => $data->has_calculated_shipping(),
			'calculatedShipping'    => $data->get_calculated_shipping(),
			'orderCount'            => $data->get_order_count(),
			'totalSpent'            => (float) $data->get_total_spent(),
			'username'              => $data->get_username(),
			'email'                 => $data->get_email(),
			'firstName'             => $data->get_first_name(),
			'lastName'              => $data->get_last_name(),
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
		);
    }

    /**
	 * Returns failed customer response data
	 * 
	 * @param int $id - customer ID
	 * 
	 * @return array
	 */
	public function get_query_data_failed( $id ) {
		$data = new WC_Customer( $id );

		return array(
			"isVatExempt"           => null,                                                                  
			"hasCalculatedShipping" => null,                                                        
			"calculatedShipping"    => null,                                                           
			"orderCount"            => null,                                                                   
			"totalSpent"            => null,                                                                   
			"username"              => null,                                                                     
			"email"                 => null,                                                                        
			"firstName"             => null,                                                                    
			"lastName"              => null,                                                                     
			"displayName"           => $data->get_display_name(),                                                                  
			"role"                  => null,                                                                         
			"date"                  => null,                                                                         
			"modified"              => null,                                                                     
			"lastOrder"             => null,                                                                    
			"billing"               => null,                                                                      
			"shipping"              => null,                                                                     
			"isPayingCustomer"      => null,
		);
	}

	public function get_all_query_data( $ids ) {
		$nodes = [];
		foreach( $ids as $id ) {
			$nodes[] = $this->get_query_data( $id );
		}

		return array( 'nodes' => $nodes );
	}
}