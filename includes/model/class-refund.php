<?php
/**
 * Model - Refund
 *
 * Resolves refund crud object model
 *
 * @package WPGraphQL\WooCommerce\Model
 * @since 0.0.1
 */

namespace WPGraphQL\WooCommerce\Model;

use GraphQLRelay\Relay;
use WPGraphQL\Model\Post;
use WC_Order_Refund;

/**
 * Class Refund.
 */
class Refund extends Order {

	/**
	 * Hold order post type slug
	 *
	 * @var string $post_type
	 */
	protected $post_type = 'shop_order_refund';

	/**
	 * Return the data source to be used by the model.
	 *
	 * @param integer $id  Refund ID.
	 *
	 * @return WC_Data
	 */
	protected function get_object( $id ) {
		return new WC_Order_Refund( $id );
	}

	/**
	 * Initializes the Refund field resolvers.
	 */
	protected function init() {
		if ( empty( $this->fields ) ) {
			Post::init();

			$fields = array(
				'id'             => function() {
					return ! empty( $this->wc_data->get_id() ) ? Relay::toGlobalId( 'shop_order_refund', $this->wc_data->get_id() ) : null;
				},
				'title'          => function() {
					return ! empty( $this->wc_data->get_post_title() ) ? $this->wc_data->get_post_title() : null;
				},
				'amount'         => function() {
					return ! empty( $this->wc_data->get_amount() ) ? $this->wc_data->get_amount() : null;
				},
				'reason'         => function() {
					return ! empty( $this->wc_data->get_reason() ) ? $this->wc_data->get_reason() : null;
				},
				'refunded_by_id' => array(
					'callback'   => function() {
						return ! empty( $this->wc_data->get_refunded_by() ) ? $this->wc_data->get_refunded_by() : null;
					},
					'capability' => 'list_users',
				),
			);

			$this->fields = array_merge( $this->fields, $fields );
		}
	}
}
