<?php
/**
 * Model - Order_Item
 *
 * Resolves model for order item crud objects
 *
 * @package WPGraphQL\Extensions\WooCommerce\Model
 * @since 0.0.2
 */

namespace WPGraphQL\Extensions\WooCommerce\Model;

use GraphQLRelay\Relay;
use WPGraphQL\Model\Model;

/**
 * Class Order_Item
 */
class Order_Item extends Model {

	/**
	 * Stores order item type
	 *
	 * @var int $item_type
	 * @access protected
	 */
	protected $item_type;

	/**
	 * Order_Item constructor
	 *
	 * @param int $item - order item crud object.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct( $item ) {
		$this->data                = $item;
		$this->item_type           = $item->get_type();
		$allowed_restricted_fields = array(
			'isRestricted',
			'isPrivate',
			'isPublic',
			'id',
			'orderItemId',
		);

		$restricted_cap = apply_filters( 'order_item_restricted_cap', '' );

		parent::__construct( $restricted_cap, $allowed_restricted_fields, null );
	}

	/**
	 * Determines if the order item should be considered private
	 *
	 * @access public
	 * @return bool
	 */
	protected function is_private() {
		return false;
	}

	/**
	 * Initializes the Order field resolvers
	 *
	 * @access protected
	 */
	protected function init() {
		if ( empty( $this->fields ) ) {
			$this->fields = array(
				'ID'      => function() {
					return $this->data->get_id();
				},
				'itemId'  => function() {
					return ! empty( $this->data->get_id() ) ? $this->data->get_id() : null;
				},
				'orderId' => function() {
					return ! empty( $this->data->get_order_id() ) ? $this->data->get_order_id() : null;
				},
				'type'    => function() {
					return ! empty( $this->data->get_type() ) ? $this->data->get_type() : null;
				},
			);

			switch ( $this->item_type ) {
				case 'coupon':
					$this->fields = array_merge(
						$this->fields,
						array(
							'code'        => function() {
								return ! empty( $this->data->get_code() ) ? $this->data->get_code() : null;
							},
							'discount'    => function() {
								return ! empty( $this->data->get_discount() ) ? $this->data->get_discount() : null;
							},
							'discountTax' => function() {
								return ! empty( $this->data->get_discount_tax() ) ? $this->data->get_discount_tax() : null;
							},
							'coupon_id'   => array(
								'callback'   => function() {
									$coupon_id = \wc_get_coupon_id_by_code( $this->data->get_code() );
									return ! empty( $coupon_id ) ? $coupon_id : null;
								},
								'capability' => 'edit_shop_orders',
							),
						)
					);
					break;

				case 'fee':
					$this->fields = array_merge(
						$this->fields,
						array(
							'amount'    => function() {
								return ! empty( $this->data->get_amount() ) ? $this->data->get_amount() : null;
							},
							'name'      => function() {
								return ! empty( $this->data->get_name() ) ? $this->data->get_name() : null;
							},
							'taxStatus' => function() {
								return ! empty( $this->data->get_tax_status() ) ? $this->data->get_tax_status() : null;
							},
							'taxClass'  => function() {
								if ( $this->data->get_tax_status() === 'taxable' ) {
									return ! empty( $this->data->get_tax_class() )
										? $this->data->get_tax_class()
										: 'standard';
								}
								return null;
							},
							'total'     => function() {
								return ! empty( $this->data->get_total() ) ? $this->data->get_total() : null;
							},
							'totalTax'  => function() {
								return ! empty( $this->data->get_total_tax() ) ? $this->data->get_total_tax() : null;
							},
							'taxes'     => function() {
								return ! empty( $this->data->get_taxes() )
									? \wc_graphql_map_tax_statements( $this->data->get_taxes() )
									: null;
							},
						)
					);
					break;

				case 'shipping':
					$this->fields = array_merge(
						$this->fields,
						array(
							'name'        => function() {
								return ! empty( $this->data->get_name() ) ? $this->data->get_name() : null;
							},
							'methodTitle' => function() {
								return ! empty( $this->data->get_method_title() ) ? $this->data->get_method_title() : null;
							},
							'total'       => function() {
								return ! empty( $this->data->get_total() ) ? $this->data->get_total() : null;
							},
							'totalTax'    => function() {
								return ! empty( $this->data->get_total_tax() ) ? $this->data->get_total_tax() : null;
							},
							'taxes'       => function() {
								return ! empty( $this->data->get_taxes() )
									? \wc_graphql_map_tax_statements( $this->data->get_taxes() )
									: null;
							},
							'taxClass'    => function() {
								return ! empty( $this->data->get_tax_class() ) ? $this->data->get_tax_class() : 'standard';
							},
							'method_id'   => function() {
								return ! empty( $this->data->get_method_id() ) ? $this->data->get_method_id() : null;
							},
						)
					);
					break;

				case 'tax':
					$this->fields = array_merge(
						$this->fields,
						array(
							'rateCode'         => function() {
								return ! empty( $this->data->get_rate_code() ) ? $this->data->get_rate_code() : null;
							},
							'label'            => function() {
								return ! empty( $this->data->get_label() ) ? $this->data->get_label() : null;
							},
							'taxTotal'         => function() {
								return ! empty( $this->data->get_tax_total() ) ? $this->data->get_tax_total() : null;
							},
							'shippingTaxTotal' => function() {
								return ! empty( $this->data->get_shipping_tax_total() ) ? $this->data->get_shipping_tax_total() : null;
							},
							'isCompound'       => function() {
								return ! empty( $this->data->is_compound() ) ? $this->data->is_compound() : null;
							},
							'rate_id'          => function() {
								return ! empty( $this->data->get_rate_id() ) ? $this->data->get_rate_id() : null;
							},
						)
					);
					break;
				default:
					$this->fields = array_merge(
						$this->fields,
						array(
							'productId'     => function() {
								return ! empty( $this->data->get_product_id() ) ? $this->data->get_product_id() : null;
							},
							'variationId'   => function() {
								return ! empty( $this->data->get_variation_id() ) ? $this->data->get_variation_id() : null;
							},
							'quantity'      => function() {
								return ! empty( $this->data->get_quantity() ) ? $this->data->get_quantity() : null;
							},
							'subtotal'      => function() {
								return ! empty( $this->data->get_subtotal() ) ? $this->data->get_subtotal() : null;
							},
							'subtotalTax'   => function() {
								return ! empty( $this->data->get_subtotal_tax() ) ? $this->data->get_subtotal_tax() : null;
							},
							'total'         => function() {
								return ! empty( $this->data->get_total() ) ? $this->data->get_total() : null;
							},
							'totalTax'      => function() {
								return ! empty( $this->data->get_total_tax() ) ? $this->data->get_total_tax() : null;
							},
							'taxes'         => function() {
								return ! empty( $this->data->get_taxes() )
									? \wc_graphql_map_tax_statements( $this->data->get_taxes() )
									: null;
							},
							'itemDownloads' => function() {
								return ! empty( $this->data->get_item_downloads() ) ? $this->data->get_item_downloads() : null;
							},
							'taxStatus'     => function() {
								return ! empty( $this->data->get_tax_status() ) ? $this->data->get_tax_status() : null;
							},
							'taxClass'      => function() {
								if ( $this->data->get_tax_status() === 'taxable' ) {
									return ! empty( $this->data->get_tax_class() )
										? $this->data->get_tax_class()
										: 'standard';
								}
								return null;
							},
						)
					);
					break;
			}
		}

		parent::prepare_fields();
	}
}
