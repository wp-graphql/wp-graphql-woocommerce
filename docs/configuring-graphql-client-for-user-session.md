---
title: "Configuring GraphQL Client for User Session"
description: "A step-by-step guide on how to configure your GraphQL client to handle user sessions, authentication, and more when working with WooGraphQL and WPGraphQL."
keywords: "WooGraphQL, WPGraphQL, WooCommerce, GraphQL, user session, authentication, configuration, client"
author: "Geoff Taylor"
---

# Configuring a GraphQL Client for WooCommerce User Session Management

In this section, we'll walk you through the process of configuring a GraphQL client to manage user sessions and credentials when working with WooGraphQL. By following the steps outlined in this tutorial, you'll learn how to create a GraphQL client that maintains a valid WooCommerce session in the `woocommerce_sessions` DB table. This knowledge will enable you to build robust applications that interact smoothly with WooCommerce while providing a seamless experience for your users and shortening development time.

By properly handling the session token, you can implement session pass-off functionality, allowing you to fallback on the cart page, my-account page, or any other page living in WordPress that relies on user sessions. (Note that implementing the session pass-off functionality is out of the scope of this section.) So, let's dive in and explore the intricacies of setting up a GraphQL client that effectively manages user sessions for your e-commerce store!

## Sending the `woocommerce-session` HTTP request header

When using WooGraphQL cart and customer functionality, there are certain prerequisites. A WooGraphQL session token, distributed by the QL Session Handler, must be passed as an HTTP header with the name `woocommerce-session`, prefixed with `Session `. This header should be included in all session data-altering mutations. Note that the required name `woocommerce-session` can be changed using WordPress filters.

For simple requests using `fetch`, this is quite easy to implement. Here's an example of a WooGraphQL request executed with `fetch`, performing a cart query and passing the woocommerce-session header with a value of `Session ${sessionToken}`. The `sessionToken` is read from `localStorage`.

```javascript
fetch(endpoint, {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'woocommerce-session': `Session ${sessionToken}`,
  },
  body: JSON.stringify({
    query: `
      query {
        cart {
          contents {
            nodes {
              key
              product {
                node {
                  id
                  name
                }
              }
              quantity
              subtotal
            }
          }
        }
      }
    `,
  }),
})
  .then((response) => response.json())
  .then((data) => console.log(data));
```

This works for simple streamlined applications that don't rely heavily on cart functionality. Note that this example also does not retrieve the updated token from the `woocommerce-session` HTTP response header.

And if you're using a library or framework like Apollo, configuring middleware and afterware layers are required. In this section, we'll walk you through setting up the Apollo Client and its middleware/afterware to work with WooGraphQL.

## Creating the Apollo Client instance

First, let's create the Apollo Client instance and focus on the `link` option. We'll use the `from` utility function from `@apollo/client`:

```javascript
import { from } from '@apollo/client';

// ...

const client = new ApolloClient({
  link: from([
    createSessionLink(),
    createErrorLink(),
    createUpdateLink(),
    new HttpLink({ uri: endpoint }),
  ]),
  cache: new InMemoryCache(),
});
```

In the example you see the creation of our `client`. It include middleware/afterware callbacks managed by the `ApolloLink` class. For those not familiar with it, the `ApolloLink` class allows you to customize the flow of data by defining your network's behavior as a chain of link objects. I'm stating this so you know the order of the callbacks is also important and it will be understood why as we define these callbacks themselves.

## Defining the `createSessionLink` function

Next, define the `createSessionLink` function as follows:

```javascript
import { setContext } from '@apollo/client/link/context';

function createSessionLink() {
  return setContext(async (operation) => {
    const headers = {};
    const sessionToken = await getSessionToken();

    if (sessionToken) {
      headers['woocommerce-session'] = `Session ${sessionToken}`;

      return { headers };
    }

    return {};
  });
}

//...rest of code
```

And that's our callback for applying the our session token to each request made through our client. Note that I am using the shorthand method of importing the `setContext` function, however most examples you will find will use the `ApolloLink` class directly to define the link object.

```javascript
import { ApolloLink } from '@apollo/client';

const consoleLink = new ApolloLink((operation, forward) => {
  return operation.setContext(/* our callback */);
});
```

