---
title: "Handling User Authentication with WooGraphQL"
description: "Learn how to handle user authentication in your headless WooCommerce application using WooGraphQL and WPGraphQL for secure and seamless user experiences."
keywords: "WooGraphQL, WPGraphQL, WooCommerce, GraphQL, user authentication, login, register, secure, headless"
author: "Geoff Taylor"
---

# Handling User Authentication

In this section, we'll pick up where the last one stopped and focus on handling user authentication, auth tokens, and refresh tokens. This will allow your application to not only manage WooCommerce sessions effectively but also handle WordPress authentication, providing a seamless experience for your users.

The execution of this section of the documentation should be similar to the [previous section](configuring-graphql-client-for-user-session.md), with some additional steps to account for the different behavior around validation and renewal of auth tokens. We'll walk you through modifying the `createSessionLink`, `fetchSessionToken`,
and `createErrorLink` functions, creating the `getAuthToken` function, and implementing the necessary steps to manage auth token renewal.

## Updating the `createSessionLink` function

First, let's start by modifying the `createSessionLink` function:

```javascript
function createSessionLink() {
  return setContext(async ({ context: { headers: currentHeaders } = {} }) => {
    const headers = { ...currentHeaders };
    const authToken = await getAuthToken();
    const sessionToken = await getSessionToken();

    if (authToken) {
      headers.Authorization = `Bearer ${authToken}`;
    }

    if (sessionToken) {
      headers['woocommerce-session'] = `Session ${sessionToken}`;
    }

    if (authToken || sessionToken) {
      return { headers };
    }

    return {};
  });
}
```

Not too much changing here - it's still as simple as it was before except now we're setting an `Authorization` header too.

## Creating the `getAuthToken` and `fetchAuthToken` functions.

Next, we'll create a new function called `getAuthToken`. This function is similar to the `getSessionToken` function but has some key differences due to the way session tokens and auth tokens handle renewal. Start with the following mutation.

```javascript
import { gql } from '@apollo/client';

const RefreshAuthTokenDocument = gql`
  mutation RefreshAuthToken($refreshToken: String!) {
    refreshJwtAuthToken(input: { jwtRefreshToken: $refreshToken }) {
      authToken
    }
  }
`;
```

To help you understand the differences, let's briefly discuss how the session token and auth token handle renewal. As stated in the previous section session tokens are self-managed and renewed automatically by WooGraphQL when sent within the 14 day limit, and an updated session token is generated on every request. All you have to do is retrieve it. Auth tokens, on the other hand, require you to use the mutation above and the refresh token that's distributed with the auth token to get a new auth token before the auth token expires, which is approximately 15 minutes after creation 😅.



```javascript
export function hasCredentials() {
  const authToken = sessionStorage.getItem(process.env.AUTH_TOKEN_SS_KEY);
  const refreshToken = localStorage.getItem(process.env.REFRESH_TOKEN_LS_KEY);

  if (!!authToken && !!refreshToken) {
    return true;
  }

  return false;
}
```

As the name states, this confirms the existence of the auth and refresh tokens.

```javascript
export async function getAuthToken() {
  let authToken = sessionStorage.getItem(process.env.AUTH_TOKEN_SS_KEY );
  if (!authToken || !tokenSetter) {
    authToken = await fetchAuthToken();
  }
  return authToken;
}
```

This should look familiar if you read the previous section, as it's almost identical `getSessionToken()`, only difference is there is no `forceFetch` option because it's simply not needed.

```javascript
let tokenSetter;
async function fetchAuthToken() {
  const refreshToken = localStorage.getItem(process.env.REFRESH_TOKEN_LS_KEY);
  if (!refreshToken) {
    // No refresh token means the user is not authenticated.
    return;
  }

  try {
    const graphQLClient = new GraphQLClient(process.env.GRAPHQL_ENDPOINT);

    const results = await graphQLClient.request(RefreshAuthTokenDocument, { refreshToken });

    const authToken = results?.refreshJwtAuthToken?.authToken;
    if (!authToken) {
      throw new Error('Failed to retrieve a new auth token');
    }
  } catch (err) {
    console.error(err);
  }

  // Save token.
  sessionStorage.setItem(process.env.AUTH_TOKEN_SS_KEY, authToken);
  if (tokenSetter) {
    clearInterval(tokenSetter);
  }
  tokenSetter = setInterval(
    async () => {
      if (!hasCredentials()) {
        clearInterval(tokenSetter);
        return;
      }
      fetchAuthToken();
    },
    Number(process.env.AUTH_KEY_TIMEOUT || 30000),
  );

  return authToken;
}
```

