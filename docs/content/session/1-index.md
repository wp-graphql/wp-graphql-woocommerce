---
title: "Why is this important?"
metaTitle: "Why is understanding a WooGraphQL's session management important? | WooGraphQL Docs | AxisTaylor"
metaDescription: "Learn the responsibilites and capabilities of the custom session handler WooGraphQL uses."
---

Typically, GraphQL requests don't have an effect on the **user context of the PHP environment** when a request is sent. This is due to user context (or custom context) rarely ever being necessary when querying for public data.  However, when dealing with private data, you are going to want to hide values, fields, or possibly types behind user context. 

When I refer to user context, I'm referring to changes to the environment that cause user checks like the following
```php
current_user_can( 'capability_name' )
```
or
```php
is_user_logged_in()
```
to return `true` when executing the query.

To create some context, typical means authenticating the end-user or start a session and providing means for identifying the end-user in all request to follow, in the form of some kind of token like a HTTP cookie or JSON Web token.

For WordPress user authentication, a WPGraphQL request made from the same origin or domain will usually pass any existing HTTP cookies along with the request. This includes cookies created by WordPress' native login methods.

When dealing with front-end application and GraphQL requests made from external origins and domains, you're most likely be better off using a JSON Web Token User Authentication solution.

A JSON Web Token (JWT) User Authentication solution is a tool, most commonly in the form of a wordpress plugin, that provide a means to authenticate an user through the HTTP "Authorization" header by passing a JWT as the value.
There are a couple of solution out there but throughout the documentation you'll find reference to the WPGraphQL-JWT-Authentication plugin, so that will be the JWT User Authentication solution discussed here.

So to answer the question of "why is this important?"

WooGraphQL takes advantage of the user context created by JWT User Authentication solutions and an in house JWT solution to provide a gateway to WooCommerce's functionality that relies the user context.
These features are the shopping cart, shipping calculator, guest data/orders. The shopping cart data and guest data, are saved temporarily in the database, so rolling your own objects for these client-side won't be necessary if using methods demonstrated throughout this section.