And this works fine too, but it's more verbose and kinda overkill if you're just making a stateless link like we are here. `Stateless` links are middleware callbacks that don't care to know anything about the context of the operation and just does it own thing regardless of what operation Apollo is about to execute.

## About the environment variables

Before we dive into the section, it's important to note that the `process.env.*` variables used throughout the tutorial are simply string values stored in an `.env` file and loaded using the [**dotenv**](https://www.npmjs.com/package/dotenv) package. As a reader, you can replace these variables with any values that suit your needs.

Here's a sample .env file to help you get started:

```makefile
SESSION_TOKEN_LS_KEY=my_session_token
REFRESH_TOKEN_LS_KEY=my_refresh_token
AUTH_TOKEN_LS_KEY=my_auth_token
AUTH_KEY_TIMEOUT=30000
GRAPHQL_ENDPOINT=http://woographql.local/graphql
```

With a .env file created you will be ready to move on to what's next, which is defining the `getSessionToken` function.

## Defining the `getSessionToken` function

```javascript
export async function getSessionToken(forceFetch = false) {
  let sessionToken = localStorage.getItem(process.env.SESSION_TOKEN_LS_KEY as string);
  if (!sessionToken || forceFetch) {
    sessionToken = await fetchSessionToken();
  }
  return sessionToken;
}
```

The function is rather simple. It attempt to retrieve the `sessionToken` from `localStorage`, and if that fails or `forceFetch` is passed it fetches a new one using `fetchSessionToken()`. And now `fetchSessionToken` is defined.

```javascript
import { GraphQLClient } from 'graphql-request';
import { GetCartDocument } from './graphql'

// Session Token Management.
async function fetchSessionToken() {
  let sessionToken;
  try {
    const graphQLClient = new GraphQLClient(process.env.GRAPHQL_ENDPOINT);

    const cartData = await graphQLClient.request(GetCartDocument);

    // If user doesn't have an account return accountNeeded flag.
    sessionToken = cartData?.cart?.sessionToken;

    if (!sessionToken) {
      throw new Error('Failed to retrieve a new session token');
    }
  } catch (err) {
    console.error(err);
  }

  return sessionToken;
}

// ...rest of code

```

This works for most cases but typically you want the obscure the retrieval of the token and the endpoint from the end-user, especially if dealing with authenticated users. There are a number of a ways to do this like serverless functions or Next.js API routes and they should be doing exactly what is done here: retrieve the sessionToken and/or user authentication tokens and nothing else. See the `GetCartDocument` below in `./graphql`.

```javascript
import { gql } from '@apollo/client';

export const GetCartDocument = gql`
  query {
    customer {
      sessionToken
    }
  }
`;

```

By separating retrieval of the `sessionToken` it also enables better control of its value. We can take this further by ensuring no request gets sent without a session token or with an invalid session token. This is where our `createErrorLink` error handling middleware and `createUpdateLink` token updating afterware come into play. First `createErrorLink`, to capture failed queries caused by an invalid or expired session tokens, deleting the currently stored session token, and retrieve a new one.

```javascript
import { onError } from '@apollo/client/link/error';
import { Observable } from '@apollo/client/utilities';

function createErrorLink() {
  return onError(({ graphQLErrors, operation, forward }) => {
    const targetErrors = [
      'The iss do not match with this server',
      'invalid-secret-key | Expired token',
      'invalid-secret-key | Signature verification failed',
      'Expired token',
      'Wrong number of segments',
    ];
    let observable;
    if (graphQLErrors?.length) {
      graphQLErrors.map(({ debugMessage, message }) => {
        if (targetErrors.includes(message) || targetErrors.includes(debugMessage)) {
          observable = new Observable((observer) => {
            getSessionToken(true)
              .then((sessionToken) => {
                operation.setContext(({ headers = {} }) => {
                  const nextHeaders = headers;

                  if (sessionToken) {
                    nextHeaders['woocommerce-session'] = `Session ${sessionToken}`;
                  } else {
                    delete nextHeaders['woocommerce-session'];
                  }

                  return {
                    headers: nextHeaders,
                  };
                });
              })
              .then(() => {
                const subscriber = {
                  next: observer.next.bind(observer),
                  error: observer.error.bind(observer),
                  complete: observer.complete.bind(observer),
                };
                forward(operation).subscribe(subscriber);
              })
              .catch((error) => {
                observer.error(error);
              });
          });
        }
      });
    }
    return observable;
  });
}
```

