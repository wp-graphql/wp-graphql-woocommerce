<?php

class CouponQueriesTest extends \Codeception\TestCase\WPTestCase
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
    public function testCouponQuery()
    {
        $query = "
            query {
                coupon(id: \" \") {
                    couponId
                    code
                    amount
                    dateCreated
                    dateModified
                    discountType
                    description
                    dateExpires
                    usageCount
                    individualUse
                    products {
                        nodes {
                            id
                        }
                    }
                    excludedProducts {
                        nodes {
                            id
                        }
                    }
                    usageLimit
                    usageLimitPerUser
                    limitUsageToXItems
                    freeShipping
                    productCategories {
                        nodes {
                            id
                        }
                    }
                    excludedProductCategories {
                        nodes {
                            id
                        }
                    }
                    excludeSaleItems
                    minimumAmount
                    maximumAmount
                    emailRestrictions
                    usedBy {
                        nodes {
                            id
                        }
                    }
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

    public function testCouponByQuery()
    {
        $query = "
            query {
                couponBy(couponId: \" \") {
                    couponId
                    code
                    amount
                    dateCreated
                    dateModified
                    discountType
                    description
                    dateExpires
                    usageCount
                    individualUse
                    products {
                        nodes {
                            id
                        }
                    }
                    excludedProducts {
                        nodes {
                            id
                        }
                    }
                    usageLimit
                    usageLimitPerUser
                    limitUsageToXItems
                    freeShipping
                    productCategories {
                        nodes {
                            id
                        }
                    }
                    excludedProductCategories {
                        nodes {
                            id
                        }
                    }
                    excludeSaleItems
                    minimumAmount
                    maximumAmount
                    emailRestrictions
                    usedBy {
                        nodes {
                            id
                        }
                    }
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

    public function testCouponsQuery()
    {
        $query = "
            query {
                coupons() {
                    nodes {
                        couponId
                        code
                        amount
                        dateCreated
                        dateModified
                        discountType
                        description
                        dateExpires
                        usageCount
                        individualUse
                        products {
                            nodes {
                                id
                            }
                        }
                        excludedProducts {
                            nodes {
                                id
                            }
                        }
                        usageLimit
                        usageLimitPerUser
                        limitUsageToXItems
                        freeShipping
                        productCategories {
                            nodes {
                                id
                            }
                        }
                        excludedProductCategories {
                            nodes {
                                id
                            }
                        }
                        excludeSaleItems
                        minimumAmount
                        maximumAmount
                        emailRestrictions
                        usedBy {
                            nodes {
                                id
                            }
                        }
                    }
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