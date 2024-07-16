<?php

class ShippingZoneMutationsTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {
    public function testCreateShippingZoneMutation() {
        // Prepare the request.
        $query = 'mutation ($input: CreateShippingZoneInput!) {
            createShippingZone(input: $input) {
                shippingZone {
                    id
                    name
                    order
                }
            }
        }';

        // Prepare the variables.
        $variables = [
            'input' => [
                'name'  => 'Test Shipping Zone',
                'order' => 0
            ]
        ];

        // Execute the request.
        $response = $this->graphql( compact( 'query', 'variables' ) );

        // Confirm permissions error.
        $this->assertQueryError( $response );

        // Login as admin and re-execute expecting success.
        $this->loginAsShopManager();
        $response = $this->graphql( compact( 'query', 'variables' ) );
        $expected = [
            $this->expectedObject(
                'createShippingZone.shippingZone',
                [
                    $this->expectedField( 'id', static::NOT_NULL ),
                    $this->expectedField( 'name', 'Test Shipping Zone' ),
                    $this->expectedField( 'order', 0 ),
                ]
            ),
        ];

        // Validate the response.
        $this->assertQuerySuccessful( $response, $expected );
    }

    public function testUpdateShippingZoneMutation() {
        // Create a shipping zone.
        $shipping_zone_id = $this->factory->shipping_zone->create(
            [
                'zone_name' => 'Test Shipping Zone',
                'zone_order' => 0,
            ]
        );

        // Prepare the request.
        $query = 'mutation ($input: UpdateShippingZoneInput!) {
            updateShippingZone(input: $input) {
                shippingZone {
                    id
                    databaseId
                    name
                    order
                }
            }
        }';

        // Prepare the variables.
        $variables = [
            'input' => [
                'id'    => $shipping_zone_id,
                'name'  => 'Updated Shipping Zone',
                'order' => 1
            ]
        ];

        // Execute the request.
        $response = $this->graphql( compact( 'query', 'variables' ) );

        // Confirm permissions error.
        $this->assertQueryError( $response );

        // Login as admin and re-execute expecting success.
        $this->loginAsShopManager();
        $response = $this->graphql( compact( 'query', 'variables' ) );
        $expected = [
            $this->expectedObject(
                'updateShippingZone.shippingZone',
                [
                    $this->expectedField( 'id', $this->toRelayId( 'shipping_zone', $shipping_zone_id ) ),
                    $this->expectedField( 'databaseId', $shipping_zone_id ),
                    $this->expectedField( 'name', 'Updated Shipping Zone' ),
                    $this->expectedField( 'order', 1 ),
                ]
            ),
        ];

        // Validate the response.
        $this->assertQuerySuccessful( $response, $expected );
    }

    public function testDeleteShippingZoneMutation() {
        // Create a shipping zone.
        $shipping_zone = $this->factory->shipping_zone->create_and_get();

        // Prepare the request.
        $query = 'mutation ($input: DeleteShippingZoneInput!) {
            deleteShippingZone(input: $input) {
                shippingZone {
                    id
                    databaseId
                    name
                    order
                }
            }
        }';

        // Prepare the variables.
        $variables = [
            'input' => [
                'id' => $shipping_zone->get_id()
            ]
        ];

        // Execute the request.
        $response = $this->graphql( compact( 'query', 'variables' ) );

        // Confirm permissions error.
        $this->assertQueryError( $response );

        // Login as admin and re-execute expecting success.
        $this->loginAsShopManager();
        $response = $this->graphql( compact( 'query', 'variables' ) );
        $expected = [
            $this->expectedObject(
                'deleteShippingZone.shippingZone',
                [
                    $this->expectedField( 'id', $this->toRelayId( 'shipping_zone', $shipping_zone->get_id() ) ),
                    $this->expectedField( 'databaseId', $shipping_zone->get_id() ),
                    $this->expectedField( 'name', $shipping_zone->get_zone_name() ),
                    $this->expectedField( 'order', $shipping_zone->get_zone_order() ),
                ]
            ),
        ];

        // Validate the response.
        $this->assertQuerySuccessful( $response, $expected );
    }

    public function testUpdateShippingZoneLocationsMutation() {
        // Create a shipping zone.
        $shipping_zone_id = $this->factory->shipping_zone->create();

        // Prepare the request.
        $query = 'mutation ($input: UpdateShippingZoneLocationsInput!) {
            updateShippingZoneLocations(input: $input) {
                shippingZone {
                    id
                    locations {
                        code
                        type
                    }
                }
                locations {
                    code
                    type
                }
            }
        }';

        // Prepare the variables.
        $variables = [
            'input' => [
                'zoneId'    => $shipping_zone_id,
                'locations' => [
                    [
                        'code' => 'US',
                    ],
                    [
                        'code' => 'CALIFORNIA',
                        'type' => 'STATE',
                    ],
                    [
                        'code' => '12345',
                        'type' => 'POSTCODE',
                    ],
                    [
                        'code' => 'NA',
                        'type' => 'CONTINENT',
                    ],
                ]
            ]
        ];

        // Execute the request.
        $response = $this->graphql( compact( 'query', 'variables' ) );

        // Confirm permissions error.
        $this->assertQueryError( $response );

        // Login as admin and re-execute expecting success.
        $this->loginAsShopManager();
        $response = $this->graphql( compact( 'query', 'variables' ) );
        $expected = [
            $this->expectedObject(
                'updateShippingZoneLocations.shippingZone',
                [
                    $this->expectedField( 'id', $this->toRelayId( 'shipping_zone', $shipping_zone_id ) ),
                    $this->expectedObject(
                        'locations.0',
                        [
                            $this->expectedField( 'code', 'US' ),
                            $this->expectedField( 'type', 'COUNTRY' )
                        ]
                    ),
                    $this->expectedObject(
                        'locations.1',
                        [
                            $this->expectedField( 'code', 'CALIFORNIA' ),
                            $this->expectedField( 'type', 'STATE' )
                        ]
                    ),
                    $this->expectedObject(
                        'locations.2',
                        [
                            $this->expectedField( 'code', '12345' ),
                            $this->expectedField( 'type', 'POSTCODE' )
                        ]
                    ),
                    $this->expectedObject(
                        'locations.3',
                        [
                            $this->expectedField( 'code', 'NA' ),
                            $this->expectedField( 'type', 'CONTINENT' )
                        ]
                    )
                ]
            ),
            $this->expectedObject(
                'updateShippingZoneLocations.locations.0',
                [
                    $this->expectedField( 'code', 'US' ),
                    $this->expectedField( 'type', 'COUNTRY' )
                ]
            ),
            $this->expectedObject(
                'updateShippingZoneLocations.locations.1',
                [
                    $this->expectedField( 'code', 'CALIFORNIA' ),
                    $this->expectedField( 'type', 'STATE' )
                ]
            ),
            $this->expectedObject(
                'updateShippingZoneLocations.locations.2',
                [
                    $this->expectedField( 'code', '12345' ),
                    $this->expectedField( 'type', 'POSTCODE' )
                ]
            ),
            $this->expectedObject(
                'updateShippingZoneLocations.locations.3',
                [
                    $this->expectedField( 'code', 'NA' ),
                    $this->expectedField( 'type', 'CONTINENT' )
                ]
            )
        ];

        // Validate the response.
        $this->assertQuerySuccessful( $response, $expected );
    }

    public function testClearShippingZoneLocationsMutation() {
        // Create a shipping zone.
        $shipping_zone = $this->factory->shipping_zone->create_and_get();

        // Add a location to the shipping zone.
        $shipping_zone->add_location( 'US', 'country' );
        $shipping_zone->save();

        // Prepare the request.
        $query = 'mutation ($input: ClearShippingZoneLocationsInput!) {
            clearShippingZoneLocations(input: $input) {
                shippingZone {
                    id
                    locations {
                        code
                    }
                }
                removedLocations {
                    code
                    type
                }
            }
        }';

        // Prepare the variables.
        $variables = [
            'input' => [ 'zoneId' => $shipping_zone->get_id() ]
        ];

        // Execute the request.
        $response = $this->graphql( compact( 'query', 'variables' ) );

        // Confirm permissions error.
        $this->assertQueryError( $response );

        // Login as admin and re-execute expecting success.
        $this->loginAsShopManager();
        $response = $this->graphql( compact( 'query', 'variables' ) );
        $expected = [
            $this->expectedObject(
                'clearShippingZoneLocations.shippingZone',
                [
                    $this->expectedField( 'id', $this->toRelayId( 'shipping_zone', $shipping_zone->get_id() ) ),
                    $this->expectedField( 'locations', static::IS_FALSY )
                ]
            ),
            $this->expectedObject(
                'clearShippingZoneLocations.removedLocations.0',
                [
                    $this->expectedField( 'code', 'US' ),
                    $this->expectedField( 'type', 'COUNTRY' )
                ]
            )
        ];

        // Validate the response.
        $this->assertQuerySuccessful( $response, $expected );
    }

    public function testAddMethodToShippingZoneMutation() {
        // Create a shipping zone.
        $shipping_zone_id = $this->factory->shipping_zone->create();

        // Prepare the request.
        $query = 'mutation ($input: AddMethodToShippingZoneInput!) {
            addMethodToShippingZone(input: $input) {
                shippingZone {
                    id
                    methods {
                        edges {
                            id
                            instanceId
                            order
                            enabled
                            settings {
                                id
                                label
                                description
                                type
                                value
                                default
                                tip
                                placeholder
                            }
                            node {
                                id
                                databaseId
                                title
                                description
                            }
                        }
                    }
                }
                method {
                    id
                    instanceId
                    order
                    enabled
                    settings {
                        id
                        label
                        description
                        type
                        value
                        default
                        tip
                        placeholder
                    }
                    node {
                        id
                        databaseId
                        title
                        description
                    }
                }
            }
        }';

        // Prepare the variables.
        $variables = [
            'input' => [
                'zoneId'   => $shipping_zone_id,
                'methodId' => 'flat_rate',
                'order'    => 0,
                'enabled'  => true,
                'settings' => [
                    [
                        'id'    => 'cost',
                        'value' => '10'
                    ]
                ]
            ]
        ];

        // Execute the request.
        $response = $this->graphql( compact( 'query', 'variables' ) );

        // Confirm permissions error.
        $this->assertQueryError( $response );

        // Login as admin and re-execute expecting success.
        $this->loginAsShopManager();
        $response = $this->graphql( compact( 'query', 'variables' ) );
        $expected = [
            $this->expectedObject(
                'addMethodToShippingZone.shippingZone',
                [
                    $this->expectedField( 'id', $this->toRelayId( 'shipping_zone', $shipping_zone_id ) ),
                    $this->expectedNode(
                        'methods.edges',
                        [
                            $this->expectedField( 'id', static::NOT_NULL ),
                            $this->expectedField( 'instanceId', static::NOT_NULL ),
                            $this->expectedField( 'order', 0 ),
                            $this->expectedField( 'enabled', true ),
                            $this->expectedNode(
                                'settings',
                                [
                                    $this->expectedField( 'id', 'cost' ),
                                    $this->expectedField( 'value', '10' )
                                ]
                            ),
                            $this->expectedObject(
                                'node',
                                [
                                    $this->expectedField( 'id', $this->toRelayId( 'shipping_method', 'flat_rate' ) ),
                                    $this->expectedField( 'databaseId', static::NOT_NULL ),
                                    $this->expectedField( 'title', static::NOT_NULL ),
                                ]
                            )
                        ],
                        0
                    ),
                ]
            ),
            $this->expectedObject(
                'addMethodToShippingZone.method',
                [
                    $this->expectedField( 'id', static::NOT_NULL ),
                    $this->expectedField( 'instanceId', static::NOT_NULL ),
                    $this->expectedField( 'order', 0 ),
                    $this->expectedField( 'enabled', true ),
                    $this->expectedNode(
                        'settings',
                        [
                            $this->expectedField( 'id', 'cost' ),
                            $this->expectedField( 'value', '10' )
                        ]
                    ),
                    $this->expectedObject(
                        'node',
                        [
                            $this->expectedField( 'id', $this->toRelayId( 'shipping_method', 'flat_rate' ) ),
                            $this->expectedField( 'databaseId', static::NOT_NULL ),
                            $this->expectedField( 'title', static::NOT_NULL ),
                        ]
                    )
                ]
            )
        ];

        // Validate the response.
        $this->assertQuerySuccessful( $response, $expected );
    }

    public function testUpdateMethodOnShippingZoneMutation() {
        // Create a shipping zone.
        $shipping_zone   = $this->factory->shipping_zone->create_and_get();
        $instance_id     = $shipping_zone->add_shipping_method( 'flat_rate' );
        
        // Prepare the request.
        $query = 'mutation ($input: UpdateMethodOnShippingZoneInput!) {
            updateMethodOnShippingZone(input: $input) {
                shippingZone {
                    id
                    methods {
                        edges {
                            id
                            instanceId
                            order
                            enabled
                            settings {
                                id
                                label
                                description
                                type
                                value
                                default
                                tip
                                placeholder
                            }
                            node {
                                id
                                title
                                description
                            }
                        }
                    }
                }
                method {
                    id
                    instanceId
                    order
                    enabled
                    settings {
                        id
                        label
                        description
                        type
                        value
                        default
                        tip
                        placeholder
                    }
                    node {
                        id
                        title
                        description
                    }
                }
            }
        }';

        // Prepare the variables.
        $variables = [
            'input' => [
                'zoneId'     => $shipping_zone->get_id(),
                'instanceId' => $instance_id,
                'settings'   => [
                    [
                        'id'    => 'cost',
                        'value' => '10'
                    ]
                ],
            ]
        ];

        // Execute the request.
        $response = $this->graphql( compact( 'query', 'variables' ) );

        // Confirm permissions error.
        $this->assertQueryError( $response );

        // Login as admin and re-execute expecting success.
        $this->loginAsShopManager();
        $response = $this->graphql( compact( 'query', 'variables' ) );

        $expected = [
            $this->expectedObject(
                'updateMethodOnShippingZone.shippingZone',
                [
                    $this->expectedField( 'id', $this->toRelayId( 'shipping_zone', $shipping_zone->get_id() ) ),
                    $this->expectedNode(
                        'methods.edges',
                        [
                            $this->expectedField( 'id', $this->toRelayId( 'shipping_zone_method', $instance_id ) ),
                            $this->expectedField( 'instanceId', $instance_id ),
                            $this->expectedField( 'order', static::NOT_NULL ),
                            $this->expectedField( 'enabled', true ),
                            $this->expectedNode(
                                'settings',
                                [
                                    $this->expectedField( 'id', 'cost' ),
                                    $this->expectedField( 'value', '10' )
                                ]
                            ),
                            $this->expectedField( 'node.id', $this->toRelayId( 'shipping_method', 'flat_rate' ) ),
                        ],
                        0
                    ),
                ]
            ),
            $this->expectedObject(
                'updateMethodOnShippingZone.method',
                [
                    $this->expectedField( 'id', $this->toRelayId( 'shipping_zone_method', $instance_id ) ),
                    $this->expectedField( 'instanceId', $instance_id ),
                    $this->expectedField( 'order', static::NOT_NULL ),
                    $this->expectedField( 'enabled', true ),
                    $this->expectedNode(
                        'settings',
                        [
                            $this->expectedField( 'id', 'cost' ),
                            $this->expectedField( 'value', '10' )
                        ]
                    ),
                    $this->expectedField( 'node.id', $this->toRelayId( 'shipping_method', 'flat_rate' ) ),
                ]
            )
        ];

        // Validate the response.
        $this->assertQuerySuccessful( $response, $expected );
    }

    public function testRemoveMethodFromShippingZoneMutation() {
        // Create a shipping zone and add the shipping method.
        $shipping_zone   = $this->factory->shipping_zone->create_and_get();
        $instance_id     = $shipping_zone->add_shipping_method( 'flat_rate' );
        $shipping_method = new \WC_Shipping_Flat_Rate( $instance_id );

        // Prepare the request.
        $query = 'mutation ($input: RemoveMethodFromShippingZoneInput!) {
            removeMethodFromShippingZone(input: $input) {
                shippingZone {
                    id
                    methods {
                        edges {
                            order
                            enabled
                            node { id }
                        }
                    }
                }
                removedMethod {
                    order
                    enabled
                    node { id }
                }
            }
        }';

        // Prepare the variables.
        $variables = [
            'input' => [
                'zoneId'     => $shipping_zone->get_id(),
                'instanceId' => $instance_id
            ]
        ];

        // Execute the request.
        $response = $this->graphql( compact( 'query', 'variables' ) );

        // Confirm permissions error.
        $this->assertQueryError( $response );

        // Login as admin and re-execute expecting success.
        $this->loginAsShopManager();
        $response = $this->graphql( compact( 'query', 'variables' ) );
        $expected = [
            $this->expectedObject(
                'removeMethodFromShippingZone.shippingZone',
                [
                    $this->expectedField( 'id', $this->toRelayId( 'shipping_zone', $shipping_zone->get_id() ) ),
                    $this->expectedField( 'methods.edges', static::IS_FALSY ),
                ]
            ),
            $this->expectedObject(
                'removeMethodFromShippingZone.removedMethod',
                [
                    $this->expectedField( 'enabled', $shipping_method->is_enabled() ),
                    $this->expectedNode(
                        'node',
                        [ $this->expectedField( 'id', $this->toRelayId( 'shipping_method', $shipping_method->id ) ) ]
                    )
                ]
            ),
        ];

        // Validate the response.
        $this->assertQuerySuccessful( $response, $expected );
    }
}
