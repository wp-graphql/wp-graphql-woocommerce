# Configuring a GraphQL Client for WooCommerce User Session Management

In this comprehensive guide, we'll walk you through the process of configuring a GraphQL client to manage user sessions and credentials when working with WooGraphQL. By following the steps outlined in this tutorial, you'll learn how to create a GraphQL client that maintains a valid WooCommerce session in the `woocommerce_sessions` DB table. This knowledge will enable you to build robust applications that interact smoothly with WooCommerce while providing a seamless experience for your users and shortening development time.

By properly handling the session token, you can implement session pass-off functionality, allowing you to fallback on the cart page, my-account page, or any other page living in WordPress that relies on user sessions. Note that implementing the session pass-off functionality is out of the scope of this guide. So, let's dive in and explore the intricacies of setting up a GraphQL client that effectively manages user sessions for your e-commerce store!

When using WooGraphQL cart and customer functionality, there are certain prerequisites. A WooGraphQL session token, distributed by the QL Session Handler, must be passed as an HTTP header with the name `woocommerce-session`, prefixed with `Session`. This header should be included in all session data-altering mutations. Note that the required name `woocommerce-session` can be changed using WordPress filters.

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
          items {
            key
            product {
              node {
                id
                name
              }
            }
            quantity
          }
        }
      }
    `,
  }),
})
  .then((response) => response.json())
  .then((data) => console.log(data));
```

However, if you're using a library or framework like Apollo, configuring a middleware layer is required, which can be confusing if not explained or demonstrated effectively. In this guide, we'll walk you through setting up the Apollo Client and its middleware to work with WooGraphQL.

## Creating the Apollo Client instance

First, let's create the Apollo Client instance and focus on the `link` option. We'll use the `from` utility function from `@apollo/client`:

```javascript
import { from } from '@apollo/client';

// ...

const client = new ApolloClient({
  link: from([
    createSessionLink(),
    createErrorLink(),
    new HttpLink({ uri: endpoint }),
  ]),
  cache: new InMemoryCache(),
});
```

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
```

## About the environment variables

Before we dive into the guide, it's important to note that the `process.env.*` variables used throughout the tutorial are simply string values stored in an `.env` file and loaded using the [**dotenv**](https://www.npmjs.com/package/dotenv) package. As a reader, you can replace these variables with any values that suit your needs.

Here's a sample .env file to help you get started:

```makefile
SESSION_TOKEN_LS_KEY=my_session_token
REFRESH_TOKEN_LS_KEY=my_refresh_token
AUTH_TOKEN_LS_KEY=my_auth_token
AUTH_KEY_TIMEOUT=30000
GRAPHQL_ENDPOINT=http://woographql.local/graphql
```

## Defining the `getSessionToken` and `fetchSessionToken` functions

Here are the `getSessionToken` and `fetchSessionToken` functions:

```javascript
import { GraphQLClient } from 'graphql-request';

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

export async function getSessionToken(forceFetch = false) {
  let sessionToken = localStorage.getItem(process.env.SESSION_TOKEN_LS_KEY as string);
  if (!sessionToken || forceFetch) {
    sessionToken = await fetchSessionToken();
  }
  return sessionToken;
}
```

Defining the `GetCartDocument`

Here's the `GetCartDocument`:

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

It's highly recommended to retrieve the session token outside of the Apollo Client for better control of its value. To ensure no request gets sent without a session token, we must define an error link to capture failed queries caused by an invalid or expired session token, delete the current session, and retrieve a new one. Here's the `createErrorLink` function:

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
        return message;
      });
    }
    return observable;
  });
}
```

With the creation of the error link, we now have an Apollo Client that completely manages the WooCommerce session. Note that this doesn't account for all use cases, specifically dealing with registered WooCommerce customers. In such cases, you'll need to use a second JWT for identifying their WordPress account, called an Authentication Token or auth token for short. For handling user authentication, auth tokens, and refresh tokens, refer to the next guide.

This should provide you with a solid foundation for setting up a GraphQL client that effectively manages user sessions in your e-commerce application. By following the steps outlined, you'll be able to create a seamless experience for your users when interacting with both WooCommerce, ultimately saving development time and effort.
