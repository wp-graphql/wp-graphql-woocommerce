---
title: "Using Composite Product Data + Mutations with WooGraphQL"
description: "Learn how to use the Composite Product functionality with WooGraphQL by building upon the code from `Using Product Data` and `Creating Session Provider and using Cart Mutations`."
keywords: "WooGraphQL, WPGraphQL, WooCommerce, GraphQL, Composite Product functionality, Product Data, Session Provider, Cart Mutations"
author: "Geoff Taylor"
---

# Using Composite Product Data + Mutations

In this section, we will be discussing how to use composite product data and mutations. We will be building on the code written in previous sections of the documentation, specifically the sections on [using product data](https://woographql.com/docs/using-product-data) and [handling user session and using cart mutations](https://woographql.com/docs/handling-user-session-and-using-cart-mutations).

Composite products are a unique type of product in WooCommerce that allow store owners to build complex products by combining simple products. These products are designed to manage and provide a lot of visual context data, like the behavior for displaying certain parts of components or totals. It's up to the demands of the store and client application how much of this context should be used.

When adding a composite product to the cart, all components must be provided to the `AddCompositeToCart`'s `configuration` field, even optional components.

Here's the component we'll be using in the examples ahead.

```jsx
import React from 'react';
import { useQuery } from '@apollo/client';
import { GetProduct } from './graphql';
import useCartMutations from './useCartMutations';

const CompositeProduct = ({ productId }) => {
  const [quantity, setQuantity] = React.useState(1);
  const { data, loading, error } = useQuery(GetProduct, {
    variables: { id: productId, idType: 'DATABASE_ID' },
  });

  const { quantityInCart: inCart, mutate, loading: cartLoading } = useCartMutations(productId);

  React.useEffect(() => {
    if (inCart) {
      setQuantity(inCart);
    }
  }, [inCart]);

  if (loading) return <p>Loading...</p>;
  if (error) return <p>Error: {error.message}</p>;

  const product = data.product;

  const handleAddOrUpdateAction = async () => {
    mutate({ quantity });
  };

  const handleRemoveAction = async () => {
    mutate({ mutation: 'remove', quantity: 0 });
  };

  const buttonText = inCart ? 'Update' : 'Add To Cart';

  return (
    <div className="composite-product">
      <h1>{product.name}</h1>
      <div dangerouslySetInnerHTML={{ __html: product.description }}></div>
      <p>
        {product.onSale && <del>{product.regularPrice}</del>}
        {product.price}
      </p>

      <div className="attributes">
        {product.attributes.nodes.map((attr) => (
          <div key={attr.id}>
            <strong>{attr.name}:</strong> {attr.options.join(', ')}
          </div>
        ))}
      </div>

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
              onChange={(event) => setQuantity(Number.parseInt(event.target.value))}
            />
          </div>
        )}
        {product.stockStatus === 'IN_STOCK' ? (
          <>
            <button
              type="button"
              className="add-to-cart"
              onClick={handleAddOrUpdateAction}
              disabled={cartLoading}
            >
              {buttonText}
            </button>
            {inCart && (
              <button
                type="button"
                className="remove-from-cart"
                onClick={handleRemoveAction}
                disabled={cartLoading}
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

export default CompositeProduct;
```

This `CompositeProduct` component receives a `productId` as a prop. It uses the `useQuery` hook from Apollo Client to fetch the product data from the GraphQL API. The product data includes the product's name, description, price, and attributes.

The `useCartMutations` hook is used to manage the cart actions. It returns the quantity of the item currently in the cart, a `mutate` function that can be used to add, update, or remove items, and a `loading` flag indicating whether any cart mutations are in progress.

The `handleAddOrUpdateAction` and `handleRemoveAction` functions call the `mutate` function returned by `useCartMutations`. The `loading` flag is used to disable the buttons while any cart mutations are in progress.

The component renders the product information and provides buttons to add, update, or remove the item from the cart. The "Add to Cart" button's text changes to "Update" if the item is already in the cart. If the product is out of stock, a message is displayed instead of the cart options.

This component is a good starting point. You can further customize and extend it to suit your specific needs.

Alright, let's start by updating the `SingleProduct` component to support composite products. We'll use the `CompositeCard` component code provided, but we'll rename it to `CompositeProduct` for consistency. 

Here's the updated `SingleProduct` component:

```jsx
import React from 'react';
import { CompositeProduct } from './CompositeProduct';
import { Product as ProductType } from '@axis/graphql';

export const SingleProduct = ({ product }) => {
  if (product.__typename === 'CompositeProduct') {
    return <CompositeProduct product={product} />;
  }

  // ...rest of the component
};
```

In the code above, we're checking if the product type is `CompositeProduct`. If it is, we're rendering the `CompositeProduct` component. If it's not, we're rendering the rest of the `SingleProduct` component as usual.

Next, let's update the `useCartMutations` hook to add support for `addCompositeToCart`. Here's the updated hook:

```jsx
import { useEffect, useMemo, useState } from 'react';

import { useSession } from '@axis/components/SessionProvider';
import {
  useAddToCartMutation,
  useAddCompositeToCartMutation,
  useUpdateCartItemQuantitiesMutation,
  useRemoveItemsFromCartMutation,
  Cart,
  GetCartDocument,
  CartItem,
} from '@axis/graphql';

export interface CartMutationCompositeInput extends CartMutationInput {
  configuration: {
    componentId: string;
    productId?: number;
    hidden?: boolean;
    quantity?: number;
    variation?: {
      attributeName: string;
      attributeValue: string;
    }[]
    variationId?: number;
  }[];
}

// ...rest of the hook

const useCartMutations = (
  productId: number,
  variationId?: number,
  extraData?: string,
) => {
  // ...rest of the hook

  const [addCompositeToCart, { loading: addingComposite }] = useAddCompositeToCartMutation({
    onCompleted({ addCompositeToCart: data }) {
      if (data?.cart) {
        setCart(data.cart as Cart);
      }
    },
    notifyOnNetworkStatusChange: true,
  });

  // ...rest of the hook

  async function mutate(values) {
    const {
      quantity = 1,
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

    let item: CartItem|undefined;
    switch (mutation) {
      // ...rest of the switch statement

      case 'addComposite':
        if (!values.configuration) {
          throw new Error('No component configurations provided');
        }
        addCompositeToCart({
          variables: {
            productId,
            quantity,
            configuration: values.configuration,
            extraData,
          },
        });
        break;

      // ...rest of the switch statement
    }
  }

  // ...rest of the hook

  return store;
};

export default useCartMutations;
```

In the updated hook, we've added a new mutation `addCompositeToCart` and a new case in the `mutate` function to handle adding composite products to the cart.

Now before finishing up, let's revisit the facts touched upon at the started of section:

1. Composite products are designed to manage and provide a lot of visual context data, like the behavior for displaying certain parts of components or totals. It's up to the demands of the store and client application how much of this context should be used. Some specific GraphQL fields that provide this optional context are the `CompositeProduct` type's `addToCartFormLocation` field, the `CompositeProductComponent` type's `optionsStyle` and `paginationStyle`.

2. All components must be provided to the `AddCompositeToCart`'s `configuration` field, even optional components. Optional components should be set with a quantity of `0`.

By following these steps and understanding these facts, you can effectively use composite products in your WooCommerce store with GraphQL.

## Conclusion

In this section, we have delved into the intricacies of working with Composite Product Data and Mutations. We have demonstrated how to adapt the code from the `Using Product Data` and `Creating Session Provider and using Cart Mutations` sections to handle the unique specifications of composite products.

We've explored how to modify the `ProductListing` and `SingleProduct` components to support `CompositeProduct` types. We've also shown how to use the `addToCart` mutation to add composite products to the cart, taking into account the unique structure of these products.

This exploration has highlighted the flexibility and power of WooGraphQL, demonstrating how it can be used to handle a wide range of product types in a WooCommerce store.

As we wrap up this section, we hope that you now feel confident in your ability to work with composite product data and mutations. The skills and knowledge you've gained here will be invaluable as you continue to build and enhance your headless WooCommerce applications.

In the upcoming sections, we will continue to explore other product types, including Product Bundles and Product Add-ons. Each of these product types presents its own unique challenges and opportunities, and we look forward to guiding you through them.

As always, we encourage you to experiment with the concepts and code snippets provided in this section, applying them to your own projects. Happy coding!
