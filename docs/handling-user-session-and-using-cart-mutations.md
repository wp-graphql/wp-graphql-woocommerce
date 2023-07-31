---
title: "Handling User Session and Using Cart Mutations with WooGraphQL"
description: "Discover how to manage user sessions and perform cart mutations using WooGraphQL and WPGraphQL in your headless WooCommerce application for a smooth shopping experience."
keywords: "WooGraphQL, WPGraphQL, WooCommerce, GraphQL, user session, cart mutations, headless, shopping experience"
author: "Geoff Taylor"
---

# Handling User Session and Using Cart Mutations

In this section, we will demonstrate how to implement cart controls on the single product page, which will take into account the state of the cart stored in the user session. This section builds upon the app created in the previous sections, so use the code samples from those as a starting point. The section is broken down into three parts: The implementation and use of `UserSessionProvider.jsx`, `useCartMutations.js`, and `CartOptions.jsx`.

## Prerequisites

- Basic knowledge of React and React Router.
- Familiarity with GraphQL and WPGraphQL.
- A WPGraphQL/WooGraphQL backend.
- Read previous sections on [Routing By URI](routing-by-uri.md) and [Using Product Data](using-product.data.md)

## Step 0: Create our `graphql.js` file

```javascript
import { gql } from '@apollo/client';

export const ProductContentSlice = gql`
  fragment ProductContentSlice on Product {
    id
    databaseId
    name
    slug
    type
    image {
      id
      sourceUrl(size: WOOCOMMERCE_THUMBNAIL)
      altText
    }
    ... on SimpleProduct {
      price
      regularPrice
      soldIndividually
    }
    ... on VariableProduct {
      price
      regularPrice
      soldIndividually
    }
  }
`;

export const ProductVariationContentSlice = gql`
  fragment ProductVariationContentSlice on ProductVariation {
    id
    databaseId
    name
    slug
    image {
      id
      sourceUrl(size: WOOCOMMERCE_THUMBNAIL)
      altText
    }
    price
    regularPrice
  }
`;

export const ProductContentFull = gql`
  fragment ProductContentFull on Product {
    id
    databaseId
    slug
    name
    type
    description
    shortDescription(format: RAW)
    image {
      id
      sourceUrl
      altText
    }
    galleryImages {
      nodes {
        id
        sourceUrl(size: WOOCOMMERCE_THUMBNAIL)
        altText
      }
    }
    productTags(first: 20) {
      nodes {
        id
        slug
        name
      }
    }
    attributes {
      nodes {
        id
        attributeId
        ... on LocalProductAttribute {
          name
          options
          variation
        }
        ... on GlobalProductAttribute {
          name
          options
          variation
        }
      }
    }
    ... on SimpleProduct {
      onSale
      stockStatus
      price
      rawPrice: price(format: RAW)
      regularPrice
      salePrice
      stockStatus
      stockQuantity
      soldIndividually
    }
    ... on VariableProduct {
      onSale
      price
      rawPrice: price(format: RAW)
      regularPrice
      salePrice
      stockStatus
      stockQuantity
      soldIndividually
      variations(first: 50) {
        nodes {
          id
          databaseId
          name
          price
          rawPrice: price(format: RAW)
          regularPrice
          salePrice
          onSale
          attributes {
            nodes {
              name
              label
              value
            }
          }
        }
      }
    }
  }
`;

export const VariationContent = gql`
  fragment VariationContent on ProductVariation {
    id
    name
    slug
    price
    regularPrice
    salePrice
    stockStatus
    stockQuantity
    onSale
    image {
      id
      sourceUrl
      altText
    }
  }
`;

export const CartItemContent = gql`
  fragment CartItemContent on CartItem {
    key
    product {
      node {
        ...ProductContentSlice
      }
    }
    variation {
      node {
        ...ProductVariationContentSlice
      }
    }
    quantity
    total
    subtotal
    subtotalTax
    extraData {
      key
      value
    }
  }
  ${ProductContentSlice}
  ${ProductVariationContentSlice}
`;

export const CartContent = gql`
  fragment CartContent on Cart {
    contents(first: 100) {
      itemCount
      nodes {
        ...CartItemContent
      }
    }
    appliedCoupons {
      code
      discountAmount
      discountTax
    }
    needsShippingAddress
    availableShippingMethods {
      packageDetails
      supportsShippingCalculator
      rates {
        id
        instanceId
        methodId
        label
        cost
      }
    }
    subtotal
    subtotalTax
    shippingTax
    shippingTotal
    total
    totalTax
    feeTax
    feeTotal
    discountTax
    discountTotal
  }
  ${CartItemContent}
