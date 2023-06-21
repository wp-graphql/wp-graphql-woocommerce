<p align="center">
  <img src="./logo.svg" width="200px" alt="WPGraphQL WooCommerce (WooGraphQL) Logo" />
</p>

# WPGraphQL WooCommerce (WooGraphQL)

<a href="https://woographql.com" target="_blank">Website</a> • <a href="https://woographql.com/docs" target="_blank">Docs</a> • <a href="https://www.axistaylor.com" target="_blank">AxisTaylor</a> • <a href="https://join.slack.com/t/wp-graphql/shared_invite/zt-3vloo60z-PpJV2PFIwEathWDOxCTTLA" target="_blank">Join Slack</a>

[![Automated-Testing](https://github.com/wp-graphql/wp-graphql-woocommerce/workflows/Automated-Testing/badge.svg?branch=develop)](https://github.com/wp-graphql/wp-graphql-woocommerce/actions?query=workflow%3A%22Automated-Testing%22) [![Coding-Standards](https://github.com/wp-graphql/wp-graphql-woocommerce/workflows/Coding-Standards/badge.svg?branch=develop)](https://github.com/wp-graphql/wp-graphql-woocommerce/actions?query=workflow%3A%22Coding-Standards%22) [![Coverage Status](https://coveralls.io/repos/github/wp-graphql/wp-graphql-woocommerce/badge.svg?branch=develop)](https://coveralls.io/github/wp-graphql/wp-graphql-woocommerce?branch=develop) [![Financial Contributors on Open Collective](https://opencollective.com/woographql/all/badge.svg?label=financial+contributors)](https://opencollective.com/woographql)

## Install

### Installing manually

1. Install and activate [WPGraphQL](https://wpgraphql.com/) and [WooCommerce](https://woocommerce.com/)
2. Download wp-graphql-woocommerce.zip file under the Assets section for the most stable [release](https://github.com/wp-graphql/wp-graphql-woocommerce/releases) from the repository into your WordPress plugin directory & activate the plugin.
3. Set your GraphQL client endpoint to the GraphQL endpoint of your site. Typically, this is your-store.domain/graphql.

### Installing w/ Composer

_This is the recommend way for users using unique installations like WP Bedrock or SpinupWP._

1. Install [WordPress](https://composer.rarst.net/) and [WooCommerce](https://wpackagist.org/search?q=woocommerce&type=plugin&search=).
2. Install WPGraphQL and WooGraphQL by running `composer require wp-graphql/wp-graphql wp-graphql/wp-graphql-woocommerce`
3. Set your GraphQL client endpoint to the GraphQL endpoint of your site. Typically, this is `your-store.domain/graphql`. _NOTE: for typically Bedrock or SpinupWP setups it'll be `your-store.domain/wp/graphql` by default.

### Optional extras

- Install & activate [WPGraphQL-JWT-Authentication](https://github.com/wp-graphql/wp-graphql-jwt-authentication) to add a `login` mutation that returns a JSON Web Token.
- Install & activate [WPGraphQL-CORS](https://github.com/funkhaus/wp-graphql-cors) to add an extra layer of security using HTTP CORS and some of WPGraphQL advanced functionality.

## What can you do with this extension?

- Query your shops products and variations with complex filtering options.
- Query customers, orders, coupons, and refunds (*).
- Manage a customer's session with JWTs and cart/customer queries and mutations(*).
- Create orders manually (*), automatically with the checkout mutation, or pass a customer's session to the WooCommerce checkout page in your theme for complete payment gateway support (#).

(*) These operations have user restrictions. Please read up on authenticating an user with [here](https://www.wpgraphql.com/docs/authentication-and-authorization/), then view [this React/Apollo example](https://github.com/wp-graphql/wp-graphql-woocommerce/pull/88) with the added on usage of customer session Token.

(#) This is the recommended method on checkout. You can read it's usage in [this](https://jacobarriola.com/post/hosted-woocommerce-checkout-headless-application#load-the-session-in-woocommerce) excellent write-up by @jacobarriola

## Why don't the WooCommerce CPT GraphQL types support all the same features as most WordPress CPTs that WPGraphQL exposes?

The CPTs as well as most of the data objects that WooCommerce defines are wrapped in a object managers distributed by a data store system.

This data store system allows for each individual data object to be defined however needed. What this means is, although by out of the box objects like **products**, **orders**, and **coupons** are defined as WordPress CPTs they don't have to be.

This is what also enables WooCommerce to store most meta connected to these CPTs in separate tables. The object data doesn't even have to be in the same database if the object's data store designed to manage somewhere else, but we are getting out of the scope of this question.

What does all this :point_up: have to do with WooCommerce's CPTs' functionality? Well, the object managers distributed by the data store are WooGraphQL first point of contact for pretty much everything. Unlike the most common CPTs which use a **WP_Post** object as their data source and a **WPGraphQL\Model\Post** object as their model, WooGraphQL uses object managers as the data source for the CPTs and each individual has it's own model with it's own set of permissions and restrictions.

This has led to some friction is certain areas of the schema where WooGraphQL support is lacking. I'm sorry for the inconvience, myself and whole **WPGraphQL** org are working to reduce this friction and WooGraphQL properly integrated with all **WPGraphQL** + **WPGraphQL ACF** features.

Thank you for your patience
@kidunot89

## Future Features

- Product CRUD mutations.
- And some other stuff I'm sure :thinking_face:

## For WooCommerce Extensions Support

WooGraphQL Pro is an advanced version of WooGraphQL that provides compatibility with a variety of popular WooCommerce extensions. This compatibility empowers you to leverage these extensions within the context of the GraphQL API, thereby enabling you to build more dynamic and powerful headless eCommerce applications.

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

To install and activate WooGraphQL Pro, follow these steps:

1. Purchase WooGraphQL Pro from our official [website](https://woographql.com/pro).
2. After purchase you should find yourself on your account dashboard. Go to the `Licenses` page and generate and new license and copy it for later.
3. Next go to the `Downloads` page and download the latest version of WooGraphQL Pro.
4. Go to your WordPress Admin Dashboard, navigate to Plugins > Add New > Upload Plugin, and upload the `woographql-pro.zip` file you downloaded.
5. After uploading, click 'Activate Plugin' to activate WooGraphQL Pro.

### Enabling Schema Support for Installed Extensions

To enable schema support for your installed extensions, follow these steps:

1. Navigate to the WPGraphQL settings page on your WordPress Admin Dashboard.
2. Click on the 'WooGraphQL' tab.
3. Here, you'll find a list of WooGraphQL configuration options. Go below to the **WooGraphQL Pro** section and paste in license and check the boxes next to your installed extensions to enable schema support for them.

Note: The 'Enable Unsupported Product Type' option can be found on the same settings tab. If you enable this option, any product type without a proper GraphQL type will default to the `UnsupportedProduct` type, which is identical to the `SimpleProduct` type. With this type, the client can use the `metaData` field to get a `string` representation of the meta data on the type. This could potentially be all that's needed for simpler product types.

With WooGraphQL Pro and your chosen extensions now installed, you're ready to build more sophisticated, feature-rich eCommerce solutions with WordPress and WooCommerce.

## Playground

Feel free to test out the extension using this [GraphiQL Playground](https://woographql.com/playground). The playground allows you to execute queries and mutations, as well as view the schema (*).

(*) I have a tendency to forget to update the playground between releases :sweat_smile:, so if you believe this to be the case look me up somewhere on this page and lemme know :man_shrugging:

## Wanna help support WooGraphQL's future

- Sponsor **@kidunot89** *(WooGraphQL Creator/Developer)* on **[Github](https://github.com/sponsors/kidunot89)**
- Sponsor **WooGraphQL** on **[OpenCollective](https://opencollective.com/woographql)**
- Sponsor **WPGraphQL** on **[OpenCollective](http://opencollective.com/wp-graphql)**
- Sponsor **GraphQL-PHP** on **[OpenCollective](https://opencollective.com/webonyx-graphql-php)**
- Or **[Contribute](./CONTRIBUTING.md)**

## Follow [![alt text](http://i.imgur.com/tXSoThF.png)](https://twitter.com/woographql)[![alt text](http://i.imgur.com/P3YfQoD.png)](https://www.facebook.com/woographql)

## Demo/Examples

- Examples with Next.js
  - [Next.js WooCommerce Theme](https://github.com/imranhsayed/woo-next) [[source]](https://github.com/imranhsayed/woo-next) [[demo video]](https://youtu.be/EGjY3X868YQ)
- Examples with Gatsby
  - [Gatsby WooCommerce Theme](https://gatsby-woocommerce-theme.netlify.app/) [[source]](https://github.com/imranhsayed/gatsby-woocommerce-themes) [[demo video]](https://youtu.be/ygaE8ZdPEX8)

## Who using WooGraphQL

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
