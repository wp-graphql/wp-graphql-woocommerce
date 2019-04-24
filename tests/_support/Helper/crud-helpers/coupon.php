<?php

use GraphQLRelay\Relay;

class CouponHelper extends WCG_Helper {
    public function create( $args = array(), $save = true ) {
        // Create new coupon crud object instance.
        $coupon = new WC_Coupon();

        // Set props.
        $amount = $this->dummy->number( 0, 75 );
        $coupon->set_props(
            array_merge(
                array(
                    'code'                        => $amount . 'off',
                    'amount'                      => floatval( $amount ),
                    'date_expires'                => null,
                    'discount_type'               => 'percent',
                    'description'                 => 'Test coupon',
                ),
                $args
            )
        );

        // Return instance in not saving.
        if( ! $save ) {
            return $coupon;
        }

        // Return ID upon saving.
        return $coupon->save();

        // Insert post
        $coupon_id = wp_insert_post( array(
            'post_title'   => $coupon_code,
            'post_type'    => 'shop_coupon',
            'post_status'  => 'publish',
            'post_excerpt' => 'This is a dummy coupon',
        ) );

        $meta = wp_parse_args( $meta, array(
            'discount_type'              => 'fixed_cart',
            'coupon_amount'              => '1',
            'individual_use'             => 'no',
            'product_ids'                => '',
            'exclude_product_ids'        => '',
            'usage_limit'                => '',
            'usage_limit_per_user'       => '',
            'limit_usage_to_x_items'     => '',
            'expiry_date'                => '',
            'free_shipping'              => 'no',
            'exclude_sale_items'         => 'no',
            'product_categories'         => array(),
            'exclude_product_categories' => array(),
            'minimum_amount'             => '',
            'maximum_amount'             => '',
            'customer_email'             => array(),
            'usage_count'                => '0',
        ) );

        // Update meta.
        foreach ( $meta as $key => $value ) {
            update_post_meta( $coupon_id, $key, $value );
        }

        return $coupon_id;
    }

    public function print_query( $id ) {
        $data = new WC_Coupon( $id );

		return array(
			'id'                        => Relay::toGlobalId( 'shop_coupon', $id ),
            'couponId'                  => $data->get_id(),
            'code'                      => $data->get_code(),
            'amount'                    => $data->get_amount(),
            'date'                      => $data->get_date_created()->__toString(),
            'modified'                  => $data->get_date_modified()->__toString(),
            'discountType'              => strtoupper( $data->get_discount_type() ),
            'description'               => $data->get_description(),
            'dateExpiry'                => $data->get_date_expires(),
            'usageCount'                => $data->get_usage_count(),
            'individualUse'             => $data->get_individual_use(),
            'usageLimit'                => ! empty( $data->get_usage_limit() )
                ? $data->get_usage_limit()
                : null,
            'usageLimitPerUser'         => ! empty( $data->get_usage_limit_per_user() )
                ? $data->get_usage_limit_per_user()
                : null,
            'limitUsageToXItems'        => $data->get_limit_usage_to_x_items(),
            'freeShipping'              => $data->get_free_shipping(),
            'excludeSaleItems'          => $data->get_exclude_sale_items(),
            'minimumAmount'             => ! empty( $data->get_minimum_amount() )
                ? $data->get_minimum_amount()
                : null,
            'maximumAmount'             => ! empty( $data->get_maximum_amount() )
                ? $data->get_maximum_amount()
                : null,
            'emailRestrictions'         => ! empty( $data->get_email_restrictions() )
                ? $data->get_email_restrictions()
                : null,
            'products'                  => [
                'nodes' => array_map(
                    function( $id ) {
                        return array( 'productId' => $id );
                    },
                    $data->get_product_ids()
                ),
            ],
            'excludedProducts'          => [
                'nodes' => array_map(
                    function( $id ) {
                        return array( 'productId' => $id );
                    },
                    $data->get_excluded_product_ids()
                ),
            ],
            'productCategories'         => [
                'nodes' => array_map(
                    function( $id ) {
                        return array( 'productCategoryId' => $id );
                    },
                    $data->get_product_categories()
                ),
            ],
            'excludedProductCategories' => [
                'nodes' => array_map(
                    function( $id ) {
                        return array( 'productCategoryId' => $id );
                    },
                    $data->get_excluded_product_categories()
                ),
            ],
            'usedBy'                    => [
                'nodes' => array_map(
                    function( $id ) {
                        return array( 'customerId' => $id );
                    },
                    $data->get_used_by()
                ),
            ],
		);
    }

    public function print_failed_query( $id ) {
        $data = new WC_Coupon( $id );

		return array(
        );
    }

    public function print_nodes( $ids, $processors = array() ) {
        $default_processors = array(
            'mapper' => function( $coupon_id ) {
                return array( 'id' => Relay::toGlobalId( 'shop_coupon', $coupon_id ) ); 
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