<?php

class ProductQueriesTest extends \Codeception\TestCase\WPTestCase
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
    public function testProductQuery()
    {
        $query = "
            query {
                product(id: \" \") {
                    name
                    slug
                    dateCreated
                    dateModified
                    status
                    featured
                    catalogVisibility
                    description
                    shortDescription
                    sku
                    price
                    regularPrice
                    salePrice
                    dateOnSaleFrom
                    dateOnSaleTo
                    totalSales
                    taxStatus
                    taxClass
                    manageStock
                    stockQuantity
                    stockStatus
                    backorders
                    lowStockAmount
                    soldIndividually
                    weight
                    length
                    width
                    height
                    upsell {
                        node {
                            id
                            name
                        }
                    }
                    crossSell {
                        node {
                            id
                            name
                        }
                    }
                    parent {
                        id
                        name
                    }
                    reviewsAllowed
                    purchaseNote
                    attributes
                    defaultAttributes
                    menuOrder
                    virtual
                    downloadable
                    categories {
                        nodes {
                            id
                            name
                        }
                    }
                    tags {
                        nodes {
                            id
                            name
                        }
                    }
                    shippingClass
                    downloads
                    image {
                        id
                        url
                    }
                    galleryImages {
                        nodes {
                            id
                            url
                        }
                    }
                    downloadLimit
                    downloadExpiry
                    ratingCounts
                    averageRating
                    reviewCount
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

    public function testProductsQuery()
    {
        $query = "
            query {
                products {
                    nodes {
                        name
                        slug
                        dateCreated
                        dateModified
                        status
                        featured
                        catalogVisibility
                        description
                        shortDescription
                        sku
                        price
                        regularPrice
                        salePrice
                        dateOnSaleFrom
                        dateOnSaleTo
                        totalSales
                        taxStatus
                        taxClass
                        manageStock
                        stockQuantity
                        stockStatus
                        backorders
                        lowStockAmount
                        soldIndividually
                        weight
                        length
                        width
                        height
                        upsell {
                            node {
                                id
                                name
                            }
                        }
                        crossSell {
                            node {
                                id
                                name
                            }
                        }
                        parent {
                            id
                            name
                        }
                        reviewsAllowed
                        purchaseNote
                        attributes
                        defaultAttributes
                        menuOrder
                        virtual
                        downloadable
                        categories {
                            nodes {
                                id
                                name
                            }
                        }
                        tags {
                            nodes {
                                id
                                name
                            }
                        }
                        shippingClass
                        downloads
                        image {
                            id
                            url
                        }
                        galleryImages {
                            nodes {
                                id
                                url
                            }
                        }
                        downloadLimit
                        downloadExpiry
                        ratingCounts
                        averageRating
                        reviewCount
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