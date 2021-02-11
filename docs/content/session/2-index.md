---
title: "What is a JWT-Auth token and WooCommerce Session Token?"
metaTitle: "What is a JWT-Auth token and WooCommerce Session Token? | WooGraphQL Docs | AxisTaylor"
metaDescription: "Learn the uses and differences between a JWT-Auth token and WooCommerce Session token."
---

A JWT-Auth token refers to any JSON Web Token (JWT) sent through the HTTP "Authorization" header for the purpose of authenticating the end-user as a WordPress user before the core logic of the HTTP request has executed.

A WooCommerce session token is a JWT created by WooGraphQL for the sole purpose of identifying the WooCommerce customer session of the end-user.

They both work by storing the user/customer ID along with some info about who made and whose to recieve the token in an object and encrypting that into a string a.k.a. a JWT.
WPGraphQL-JWT-Authentication and WooGraphQL makes these tokens available through way of queryable fields
```graphql
query {
  customer {
    jwtAuthToken # JWT User Authentication token
    sessionToken # WooCommerce Session token
  }
}
```
However, the user context typically has to be already be setup before this fields are accessible.

Most of the time the way to generate the JWTs that will setup the user context in all future requests is to run a mutation the changes the context by authenticating an user or creating a session.

For instance the `login()` mutation provided by the WPGraphQL-JWT-Authentication plugin is perfect for this, and when used with WooGraphQL the following mutation can be used.
```graphql
mutation {
  login(input: {clientMutationId: "someId", username: "admin", password: "password"}) {
    customer {
      jwtAuthToken # JWT User Authentication token
      sessionToken # WooCommerce Session token
    }
  }
}
```

After retrieving these tokens they are to be provided as headers "Authorization" and "woocommerce-session" for all future request until you wish to end the end-user session by means of logging out or expiration.
There are a number of ways to do this so I won't be going into too much detail here, but if using a client-side library like Apollo or Relay, just lookup how to access the middleware layer for your library of choice.

Once you set the newly created tokens are set in their proper headers and a new request is made. The before the `init` hook is executed the tokens are decrypted and their identifiers verified, then this is where the tokens differ.

The JWT-Auth token is rather simple. Essentially, you can just say it runs `wp_set_current_user()` on the encrypted user ID.

The WooCommerce session token also simple but with a little complex. It searches the database for session data connected to the encrypted customer ID. _Please note: the customer ID maybe a guest ID that doesn't have a correspending user ID. Good to know_

JWT-Auth tokens and WooCommerce session tokens can both be used to identify an end-user's WooCommerce session but it's recommended that you use both, unless you intend to implement a guest checkout then you can use just the WooCommerce session token.
