# Changelog

## [v0.4.1](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.4.1) (2020-01-25)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.4.0...v0.4.1)

#### Checklist
- [x] Improve support for **WPGraphQL [v0.6.0](https://github.com/wp-graphql/wp-graphql/releases/tag/v0.6.0)**
  - [x] Implement `idType` in WC CRUD object queries
     - `coupon(id: value, idType: ID|DATABASE_ID|CODE)`. *Note: The `couponBy` query has been deprecated. Will be removed in `v0.5.x`*.
     - `order(id: value, idType: ID|DATABASE_ID|ORDER_NUMBER)`. *Note: The `orderId` and `orderKey` arguments have been deprecated. Will be removed in `v0.5.x`*.
     - `product(id: value, idType: ID|DATABASE_ID|SLUG|SKU)`. *Note: The `productBy` query has been deprecated. Will be removed in `v0.5.x`*.
     - `productVariation(id: value, idType: ID|DATABASE_ID)`. *Note: The `variationId` argument has been deprecated. Will be removed in `v0.5.x`*.
     - `refund(id: value, idType: ID|DATABASE_ID)`. *Note: The `refundBy` query has been removed. Will be removed in `v0.5.x`*.
     - `shippingMethod(id: value, idType: ID|DATABASE_ID)`. *Note: The `methodId` argument has been deprecated. Will be removed in `v0.5.x`*.
     - `taxRate(id: value, idType: ID|DATABASE_ID)`. *Note: The `rateId` argument has been deprecated. Will be removed in `v0.5.x`*.

## [v0.4.0](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.4.0) (2020-01-22)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.3.3...v0.4.0)

### Release v0.4.0
#### Checklist
- [x] ProductAttribute schema shape re-designed [#195](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/195)
- [x] Added supported for the next release WPGraphQL-JWT-Authentication [#196](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/196) 
- [x] Product to review connection created [#198](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/198)
- [x] DownloadableItems type and connection added [#219](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/219) 

**Enhancements:**

- Get reviews for products [\#193](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/193)
- Add a centralize product attribute connection [\#76](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/76)
- "DownloadableItem" type and connection added. [\#219](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/219) ([kidunot89](https://github.com/kidunot89))
- Adds product reviews [\#198](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/198) ([kidunot89](https://github.com/kidunot89))
- Adds support for the next version of WPGraphQL-JWT-Authentication [\#196](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/196) ([kidunot89](https://github.com/kidunot89))
- Feature/product attributes upgrade [\#195](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/195) ([kidunot89](https://github.com/kidunot89))

**Closed issues:**

- downloadableItems inside order are not accessible [\#218](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/218)
- Add object with name and slug of the attribute to options array [\#175](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/175)

## [v0.3.3](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.3.3) (2020-01-14)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.3.2-beta...v0.3.3)

### Release v0.3.3
Minor bugfixes, security patches, and enhancements.
- Extra layer of security on some product fields #213 
- Cart to CartItem connection enhanced #215 
- Customer functionality improved #212 #214 
- `orderBy` query removed, and it's parameters have added to `order` query.
- Fixed `Order` to `Refund` connection and `Customer` to `Refund` connection. 
- Fixed bug concerning guest customer order resolution. 
- Connection patch for **[WPGraphQL #1111](https://github.com/wp-graphql/wp-graphql/pull/1111)**
- `metaData` field added to `checkout` mutation.
- Extra layer of security added to `order` model
- Pagination testing implemented for connections that support pagination.

Updated tests
- ProductQueriesTest #213 
- RefundQueriesTest 
- OrderQueriesTest
- CartQueriesTest #215 
- ConnectionPaginationTest

**Special Thanks to @jasonbahl & @saleebm for contributing**

**Enhancements:**

- Cart mutations should return updated `cart` object. [\#192](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/192)
- Shipping Method Queries and Mutators for Cart [\#167](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/167)
- ShippingZone and ShippingMethodInstance type [\#26](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/26)
- Release v0.3.2 [\#200](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/200) ([kidunot89](https://github.com/kidunot89))
- Cart to CartItem connection enhanced. [\#215](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/215) ([kidunot89](https://github.com/kidunot89))
- allow optional password creation for registerCustomer [\#214](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/214) ([saleebm](https://github.com/saleebm))
- Updates some product fields' access levels [\#213](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/213) ([kidunot89](https://github.com/kidunot89))
- More customer improvements. [\#212](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/212) ([kidunot89](https://github.com/kidunot89))
- Better cart mutation support/unsupported type error handling [\#211](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/211) ([kidunot89](https://github.com/kidunot89))
- Update composer.json [\#203](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/203) ([jasonbahl](https://github.com/jasonbahl))

**Fixed:**

- WooCommerce Subscriptions plugin throws Internal server error [\#185](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/185)
- Improve createOrder mutation to enable creating order as a guest, without the need of creating new customer account. [\#168](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/168)
- Make package available through composer [\#202](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/202)

**Closed issues:**

- addToCart mutation returns error on multiple calls when variationId is set. [\#209](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/209)
- Trouble with QL Session Handler [\#208](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/208)
- Need price in main products nodes [\#206](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/206)
- Sensitive API endpoints are exposed publicly and endpoints throughout do not mirror access priviliges of WooCommerce REST API [\#210](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/210)
- Total cart item count in cart mutation return data [\#205](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/205)
- Hook into WC customer created on registration [\#204](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/204)

**Merged pull requests:**

- Master to Release v0.3.1 [\#178](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/178) ([kidunot89](https://github.com/kidunot89))
- Master to v0.3.0 [\#161](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/161) ([kidunot89](https://github.com/kidunot89))
- v0.2.1 hotfix to master [\#134](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/134) ([kidunot89](https://github.com/kidunot89))
- Master to v0.2.1 [\#133](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/133) ([kidunot89](https://github.com/kidunot89))

## [v0.3.2-beta](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.3.2-beta) (2019-12-21)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.3.1-beta...v0.3.2-beta)

**Enhancements:**

- Registers "attributes" as a "Product" field. [\#197](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/197) ([kidunot89](https://github.com/kidunot89))
- "cart" field add to mutation output. [\#194](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/194) ([kidunot89](https://github.com/kidunot89))
- local version "is\_graphql\_http\_request" replaced with core version." [\#190](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/190) ([kidunot89](https://github.com/kidunot89))
- "WPUnionType" injection filters implemented. [\#188](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/188) ([kidunot89](https://github.com/kidunot89))
- Better guest customer support [\#187](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/187) ([kidunot89](https://github.com/kidunot89))
- Bugfix/customer security patch [\#184](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/184) ([kidunot89](https://github.com/kidunot89))
- Improved shipping support [\#182](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/182) ([kidunot89](https://github.com/kidunot89))

**Fixed:**

- WPGraphQL for Custom Post Type UI taxonomies on Products not registering. [\#180](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/180)
- Duplicate Connections being registered [\#169](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/169)
- CPT UI taxonomies not showing in Products schema [\#166](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/166)
- Schema validation fails for product types [\#164](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/164)
- Union Filter Callback missing [\#162](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/162)
- Better handling of unsupported product types. [\#199](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/199) ([kidunot89](https://github.com/kidunot89))
- WPGraphQL for Custom Post Type UI taxonomy support [\#181](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/181) ([kidunot89](https://github.com/kidunot89))

**Closed issues:**

- Can't find woocommerce in graphql schema [\#191](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/191)
- Latest Update - Breaks Product Query [\#179](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/179)

**Merged pull requests:**

- More support provided for WPGraphQL ACF [\#189](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/189) ([kidunot89](https://github.com/kidunot89))

## [v0.3.1-beta](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.3.1-beta) (2019-11-26)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.3.0-beta...v0.3.1-beta)

**Enhancements:**

- Product post\_type should be set to `show\_in\_graphql` [\#85](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/85)
- QL Session Handler 2.0 [\#174](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/174) ([kidunot89](https://github.com/kidunot89))
- Testing/CI configurations upgrade. [\#173](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/173) ([kidunot89](https://github.com/kidunot89))
- QL Search support added. [\#172](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/172) ([kidunot89](https://github.com/kidunot89))
- Release v0.3.1 [\#171](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/171) ([kidunot89](https://github.com/kidunot89))
- Release v0.3.0 [\#155](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/155) ([kidunot89](https://github.com/kidunot89))

**Fixed:**

- Pagination with orderby when fetching Products causes products to be skipped [\#153](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/153)
- Unneeded "register\_graphql\_connection\(\)" calls removed. [\#165](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/165) ([kidunot89](https://github.com/kidunot89))
- Removes potential trouble filter [\#163](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/163) ([kidunot89](https://github.com/kidunot89))

**Closed issues:**

- Price not showing on products query [\#176](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/176)

**Merged pull requests:**

- Codecoverage boost [\#177](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/177) ([kidunot89](https://github.com/kidunot89))



\* *This Changelog was automatically generated by [github_changelog_generator](https://github.com/github-changelog-generator/github-changelog-generator)*
