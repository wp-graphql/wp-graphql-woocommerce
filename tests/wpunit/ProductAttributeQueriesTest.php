<?php

class ProductAttributeQueriesTest extends \Codeception\TestCase\WPTestCase {
	private $shop_manager;
	private $customer;
	private $helper;
	private $variation_helper;
	private $product_id;
	private $variation_ids;

	public function setUp(): void {
		parent::setUp();

		$this->shop_manager     = $this->factory->user->create( [ 'role' => 'shop_manager' ] );
		$this->customer         = $this->factory->user->create( [ 'role' => 'customer' ] );
		$this->helper           = $this->getModule( '\Helper\Wpunit' )->product();
		$this->variation_helper = $this->getModule( '\Helper\Wpunit' )->product_variation();
		$this->product_id       = $this->helper->create_variable();
		$this->variation_ids    = $this->variation_helper->create( $this->product_id )['variations'];

		\WPGraphQL::clear_schema();
	}

	// tests
	public function testProductAttributeQuery() {
		$query = '
            query attributeQuery( $id: ID! ) {
                product( id: $id ) {
                    ... on VariableProduct {
                        id
                        attributes {
                            nodes {
                                id
                                attributeId
								name
								label
                                options
                                position
                                visible
                                variation
                            }
                        }
                    }
                }
            }
        ';

		$variables = [ 'id' => $this->helper->to_relay_id( $this->product_id ) ];
		$actual    = graphql(
			[
				'query'          => $query,
				'operation_name' => 'attributeQuery',
				'variables'      => $variables,
			]
		);
		$expected  = [
			'product' => [
				'id'         => $this->helper->to_relay_id( $this->product_id ),
				'attributes' => $this->helper->print_attributes( $this->product_id ),
			],
		];

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual['data'] );
	}

	public function testProductAttributeToProductConnectionQuery() {
		$query = '
            query attributeConnectionQuery( $color: [String!] ) {
                allPaColor( where: { name: $color } ) {
                    nodes {
                        products {
                            nodes {
                                ... on VariableProduct {
                                    id
                                }
                            }
                        }
                    }
                }
            }
        ';

		$variables = [ 'color' => 'red' ];
		$actual    = graphql(
			[
				'query'          => $query,
				'operation_name' => 'attributeConnectionQuery',
				'variables'      => $variables,
			]
		);
		$expected  = [
			'allPaColor' => [
				'nodes' => [
					[
						'products' => [
							'nodes' => [
								[
									'id' => $this->helper->to_relay_id( $this->product_id ),
								],
							],
						],
					],
				],
			],
		];

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual['data'] );
	}

	public function testProductAttributeToVariationConnectionQuery() {
		$query = '
            query attributeConnectionQuery( $size: [String!] ) {
                allPaSize( where: { name: $size } ) {
                    nodes {
                        variations {
                            nodes {
                                id
                            }
                        }
                    }
                }
            }
        ';

		$variables = [ 'size' => 'small' ];
		$actual    = graphql(
			[
				'query'          => $query,
				'operation_name' => 'attributeConnectionQuery',
				'variables'      => $variables,
			]
		);
		$expected  = [
			'allPaSize' => [
				'nodes' => [
					[
						'variations' => [
							'nodes' => $this->variation_helper->print_nodes(
								$this->variation_ids,
								[
									'filter' => function( $id ) {
										$variation       = new \WC_Product_Variation( $id );
										$small_attribute = array_filter(
											$variation->get_attributes(),
											function( $attribute ) {
												return 'small' === $attribute;
											}
										);
										return ! empty( $small_attribute );
									},
								]
							),
						],
					],
				],
			],
		];

		// use --debug flag to view.
		codecept_debug( $actual );

		$this->assertEquals( $expected, $actual['data'] );
	}
}
