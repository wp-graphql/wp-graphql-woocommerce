<?php

use GraphQLRelay\Relay;

class RefundHelper extends WCG_Helper {
	public function __construct() {
		$this->node_type = 'shop_order_refund';

		parent::__construct();
	}

	public function to_relay_id( $id ) {
		return Relay::toGlobalId( 'order', $id );
	}

	public function create( $order, $args = array() ) {
		$order = new WC_Order( $order );
		if ( empty( $order ) ) {
			return false;
		}

		$refund = wc_create_refund(
			array_merge(
				array(
					'amount'         => floatval( $order->get_total() ),
					'order_id'       => $order->get_id(),
					'reason'         => 'defective',
					'refund_payment' => false,
					'restock_items'  => false,
				),
				$args
			)
		);

		if ( ! empty( $refund ) && ! empty( $args['meta_data'] ) ) {
			$refund->set_meta_data( $args['meta_data'] );
			$refund->save_meta_data();
		}

		return ! empty( $refund ) ? $refund->get_id() : 0;
	}

	public function print_query( $id ) {
		$data = new WC_Order_Refund( $id );
		if ( ! $data ) {
			return null;
		}

		return array(
			'id'         => $this->to_relay_id( $id ),
			'databaseId' => $data->get_id(),
			'title'      => $data->get_post_title(),
			'reason'     => $data->get_reason(),
			'amount'     => $data->get_amount(),
			'refundedBy' => array(
				'id' => Relay::toGlobalId( 'user', $data->get_refunded_by() )
			),
			'date'       => $data->get_date_modified(),
		);
	}

	public function print_failed_query( $id ) {
		$data = new WC_Order_Refund( $id );
		if ( ! $data ) {
			return null;
		}

		return array(
			'id'         => $this->to_relay_id( $id ),
			'databaseId' => $data->get_id(),
			'title'      => null,
			'reason'     => null,
			'amount'     => null,
			'refundedBy' => null,
			'date'       => null,
		);
	}
}
