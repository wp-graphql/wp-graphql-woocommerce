# Changelog

## [v0.7.0](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.7.0) (2020-11-24)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.6.2...v0.7.0)

**Fixed:**

- Getting NO SCHEMA AVAILABLE after activating this plugin in GraphiQL Ide [\#357](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/357)
- Allow multiple "orderby" fields [\#374](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/374) ([loganstellway](https://github.com/loganstellway))

**Closed issues:**

- \[WPGraphQL 0.14.0\] Duplicate Type errors [\#356](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/356)
- Missing CartItem Variation Field in Cart Query [\#284](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/284)

**Merged pull requests:**

- Release v0.7.0 [\#383](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/383) ([kidunot89](https://github.com/kidunot89))

## [v0.6.2](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.6.2) (2020-11-24)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.6.1...v0.6.2)

**Enhancements:**

- Better extension support [\#353](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/353) ([kidunot89](https://github.com/kidunot89))

**Fixed:**

- \[release 0.6.1\] Querying a Custom Taxonomy on Product will list all terms, not just the ones associated to the product [\#348](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/348)
- Make the username field optional in registerCustomer mutation [\#381](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/381) ([kidunot89](https://github.com/kidunot89))

**Closed issues:**

- Fetch cart not working in Chrome [\#382](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/382)
- Checkout mutation not working \(Access level to WPGraphQL\WooCommerce\Model\Order::is\_private\(\) must be public\) [\#379](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/379)
- Taxonomy query returns all terms when not set [\#372](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/372)
- Total count when paginating [\#365](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/365)
- Term Connection: ErrorException: Invalid argument supplied for foreach\(\) [\#363](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/363)
- Generate an access token for anonymous customers. \(To checkout without signing in\) [\#358](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/358)
- Gatsby: occured while fetching nodes of the "PaQuantity" [\#354](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/354)
- Order query is returning null  [\#352](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/352)
- paColors and paSizes return all attributes, not connected ones [\#345](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/345)
- Expose uri field in Product type [\#336](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/336)

**Merged pull requests:**

- Feature/itemized cart tax [\#380](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/380) ([kidunot89](https://github.com/kidunot89))
- "Product\_Connection\_Resolver::set\_query\_arg\(\)" removed. [\#376](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/376) ([kidunot89](https://github.com/kidunot89))
- WPGraphQL v1 CI Fix [\#375](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/375) ([kidunot89](https://github.com/kidunot89))
- Guard against empty terms [\#373](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/373) ([jacobarriola](https://github.com/jacobarriola))
- support added for externally defined product type queries. [\#366](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/366) ([kidunot89](https://github.com/kidunot89))
- Guard against false terms when plucking IDs [\#364](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/364) ([jacobarriola](https://github.com/jacobarriola))
- Fix Syntax Error in php7.2 and 7.4 [\#355](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/355) ([namli](https://github.com/namli))
- Connect terms to their source [\#351](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/351) ([jacobarriola](https://github.com/jacobarriola))
- Return connected TermObjects from the PostObjectType [\#346](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/346) ([jacobarriola](https://github.com/jacobarriola))

## [v0.6.1](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.6.1) (2020-10-15)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.5.1...v0.6.1)

## Release Summary

- [x] Demo/Examples sections added to README.md
- [x] More WooGraphQL + WPGraphQL extension integration bugfixes
- [x] Connection resolver classes support all new WPGraphQL v0.6.0+ features
- [x] Better cart validation and error messages
- [x] Replaces unauthorized queries with authorized queries for unauthorized queries instead of return `null`. For example `orders(...) {...}` should default to `customer{ orders(...) {...} }` when user is not authorized to execute `orders(...) {...}`

**Enhancements:**

- EmptyCartPayload return the cart before empty [\#300](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/300)
- Support for some node interfaces added to the product and order models. [\#337](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/337) ([kidunot89](https://github.com/kidunot89))
- Field caps removed from product raw price and description fields. [\#332](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/332) ([kidunot89](https://github.com/kidunot89))
- "Root\_Query" class implemented. [\#331](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/331) ([kidunot89](https://github.com/kidunot89))
- "price" field added to "GroupProduct" type. [\#319](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/319) ([kidunot89](https://github.com/kidunot89))
- Two new fields added to the "ProductCategory" type. [\#318](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/318) ([kidunot89](https://github.com/kidunot89))
- Adds some label fields to the attribute types. [\#314](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/314) ([kidunot89](https://github.com/kidunot89))
- New error handling method introduced in the "addToCart" mutation [\#312](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/312) ([kidunot89](https://github.com/kidunot89))

**Fixed:**

- GraphQl Error when querying price fields \("regularPrice", "price" and "salePrice"\) whether formatted or not [\#330](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/330)
- `Product.contentType.id` connection errors and returns null [\#325](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/325)
- Make Product\_Types register\_product\_query static method public [\#323](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/323)
- Cannot set query argument in Product\_Connection\_Resolver Class [\#321](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/321)
- Checkout Mutation cannot create the order if a user accounts already exists. [\#297](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/297)
- Incorrect namespace for WC\_Customer in Checkout Mutation [\#296](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/296)
- Access to undeclared static property in Checkout Mutation [\#295](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/295)
- Product Review bugs [\#292](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/292)
- Error when querying products that are ACF relationship field items [\#253](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/253)
- Composer dependecy breaks install [\#246](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/246)
- Fixes downloadableItems accessibility bug. [\#316](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/316) ([kidunot89](https://github.com/kidunot89))
- Fixes cart item validation and error handling on checkout [\#315](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/315) ([kidunot89](https://github.com/kidunot89))
- "galleryImages" connection refactored. [\#311](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/311) ([kidunot89](https://github.com/kidunot89))
- Fixes some unit tests [\#302](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/302) ([kidunot89](https://github.com/kidunot89))
- Rating input type changed to "Int" [\#301](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/301) ([kidunot89](https://github.com/kidunot89))
- Fixed : missing static keyword for static variable [\#294](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/294) ([hwsiew](https://github.com/hwsiew))

**Closed issues:**

- Call to undefined function codecept\_debug\(\) [\#342](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/342)
- \[Question\] GraphQLError: Schema is not configured for mutations. [\#328](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/328)
- Feature request: include more data for attributes -\> options [\#326](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/326)
- Link user logged in at checkout [\#320](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/320)
- Add "price" field for GroupedProduct [\#317](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/317)
- Checkout mutation allows 'Out of Stock' product  [\#308](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/308)
- Return updated product inventory in addToCart mutation [\#307](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/307)
- Add to cart when product is out of stock returns generic error [\#306](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/306)
- GraphQL error: Expired token [\#305](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/305)
- Unable to get parent order for an order [\#303](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/303)
- Unable to query 'Orders' under Root [\#299](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/299)
- Query on Products fails [\#289](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/289)
- Guest cart session is not synced with Woocommerce cart session [\#285](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/285)
- All Downloadable Items have the same related Product [\#281](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/281)
- Product Categories - "Display" and "Menu Order" attributes [\#277](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/277)

**Merged pull requests:**

- Adds demo/examples [\#344](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/344) ([imranhsayed](https://github.com/imranhsayed))
- Remove undefined codecept\_debug\(\) function [\#343](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/343) ([jacobarriola](https://github.com/jacobarriola))
- Fixed : \#303 [\#304](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/304) ([hwsiew](https://github.com/hwsiew))
- Skip conditional added to stripe test [\#298](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/298) ([kidunot89](https://github.com/kidunot89))
- Update some README.md links [\#287](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/287) ([kidunot89](https://github.com/kidunot89))
- Adds Local-Testing Contribution Guides [\#242](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/242) ([kidunot89](https://github.com/kidunot89))

## [v0.5.1](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.5.1) (2020-05-12)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.4.4...v0.5.1)

### Release Checklist
---------------------------------------------------
- [x] Support for **WPGraphQL** v0.8.0 added.
- [x] Deprecated queries removed.
  - `productBy`
  - `couponBy`

**Enhancements:**

- Add product review mutation using "createComment" is missing ratings input fields. [\#254](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/254)
- Release v0.5.1 [\#255](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/255) ([kidunot89](https://github.com/kidunot89))

**Fixed:**

- customer query without parameters is not working with just an Auth Header  [\#275](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/275)
- Bugs with Checkout Mutation input variables [\#270](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/270)
- Update Customer mutation allows for invalid shipping input variables [\#269](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/269)
- Checkout mutation payload contains WooCommerce error HTML and breaks JSON [\#265](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/265)
- "shippingMethod" field type changed to \[String\]. [\#257](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/257) ([kidunot89](https://github.com/kidunot89))

**Closed issues:**

- Products with arrow in WP admin are not returned in any products wpgraphql query [\#278](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/278)
- Payment gateway missing from paymentGateways query [\#263](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/263)
- addToCart mutation returns Internal server error in response body with HTTP 200 [\#251](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/251)
- Can't query all products [\#248](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/248)
- Show color in product attributes [\#245](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/245)
- Finalize the checkout process - payment service \(stripe\) [\#241](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/241)
- Price RAW format returns null [\#207](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/207)

**Merged pull requests:**

- Changing how we verify the JWT plugin [\#273](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/273) ([renatonascalves](https://github.com/renatonascalves))
- Activating Open Collective [\#252](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/252) ([monkeywithacupcake](https://github.com/monkeywithacupcake))
- Updates to the Connection Classes [\#243](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/243) ([renatonascalves](https://github.com/renatonascalves))
- Adds Two New Workflows [\#239](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/239) ([kidunot89](https://github.com/kidunot89))

## [v0.4.4](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.4.4) (2020-02-20)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.4.3...v0.4.4)

**Enhancements:**

- Proper PHP Documentation Standards [\#240](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/240)
- Adds Customer to DownloadableItems connection [\#238](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/238) ([kidunot89](https://github.com/kidunot89))
- Code Improvements [\#235](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/235) ([renatonascalves](https://github.com/renatonascalves))
- Adds Documentation [\#234](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/234) ([kidunot89](https://github.com/kidunot89))

**Closed issues:**

- Address and Userinfo [\#232](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/232)
- Guest Account Checkout - Status 405 Not Allowed [\#222](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/222)

## [v0.4.3](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.4.3) (2020-02-04)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.4.2...v0.4.3)

**Enhancements:**

- sales tax query: add postal code to where clause [\#105](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/105)
- TaxRate connection improvements [\#231](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/231) ([kidunot89](https://github.com/kidunot89))
- Checkout mutation updates [\#229](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/229) ([kidunot89](https://github.com/kidunot89))

**Fixed:**

- Need `transactionId` for checkout mutation [\#226](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/226)

**Closed issues:**

- Ignore this. ¯\\_\(ツ\)\_/¯ [\#230](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/230)
- Problem with union query on some products. [\#227](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/227)
- Version number not updated [\#225](https://github.com/wp-graphql/wp-graphql-woocommerce/issues/225)

## [v0.4.2](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.4.2) (2020-01-28)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.4.1...v0.4.2)

**Enhancements:**

- Fixes introspection query [\#221](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/221) ([kidunot89](https://github.com/kidunot89))

## [v0.4.1](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.4.1) (2020-01-26)

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
- Release v0.3.1 [\#171](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/171) ([kidunot89](https://github.com/kidunot89))

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
