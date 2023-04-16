# Handling User Authentication

In this guide, we'll pick where the last one stopped and focus on handling user authentication, auth tokens, and refresh tokens. This will allow your application to not only manage WooCommerce sessions effectively but also handle WordPress authentication, providing a seamless experience for your users.

The execution of this part of the guide should be similar to the first part, with some additional steps to account for the different behavior around validation and renewal of auth tokens. We'll walk you through modifying the `createSessionLink` function, creating the `getAuthToken` function, and implementing the necessary steps to manage auth token renewal.

First, let's modify the `createSessionLink` function:

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
Next, we'll create a new function called getAuthToken. This function is similar to the getSessionToken function but has some key differences due to the way session tokens and auth tokens handle renewal. Starting with the following mutation.

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

To help you understand the differences, let's briefly discuss how the session token and auth token handle renewal. Session tokens are renewed automatically, and an updated session token is generated on every request. All you have to do is retrieve it. Auth tokens, on the other hand, require you to use the mutation above and the refresh token that's distributed with the auth token to get a new auth token before the auth token expires, which is approximately 15 minutes after creation 😅.

```javascript
import { GraphQLClient } from 'graphql-request';

function saveCredentials(authToken, sessionToken, refreshToken = null) {
  sessionStorage.setItem(process.env.AUTH_TOKEN_LS_KEY, authToken);
  sessionStorage.setItem(process.env.SESSION_TOKEN_LS_KEY, sessionToken);
  if (refreshToken) {
    localStorage.setItem(process.env.REFRESH_TOKEN_LS_KEY, refreshToken);
  }
}

export function hasCredentials() {
  const authToken = sessionStorage.getItem(process.env.AUTH_TOKEN_LS_KEY);
  const refreshToken = localStorage.getItem(process.env.REFRESH_TOKEN_LS_KEY);

  if (!!authToken && !!refreshToken) {
    return true;
  }

  return false;
}

let tokenSetter;
async function fetchAuthToken() {
  const refreshToken = localStorage.getItem(process.env.REFRESH_TOKEN_LS_KEY);
  if (!refreshToken) {
    // No refresh token means the user is not authenticated.
    throw new Error('Not authenticated');
  }

  try {
    const graphQLClient = new GraphQLClient(process.env.GRAPHQL_ENDPOINT);

    const results = await graphQLClient.request(RefreshAuthTokenDocument, { refreshToken });

    const authToken = results?.refreshJwtAuthToken?.authToken;

    if (!authToken) {
      throw new Error('Failed to retrieve a new auth token');
    }

    const customerResults = await graphQLClient.request(
      GetCartDocument,
      undefined,
      { Authorization: `Bearer ${authToken}` },
    );

    const customer = customerResults?.customer;
    const sessionToken = customer?.sessionToken;
    if (!sessionToken) {
      throw new Error('Failed to retrieve a new session token');
    }
  } catch (err) {
    if (isDev()) {
      // eslint-disable-next-line no-console
      console.error(err);
    }
  }

  saveCredentials(authToken, sessionToken);
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

export async function getAuthToken() {
  let authToken = sessionStorage.getItem(process.env.AUTH_TOKEN_LS_KEY );
  if (!authToken || !tokenSetter) {
    authToken = await fetchAuthToken();
  }
  return authToken;
}
```

In the `saveCredentials` above, we store the auth token in sessionStorage instead of localStorage, which means it will be deleted when the user closes the browser. A new auth token will be needed every time the user opens the page after closing the browser. You'll also notice the use of tokenSetter and setInterval to auto-renew the auth token every 5 minutes in `fetchAuthToken`.

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

Use the mutation to implement a login callback function in your application to handle the login process:

```javascript
export async function login(username, password) {
    try {
        const graphQLClient = new GraphQLClient(process.env.SHOP_GRAPHQL_ENDPOINT);
        const results = await graphQLClient.request(
            LoginDocument,
            { username, password },
        );
        const loginResults = results?.login;
        const {
            authToken,
            refreshToken,
            customer,
        } = loginResults;

        if (!authToken || !refreshToken || !customer?.sessionToken) {
            throw new Error( 'Failed to retrieve credentials.');
        }
    } catch (error) {
        throw new Error(error);
    }

    saveCredentials(authToken, customer.sessionToken, refreshToken);

    return customer;
}
```

In summary, we demonstrated how to configure a GraphQL client to work with WooGraphQL, manage WooCommerce sessions, and handle WordPress authentication. With this setup, you should be able to create a robust and secure client that manages user authentication efficiently and seamlessly.

The next guide will begin teaching how you best utilize the data received from WooGraphQL to create showstopping components.