`;

export const AddressFields = gql`
  fragment AddressFields on CustomerAddress {
    firstName
    lastName
    company
    address1
    address2
    city
    state
    country
    postcode
    phone
  }
`;

export const LineItemFields = gql`
  fragment LineItemFields on LineItem {
    databaseId
    product {
      node {
        ...ProductContentSlice
      }
    }
    orderId
    quantity
    subtotal
    total
    totalTax
  }
  ${ProductContentSlice}
`;

export const OrderFields = gql`
  fragment OrderFields on Order {
    id
    databaseId
    orderNumber
    orderVersion
    status
    needsProcessing
    subtotal
    paymentMethodTitle
    total
    totalTax
    date
    dateCompleted
    datePaid
    billing {
      ...AddressFields
    }
    shipping {
      ...AddressFields
    }
    lineItems(first: 100) {
      nodes {
          ...LineItemFields
      }
    }
  }
  ${AddressFields}
  ${LineItemFields}
`;

export const CustomerFields = gql`
  fragment CustomerFields on Customer {
    id
    databaseId
    firstName
    lastName
    displayName
    billing {
      ...AddressFields
    }
    shipping {
      ...AddressFields
    }
    orders(first: 100) {
      nodes {
        ...OrderFields
      } 
    }
  }
  ${AddressFields}
  ${OrderFields}
`;

export const CustomerContent = gql`
  fragment CustomerContent on Customer {
    id
    sessionToken
  }
`;



export const GetProduct = gql`
  query GetProduct($id: ID!, $idType: ProductIdTypeEnum) {
    product(id: $id, idType: $idType) {
      ...ProductContentFull
    }
  }
  ${ProductContentFull}
`;

export const GetProductVariation = gql`
  query GetProductVariation($id: ID!) {
    productVariation(id: $id, idType: DATABASE_ID) {
      ...VariationContent
    }
  }
  ${VariationContent}
`;

export const GetCart = gql`
  query GetCart($customerId: Int) {
    cart {
      ...CartContent
    }
    customer(customerId: $customerId) {
      ...CustomerContent
    }
  }
  ${CartContent}
  ${CustomerContent}
`;

export const AddToCart = gql`
  mutation AddToCart($productId: Int!, $variationId: Int, $quantity: Int, $extraData: String) {
    addToCart(
      input: {productId: $productId, variationId: $variationId, quantity: $quantity, extraData: $extraData}
    ) {
      cart {
        ...CartContent
      }
      cartItem {
        ...CartItemContent
      }
    }
  }
  ${CartContent}
  ${CartItemContent}
`;

export const UpdateCartItemQuantities = gql`
  mutation UpdateCartItemQuantities($items: [CartItemQuantityInput]) {
    updateItemQuantities(input: {items: $items}) {
      cart {
        ...CartContent
      }
      items {
        ...CartItemContent
      }
    }
  }
  ${CartContent}
  ${CartItemContent}
`;

export const RemoveItemsFromCart = gql`
  mutation RemoveItemsFromCart($keys: [ID], $all: Boolean) {
    removeItemsFromCart(input: {keys: $keys, all: $all}) {
      cart {
        ...CartContent
      }
      cartItems {
        ...CartItemContent
      }
    }
  }
  ${CartContent}
  ${CartItemContent}
`;
export const Login = gql`
  mutation Login($username: String!, $password: String!) {
    login(input: { username: $username, password: $password }) {
      authToken
      refreshToken
      customer {
        ...CustomerFields
      }
    }
  }
  ${CustomerFields}
`;

export const UpdateCustomer = gql`
  mutation UpdateCustomer($input: UpdateCustomerInput!) {
    updateCustomer(input: $input) {
      customer {
        ...CustomerFields
      }
    }
  }
  ${CustomerFields}
`;
```

We've included all the queries we'll be using going forward and leveraging some fragments here and there. Now we can move onto implementing the components sourcing these queries and mutations.
We won't go over them into much detail here but you can learn more about them in the [schema](/schema) docs.

## Step 1: UserSessionProvider.jsx

`UserSessionProvider.jsx` is a state manager that queries and maintains the app's copy of the end-user's session state from WooCommerce on the backend. We'll also be implementing a helper hook called `useSession()` that will provide the user session state to components nested within the provider. In order for the `UserSessionProvider` code in our samples to work properly, the end user will have to implement an ApolloClient with a middleware layer configured to manage the WooCommerce session token, like the one demonstrated in our [**Configuring GraphQL Client for User Session**](configuring-graphql-client-for-user-session.md) section.

Here is the code for `UserSessionProvider.jsx`:

