<?php
/**
 * WPEnum Type - CouponsOrderbyEnum
 *
 * @package \WPGraphQL\WooCommerce\Type\WPEnum
 * @since   0.3.2
 */

namespace WPGraphQL\WooCommerce\Type\WPEnum;

/**
 * Class Coupons_Orderby_Enum
 */
class Coupons_Orderby_Enum extends Post_Type_Orderby_Enum {
	/**
	 * Holds ordering enumeration base name.
	 *
	 * @var string
	 */
	protected static $name = 'Coupons';

	/**
	 * Define enumeration values related to the "shop_coupon" post-type ordering fields.
	 *
	 * @return array
	 */
	protected static function values() {
		return self::post_type_values();
	}
}
