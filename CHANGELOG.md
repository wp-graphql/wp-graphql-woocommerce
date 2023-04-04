# Changelog

## [v0.12.3](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.12.3) (2023-04-04)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.12.2...v0.12.3)

**New Features:**

- feat: WooGraphQL settings tab added. [\#726](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/726) ([kidunot89](https://github.com/kidunot89))

## [v0.12.2](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.12.2) (2023-04-01)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.12.1...v0.12.2)

**New Features:**

- feat: "NAME" added to "PostTypeOrderbyEnum" values [\#722](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/722) ([kidunot89](https://github.com/kidunot89))

**Fixed:**

- fix: Fixes order return type for guest. [\#723](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/723) ([kidunot89](https://github.com/kidunot89))
- Adds taxes to product variation prices. [\#717](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/717) ([creative-andrew](https://github.com/creative-andrew))
- Fix wrong function args in sale price and remove wc\_get\_price\_to\_display from raw price. [\#716](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/716) ([creative-andrew](https://github.com/creative-andrew))
- Adds tax calculation to regular and sale prices. [\#714](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/714) ([creative-andrew](https://github.com/creative-andrew))
- Adds noop for set\_customer\_session\_cookie. [\#710](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/710) ([creative-andrew](https://github.com/creative-andrew))

**Other Changes:**

- CHANGELOG and version updated. [\#724](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/724) ([kidunot89](https://github.com/kidunot89))

## [v0.12.1](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.12.1) (2023-02-02)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.12.0...v0.12.1)

**New Features:**

- feat: Auth no longer needed for the raw order totals [\#700](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/700) ([kidunot89](https://github.com/kidunot89))
- Add filter to add custom fields to product sort [\#690](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/690) ([berryhijwegen](https://github.com/berryhijwegen))

**Fixed:**

- fix: Fixed all product connection filtering regressions [\#704](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/704) ([kidunot89](https://github.com/kidunot89))
- fix: Product "price" field now supports the "taxes included" display … [\#703](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/703) ([kidunot89](https://github.com/kidunot89))
- fix: Customer order connection args priority fixed. [\#698](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/698) ([kidunot89](https://github.com/kidunot89))
- Updates deprecated DataSource::resolve\_post\_object [\#697](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/697) ([lstellway](https://github.com/lstellway))
- fix: Parent connection classes namespaces updated. [\#696](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/696) ([kidunot89](https://github.com/kidunot89))
- fix:`$post_type` must be an array when passed to `in_array()` [\#695](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/695) ([therealgilles](https://github.com/therealgilles))

**Other Changes:**

- chore: CHANGELOG.md and version numbers updated [\#705](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/705) ([kidunot89](https://github.com/kidunot89))
- devops: Test Scripts updated. [\#702](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/702) ([kidunot89](https://github.com/kidunot89))
- Updates WPGRAPHQL\_WOOCOMMERCE\_VERSION constant to match plugin version. [\#676](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/676) ([jmotes](https://github.com/jmotes))

## [v0.12.0](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.12.0) (2022-12-07)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.11.2...v0.12.0)

**Breaking changes:**

- fix: Connections need to connect to Types that implement the Node interface [\#675](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/675) ([jasonbahl](https://github.com/jasonbahl))

**New Features:**

- fix: product variation raw price not visible for public users [\#671](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/671) ([creative-andrew](https://github.com/creative-andrew))

**Fixed:**

- Remove error when search coupons [\#672](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/672) ([AVert](https://github.com/AVert))
- Change deprecated method is\_graphql\_request [\#667](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/667) ([fabiojundev](https://github.com/fabiojundev))

## [v0.11.2](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.11.2) (2022-08-29)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.11.1...v0.11.2)

**New Features:**

- feat: Add filter hook to stock status enum [\#634](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/634) ([matthijs166](https://github.com/matthijs166))

**Fixed:**

- fix: is\_post\_private overrode in WC\_Post model abstract class [\#651](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/651) ([kidunot89](https://github.com/kidunot89))
- fix: temporary customers node fix applied and tested. [\#650](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/650) ([kidunot89](https://github.com/kidunot89))
- fix: Cart emptied after checkout [\#649](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/649) ([kidunot89](https://github.com/kidunot89))
- fix: Most product attribute fields made nullable [\#648](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/648) ([kidunot89](https://github.com/kidunot89))
- chore: WPGraphQL v1.9.x connection resolver support added and autoloader removed. [\#647](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/647) ([kidunot89](https://github.com/kidunot89))

**Other Changes:**

- \(Query\) Format currency in cart type [\#619](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/619) ([victormattosvm](https://github.com/victormattosvm))
- chore: Old docs removed. New logo added. [\#652](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/652) ([kidunot89](https://github.com/kidunot89))

## [v0.11.1](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.11.1) (2022-06-30)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.11.0...v0.11.1)

**New Features:**

- chore: PHP-JWT upgraded to v6.1.0 [\#633](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/633) ([kidunot89](https://github.com/kidunot89))
- feat: "id" field added to "MetaDataInput" type [\#631](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/631) ([kidunot89](https://github.com/kidunot89))

## [v0.11.0](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.11.0) (2022-03-15)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.10.7...v0.11.0)

**Breaking changes:**

- fix: QLSessionHandler behaviour changes and QLSessionHandlerTest wpunit test added [\#616](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/616) ([kidunot89](https://github.com/kidunot89))
- feat: "product" and "variation" connections added to LineItem type [\#604](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/604) ([kidunot89](https://github.com/kidunot89))
- fix: Product Attribute naming conventions changed [\#603](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/603) ([kidunot89](https://github.com/kidunot89))

**Fixed:**

- fix: warnings in fillCart with empty coupons/shippingMethods [\#613](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/613) ([khlieng](https://github.com/khlieng))
- add return array to get\_query\_args because without this endpoint show error [\#610](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/610) ([krystianjj](https://github.com/krystianjj))
- Get $order in checkout mutation [\#605](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/605) ([oskarmodig](https://github.com/oskarmodig))
- Fix Fee name not showing [\#602](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/602) ([scottyzen](https://github.com/scottyzen))

## [v0.10.7](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.10.7) (2022-01-25)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.10.6...v0.10.7)

**Fixed:**

- fix: change deprecated incr\_cache\_prefix [\#598](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/598) ([fabiojundev](https://github.com/fabiojundev))
- \[Bugfix\] Update order status [\#595](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/595) ([victormattosvm](https://github.com/victormattosvm))
- Update class-root-query.php [\#584](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/584) ([stevezehngut](https://github.com/stevezehngut))

## [v0.10.6](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.10.6) (2021-11-04)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.10.5...v0.10.6)

**Fixed:**

- hotfix: Fixes breaks created by WPGraphQL v1.6.7 [\#580](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/580) ([kidunot89](https://github.com/kidunot89))

## [v0.10.5](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.10.5) (2021-10-26)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.10.4...v0.10.5)

**New Features:**

- feat: applied coupon description field added [\#572](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/572) ([kidunot89](https://github.com/kidunot89))
- chore: CartItem product connection resolvers updated. [\#571](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/571) ([kidunot89](https://github.com/kidunot89))

**Fixed:**

- fix: order item connection cursor fixed. [\#574](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/574) ([kidunot89](https://github.com/kidunot89))
- fix: updateReview input requirements fixed [\#570](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/570) ([kidunot89](https://github.com/kidunot89))
- fix: respect woocommerce tax display settings in cart [\#567](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/567) ([florianbepunkt](https://github.com/florianbepunkt))
- make order processing methods static [\#566](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/566) ([oskarmodig](https://github.com/oskarmodig))
- Product and order connections queryClass set. [\#550](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/550) ([kidunot89](https://github.com/kidunot89))

## [v0.10.4](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.10.4) (2021-09-08)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.10.3...v0.10.4)

**Fixed:**

- fix: session expiration extended to 2 weeks [\#551](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/551) ([kidunot89](https://github.com/kidunot89))
- Replace deprecated function nonce\_user\_logged\_out [\#547](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/547) ([kpoelhekke](https://github.com/kpoelhekke))
- Fix raw pricing returning null [\#544](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/544) ([dpacmittal](https://github.com/dpacmittal))
- bugfix: customer call returns session token for guest users [\#541](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/541) ([johnforte](https://github.com/johnforte))
- Fix: pass expected checkout validation WP\_Error instance [\#531](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/531) ([jeebay](https://github.com/jeebay))

**Other Changes:**

- Update Quick Install in README.md [\#538](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/538) ([ramyareye](https://github.com/ramyareye))

## [v0.10.3](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.10.3) (2021-08-11)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.10.2...v0.10.3)

**New Features:**

- Updates to be compatible with WPGraphQL v1.6.1 [\#537](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/537) ([kidunot89](https://github.com/kidunot89))

## [v0.10.2](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.10.2) (2021-07-07)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.10.1...v0.10.2)

**Fixed:**

- Bugfix/global autoloader support [\#524](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/524) ([kidunot89](https://github.com/kidunot89))

## [v0.10.1](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.10.1) (2021-07-06)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.0-beta...v0.10.1)

**New Features:**

- CartItem Product edge field "simpleAttributes" implemented and tested. [\#521](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/521) ([kidunot89](https://github.com/kidunot89))
- Support for custom order statuses. [\#518](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/518) ([kidunot89](https://github.com/kidunot89))
- Coupon mutations added. [\#510](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/510) ([kidunot89](https://github.com/kidunot89))

**Fixed:**

- Fix: product connection sorting [\#522](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/522) ([kidunot89](https://github.com/kidunot89))
- Fix: Access denied state for coupon and order connections. [\#523](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/523) ([kidunot89](https://github.com/kidunot89))

## [v0.0-beta](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.0-beta) (2021-07-05)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.10.0...v0.0-beta)

## [v0.10.0](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.10.0) (2021-06-14)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.8.1...v0.10.0)

**Fixed:**

- "Customer\_Connection\_Resolver" deprecated. [\#511](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/511) ([kidunot89](https://github.com/kidunot89))
- Cart\_Item\_Connection\_Resolver pagination fixed. [\#509](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/509) ([kidunot89](https://github.com/kidunot89))
- Update class-checkout-mutation.php [\#507](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/507) ([manuelsampl](https://github.com/manuelsampl))
- "Order/Refund\_Connection\_Resolver" classes deprecated [\#500](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/500) ([kidunot89](https://github.com/kidunot89))
- "Coupon\_Connection\_Resolver" deprecated. [\#497](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/497) ([kidunot89](https://github.com/kidunot89))
- "Product\_Connection\_Resolver" class deprecated [\#495](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/495) ([kidunot89](https://github.com/kidunot89))
- Session Transaction Manager bugfixes and enhancements [\#492](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/492) ([jacobarriola](https://github.com/jacobarriola))
- Checkout mutation: Add or Update order metas [\#484](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/484) ([adrienpicard](https://github.com/adrienpicard))

**Other Changes:**

- update plugin URL to point to the actual repo [\#459](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/459) ([andrewminion-luminfire](https://github.com/andrewminion-luminfire))

## [v0.8.1](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.8.1) (2021-03-17)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.8.0...v0.8.1)

**New Features:**

- Added refund date field [\#450](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/450) ([hilmerx](https://github.com/hilmerx))

**Fixed:**

- Bugfix/product reviews connections [\#457](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/457) ([kidunot89](https://github.com/kidunot89))
- JSON stringify string to match expectation of "extraData" on cartItem [\#453](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/453) ([davevanhoorn](https://github.com/davevanhoorn))

**Other Changes:**

- \[Docs\]: homepage typo fixes [\#445](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/445) ([jacobarriola](https://github.com/jacobarriola))
- Release v0.8.0 [\#444](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/444) ([kidunot89](https://github.com/kidunot89))

## [v0.8.0](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.8.0) (2021-03-01)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.7.0...v0.8.0)

**Breaking changes:**

- Cart transaction queue refactored [\#398](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/398) ([kidunot89](https://github.com/kidunot89))

**New Features:**

- New cart mutations and cart bugfixes. [\#439](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/439) ([kidunot89](https://github.com/kidunot89))
- Docker/Codeception/CI configurations overhauled [\#416](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/416) ([kidunot89](https://github.com/kidunot89))
- implements metadata for customer register and update [\#402](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/402) ([believelody](https://github.com/believelody))

**Fixed:**

- Variation image: guard against null image\_id [\#441](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/441) ([jacobarriola](https://github.com/jacobarriola))
- Typecast added to "MetaData" type field resolvers return values [\#430](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/430) ([kidunot89](https://github.com/kidunot89))
- Bugfix variations [\#424](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/424) ([kidunot89](https://github.com/kidunot89))
- Variable product pricing ranges fixed. [\#387](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/387) ([kidunot89](https://github.com/kidunot89))

**Other Changes:**

- Update Slack link [\#417](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/417) ([jasonbahl](https://github.com/jasonbahl))
- \[Docs\]: Fix 'Edit on GitHub' link [\#395](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/395) ([jacobarriola](https://github.com/jacobarriola))
- \[Docs\]: Update sample queries with latest schema changes [\#394](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/394) ([jacobarriola](https://github.com/jacobarriola))
- WPGraphQLTestCase implemented [\#322](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/322) ([kidunot89](https://github.com/kidunot89))

## [v0.7.0](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.7.0) (2020-11-24)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.6.2...v0.7.0)

**Fixed:**

- Allow multiple "orderby" fields [\#374](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/374) ([lstellway](https://github.com/lstellway))

**Other Changes:**

- Release v0.7.0 [\#383](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/383) ([kidunot89](https://github.com/kidunot89))



\* *This Changelog was automatically generated by [github_changelog_generator](https://github.com/github-changelog-generator/github-changelog-generator)*