There is a lot going on here, but it's very similar to our `fetchSessionToken()` from the previous section. The difference here is the auth token is in `sessionStorage` instead of `localStorage`, which means it will be deleted when the user closes the browser. A new auth token will be needed every time the user opens the page after closing the browser. To better breakdown the function, let's step through the possible outcomes.

1. A quiet exit if no `refreshToken` is found. This is the scenario of an unauthenticated user. This is pretty much any new user that shows up to your application.
2. An error thrown if no `authToken` is returned. This is the scenario of an user with a invalid/expired refresh token, in which case you may just want to delete the stored refresh token and quietly exit the function.
3. The error handler is in case anything goes wrong during the `GraphQLClient.query()` call.
4. Finally, if nothing goes wrong, `tokenSetter` is assigned with a new recurring fetcher set for 5 minute interval and the `authToken` is returned.

The purpose of the `tokenSetter` fetcher is to address the short lifespan of the `authToken`. This also ensures that a invalid `authToken` is never sent, and because of this we don't have update the `createErrorLink` or `createUpdateLink` callbacks from the previous section, but we do have to update our `fetchSessionToken()` function.

## Updating the `fetchSessionToken()` function

```javascript
async function fetchSessionToken() {
  const headers = {};
  const authToken = await getAuthToken();
  if (authToken) {
    headers.Authorization = `Bearer ${authToken}`;
  }

  let sessionToken;
  try {
    const graphQLClient = new GraphQLClient(process.env.GRAPHQL_ENDPOINT, { headers });

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
```

Now we're setting the `Authorization` header if the `authToken` to ensure the `sessionToken` returned is belongs to the authenticated user.

## Creating the `login` callback.

For any of this to work, you need to be able to log the user into WordPress. We recommend using the WPGraphQL-JWT-Authentication plugin, which provides a login mutation.

```javascript
import { gql } from '@apollo/client';

const LoginDocument = gql`
  mutation Login($username: String!, $password: String!) {
    login(input: { username: $username, password: $password }) {
      authToken
      refreshToken
      customer {
        sessionToken
      }
    }
  }
```

We'll start by making a quick helper that'll sort our newly obtained credentials.

```javascript

function saveCredentials(authToken, sessionToken, refreshToken = null) {
  sessionStorage.setItem(process.env.AUTH_TOKEN_SS_KEY, authToken);
  sessionStorage.setItem(process.env.SESSION_TOKEN_LS_KEY, sessionToken);
  if (refreshToken) {
    localStorage.setItem(process.env.REFRESH_TOKEN_LS_KEY, refreshToken);
  }
}
```

Use the mutation to implement a login callback function in your application to handle the login process:

```javascript
import { rawRequest } from 'graphql-request';
export async function login(username, password) {
  const headers = {};
  const sessionToken = await getSessionToken();
  if (sessionToken) {
    headers['woocommerce-session'] = `Session ${sessionToken}`;
  }
  try {
    const graphQLClient = new GraphQLClient(process.env.SHOP_GRAPHQL_ENDPOINT, { headers });
    const { data, headers: responseHeaders, status, } = await rawRequest<SsoLoginMutation>(
      process.env.GRAPHQL_ENDPOINT as string,
      LoginDocument,
      { username, password },
      headers,
    );
    const loginResults = data?.login;
    const newSessionToken = responseHeaders.get('woocommerce-session');
    const {
      authToken,
      refreshToken,
      customer,
    } = loginResults;

    if (!authToken || !refreshToken || !newSessionToken) {
      throw new Error( 'Failed to retrieve credentials.');
    }


  } catch (error) {
    throw new Error(error);
  }

  saveCredentials(authToken, newSessionToken, refreshToken);

  return customer;
}
```

Just like with `fetchSessionToken()`, it is highly recommended that you obscure the API calls here by deferring the logic to something like a serverless function or Next.js API route. Note, we are also return the `customer` object here which could potentially be problematic if sensitive information like the user's email or phone number is being pulled.

## Conclusion

In summary, we demonstrated how to configure a GraphQL client to work with WooGraphQL, manage WooCommerce sessions, and handle WordPress authentication. With this setup, you should be able to create a robust and secure client that manages user authentication efficiently and seamlessly.

The next section will begin teaching how you best utilize the data received from WooGraphQL to create showstopping components.
