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
                    productId
                    name
                    slug
                    permalink
                    dateCreated
                    dateModified
                    type
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
                    onSale
                    purchasable
                    totalSales
                    virtual
                    downloadable
                    downloads
                    downloadLimit
                    downloadExpiry
                    externalUrl
                    buttonText
                    taxStatus
                    taxClass
                    manageStock
                    stockQuantity
                    stockStatus
                    backorders
                    backordersAllowed
                    backordered
                    soldIndividually
                    weight
                    dimensions {
                        length
                        width
                        height
                    }
                    shippingRequired
                    shippingTaxable
                    shippingClass
                    shippingClassId
                    reviewsAllowed
                    averageRating
                    ratingCount
                    related {
                        nodes {
                            id
                            name
                        }
                    }
                    upsell {
                        nodes {
                            id
                            name
                        }
                    }
                    crossSell {
                        nodes {
                            id
                            name
                        }
                    }
                    parent {
                        id
                        name
                    }
                    purchaseNote
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
                    images {
                        nodes {
                            id
                            src
                        }
                    }
                    attributes {
                        id
                        name
                        position
                        visible
                        variation
                        options
                    }
                    defaultAttributes {
                        id
                        name
                        option
                    }
                    variations {
                        nodes {
                            id
                        }
                    }
                    groupedProducts {
                        nodes {
                            id
                        }
                    }
                    menuOrder
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

    public function testProductByQuery()
    {
        $query = "
            query {
                productBy(productId: \" \") {
                    productId
                    name
                    slug
                    permalink
                    dateCreated
                    dateModified
                    type
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
                    onSale
                    purchasable
                    totalSales
                    virtual
                    downloadable
                    downloads
                    downloadLimit
                    downloadExpiry
                    externalUrl
                    buttonText
                    taxStatus
                    taxClass
                    manageStock
                    stockQuantity
                    stockStatus
                    backorders
                    backordersAllowed
                    backordered
                    soldIndividually
                    weight
                    dimensions {
                        length
                        width
                        height
                    }
                    shippingRequired
                    shippingTaxable
                    shippingClass
                    shippingClassId
                    reviewsAllowed
                    averageRating
                    ratingCount
                    related {
                        nodes {
                            id
                            name
                        }
                    }
                    upsell {
                        nodes {
                            id
                            name
                        }
                    }
                    crossSell {
                        nodes {
                            id
                            name
                        }
                    }
                    parent {
                        id
                        name
                    }
                    purchaseNote
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
                    images {
                        nodes {
                            id
                            src
                        }
                    }
                    attributes {
                        id
                        name
                        position
                        visible
                        variation
                        options
                    }
                    defaultAttributes {
                        id
                        name
                        option
                    }
                    variations {
                        nodes {
                            id
                        }
                    }
                    groupedProducts {
                        nodes {
                            id
                        }
                    }
                    menuOrder
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
                        productId
                        name
                        slug
                        permalink
                        dateCreated
                        dateModified
                        type
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
                        onSale
                        purchasable
                        totalSales
                        virtual
                        downloadable
                        downloads
                        downloadLimit
                        downloadExpiry
                        externalUrl
                        buttonText
                        taxStatus
                        taxClass
                        manageStock
                        stockQuantity
                        stockStatus
                        backorders
                        backordersAllowed
                        backordered
                        soldIndividually
                        weight
                        dimensions {
                            length
                            width
                            height
                        }
                        shippingRequired
                        shippingTaxable
                        shippingClass
                        shippingClassId
                        reviewsAllowed
                        averageRating
                        ratingCount
                        related {
                            nodes {
                                id
                                name
                            }
                        }
                        upsell {
                            nodes {
                                id
                                name
                            }
                        }
                        crossSell {
                            nodes {
                                id
                                name
                            }
                        }
                        parent {
                            id
                            name
                        }
                        purchaseNote
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
                        images {
                            nodes {
                                id
                                src
                            }
                        }
                        attributes {
                            id
                            name
                            position
                            visible
                            variation
                            options
                        }
                        defaultAttributes {
                            id
                            name
                            option
                        }
                        variations {
                            nodes {
                                id
                            }
                        }
                        groupedProducts {
                            nodes {
                                id
                            }
                        }
                        menuOrder
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