<?php
/**
 * Model - Order_Item
 *
 * Resolves model for order item crud objects
 *
 * @package WPGraphQL\WooCommerce\Model
 * @since 0.0.2
 */

namespace WPGraphQL\WooCommerce\Model;

use WPGraphQL\Model\Model;

/**
 * Class Order_Item
 */
class Order_Item extends Model {

	/**
	 * Stores order item type.
	 *
	 * @var int
	 */
	protected $item_type;

	/**
	 * Stores parent order model.
	 *
	 * @var Order
	 */
	protected $order;

	/**
	 * Order_Item constructor
	 *
	 * @param int $item - order item crud object.
	 */
	public function __construct( $item ) {
		$this->data                = $item;
		$this->item_type           = $item->get_type();
		$order_id                  = $item->get_order_id();
		$author_id                 = get_post_field( 'post_author', $order_id );
		$this->order               = ! empty( $item->cached_order ) ? $item->cached_order : new Order( $order_id );
		$allowed_restricted_fields = array(
			'isRestricted',
			'isPrivate',
			'isPublic',
			'id',
			'databaseId',
		);

		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
		$restricted_cap = apply_filters( 'order_item_restricted_cap', '' );

		parent::__construct( $restricted_cap, $allowed_restricted_fields, $author_id );
	}

	/**
	 * Forwards function calls to WC_Data sub-class instance.
	 *
	 * @param string $method - function name.
	 * @param array  $args  - function call arguments.
	 * @return mixed
	 */
	public function __call( $method, $args ) {
		return $this->data->$method( ...$args );
	}

	/**
	 * Initializes the Order field resolvers
	 */
	protected function init() {
		if ( empty( $this->fields ) ) {
			$this->fields = array(
				'ID'         => function() {
					return $this->data->get_id();
				},
				'databaseId' => function() {
					return $this->ID;
				},
				'orderId'    => function() {
					return ! empty( $this->data->get_order_id() ) ? $this->data->get_order_id() : null;
				},
				'type'       => function() {
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
										: '';
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
								return ! is_null( $this->data->get_shipping_tax_total() ) ? $this->data->get_shipping_tax_total() : 0;
							},
							'isCompound'       => function() {
								return ! is_null( $this->data->is_compound() ) ? $this->data->is_compound() : false;
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
										: '';
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


	/**
	 * Determines if the order item should be considered private
	 *
	 * @since 0.2.0
	 *
	 * @return bool
	 */
	protected function is_private() {
		return $this->order->is_private();
	}

	/**
	 * Retrieve the cap to check if the data should be restricted for the order
	 *
	 * @since 0.2.0
	 *
	 * @return string
	 */
	protected function get_restricted_cap() {
		return $this->order->get_restricted_cap();
	}
}