There is a lot going on here but is not very complex once broken down.

```javascript
const targetErrors = [
  'The iss do not match with this server',
  'invalid-secret-key | Expired token',
  'invalid-secret-key | Signature verification failed',
  'Expired token',
  'Wrong number of segments',
];
```

These are the error messages we are targeting. Each are exclusively results of an invalid tokens.

```javascript
let observable;
  if (graphQLErrors?.length) {
    graphQLErrors.map(({ debugMessage, message }) => {
      if (targetErrors.includes(message) || targetErrors.includes(debugMessage)) {
        observable = new Observable((observer) => {
          getSessionToken(true)
            .then((sessionToken) => {
              operation.setContext(({ headers = {} }) => {
                const nextHeaders = headers;

                if (sessionToken) {
                  nextHeaders['woocommerce-session'] = `Session ${sessionToken}`;
                } else {
                  delete nextHeaders['woocommerce-session'];
                }

                return {
                  headers: nextHeaders,
                };
              });
            })
```

This is the scary looking part if you are not familar with observables, so let me explain it briefly. Observables are similar to Promises, but instead of handling a single asynchronous event, they handle multiple events over time. While Promises resolve only once and return a single value, Observables emit multiple values and can be canceled, providing greater control over asynchronous data streams.

Our usage here is to tell Apollo to retry the last operation after we have retrieved a new token with `getSessionToken` if the current `graphQLError` matches any of our targetted errors, otherwise `observable` is left as an `undefined` value and Apollo continues as normal.

Next is the `createUpdateLink` callback, responsible for retrieving an updated `sessionToken` from the `woocommerce-session` HTTP response token. The reason for this is the session token generated by WooGraphQL is self-managing and a new token with an updated expiration time of 14 days from the last action is generated on each request that a `woocommerce-session` HTTP request header is sent. To retrieve and store this updated token we use Apollo afterware.

## Defining the `createUpdateLink` function

Next, define the `createUpdateLink` function as follows:

```javascript
import { setContext } from '@apollo/client/link/context';

function createUpdateLink(operation, forward) => {
  return forward(operation).map((response) => {
    /**
     * Check for session header and update session in local storage accordingly. 
     */
    const context = operation.getContext();
    const { response: { headers } } = context;
    const oldSessionToken = localStorage.getItem(process.env.SESSION_TOKEN_LS_KEY as string);
    const sessionToken = headers.get('woocommerce-session');
    if (sessionToken) {
      if ( oldSessionToken !== session ) {
        localStorage.setItem(process.env.SESSION_TOKEN_LS_KEY as string, sessionToken);
      }
    }

    return response;
  });
}
```

This is an our Apollo afterware callback, and if you are wondering how does this differ from Apollo middleware, look at the following.

```javascript
return forward(operation).map((response) => {
```

By calling `.map()` on the result of `forward()`, we're telling Apollo to execute this after operation completion, you can even take it a further by modifying the `response` object if necessary. It is not necessary here, but I figured I should at least state that fact.

We can also put the `createErrorLink` callback in our `from()` call when defining the `ApolloClient` to ensure it's never executed on a request failed due to an invalid token.

And with the creation of the `createUpdateLink` link, we now have an Apollo Client that completely manages the WooCommerce session. Note that this doesn't account for all use cases, specifically dealing with registered WooCommerce customers. In such cases, you'll need to use a second JWT for identifying their WordPress account, called an Authentication Token or auth token for short. For handling user authentication, auth tokens, and refresh tokens, refer to the next section.

This should provide you with a solid foundation for setting up a GraphQL client that effectively manages user sessions in your e-commerce application. By following the steps outlined, you'll be able to create a seamless experience for your users when interacting with both WooCommerce, ultimately saving development time and effort.
