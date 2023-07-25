---
title: "Using Product Bundle Data + Mutations"
description: ""
keywords: "WooGraphQL, WPGraphQL, WooCommerce, GraphQL"
author: "Geoff Taylor"
---
## Using Product Bundle Data + Mutations

In this section, we will learn how to handle product bundles using WooGraphQL. Product bundles are a group of products that are sold together as a set. This functionality is extremely useful for e-commerce websites that sell products in bundles or sets.

We will be using the `BundleProduct` component and updating the `SingleProduct` and `useCartMutations` components to handle product bundles. We will be using the code samples from the [Using Product Data](https://woographql.com/docs/using-product-data) and [Handling User Session and Using Cart Mutations](https://woographql.com/docs/handling-user-session-and-using-cart-mutations) as a starting point.

Let's start by looking at the `BundleProduct` component:

```jsx
import React, { useState } from 'react';
import useCartMutations from './useCartMutations';
import { useSession } from './SessionProvider';
import { LoadingSpinner } from './LoadingSpinner';
import { CartCard } from './CartCard';

// ... rest of the code from the provided sample ...

export function BundleCard({ product }) {
  // ... rest of the code from the provided sample ...
}
```

Next, we will update the `SingleProduct` component to handle product bundles:

```jsx
import React from 'react';
import BundleCard from './BundleCard';

function SingleProduct({ product }) {
  if (product.__typename === 'BundleProduct') {
    return <BundleCard product={product} />;
  }

  // ... rest of the code for handling other product types ...
}
```

Finally, we will update the `useCartMutations` hook to handle adding and removing product bundles from the cart:

```jsx
import { useState } from 'react';
import { useSession } from './SessionProvider';
import {
  useAddToCartMutation,
  useAddBundleToCartMutation,
  useUpdateCartItemQuantitiesMutation,
  useRemoveItemsFromCartMutation,
} from '@axis/graphql';

export default function useCartMutations(productId) {
  // ... rest of the code from the provided sample ...

  async function mutate(values) {
    // ... rest of the code from the provided sample ...

    switch (mutation) {
      // ... rest of the code from the provided sample ...

      case 'addBundle':
        if (!values.bundleItems) {
          throw new Error('No bundle items provided');
        }
        addBundleToCart({
          variables: {
            productId,
            quantity,
            bundleItems: values.bundleItems,
          },
        });
        break;

      // ... rest of the code from the provided sample ...
    }
  }

  // ... rest of the code from the provided sample ...
}
```

In this section, we have learned how to handle product bundles in WooGraphQL. We have updated the `SingleProduct` and `useCartMutations` components to handle product bundles, and we have created a new `BundleProduct` component for displaying product bundles. With these updates, your application should now be able to handle product bundles effectively.
