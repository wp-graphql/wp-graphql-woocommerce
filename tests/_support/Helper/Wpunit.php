<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I
class Wpunit extends \Codeception\Module {

    private $coupon;
    private $customer;
    private $order;
    private $product;
    private $product_variation;
    private $refund;

    /**
     * HOOK:
     * triggered after module is created and configuration is loaded
     */
    public function _initialize()
    {
        require_once __DIR__ . '/crud-helpers/coupon.php';
        require_once __DIR__ . '/crud-helpers/customer.php';
        require_once __DIR__ . '/crud-helpers/order.php';
        require_once __DIR__ . '/crud-helpers/product.php';
        require_once __DIR__ . '/crud-helpers/product-variation.php';
        require_once __DIR__ . '/crud-helpers/refund.php';
    }

    public function coupon() {
        if ( is_null( $this->coupon ) ) {
            $this->coupon = new \CouponHelper();
        }

        return $this->coupon;
    }

    public function customer() {
        if ( is_null( $this->customer ) ) {
            $this->customer = new \CustomerHelper();
        }

        return $this->customer;
    }

    public function order() {
        if ( is_null( $this->order ) ) {
            $this->order = new OrderHelper();
        }

        return $this->order;
    }

    public function product() {
        if ( is_null( $this->product ) ) {
            $this->product = new Product();
        }

        return $this->product;
    }

    public function product_variation() {
        if ( is_null( $this->product_variation ) ) {
            $this->product_variation = new ProductVariationHelper();
        }

        return $this->product_variation;
    }

    public function refund() {
        if ( is_null( $this->refund ) ) {
            $this->refund = new RefundHelper();
        }

        return $this->refund;
    }

    public function get_nodes( $ids, $crud ) {
        $nodes = array();
		foreach( $ids as $id ) {
			$nodes[] = $crud->get_query_data( $id );
		}

		return array( 'nodes' => $nodes );
	}

    public function clear_loader_cache( $loader_name ) {
		$loader = \WPGraphQL::get_app_context()->getLoader( $loader_name );
		$loader->clearAll();
	}
}
