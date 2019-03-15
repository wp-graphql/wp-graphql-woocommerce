<?php

class OrderQueriesTest extends \Codeception\TestCase\WPTestCase
{

    public function setUp()
    {
        // before
        parent::setUp();

        // your set up methods here
    }

    public function tearDown()
    {
        // your tear down methods here

        // then
        parent::tearDown();
    }

    // tests
    public function testOrderQuery()
    {
        $query = "
            query {
                order(id: \" \") {
                    orderId
                    parentId
                    number
                    orderKey
                    createdVia
                    version
                    status
                    currency
                    dateCreated
                    dateModified
                    discountTotal
                    discountTax
                    shippingTotal
                    shippingTax
                    cartTax
                    total
                    totalTax
                    pricesIncludeTax
                    customer {
                        id
                    }
                    customerIpAddress
                    customerUserAgent
                    customerNote
                    billing {
                        firstName
                        lastName
                        company
                        address1
                        address2
                        city
                        state
                        postcode
                        country
                        email
                        phone
                    }
                    shipping {
                        firstName
                        lastName
                        company
                        address1
                        address2
                        city
                        state
                        postcode
                        country
                    }
                    paymentMethod
                    paymentMethodTitle
                    transactionId
                    datePaid
                    dateCompleted
                    cartHash
                    lineItem {
                        nodes {
                            id
                        }
                    }
                    tax_lines {
                        nodes {
                            id
                        }
                    }
                    shippingLines{
                        nodes {
                            id
                        }
                    }
                    feeLines {
                        nodes {
                            id
                        }
                    }
                    couponLines {
                        nodes {
                            id
                        }
                    }
                    refunds {
                        id
                        reason
                        total
                    }
                    setPaid
                }
            }
        ";

        $actual = do_graphql_request( $query );

        /**
         * use --debug flag to view
         */
        \Codeception\Util\Debug::debug( $actual );

        $expected = [];

        $this->assertEquals( $expected, $actual );
    }

}