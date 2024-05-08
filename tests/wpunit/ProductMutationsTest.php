<?php

class ProductMutationsTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {
    public function testCreateProduct() {
        $query = '
            mutation ( $input: CreateProductInput! ) {
                createProduct(input: $input) {
                    product {
                        id
                        name
                        type
                        dateOnSaleFrom
                        dateOnSaleTo
                        ... on ProductWithPricing {
                            price
                            regularPrice
                            salePrice
                        }
                        ... on InventoriedProduct {
                            stockQuantity
                        }
                        attributes {
                            nodes {
                                id
                                name
                                label
                                options
                            }
                        }
                    }
                }
            }
        ';

        $variables = [
            'input' => [
                'name'           => 'Test Product',
                'type'           => 'SIMPLE',
                'regularPrice'   => 10,
                'salePrice'      => 7,
                'dateOnSaleFrom' => date( 'Y-m-d H:i:s', strtotime( '-1 day' ) ),
                'dateOnSaleTo'   => date( 'Y-m-d H:i:s', strtotime( '+1 day' ) ),
                'manageStock'    => true,
                'stockStatus'    => 'IN_STOCK',
                'stockQuantity'  => 3,
            ]
        ];
        
        // Assert mutation fails as unauthenticated user.
        $response  = $this->graphql( compact( 'query', 'variables' ) );
        $this->assertQueryError( $response );


        // Assert mutation fails as authenticated user without proper capabilities.
        $this->loginAsCustomer();
        $response  = $this->graphql( compact( 'query', 'variables' ) );
        $this->assertQueryError( $response );

        // Assert mutation succeeds as authenticated user with proper capabilities.
        $this->loginAsShopManager();
        $response  = $this->graphql( compact( 'query', 'variables' ) );
        $expected  = [
            $this->expectedObject(
                'createProduct.product',
                [
                    $this->expectedField( 'id', self::NOT_NULL ),
                    $this->expectedField( 'name', 'Test Product' ),
                    $this->expectedField( 'type', 'SIMPLE' ),
                    $this->expectedField( 'price', "$7.00" ),
                    $this->expectedField( 'regularPrice', "$10.00" ),
                    $this->expectedField( 'salePrice', "$7.00" ),
                    $this->expectedField( 'stockQuantity', 3 ),
                ]
            ),
        ];

        $this->assertQuerySuccessful( $response, $expected );
    }

    public function testCreateProductWithAttributes() {
        $query = '
            mutation ( $input: CreateProductInput! ) {
                createProduct(input: $input) {
                    product {
                        id
                        name
                        type
                        attributes {
                            nodes {
                                id
                                name
                                label
                                options
                            }
                        }
                    }
                }
            }
        ';

        $kind_attribute = $this->factory()->product->createAttribute( 'kind', [ 'normal', 'special' ] );
        
        $this->loginAsShopManager();
        $variables = [
            'input' => [
                'name'           => 'Test Product',
                'type'           => 'SIMPLE',
                'regularPrice'   => 10,
                'attributes'     => [
                    [
                        'id'      => $kind_attribute['attribute_id'],
                        'name'    => $kind_attribute['attribute_name'],
                        'options' => [ 'special' ],
                    ],
                ],
            ]
        ];
        $response  = $this->graphql( compact( 'query', 'variables' ) );
        $expected  = [
            $this->expectedObject(
                'createProduct.product',
                [
                    $this->expectedField( 'id', self::NOT_NULL ),
                    $this->expectedField( 'name', 'Test Product' ),
                    $this->expectedField( 'type', 'SIMPLE' ),
                    $this->expectedObject(
                        'attributes',
                        [
                            $this->expectedNode(
                                'nodes',
                                [
                                    $this->expectedField( 'id', self::NOT_NULL ),
                                    $this->expectedField( 'name', 'pa_kind' ),
                                    $this->expectedField( 'label', 'Kind' ),
                                    $this->expectedField( 'options', [ 'special' ] ),
                                ],
                                0
                            ),
                        ]
                    ),
                ]
            ),
        ];

        $this->assertQuerySuccessful( $response, $expected );
    }

