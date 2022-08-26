<?php

class DownloadableItemQueriesTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {

	public function setUp(): void {
		// before
		parent::setUp();

		update_option( 'woocommerce_downloads_grant_access_after_payment', 'yes' );
	}

	// tests
	public function testOrderToDownloadableItemsQuery() {
		$downloadable_product = $this->factory->product->createSimple(
			[
				'downloadable' => true,
				'downloads'    => [ $this->factory->product->createDownload() ],
			]
		);
		$order_id             = $this->factory->order->createNew(
			[
				'status'      => 'completed',
				'customer_id' => $this->customer,
			],
			[
				'line_items' => [
					[
						'product' => $downloadable_product,
						'qty'     => 1,
					],
				],
			]
		);

		// Force download permission updated.
		wc_downloadable_product_permissions( $order_id, true );

		$order              = wc_get_order( $order_id );
		$downloadable_items = $order->get_downloadable_items();

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
		 * Tests query results
		 */
		$this->loginAsCustomer();
		$response = $this->graphql( compact( 'query' ) );
		$expected = array_map(
			function( $item ) {
				return $this->expectedNode(
					'customer.orders.nodes',
					[
						$this->expectedNode(
							'downloadableItems.nodes',
							[
								$this->expectedField( 'url', $item['download_url'] ),
								$this->expectedField( 'accessExpires', $item['access_expires'] ),
								$this->expectedField( 'downloadId', $item['download_id'] ),
								$this->expectedField(
									'downloadsRemaining',
									isset( $item['downloads_remaining'] ) && 'integer' === gettype( $item['downloads_remaining'] )
										? $item['downloads_remaining']
										: self::IS_NULL
								),
								$this->expectedField( 'name', $item['download_name'] ),
								$this->expectedField( 'product.databaseId', $item['product_id'] ),
								$this->expectedField( 'download.downloadId', $item['download_id'] ),
							]
						),
					],
					0
				);
			},
			$downloadable_items
		);

		$this->assertQuerySuccessful( $response, $expected );
	}


