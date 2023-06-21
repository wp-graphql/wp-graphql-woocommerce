# Changelog

## [v0.14.0](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.14.0) (2023-06-21)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.13.0...v0.14.0)

**Breaking changes:**

- feat: HPOS support added. [\#748](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/748) ([kidunot89](https://github.com/kidunot89))

**Other Changes:**

- chore: setup PHPStan [\#746](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/746) ([justlevine](https://github.com/justlevine))

## [v0.13.0](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.13.0) (2023-05-22)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.12.5...v0.13.0)

**New Features:**

- feat: Authorizing URLs introduced and Harmonizing with WordPress guide written. [\#745](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/745) ([kidunot89](https://github.com/kidunot89))

**Other Changes:**

- devops: Docs refactored heavily and provided meta data. [\#743](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/743) ([kidunot89](https://github.com/kidunot89))

## [v0.12.5](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.12.5) (2023-04-21)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.12.4...v0.12.5)

**New Features:**

- feat: woographql\_viewable\_order\_types hook added [\#741](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/741) ([kidunot89](https://github.com/kidunot89))
- feat: filters added to product and order orderby enumerations [\#737](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/737) ([kidunot89](https://github.com/kidunot89))
- feat: Country queries implemented. [\#736](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/736) ([kidunot89](https://github.com/kidunot89))
- feat: payment method mutations and fields implemented. [\#735](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/735) ([kidunot89](https://github.com/kidunot89))

**Fixed:**

- fix: PaymentToken child types fixed. [\#739](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/739) ([kidunot89](https://github.com/kidunot89))

## [v0.12.4](https://github.com/wp-graphql/wp-graphql-woocommerce/tree/v0.12.4) (2023-04-19)

[Full Changelog](https://github.com/wp-graphql/wp-graphql-woocommerce/compare/v0.12.3...v0.12.4)

**New Features:**

- feat: Docs Restored. Unsupported product type setting implemented. [\#731](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/731) ([kidunot89](https://github.com/kidunot89))

**Fixed:**

- fix: Case-sensitive apply coupon mutation fix [\#729](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/729) ([sbolinger-godaddy](https://github.com/sbolinger-godaddy))
- fix: Meta data type error fixed. [\#728](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/728) ([kidunot89](https://github.com/kidunot89))

**Other Changes:**

- schema link added to docs toc. [\#734](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/734) ([kidunot89](https://github.com/kidunot89))

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

- devops: Test Scripts updated. [\#702](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/702) ([kidunot89](https://github.com/kidunot89))

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



\* *This Changelog was automatically generated by [github_changelog_generator](https://github.com/github-changelog-generator/github-changelog-generator)*