    public function testCreatingVariableProductWithCreateProduct() {
        $query = '
            mutation ( $input: CreateProductInput! ) {
                createProduct(input: $input) {
                    product {
                        id
                        name
                        type
                        ... on VariableProduct {
                            attributes {
                                nodes {
                                    id
                                    name
                                    label
                                    options
                                    variation
                                    scope
                                }
                            }
                            defaultAttributes {
                                nodes {
                                    id
                                    name
                                    label
                                    value
                                }
                            }
                        }
                    }
                }
            }
        ';

        $kind_attribute = $this->factory()->product->createAttribute( 'kind', [ 'normal', 'special' ] );
        
        $this->loginAsShopManager();
        $variables = [
            'input' => [
                'name'             => 'Test Product',
                'type'             => 'VARIABLE',
                'attributes'       => [
                    [
                        'id'        => $kind_attribute['attribute_id'],
                        'name'      => $kind_attribute['attribute_name'],
                        'options'   => [ 'normal', 'special',  ],
                        'variation' => true,
                    ],
                    [
                        'name'      => 'logo',
                        'options'   => [ 'yes', 'no' ],
                        'variation' => true,
                    ],
                ],
                'defaultAttributes' => [
                    [
                        'id'             => $kind_attribute['attribute_id'],
                        'attributeName'  => $kind_attribute['attribute_name'],
                        'attributeValue' => 'special',
                    ],
                    [
                        'attributeName'  => 'logo',
                        'attributeValue' => 'yes',
                    ]
                ],
            ],
        ];
        $response  = $this->graphql( compact( 'query', 'variables' ) );
        $expected  = [
            $this->expectedObject(
                'createProduct.product',
                [
                    $this->expectedField( 'id', self::NOT_NULL ),
                    $this->expectedField( 'name', 'Test Product' ),
                    $this->expectedField( 'type', 'VARIABLE' ),
                    $this->expectedObject(
                        'attributes',
                        [
                            $this->expectedNode(
                                'nodes',
                                [
                                    $this->expectedField( 'id', self::NOT_NULL ),
                                    $this->expectedField( 'name', 'pa_kind' ),
                                    $this->expectedField( 'label', 'Kind' ),
                                    $this->expectedField( 'options', [ 'normal', 'special' ] ),
                                    $this->expectedField( 'variation', true ),
                                    $this->expectedField( 'scope', 'GLOBAL' ),
                                ],
                            ),
                            $this->expectedNode(
                                'nodes',
                                [
                                    $this->expectedField( 'id', self::NOT_NULL ),
                                    $this->expectedField( 'name', 'logo' ),
                                    $this->expectedField( 'label', 'Logo' ),
                                    $this->expectedField( 'options', [ 'yes', 'no' ] ),
                                    $this->expectedField( 'variation', true ),
                                    $this->expectedField( 'scope', 'LOCAL' ),
                                ],
                            ),
                        ]
                    ),
                    $this->expectedObject(
                        'defaultAttributes',
                        [
                            $this->expectedNode(
                                'nodes',
                                [
                                    $this->expectedField( 'id', self::NOT_NULL ),
                                    $this->expectedField( 'name', 'pa_kind' ),
                                    $this->expectedField( 'label', 'Kind' ),
                                    $this->expectedField( 'value', 'special' ),
                                ],
                            ),
                            $this->expectedNode(
                                'nodes',
                                [
                                    $this->expectedField( 'id', self::NOT_NULL ),
                                    $this->expectedField( 'name', 'logo' ),
                                    $this->expectedField( 'label', 'Logo' ),
                                    $this->expectedField( 'value', 'yes' ),
                                ],
                            ),
                        ]
                    ),
                ]
            ),
        ];

        $this->assertQuerySuccessful( $response, $expected );
    }

    public function testCreatingExternalProductWithCreateProduct() {
        $query = '
            mutation ( $input: CreateProductInput! ) {
                createProduct(input: $input) {
                    product {
                        id
                        name
                        type
                        ... on ExternalProduct {
                            externalUrl
                            buttonText
                        }
                    }
                }
            }
        ';

        $this->loginAsShopManager();
        $variables = [
            'input' => [
                'name'        => 'Test Product',
                'type'        => 'EXTERNAL',
                'externalUrl' => 'https://example.com',
                'buttonText'  => 'Buy Now',
            ]
        ];
        $response  = $this->graphql( compact( 'query', 'variables' ) );
        $expected  = [
            $this->expectedObject(
                'createProduct.product',
                [
                    $this->expectedField( 'id', self::NOT_NULL ),
                    $this->expectedField( 'name', 'Test Product' ),
                    $this->expectedField( 'type', 'EXTERNAL' ),
                    $this->expectedField( 'externalUrl', 'https://example.com' ),
                    $this->expectedField( 'buttonText', 'Buy Now' ),
                ]
            ),
        ];

        $this->assertQuerySuccessful( $response, $expected );
    }

    public function testCreatingGroupProductWithCreateProduct() {
        $product_ids = [
            $this->factory()->product->createSimple( [ 'name' => 'Product 1' ] ),
            $this->factory()->product->createSimple( [ 'name' => 'Product 2' ] ),
        ];

        $query = '
            mutation ( $input: CreateProductInput! ) {
                createProduct(input: $input) {
                    product {
                        id
                        name
                        type
                        ... on GroupProduct {
                            products {
                                nodes {
                                    id
                                    name
                                }
                            }
                        }
                    }
                }
            }
        ';

        $this->loginAsShopManager();
        $variables = [
            'input' => [
                'name'            => 'Test Product',
                'type'            => 'GROUPED',
                'groupedProducts' => $product_ids,
            ]
        ];
        $response  = $this->graphql( compact( 'query', 'variables' ) );
        $expected  = [
            $this->expectedObject(
                'createProduct.product',
                [
                    $this->expectedField( 'id', self::NOT_NULL ),
                    $this->expectedField( 'name', 'Test Product' ),
                    $this->expectedField( 'type', 'GROUPED' ),
                    $this->expectedField( 'products.nodes.#.name', 'Product 1' ),
                    $this->expectedField( 'products.nodes.#.name', 'Product 2' ),
                ]
            ),
        ];

        $this->assertQuerySuccessful( $response, $expected );
    }