	public function testOrderToDownloadableItemsQueryArgs() {
		$valid_product        = $this->factory->product->createSimple(
			[
				'downloadable' => true,
				'downloads'    => [ $this->factory->product->createDownload() ],
			]
		);
		$downloadable_product = $this->factory->product->createSimple(
			[
				'download_expiry' => 5,
				'download_limit'  => 3,
				'downloadable'    => true,
				'downloads'       => [ $this->factory->product->createDownload() ],
			]
		);
		$downloaded_product   = $this->factory->product->createSimple(
			[
				'download_limit' => 0,
				'downloadable'   => true,
				'downloads'      => [ $this->factory->product->createDownload() ],
			]
		);

		$order_id = $this->factory->order->createNew(
			[
				'status'      => 'completed',
				'customer_id' => $this->customer,
			],
			[
				'line_items' => [
					[
						'product' => $valid_product,
						'qty'     => 1,
					],
					[
						'product' => $downloadable_product,
						'qty'     => 1,
					],
					[
						'product' => $downloaded_product,
						'qty'     => 1,
					],
				],
			]
		);

		// Force download permission updated.
		wc_downloadable_product_permissions( $order_id, true );

		$query = '
            query($active: Boolean, $hasDownloadsRemaining: Boolean) {
                customer {
                    orders {
                        nodes {
                            downloadableItems(where: { active: $active, hasDownloadsRemaining: $hasDownloadsRemaining }) {
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
		 * Tests "active" whereArg
		 */
		$this->loginAsCustomer();
		$variables = [ 'active' => true ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedNode(
				'customer.orders.nodes',
				[
					$this->expectedNode(
						'downloadableItems.nodes',
						[ $this->expectedField( 'product.databaseId', $valid_product ) ]
					),
					$this->expectedNode(
						'downloadableItems.nodes',
						[ $this->expectedField( 'product.databaseId', $downloadable_product ) ]
					),
					$this->not()->expectedNode(
						'downloadableItems.nodes',
						[ $this->expectedField( 'product.databaseId', $downloaded_product ) ]
					),
				],
				0
			),
		];

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Two
		 *
		 * Tests "active" whereArg reversed
		 */
		$variables = [ 'active' => false ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedNode(
				'customer.orders.nodes',
				[
					$this->expectedNode(
						'downloadableItems.nodes',
						[ $this->expectedField( 'product.databaseId', $downloaded_product ) ]
					),
					$this->not()->expectedNode(
						'downloadableItems.nodes',
						[ $this->expectedField( 'product.databaseId', $valid_product ) ]
					),
					$this->not()->expectedNode(
						'downloadableItems.nodes',
						[ $this->expectedField( 'product.databaseId', $downloadable_product ) ]
					),
				],
				0
			),
		];

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Three
		 *
		 * Tests "hasDownloadsRemaining" whereArg
		 */
		$variables = [ 'hasDownloadsRemaining' => true ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedNode(
				'customer.orders.nodes',
				[
					$this->not()->expectedNode(
						'downloadableItems.nodes',
						[ $this->expectedField( 'product.databaseId', $downloaded_product ) ]
					),
					$this->expectedNode(
						'downloadableItems.nodes',
						[ $this->expectedField( 'product.databaseId', $valid_product ) ]
					),
					$this->expectedNode(
						'downloadableItems.nodes',
						[ $this->expectedField( 'product.databaseId', $downloadable_product ) ]
					),
				],
				0
			),
		];

		$this->assertQuerySuccessful( $response, $expected );

		/**
		 * Assertion Four
		 *
		 * Tests "hasDownloadsRemaining" whereArg reversed
		 */
		$variables = [ 'hasDownloadsRemaining' => false ];
		$response  = $this->graphql( compact( 'query', 'variables' ) );
		$expected  = [
			$this->expectedNode(
				'customer.orders.nodes',
				[
					$this->expectedNode(
						'downloadableItems.nodes',
						[ $this->expectedField( 'product.databaseId', $downloaded_product ) ]
					),
					$this->not()->expectedNode(
						'downloadableItems.nodes',
						[ $this->expectedField( 'product.databaseId', $valid_product ) ]
					),
					$this->not()->expectedNode(
						'downloadableItems.nodes',
						[ $this->expectedField( 'product.databaseId', $downloadable_product ) ]
					),
				],
				0
			),
		];

		$this->assertQuerySuccessful( $response, $expected );
	}

	public function testCustomerToDownloadableItemsQuery() {
		$downloadable_product = $this->factory->product->createSimple(
			[
				'downloadable' => true,
				'downloads'    => [ $this->factory->product->createDownload() ],
			]
		);
		$order_id             = $this->factory->order->createNew(
			[
				'status'      => 'completed',
				'customer_id' => $this->customer,
			],
			[
				'line_items' => [
					[
						'product' => $downloadable_product,
						'qty'     => 1,
					],
				],
			]
		);

		// Force download permission updated.
		wc_downloadable_product_permissions( $order_id, true );

		$downloadable_items = \wc_get_customer_available_downloads( $this->customer );

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
		 * Tests query results
		 */
		$this->loginAsCustomer();
		$response = $this->graphql( compact( 'query' ) );
		$expected = array_map(
			function( $item ) {
				return $this->expectedNode(
					'customer.downloadableItems.nodes',
					[
						$this->expectedField( 'url', $item['download_url'] ),
						$this->expectedField(
							'accessExpires',
							! empty( $item['access_expires'] ) ? $item['access_expires'] : self::IS_NULL
						),
						$this->expectedField( 'downloadId', $item['download_id'] ),
						$this->expectedField(
							'downloadsRemaining',
							isset( $item['downloads_remaining'] ) && 'integer' === gettype( $item['downloads_remaining'] )
								? $item['downloads_remaining']
								: self::IS_NULL
						),
						$this->expectedField( 'name', $item['download_name'] ),
						$this->expectedField( 'product.databaseId', $item['product_id'] ),
						$this->expectedField( 'download.downloadId', $item['download_id'] ),
					],
					0
				);
			},
			$downloadable_items
		);

		$this->assertQuerySuccessful( $response, $expected );
	}
}
