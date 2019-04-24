<?php

use GraphQLRelay\Relay;

class RefundHelper extends WCG_Helper {
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

        return ! empty( $refund ) ? $refund->get_id() : 0;
    }

    public function print_query( $id ) {
        $data = new WC_Order_Refund( $id );
        if ( ! $data ) {
            return null; 
        }

        return array(
            'id'         => Relay::toGlobalId( 'shop_order_refund', $data->get_id() ),
            'refundId'   => $data->get_id(),
            'title'      => $data->get_post_title(),
            'reason'     => $data->get_reason(),
            'amount'     => $data->get_amount(),
            'refundedBy' => array(
                'id' => Relay::toGlobalId( 'user', $data->get_refunded_by() )
            )
        );
    }

    public function print_failed_query( $id ) {
        $data = new WC_Order_Refund( $id );
        if ( ! $data ) {
            return null; 
        }

        return array(
            'id'         => Relay::toGlobalId( 'shop_order_refund', $data->get_id() ),
            'refundId'   => $data->get_id(),
            'title'      => null,
            'reason'     => null,
            'amount'     => null,
            'refundedBy' => null,
        );
    }

    public function print_nodes( $ids, $processors = array() ) {
        $default_processors = array(
            'mapper' => function( $refund_id ) {
                return array( 'id' => Relay::toGlobalId( 'shop_order_refund', $refund_id ) ); 
            },
            'sorter' => function( $id_a, $id_b ) {
                if ( $id_a == $id_b ) {
                    return 0;
                }

                return ( $id_a > $id_b ) ? -1 : 1;
            },
            'filter' => function( $id ) {
                return true;
            }
        );

        $processors = array_merge( $default_processors, $processors );

        $results = array_filter( $ids, $processors['filter'] );
        if( ! empty( $results ) ) {
            usort( $results, $processors['sorter'] );
        }

        return array_values( array_map( $processors['mapper'], $results ) );
    }
}