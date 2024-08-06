<?php

class ShippingZoneQueriesTest extends \Tests\WPGraphQL\WooCommerce\TestCase\WooGraphQLTestCase {
    public function testShippingZoneQuery() {
        // Create a shipping zone.
        $shipping_zone = $this->factory->shipping_zone->create_and_get();

        // Add location.
        $shipping_zone->add_location( 'US', 'country' );
        $shipping_zone->save();


        // Add shipping method.
        $instance_id     = $shipping_zone->add_shipping_method( 'flat_rate' );
        $shipping_method = null;
        foreach ( $shipping_zone->get_shipping_methods() as $method ) {
            if ( $method->instance_id === $instance_id ) {
                $shipping_method = $method;
                break;
            }
        }
        $instance_settings               = $shipping_method->instance_settings;
        $instance_settings['cost']       = 10.00;
        update_option( $shipping_method->get_instance_option_key(), $instance_settings );

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
        }';

        // Prepare the variables.
        $variables = [ 'id' => $this->toRelayId( 'shipping_zone', $shipping_zone->get_id() ) ];

        // Execute the request expecting failure.
        $response = $this->graphql( compact( 'query', 'variables' ) );
        $this->assertQueryError( $response );

        // Login as shop manager.
        $this->loginAsShopManager();

        // Execute the request expecting success.
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
                            $this->expectedField( 'id', $this->toRelayId( 'shipping_zone_method', $instance_id ) ),
                            $this->expectedField( 'instanceId', $instance_id ),
                            $this->expectedField( 'order', $shipping_method->method_order ),
                            $this->expectedField( 'enabled', $shipping_method->is_enabled() ),
                            $this->expectedNode(
                                'settings',
                                [
                                    $this->expectedField( 'id', 'cost' ),
                                    $this->expectedField( 'label', 'Cost' ),
                                    $this->expectedField( 'description', static::NOT_FALSY ),
                                    $this->expectedField( 'type', 'TEXT' ),
                                    $this->expectedField( 'value', '10' ),
                                    $this->expectedField( 'default', static::IS_NULL ),
                                    $this->expectedField( 'placeholder', static::IS_NULL ),
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

        /**
		 * Assertion One
		 *
		 * Confirm permission check is working
		 */
		$response  = $this->graphql( compact( 'query' ) );
		$this->assertQuerySuccessful( $response, [ $this->expectedField( 'shippingZones.nodes', static::IS_FALSY ) ] );

		// Login as shop manager.
		$this->loginAsShopManager();

        // Execute the request.
        $response = $this->graphql( compact( 'query' ) );
        $expected = [
            $this->expectedNode(
                'shippingZones.nodes',
                [
                    $this->expectedField( 'id', $this->toRelayId( 'shipping_zone', $shipping_zones[0] ) )
                ]
            ),
            $this->expectedNode(
                'shippingZones.nodes',
                [
                    $this->expectedField( 'id', $this->toRelayId( 'shipping_zone', $shipping_zones[1] ) )
                ]
            ),
            $this->expectedNode(
                'shippingZones.nodes',
                [
                    $this->expectedField( 'id', $this->toRelayId( 'shipping_zone', $shipping_zones[2] ) )
                ]
            )
        ];

        // Validate the response.
        $this->assertQuerySuccessful( $response, $expected );
    }
}
