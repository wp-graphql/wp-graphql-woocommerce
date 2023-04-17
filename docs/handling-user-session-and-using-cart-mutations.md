# Handling User Session and Using Cart Mutations

In this guide, we will demonstrate how to implement cart controls on the single product page, which will take into account the state of the cart stored in the user session. This guide builds upon the app created in the previous guides, so use the code samples from them as a starting point. The guide is broken down into three parts: The implementation and use of `UserSessionProvider.jsx`, `useCartMutations.js`, and `CartOptions.jsx`.

## Prerequisites

This guide assumes that you have read and followed the previous guides.

## Part 1: UserSessionProvider.jsx

`UserSessionProvider.jsx` is a state manager that queries and maintains the app's copy of the end-user's session state from WooCommerce on the backend. We'll also be implementing a helper hook called `useSession()` that will provide the user session state to components nested within the provider. In order for the `UserSessionProvider` code in our samples to work properly, the end user will have to implement an ApolloClient with a middleware layer configured to manage the WooCommerce session token, like the one demonstrated in our [**Configuring GraphQL Client for User Session**](configuring-graphql-client-for-user-session.md) guide.

Here is the code for `UserSessionProvider.jsx`:

```jsx
import { createContext, useContext, useEffect, useReducer } from 'react';
import { useQuery } from '@apollo/client';
import { GetCartDocument } from '../graphql';

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

  const { data, loading: fetching } = useQuery(GetCartDocument);

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

  const store = {
    ...state,
    fetching,
    setCart,
    setCustomer,
  };
  return (
    <Provider value={store}>{children}</Provider>
  );
}

export const useSession = () => useContext(SessionContext);
```

To use the `SessionProvider`, you should wrap your app component with it and wrap the `SessionProvider` with an ApolloProvider set with our session token managing ApolloClient. Make sure to demonstrate this for the reader against our previous code samples from previous posts.

## Part 2: useCartMutations.js

`useCartMutations.js` is a hook that, when provided a `product ID`, `variation ID`, and any other item data, will search the cart stored in the session provider for matching products in the cart, returning the `item key` and `quantity` in the cart. With this knowledge, you can render your single product's cart option according to what actions are available to the end-user in relation to that product.

Here is the code for `useCartMutation.js`:

```javascript
import { useEffect, useState } from 'react';

import { useSession } from './UserSessionProvider.jsx';
import {
  useAddToCartMutation,
  useUpdateCartItemQuantitiesMutation,
  useRemoveItemsFromCartMutation,
  useApplyCouponToCartMutation,
  useRemoveCouponFromCartMutation,
  useSetShippingLocaleMutation,
  useSetShippingMethodMutation,
  Cart,
  CountriesEnum,
  Customer,
  GetCartDocument,
} from '@axis/graphql';

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
    mutation: AddToCartDocument,
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

Here's an example of how you could use the useCartMutations hook within a React component:

```jsx
import React, { useState } from 'react';
import useCartMutations from './useCartMutations';

const ProductCard = ({ product }) => {
  const [quantity, setQuantity] = useState(1);
  const { productId, variationId, extraData } = product;
  const { quantityInCart, mutate, loading } = useCartMutations(productId, variationId, extraData);

  const handleAddToCart = () => {
    mutate(quantity, 'add');
  };

  const handleUpdateQuantity = () => {
    mutate(quantity, 'update');
  };

  const handleRemoveFromCart = () => {
    mutate(0, 'remove');
  };

  return (
    <div className="product-card">
      <h3>{product.name}</h3>
      <img src={product.image} alt={product.name} />
      <p>Price: {product.price}</p>

      <div className="quantity-input">
        <label htmlFor="quantity">Quantity:</label>
        <input
          type="number"
          id="quantity"
          value={quantity}
          onChange={(e) => setQuantity(parseInt(e.target.value))}
          min={0}
        />
      </div>

      <button onClick={handleAddToCart} disabled={loading}>
        Add to Cart
      </button>

      {quantityInCart > 0 && (
        <>
          <button onClick={handleUpdateQuantity} disabled={loading}>
            Update Quantity
          </button>

          <button onClick={handleRemoveFromCart} disabled={loading}>
            Remove from Cart
          </button>
        </>
      )}
    </div>
  );
};

export default ProductCard;
```

In this example, we have a `ProductCard` component that receives a product object as a prop. It uses the `useCartMutations` hook to manage the cart actions. The component renders the product information and provides buttons to add, update, or remove the item from the cart.

The `handleAddToCart`, `handleUpdateQuantity`, and `handleRemoveFromCart` functions call the `mutate` function returned by the `useCartMutations` hook with the desired action ('add', 'update', or 'remove'). The `loading` flag is used to disable the buttons while any cart mutations are in progress.

This is just an example of how you could use the `useCartMutations` hook. Depending on your application's requirements and design, you may need to modify or extend the component to fit your needs.

## Conclusion

In conclusion, we've created a custom React hook, `useCartMutations`, which allows you to manage cart actions like adding, updating, and removing items in an e-commerce application. We've used the Apollo Client's useMutation hook to interact with the GraphQL API and manage the state of the cart. Then, we've demonstrated how to use the custom `useCartMutations` hook within a `ProductCard` component to perform cart-related actions.

This custom hook can help you create a more organized and modular e-commerce application by abstracting the cart logic and keeping your components clean and focused. You can further modify and extend the `useCartMutations` hook and the `ProductCard` component to suit the specific requirements of your application.

By leveraging the power of custom hooks and GraphQL in your React application, you can create a robust and efficient e-commerce solution that scales well and provides a great user experience.
