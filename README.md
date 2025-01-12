<p align="center">
  <img src="./logo.svg" width="200px" alt="WPGraphQL for WooCommerce (WooGraphQL) Logo" />
</p>

# WPGraphQL for WooCommerce

<a href="https://woographql.com" target="_blank">Website</a> • <a href="https://woographql.com/docs" target="_blank">Docs</a> • <a href="https://woographql.com/schema" target="_blank">Schema</a> • <a href="https://woographql.com/playground" target="_blank">Playground</a> • <a href="https://woographql.com/about" target="_blank">About</a> • <a href="https://join.slack.com/t/wp-graphql/shared_invite/zt-3vloo60z-PpJV2PFIwEathWDOxCTTLA" target="_blank">Join Slack</a>

[![Automated-Testing](https://github.com/wp-graphql/wp-graphql-woocommerce/workflows/Automated-Testing/badge.svg?branch=develop)](https://github.com/wp-graphql/wp-graphql-woocommerce/actions?query=workflow%3A%22Automated-Testing%22) [![Coding Standards](https://github.com/wp-graphql/wp-graphql-woocommerce/actions/workflows/lint-code.yml/badge.svg)](https://github.com/wp-graphql/wp-graphql-woocommerce/actions/workflows/lint-code.yml) [![Coverage Status](https://coveralls.io/repos/github/wp-graphql/wp-graphql-woocommerce/badge.svg?branch=develop)](https://coveralls.io/github/wp-graphql/wp-graphql-woocommerce?branch=develop) [![Financial Contributors on Open Collective](https://opencollective.com/woographql/all/badge.svg?label=financial+contributors)](https://opencollective.com/woographql)

## Install

### Installing Manually

1. Install and activate [WPGraphQL](https://wpgraphql.com/) and [WooCommerce](https://woocommerce.com/).
2. Download the `wp-graphql-woocommerce.zip` file from the Assets section of the most stable [release](https://github.com/wp-graphql/wp-graphql-woocommerce/releases) and activate the plugin in your WordPress directory.
3. Set your GraphQL client endpoint to your site's GraphQL endpoint. Typically, this is `your-store.domain/graphql`.

### Installing with Composer

**This method is recommended for users with unique installations like WP Bedrock or SpinupWP.**

1. Install [WordPress](https://composer.rarst.net/) and [WooCommerce](https://wpackagist.org/search?q=woocommerce&type=plugin&search=).
2. Install WPGraphQL and WPGraphQL for WooCommerce by running `composer require wp-graphql/wp-graphql wp-graphql/wp-graphql-woocommerce`.
3. Set your GraphQL client endpoint to your site's GraphQL endpoint. For typical Bedrock or SpinupWP setups, the default will be `your-store.domain/wp/graphql`.

### Optional Extras

- Install & activate [WPGraphQL-JWT-Authentication](https://github.com/wp-graphql/wp-graphql-jwt-authentication) to introduce a `login` mutation that returns a JSON Web Token.
- Install & activate [WPGraphQL Headless Login](https://github.com/AxeWP/wp-graphql-headless-login) to introduce a `login` mutation with OAuth2 client support. **Shouldn't be used with WPGraphQL-JWT-Authentication**
- Install & activate [WPGraphQL-CORS](https://github.com/funkhaus/wp-graphql-cors) for enhanced security via HTTP CORS and to utilize some advanced WPGraphQL features.

## What Can You Do with This Extension?

- Query your shop's products and variations with detailed filtering options.
- Query customers, orders, coupons, and refunds. Note: Operations have user restrictions.
- Manage customer sessions using JWTs, and use cart/customer queries and mutations. Note: Operations have user restrictions.
- Manually create orders, automate order creation with the checkout mutation, or delegate a customer's session to the WooCommerce checkout page in your theme for comprehensive payment gateway support.

(*) These operations have user restrictions. Learn how to utilize them correctly at the resources listed below:

- [Authentication and Authorization](https://www.wpgraphql.com/docs/authentication-and-authorization/)
- [Configuring a GraphQL Client for WooCommerce User Session Management](https://woographql.com/docs/configuring-graphql-client-for-user-session)
- [Handling User Authentication](https://woographql.com/docs/handling-user-authentication)
- [Handling User Session and Using Cart Mutations](https://woographql.com/docs/handling-user-session-and-using-cart-mutations)
- [Using Checkout Mutation and Order Mutations](https://woographql.com/docs/using-checkout-mutation-and-order-mutations)
- [Using Order Data](https://woographql.com/docs/using-order-data)

(#) The recommended method to checkout, taking full advantage of WooCommerce's payment gateways for payment process and other checkout-related extenstions, is to pass the session back to the WordPress installation and let WooCommerce take over from the tradition checkout page. Learn more about this approach below.

- [Harmonizing with WordPress](https://woographql.com/docs/harmonizing-with-wordpress)
- [Hosted WooCommerce Checkout for a Headless Application](https://jacobarriola.com/post/hosted-woocommerce-checkout-headless-application)

Alternatively, you can create a custom checkout form and process payments externally. Here is some resources for that as well.

- [Headless WooCommerce Checkout with Gatsby and Stripe](https://jacobarriola.com/post/headless-woocommerce-stripe-checkout-graphql#step-5-load-the-checkoutform-component-in-your-checkout-page)

## Why Don't WooCommerce CPT GraphQL Types Support All the Features WPGraphQL Exposes for Most WordPress CPTs?

WooCommerce's Custom Post Types (CPTs) and most data objects are managed by a data store system. This system allows for flexible data object definitions. While objects like **products**, **orders**, and **coupons** are defined as WordPress CPTs by default, they don't have to be.

This flexibility also lets WooCommerce store metadata for these CPTs in separate tables, and the data doesn't necessarily have to reside in the same database.

The data store system and its object managers are WooGraphQL's primary contact points. Unlike standard CPTs, which use a **WP_Post** object for data sourcing and a **WPGraphQL\Model\Post** object for modeling, WPGraphQL for WooCommerce engages object managers for its data source. Each object type has a unique model with distinct permissions and restrictions.

Such a setup has resulted in some disparities between the schema where WPGraphQL for WooCommerce support might be lacking. We apologize for any inconvenience. Both I and the entire **WPGraphQL** team are actively working to harmonize WPGraphQL for WooCommerce with all **WPGraphQL** and **WPGraphQL ACF** features.

Thank you for your patience :smile:
[@kidunot89](https://github.com/kidunot89)

## Future Features

- Product CRUD mutations.
- And some other stuff I'm sure :thinking:

## For WooCommerce Extensions Support

**[WooGraphQL Pro](https://woographql.com/pro)** is an extension of WPGraphQL for WooCommerce that provides compatibility with a variety of popular WooCommerce extensions. This compatibility empowers you to leverage these extensions within the context of the GraphQL API, thereby enabling you to build more dynamic and powerful headless eCommerce applications.

The following WooCommerce extensions are supported by WooGraphQL Pro:

- WooCommerce Subscriptions
- WooCommerce Product Bundles
- WooCommerce Product Add-Ons
- WooCommerce Composite Products

### Installing Supported WooCommerce Extensions (Optional)

If you wish to use any of the supported WooCommerce extensions with WooGraphQL Pro, follow these steps:

1. Purchase your desired extensions from the WooCommerce marketplace. The supported extensions are listed above.
2. Download the `.zip` file(s) for your purchased extension(s) from your WooCommerce account.
3. In your WordPress Admin Dashboard, navigate to Plugins > Add New > Upload Plugin, and upload the downloaded `.zip` file(s).
4. Once the upload is complete, click on 'Activate Plugin' to activate the extension(s).

### Installing and Activating WooGraphQL Pro

To install and activate **WooGraphQL Pro**, follow these steps:

1. Purchase WooGraphQL Pro from our official [website](https://woographql.com/pro).
2. After purchase you should find yourself on your account dashboard. Go to the `Licenses` page and generate and new license and copy it for later.
3. Next go to the `Downloads` page and download the latest version of WooGraphQL Pro.
4. Go to your WordPress Admin Dashboard, navigate to Plugins > Add New > Upload Plugin, and upload the `woographql-pro.zip` file you downloaded.
5. After uploading, click 'Activate Plugin' to activate WooGraphQL Pro.

### Enabling Schema Support for Installed Extensions

To enable schema support for your installed extensions, follow these steps:

1. Navigate to the WPGraphQL settings page on your WordPress Admin Dashboard.
2. Click on the 'WooGraphQL' tab.
3. Here, you'll find a list of WPGraphQL for WooCommerce configuration options. Go below to the **WooGraphQL Pro** section and paste in license and check the boxes next to your installed extensions to enable schema support for them.

Note: The 'Enable Unsupported Product Type' option can be found on the same settings tab. If you enable this option, any product type without a proper GraphQL type will default to the `UnsupportedProduct` type, which is identical to the `SimpleProduct` type. With this type, the client can use the `metaData` field to get a `string` representation of the meta data on the type. This could potentially be all that's needed for simpler product types.

With WooGraphQL Pro and your chosen extensions now installed, you're ready to build more sophisticated, feature-rich eCommerce solutions with WordPress and WooCommerce.

## Development Tools

### Playground

Feel free to test out the extension using this [GraphiQL Playground](https://woographql.com/playground). The playground allows you to execute queries and mutations, as well as view the schema (*).

(*) I have a tendency to forget to update the playground between releases :sweat_smile:, so if you believe this to be the case look me up somewhere on this page and lemme know :man_shrugging:

### `create-woonext-app` CLI and `@woographql` packages

Designed to both streamline development for individuals and teams looking to utilize WooCommerce + WooCommerce extensions in larger project and not waste too much to much time on the particulars of WPGraphQL for WooCommerce like session management or checkout, the **[`create-woonext-app`](https://www.npmjs.com/package/create-woonext-app)** CLI generates a pre-created e-commerce application on [Next.js](https://nextjs.org) application tailored to the developer/team.

```bash
npx create-woonext-app <license> [options]
```

The generated application utilizes the **[`@woographql`](https://yeetsquad.net)** packages exclusive to the unlimited/annual subscribers of WooGraphQL Pro. So Go [Subscribe!](https://woographql.com/pro#pricing) :smile:. Below are more resources on the `create-woonext-app` CLI and `@woographql` packages.

- [`create-woonext-app` Homepage](https://woographql.com/create-woonext-app)
- [`create-woonext-app` on NPM](https://www.npmjs.com/package/create-woonext-app): The `create-woonext-app` README with more CLI usage instructions.
- [`create-woonext-app` Live Demo](https://woonext.woographql.com/): fully decked out putting all current possible functionalities of the `create-woonext-app` on full display.
- [`@woographql/next` README](https://yeetsquad.net/-/web/detail/@woographql/next): A Template generator CLI. Capable of generator a multitude of Next.js pages, Next.js Route Handlers, react components, react hooks, and utilities. It's also equipped to generate react component and hook stubs for speedy component create with no boilerplate.
- [`@woographql/react-hooks` README](https://yeetsquad.net/-/web/detail/@woographql/react-hooks): React hook library acting as the backbone for UI connected to session, cart, and customer.
- [`@woographql/session-utils` README](https://yeetsquad.net/-/web/detail/@woographql/session-utils): Provides utilities for managing the WPGraphQL + WPGraphQL for WooCommerce session tokens like a `TokenManager`, also provides interfaces and types for easy customization of said `TokenManager` or the complete creation of a custom `TokenManager` with minimal effort. The bundled TokenManager implemented utilizes browser storage _(Local/Session storage)_, so if you'd prefer something like [Iron Session](https://github.com/vvo/iron-session) this might be the route for you.
- [`@woographql/codegen` README](https://yeetsquad.net/-/web/detail/@woographql/codegen): A convenient wrapper for GraphQL Codegen providing a few configurations out of the box, with the ability to override the default configuration by creating a `codegen.ts` in the project root.

## Wanna help support WooGraphQL's future

- Sponsor **@kidunot89** _(WPGraphQL for WooCommerce Creator/Developer)_ on **[Github](https://github.com/sponsors/kidunot89)**
- Sponsor **WooGraphQL** on **[OpenCollective](https://opencollective.com/woographql)**
- Sponsor **WPGraphQL** on **[OpenCollective](http://opencollective.com/wp-graphql)**
- Sponsor **GraphQL-PHP** on **[OpenCollective](https://opencollective.com/webonyx-graphql-php)**
- Or **[Contribute](./CONTRIBUTING.md)**

## Follow [![alt text](http://i.imgur.com/tXSoThF.png)](https://twitter.com/woographql)[![alt text](http://i.imgur.com/P3YfQoD.png)](https://www.facebook.com/woographql)

## Demo/Examples

- Examples with Next.js
  - [WPGraphQL for WooCommerce Demo](https://github.com/kidunot89/woographql-demo)
  - [Next.js WooCommerce Theme](https://github.com/imranhsayed/woo-next) [[source]](https://github.com/imranhsayed/woo-next) [[demo video]](https://youtu.be/EGjY3X868YQ)
- Examples with Gatsby
  - [Gatsby WooCommerce Theme](https://gatsby-woocommerce-theme.netlify.app/) [[source]](https://github.com/imranhsayed/gatsby-woocommerce-themes) [[demo video]](https://youtu.be/ygaE8ZdPEX8)
- Examples with WooGraphQL Pro [[homepage]](https://woographql.com/pro)
  - [`create-woonext-app` CLI Demo](https://woonext.woographql.com) [[homepage]](https://woographql.com/create-woonext-app) [[docs]](https://www.npmjs.com/package/create-woonext-app)
  - [WPGraphQL for WooCommerce homepage](https://woographql.com)

## Who using it?

| <img src="https://www.rockymountainsewing.com/static/759d14c25bfb3243245711ce9be6600c/logo.svg" alt="Rocky Mountain Sewing & Vacuum" width="320"/> | <img src="https://russemerket.no/src/logos/Russemerket_black.svg" alt="Russemerket" width="320"/> | <img src="https://wohnparc.de/_next_public/wopa-icons/logo.svg" alt="wohnparc.de" width="320"/> |
|:---------------------------------------:|:---------------------------------------:|:---------------------------------------:|
| [Rocky Mountain Sewing & Vacuum](https://www.rockymountainsewing.com/)                     | [Russemerket](https://russemerket.no/)                     | [wohnparc.de](https://wohnparc.de/shop/)                     |

## Contributors

### Code Contributors

This project exists thanks to all the people who contribute. [[Contribute](CONTRIBUTING.md)].
<a href="https://github.com/wp-graphql/wp-graphql-woocommerce/graphs/contributors"><img src="https://opencollective.com/woographql/contributors.svg?width=890&button=false" /></a>

### Financial Contributors

Become a financial contributor and help us sustain our community. [[Contribute](https://opencollective.com/woographql/contribute)]

#### Individuals

<a href="https://opencollective.com/woographql"><img src="https://opencollective.com/woographql/individuals.svg?width=890"></a>

#### Organizations

Support this project with your organization. Your logo will show up here with a link to your website. [[Contribute](https://opencollective.com/woographql/contribute)]

<a href="https://opencollective.com/woographql/organization/0/website"><img src="https://opencollective.com/woographql/organization/0/avatar.svg"></a>
<a href="https://opencollective.com/woographql/organization/1/website"><img src="https://opencollective.com/woographql/organization/1/avatar.svg"></a>
<a href="https://opencollective.com/woographql/organization/2/website"><img src="https://opencollective.com/woographql/organization/2/avatar.svg"></a>
<a href="https://opencollective.com/woographql/organization/3/website"><img src="https://opencollective.com/woographql/organization/3/avatar.svg"></a>
<a href="https://opencollective.com/woographql/organization/4/website"><img src="https://opencollective.com/woographql/organization/4/avatar.svg"></a>
<a href="https://opencollective.com/woographql/organization/5/website"><img src="https://opencollective.com/woographql/organization/5/avatar.svg"></a>
<a href="https://opencollective.com/woographql/organization/6/website"><img src="https://opencollective.com/woographql/organization/6/avatar.svg"></a>
<a href="https://opencollective.com/woographql/organization/7/website"><img src="https://opencollective.com/woographql/organization/7/avatar.svg"></a>
<a href="https://opencollective.com/woographql/organization/8/website"><img src="https://opencollective.com/woographql/organization/8/avatar.svg"></a>
<a href="https://opencollective.com/woographql/organization/9/website"><img src="https://opencollective.com/woographql/organization/9/avatar.svg"></a>

**Disclaimer:** *WPGraphQL for WooCommerce* is an open-source WordPress plugin and WooCommerce extension developed and maintained by [Geoff Taylor](https://woographql.com/about), licensed under GPLv3. It is not owned, maintained, or affiliated with [Automattic](https://automattic.com) or [WooCommerce](https://woocommerce.com).