```jsx
import { createContext, useContext, useEffect, useReducer } from 'react';
import { useQuery, useMutation } from '@apollo/client';
import { GetCart, Login, UpdateCustomer } from './graphql';

const initialSession = {
  cart: null,
  customer: null,
};

export const SessionContext = createContext(initialSession);

const reducer = (state, action) => {
  switch (action.type) {
    case 'SET_CART':
      return {
        ...state,
        cart: action.payload,
      };
    case 'SET_CUSTOMER':
      return {
        ...state,
        customer: action.payload,
      };
    default:
      throw new Error('Invalid action dispatched to session reducer');
  }
};

const { Provider } = SessionContext;

export function SessionProvider({ children }) {
  const [state, dispatch] = useReducer(reducer, initialSession);

  const { data, loading: fetching } = useQuery(GetCart);
  const [executeLogin, { data: loginData, errors: loginErrors }] = useMutation(Login);
  const [executeUpdateCustomer, { data: updateCustomerData, errors: updateCustomerErrors }] = useMutation(UpdateCustomer);

  useEffect(() => {
    if (data?.cart) {
      dispatch({
        type: 'SET_CART',
        payload: data.cart,
      });
    }

    if (data?.customer) {
      dispatch({
        type: 'SET_CUSTOMER',
        payload: data.customer,
      });
    }
  }, [data]);

  const setCart = (cart) => dispatch({
    type: 'SET_CART',
    payload: cart,
  });

  const setCustomer = (customer) => dispatch({
    type: 'SET_CUSTOMER',
    payload: customer,
  });

  useEffect(() => {
    if (loginData.login) {
      const { 
        authToken,
        refreshToken,
        customer
      } = loginData.login;

      sessionStorage.getItem(process.env.AUTH_TOKEN_SS_KEY, authToken);
      localStorage.getItem(process.env.REFRESH_TOKEN_LS_KEY, refreshToken);

      setCustomer(customer);
    }
  }, [loginData]);

  useEffect(() => {
    if (updateCustomerData.updateCustomer) {
      const { customer } = updateCustomerData.updateCustomer;

      setCustomer(customer);
    }
  }, [updateCustomerData]);

  const login = (username, password) => {
    return executeLogin({ username, password });
  }

  const updateCustomer = (input) => {
    return executeUpdateCustomer({ input });
  }

  const store = {
    ...state,
    fetching,
    setCart,
    setCustomer,
    login,
    updateCustomer,
  };
  return (
    <Provider value={store}>{children}</Provider>
  );
}

export const useSession = () => useContext(SessionContext);
```

To use the `SessionProvider`, you should wrap your root app component with it and wrap the `SessionProvider` with an ApolloProvider set with our session token managing ApolloClient.

## Step 2: useCartMutations.js

`useCartMutations.js` is a hook that, when provided a `product ID`, `variation ID`, and any other item data, will search the cart stored in the session provider for matching products in the cart, returning the `item key` and `quantity` in the cart. With this knowledge, you can render your single product's cart option according to what actions are available to the end-user in relation to that product.

Here is the code for `useCartMutation.js`:

```javascript
import { useEffect, useState } from 'react';

import { useSession } from './UserSessionProvider.jsx';
import {
  AddToCart,
  UpdateCartItemQuantities,
  RemoveItemsFromCart,
} from './graphql';

const useCartMutations = (
  productId,
  variationId,
  extraData,
) => {
  const {
    cart,
    setCart,
    findInCart,
  } = useSession();
  const [quantityFound, setQuantityInCart] = useState(
    findInCart(productId, variationId, extraData)?.quantity as number || 0,
  );

  const [addToCart, { loading: adding }] = useMutation({
    mutation: AddToCart,
    onCompleted({ addToCart: data }) {
      if (data?.cart) {
        setCart(data.cart);
      }
    },
  });
  const [updateQuantity, { loading: updating }] = useMutation({
    mutation: UpdateCartItemQuantities,
    onCompleted({ updateItemQuantities: data }) {
      if (data?.cart) {
        setCart(data.cart as Cart);
      }
    },
  });
  const [removeCartItem, { loading: removing }] = useMutation({
    mutation: RemoveItemsFromCart,
    onCompleted({ removeItemsFromCart: data }) {
      if (data?.cart) {
        setCart(data.cart as Cart);
      }
    },
  });

  useEffect(() => {
    setQuantityInCart(
      findInCart(productId, variationId, extraData)?.quantity || 0,
    );
  }, [productId, variationId, extraData, cart?.contents?.nodes]);

  const mutate = async (values) => {
    const {
      quantity,
      all = false,
      mutation = 'update',
    } = values;

    if (!cart) {
      return;
    }

    if (!productId) {
      throw new Error('No item provided.');
      // TODO: Send error to Sentry.IO.
    }

    switch (mutation) {
      case 'remove': {
        if (!quantityFound) {
          throw new Error('Provided item not in cart');
        }

        const item = findInCart(
          productId,
          variationId,
          extraData,
        );

        if (!item) {
          throw new Error('Failed to find item in cart.');
        }

        const { key } = item;
        removeCartItem({ variables: { keys: [key], all } });
        break;
      }
      case 'update':
      default:
        if (quantityFound) {
          const item = findInCart(
            productId,
            variationId,
            extraData,
          );

          if (!item) {
            throw new Error('Failed to find item in cart.');
          }

          const { key } = item;
          updateQuantity({ variables: { items: [{ key, quantity }] } });
        } else {
            addToCart({
                variables: {
                    input: {
                        productId,
                        variationId,
                        quantity,
                        extraData,
                    },
                },
            });
        }
        break;
    }
    };

    return {
        quantityInCart: quantityFound,
        mutate,
        loading: adding || updating || removing,
    };
};

export default useCartMutations;
```

