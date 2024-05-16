<?php

class ShippingZoneQueriesTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {
    public function testShippingZoneQuery() {
        // Create a shipping zone.
        $shipping_zone = $this->factory->shipping_zone->create_and_get();

        // Add location.
        $shipping_zone->add_location( 'US', 'country' );
        $shipping_zone->save();


        // Add shipping method.
        $instance_id                     = $shipping_zone->add_shipping_method( 'flat_rate' );
        $shipping_method                 = new \WC_Shipping_Flat_Rate( $instance_id );
        $instance_settings               = $shipping_method->instance_settings;
        $instance_settings['cost']       = 10.00;
        update_option( $shipping_method->get_instance_option_key(), $instance_settings );
        $this->factory->shipping_zone->reloadShippingMethods();


        // Prepare the request.
        $query = 'query ($id: ID!) {
            shippingZone(id: $id) {
                id
                name
                order
                locations {
                    code
                    type
                }
                methods {
                    edges {
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
        }';

        // Prepare the variables.
        $variables = [ 'id' => $this->toRelayId( 'shipping_zone', $shipping_zone->get_id() ) ];

        // Execute the request.
        $response = $this->graphql( compact( 'query', 'variables' ) );
        $expected = [
            $this->expectedObject(
                'shippingZone',
                [
                    $this->expectedField( 'id', $this->toRelayId( 'shipping_zone', $shipping_zone->get_id() ) ),
                    $this->expectedField( 'name', $shipping_zone->get_zone_name() ),
                    $this->expectedField( 'order', $shipping_zone->get_zone_order() ),
                    $this->expectedNode(
                        'locations',
                        [
                            $this->expectedField( 'code', 'US' ),
                            $this->expectedField( 'type', 'COUNTRY' )
                        ]
                    ),
                    $this->expectedNode(
                        'methods.edges',
                        [
                            $this->expectedField( 'order', self::IS_FALSY ),
                            $this->expectedField( 'enabled', self::NOT_FALSY ),
                            $this->expectedNode(
                                'settings',
                                [
                                    $this->expectedField( 'id', 'cost' ),
                                    $this->expectedField( 'label', 'Cost' ),
                                    $this->expectedField( 'description', self::NOT_FALSY ),
                                    $this->expectedField( 'type', 'TEXT' ),
                                    $this->expectedField( 'value', '10' ),
                                    $this->expectedField( 'default', self::IS_NULL ),
                                    $this->expectedField( 'placeholder', self::IS_NULL ),
                                ],
                            )
                        ],
                        0
                    )
                ]
            )
        ];

        // Validate the response.
        $this->assertQuerySuccessful( $response, $expected );
    }

    public function testShippingZonesQuery() {
        // Create a shipping zones.
        $shipping_zones = $this->factory->shipping_zone->create_many( 3 );

        // Prepare the request.
        $query = 'query {
            shippingZones {
                nodes {
                    id
                }
            }
        }';

        // Execute the request.
        $response = $this->graphql( compact( 'query' ) );
        $expected = [
            $this->expectedNode(
                'shippingZones.nodes',
                [
                    $this->expectedField( 'id', $shipping_zones[0] )
                ]
            ),
            $this->expectedNode(
                'shippingZones.nodes',
                [
                    $this->expectedField( 'id', $shipping_zones[1] )
                ]
            ),
            $this->expectedNode(
                'shippingZones.nodes',
                [
                    $this->expectedField( 'id', $shipping_zones[2] )
                ]
            )
        ];

        // Validate the response.
        $this->assertQuerySuccessful( $response );
    }
}
