---
title: "Understanding the User Session"
metaTitle: "Understanding a WooGraphQL-managed session | WooGraphQL Docs | AxisTaylor"
metaDescription: "Learn in-depth about managing a WooCommerce user session in a JavaScript application"
---

import Link from '../src/components/link'

Typically, GraphQL requests don't have an effect on the **context of the PHP environment**. This is due to any context hardly ever being needed when querying for public data. However, when dealing with private data, it's best practice to hide values, fields, or possibly types behind some kind of context.

When I refer to **context of the PHP environment**, I'm referring to any preset values assigned PHP globals and storages like PHP sessions and PHP cookies, when the WPGraphQL server begin to process the query.

To elaborate further, **user context** could be any preset value related to end-user whose browser sent the request. These values can be used by WordPress to do common tasks like authenticate the end-user or identify some data object related the end-user.

In HTTP requests made when navigating links on a WordPress site, those common tasks are managed by cookies created in previous requests and saved to the end-user's computer. These cookies are then sent by the end-user's browser along with any HTTP request sent to WordPress from within the WordPress domain.

On the other hand, when dealing with an decoupled front-end application that makes GraphQL requests from external origins and domains, using cookies to manage the **user context** of these requests is difficult to setup and not always possible. Sending JSON Web Tokens as HTTP headers in the GraphQL request is the recommended solution for creating context.

JSON Web Token (JWT) solutions for the purpose of WordPress user authentication are available. Most commonly in the form of plugins, that authenticate the end-user by decoding and reading JWTs sent through the "Authorization" HTTP header container the end-user's WordPress User ID.
## Why is this important?

WooGraphQL has the ability to take advantage of JWTs build and distribute in-house to provide a gateway to WooCommerce's functionality that rely user context.

This includes the shopping cart, shipping calculator and guest data/orders.

The shopping cart data and guest data are saved temporarily in the WordPress database, so rolling your own objects for these client-side won't be necessary if using methods demonstrated throughout this section.
