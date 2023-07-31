---
title: "Using Product Bundle Data + Mutations with WooGraphQL"
description: "Learn how to use the Product Bundle functionality with WooGraphQL by building upon the code from `Using Product Data` and `Creating Session Provider and using Cart Mutations`."
keywords: "WooGraphQL, WPGraphQL, WooCommerce, GraphQL, Product Bundle functionality, Product Data, Session Provider, Cart Mutations"
author: "Geoff Taylor"
---

# Using Product Bundle Data + Mutations

In the previous sections, we have explored various aspects of using WooGraphQL, from handling user sessions and cart mutations to working with different product types. Now, we are going to delve into the world of product bundles. Product bundles are a powerful feature in WooCommerce that allows merchants to sell multiple products together as a set, often at a discounted price. This can be a great way to increase average order value and move more inventory.

In this section, we will demonstrate how to use the WooGraphQL API to work with product bundle data and mutations. We will build upon the code from the previous sections, specifically the ones on "Using Product Data" and "Creating Session Provider and using Cart Mutations". This will involve fetching product bundle data, adding product bundles to the cart, and handling the unique specifications of product bundles.

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

Now we have updated the `SingleProduct` and `useCartMutations` components to handle product bundles, and we have created a new `BundleProduct` component for displaying product bundles. With these updates, your application should now be able to handle product bundles effectively.

## Conclusion

Congratulations! You have now learned how to work with product bundle data and mutations using the WooGraphQL API. This includes fetching product bundle data, adding product bundles to the cart, and handling the unique specifications of product bundles.

Remember, while product bundles are similar to other product types in many ways, they have unique specifications that require special handling. Therefore, it's important to understand these differences and how to work with them when building your headless WooCommerce application.

In the next sections, we will continue to explore more advanced features of WooGraphQL, including working with product add-ons. Stay tuned!