    public function testUpdateProduct() {
        $product_id = $this->factory()->product->createSimple(
            [
                'name'           => 'Test Product',
                'regular_price'  => 10,
                'sale_price'     => 7,
                'manage_stock'   => true,
                'stock_status'   => 'instock',
                'stock_quantity' => 3,
            ]
        );

        $query = '
            mutation ( $input: UpdateProductInput! ) {
                updateProduct(input: $input) {
                    product {
                        id
                        name
                        type
                        ... on ProductWithPricing {
                            price
                            regularPrice
                            salePrice
                        }
                        ... on InventoriedProduct {
                            stockQuantity
                        }
                        attributes {
                            nodes {
                                id
                                name
                                label
                                options
                            }
                        }
                    }
                }
            }
        ';

        $variables = [
            'input' => [
                'id'            => $product_id,
                'name'          => 'Updated Product',
                'type'          => 'SIMPLE',
                'regularPrice'  => 19.99,
                'salePrice'     => 14.99,
                'stockQuantity' => 5,
            ]
        ];
        // Assert mutation fails as unauthenticated user.
        $response  = $this->graphql( compact( 'query', 'variables' ) );
        $this->assertQueryError( $response );


        // Assert mutation fails as authenticated user without proper capabilities.
        $this->loginAsCustomer();
        $response  = $this->graphql( compact( 'query', 'variables' ) );
        $this->assertQueryError( $response );

        // Assert mutation succeeds as authenticated user with proper capabilities.
        $this->loginAsShopManager();
        $response  = $this->graphql( compact( 'query', 'variables' ) );
        $expected  = [
            $this->expectedObject(
                'updateProduct.product',
                [
                    $this->expectedField( 'id', self::NOT_NULL ),
                    $this->expectedField( 'name', 'Updated Product' ),
                    $this->expectedField( 'type', 'SIMPLE' ),
                    $this->expectedField( 'price', "$14.99" ),
                    $this->expectedField( 'regularPrice', "$19.99" ),
                    $this->expectedField( 'salePrice', "$14.99" ),
                    $this->expectedField( 'stockQuantity', 5 ),
                ]
            ),
        ];

        $this->assertQuerySuccessful( $response, $expected );
    }

    public function testDeleteProduct() {
        $product_id = $this->factory()->product->createSimple([
            'name'          => 'Test Product',
            'regular_price'  => 10,
            'sale_price'     => 7,
            'manage_stock'   => true,
            'stock_status'   => 'instock',
            'stock_quantity' => 3,
        ]);

        $query = '
            mutation ( $input: DeleteProductInput! ) {
                deleteProduct(input: $input) {
                    product {
                        id
                        name
                        type
                        ... on ProductWithPricing {
                            price
                            regularPrice
                            salePrice
                        }
                        ... on InventoriedProduct {
                            stockQuantity
                        }
                        attributes {
                            nodes {
                                id
                                name
                                label
                                options
                            }
                        }
                    }
                }
            }
        ';

        $variables = [
            'input' => [ 
                'id'    => $product_id,
                'force' => true,
            ],
        ];

        // Assert mutation fails as unauthenticated user.
        $response  = $this->graphql( compact( 'query', 'variables' ) );
        $this->assertQueryError( $response );


        // Assert mutation fails as authenticated user without proper capabilities.
        $this->loginAsCustomer();
        $response  = $this->graphql( compact( 'query', 'variables' ) );
        $this->assertQueryError( $response );

        // Assert mutation succeeds as authenticated user with proper capabilities.
        $this->loginAsShopManager();
        $response  = $this->graphql( compact( 'query', 'variables' ) );
        $expected  = [
            $this->expectedObject(
                'deleteProduct.product',
                [
                    $this->expectedField( 'id', self::NOT_NULL ),
                    $this->expectedField( 'name', 'Test Product' ),
                    $this->expectedField( 'type', 'SIMPLE' ),
                    $this->expectedField( 'price', "$7.00" ),
                    $this->expectedField( 'regularPrice', "$10.00" ),
                    $this->expectedField( 'salePrice', "$7.00" ),
                    $this->expectedField( 'stockQuantity', 3 ),
                ]
            ),
        ];
        $this->assertQuerySuccessful( $response, $expected );

        // Assert product is deleted.
        $product = wc_get_product( $product_id );
        $this->assertFalse( $product );
    }
}