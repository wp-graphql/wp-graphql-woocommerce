<?php

class DownloadableItemQueriesTest extends \Codeception\TestCase\WPTestCase
{

    public function setUp() {
        // before
        parent::setUp();

        // your set up methods here
        $this->customers = $this->getModule('\Helper\Wpunit')->customer();
        $this->orders    = $this->getModule('\Helper\Wpunit')->order();
        $this->products  = $this->getModule('\Helper\Wpunit')->product();

        update_option( 'woocommerce_downloads_grant_access_after_payment', 'yes' );
        $this->customer  = $this->customers->create();
    }

    public function tearDown() {
        // your tear down methods here

        // then
        parent::tearDown();
    }

    public function set_user( $user ) {
		wp_set_current_user( $user );
		WC()->customer = new WC_Customer( get_current_user_id(), true );
	}

    // tests
    public function testOrderToDownloadableItemsQuery() {
        $downloadable_product = $this->products->create_simple(
            array(
                'downloadable' => true,
                'downloads'    => array( $this->products->create_download() )
            )
        );
        $order_id = $this->orders->create(
            array(
                'status'      => 'completed',
                'customer_id' => $this->customer,
            ),
            array(
                'line_items' => array(
                    array(
                        'product' => $downloadable_product,
                        'qty'     => 1,
                    ),
                ),
            )
        );

        // Force download permission updated.
        wc_downloadable_product_permissions( $order_id, true );

        $query = '
            query {
                customer {
                    orders {
                        nodes {
                            downloadableItems(first: 1) {
                                nodes {
                                    url
                                    accessExpires
                                    downloadId
                                    downloadsRemaining
                                    name
                                    product {
                                        databaseId
                                    }
                                    download {
                                        downloadId
                                    }
                                }
                            }
                        }
                    }
                }
            }
        ';

        /**
		 * Assertion One
		 *
		 * tests query results
		 */
		$this->set_user( $this->customer );
		$actual   = graphql( array( 'query' => $query ) );
		$expected = array(
            'data' => array(
                'customer' => array(
                    'orders' => array(
                        'nodes' => array(
                            array(
                                'downloadableItems' => array( 'nodes' => $this->orders->print_downloadables( $order_id ) ),
                            ),
                        ),
                    ),
                ),
            ),
        );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );
    }


    public function testOrderToDownloadableItemsQueryArgs() {
        $valid_product        = $this->products->create_simple(
            array(
                'downloadable' => true,
                'downloads'    => array( $this->products->create_download() )
            )
        );
        $downloadable_product = $this->products->create_simple(
            array(
                'download_expiry' => 5,
                'download_limit'  => 3,
                'downloadable'    => true,
                'downloads'       => array( $this->products->create_download() )
            )
        );
        $downloaded_product   = $this->products->create_simple(
            array(
                'download_limit' => 0,
                'downloadable'   => true,
                'downloads'      => array( $this->products->create_download() )
            )
        );

        $order_id = $this->orders->create(
            array(
                'status'      => 'completed',
                'customer_id' => $this->customer,
            ),
            array(
                'line_items' => array(
                    array(
                        'product' => $valid_product,
                        'qty'     => 1,
                    ),
                    array(
                        'product' => $downloadable_product,
                        'qty'     => 1,
                    ),
                    array(
                        'product' => $downloaded_product,
                        'qty'     => 1,
                    ),
                ),
            )
        );

        // Force download permission updated.
        wc_downloadable_product_permissions( $order_id, true );

        $query = '
            query($input: OrderToDownloadableItemConnectionWhereArgs) {
                customer {
                    orders {
                        nodes {
                            downloadableItems(where: $input) {
                                nodes {
                                    product {
                                        databaseId
                                    }
                                }
                            }
                        }
                    }
                }
            }
        ';

        /**
		 * Assertion One
		 *
		 * tests "active" whereArg
		 */
		$this->set_user( $this->customer );
		$actual   = graphql(
            array(
                'query'     => $query,
                'variables' => array( 'input' => array( 'active' => true ) ),
            )
        );
		$expected = array(
            'data' => array(
                'customer' => array(
                    'orders' => array(
                        'nodes' => array(
                            array(
                                'downloadableItems' => array(
                                    'nodes' => array_map(
                                        function( $product_id ) {
                                            return array( 'product' => array( 'databaseId' => $product_id ) );
                                        },
                                        array( $valid_product, $downloadable_product )
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        );

        // use --debug flag to view.
		codecept_debug( $actual );

        $this->assertEquals( $expected, $actual );

        /**
		 * Assertion Two
		 *
		 * tests "active" whereArg reversed
		 */
        $actual   = graphql(
            array(
                'query'     => $query,
                'variables' => array( 'input' => array( 'active' => false ) ),
            )
        );
		$expected = array(
            'data' => array(
                'customer' => array(
                    'orders' => array(
                        'nodes' => array(
                            array(
                                'downloadableItems' => array(
                                    'nodes' => array_map(
                                        function( $product_id ) {
                                            return array( 'product' => array( 'databaseId' => $product_id ) );
                                        },
                                        array( $downloaded_product )
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        );

        // use --debug flag to view.
		codecept_debug( $actual );

        $this->assertEquals( $expected, $actual );

        /**
		 * Assertion Three
		 *
		 * tests "hasDownloadsRemaining" whereArg
		 */
		$actual   = graphql(
            array(
                'query'     => $query,
                'variables' => array( 'input' => array( 'hasDownloadsRemaining' => true ) )
            )
        );
		$expected = array(
            'data' => array(
                'customer' => array(
                    'orders' => array(
                        'nodes' => array(
                            array(
                                'downloadableItems' => array(
                                    'nodes' => array_map(
                                        function( $product_id ) {
                                            return array( 'product' => array( 'databaseId' => $product_id ) );
                                        },
                                        array( $valid_product, $downloadable_product )
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        );

        // use --debug flag to view.
		codecept_debug( $actual );

        $this->assertEquals( $expected, $actual );

        /**
		 * Assertion Four
		 *
		 * tests "hasDownloadsRemaining" whereArg reversed
		 */
        $actual   = graphql(
            array(
                'query'     => $query,
                'variables' => array( 'input' => array( 'hasDownloadsRemaining' => false ) )
            )
        );
		$expected = array(
            'data' => array(
                'customer' => array(
                    'orders' => array(
                        'nodes' => array(
                            array(
                                'downloadableItems' => array(
                                    'nodes' => array_map(
                                        function( $product_id ) {
                                            return array( 'product' => array( 'databaseId' => $product_id ) );
                                        },
                                        array( $downloaded_product )
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        );

        // use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );
    }

    public function testCustomerToDownloadableItemsQuery() {
        $downloadable_product = $this->products->create_simple(
            array(
                'downloadable' => true,
                'downloads'    => array( $this->products->create_download() )
            )
        );
        $order_id = $this->orders->create(
            array(
                'status'      => 'completed',
                'customer_id' => $this->customer,
            ),
            array(
                'line_items' => array(
                    array(
                        'product' => $downloadable_product,
                        'qty'     => 1,
                    ),
                ),
            )
        );

        // Force download permission updated.
        wc_downloadable_product_permissions( $order_id, true );

        $query = '
            query {
                customer {
                    downloadableItems(first: 1) {
                        nodes {
                            url
                            accessExpires
                            downloadId
                            downloadsRemaining
                            name
                            product {
                                databaseId
                            }
                            download {
                                downloadId
                            }
                        }
                    }
                }
            }
        ';

        /**
		 * Assertion One
		 *
		 * tests query results
		 */
		$this->set_user( $this->customer );
		$actual   = graphql( array( 'query' => $query ) );
		$expected = array(
            'data' => array(
                'customer' => array(
                    'downloadableItems' => array( 'nodes' => $this->customers->print_downloadables( $this->customer ) ),
                ),
            ),
        );

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual );
    }

}