With the `useCartMutations` hook implemented, you can use it within your components to handle cart actions for a given product, variation, or any other item data. The hook returns the quantity of the item currently in the cart, a `mutate` function that can be used to add, update, or remove items, and a `loading` flag indicating whether any cart mutations are in progress.

You can now use this hook to create and manage cart interactions in your components. For instance, you can create an "Add to Cart" button that adds items to the cart, updates the quantity of an existing item, or removes an item from the cart.

Here's an example of how you could use the useCartMutations hook within a React component using our SingleProduct component from the previous section:

```jsx
import React, { useEffect, useState } from 'react';
import { useQuery } from '@apollo/client';
import { GetProduct } from './graphql';

const SingleProduct = ({ productId }) => {
  const [quantity, setQuantity] = useState(1);
  const { data, loading, error } = useQuery(GetProduct, {
    variables: { id: productId, idType: 'DATABASE_ID' },
  });
  const { quantityInCart: inCart, mutate, loading } = useCartMutations(productId);

  useEffect(() => {
    if (inCart) {
      setQuantity(inCart);
    }
  }, [inCart])

  if (loading) return <p>Loading...</p>;
  if (error) return <p>Error: {error.message}</p>;

  const handleAddOrUpdateAction = async () => {
    mutate({ quantity });
  }

  const handleRemoveAction = async () => {
    mutate({ mutation: 'remove', quantity: 0 });
  }

  const buttonText = inCart ? 'Update' : 'Add To Cart';

  return (
    <div className="single-product">
      {/* Rest of component */}
      <div className="cart-options">
        {!product.soldIndividually && (
          <div className="quantity">
            <label htmlFor="quantity">Quantity:</label>
            <input
              type="number"
              id="quantity"
              name="quantity"
              min="1"
              defaultValue={inCart ? inCart : 1}
              onChange={(event) => setQuantity(Number.parseInt(event.target.value)))}
            />
          </div>
        )}
        {product.stockStatus === 'IN_STOCK' ? (
          <>
            <button
              type="button"
              className="add-to-cart"
              onClick={handleAddOrUpdateAction}
              disabled={loading}
            >
              {buttonText}
            </button>
            {inCart && (
              <button
                type="button"
                className="remove-from-cart"
                onClick={handleRemoveAction}
                disabled={loading}
              >
                Remove
              </button>
            )}
          </>
        ) : (
          <p>Out of stock</p>
        )}
      </div>
    </div>
  );
};

export default SingleProduct;
```

In this example, we have our `SingleProduct` component that receives a `productId`. It uses the `useCartMutations` hook to manage the cart actions. The component renders the product information and provides buttons to add, update, or remove the item from the cart.

The `handleAddOrUpdateAction` and `handleRemoveAction` functions call the `mutate` function returned by the `useCartMutations`. The `loading` flag is used to disable the buttons while any cart mutations are in progress.

This is just an example of how you could use the `useCartMutations` hook using simple products, but as I'm sure you noticed, it support a `variationId` as the second parameter. Implementing Variable product support in our `SingleProduct` component is out of the scope this section, but with what has been provided you should have no problem implementing variable product support.

## Conclusion

In conclusion, we've created a custom React hook, `useCartMutations`, which allows you to manage cart actions like adding, updating, and removing items in an e-commerce application. We've used the Apollo Client's useMutation hook to interact with the GraphQL API and manage the state of the cart. Then, we've demonstrated how to use the custom `useCartMutations` hook within our `SingleProduct` component to perform cart-related actions.

This custom hook can help you create a more organized and modular e-commerce application by abstracting the cart logic and keeping your components clean and focused. You can further modify and extend the `useCartMutations` hook and the `SingleProduct` component to suit the specific requirements of your application.

By leveraging the power of custom hooks and GraphQL in your React application, you can create a robust and efficient e-commerce solution that scales well and provides a great user experience.
