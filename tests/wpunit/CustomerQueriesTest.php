<?php

class CustomerQueriesTest extends \Codeception\TestCase\WPTestCase
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
    public function testCustomerQuery()
    {
        $query = "
            query {
                user(id: \" \") {
                    billing
                    shipping
                    isPayingCustomer
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