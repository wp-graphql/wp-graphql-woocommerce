# Using Cart Data

In this guide, we will create a "/cart" page that displays a table of the items in the cart. We will use the `UserSessionProvider` created in the last guide to pull the cart data using `useSession()`. The table will have four columns: `Product`, `Price`, `Quantity`, and `Total`. We will also create a cart totals table that displays the shipping totals, applied coupons, and the final total. Let's start by implementing the changes to the `useCartMutations` hook also created in the previous guide.

## Prerequisites

- Basic knowledge of React and React Router.
- Familiarity with GraphQL and WPGraphQL.
- A setup WPGraphQL/WooGraphQL backend.
- Read previous guides on [Routing By URI](routing-by-uri.md), [Using Product Data](using-product-data.md), and [Handling User Session and Using Cart Mutations](handing-user-session-and-using-cart-mutations).

## Step 0: Create `graphql.js` file

First, create a `graphql.js` file to store all the GraphQL queries and mutations:

```javascript
import { gql } from '@apollo/client';

export const CustomerContent = gql`
  fragment CustomerContent on Customer {
    id
    sessionToken
    shipping {
      postcode
      state
      city
      country
    }
  }
`;

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
`;

export const GetProduct = gql`
  query GetProduct($id: ID!, $idType: ProductIdTypeEnum) {
    product(id: $id, idType: $idType) {
      ...ProductContentFull
    }
  }
`;

export const GetProductVariation = gql`
  query GetProductVariation($id: ID!) {
    productVariation(id: $id, idType: DATABASE_ID) {
      ...VariationContent
    }
  }
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
`;

export const ApplyCouponToCart = gql`
  mutation ApplyCouponToCart($code: String!) {
    applyCoupon(input: {code: $code}) {
      cart {
        ...CartContent
      }
    }
  }
`;

export const RemoveCouponFromCart = gql`
  mutation RemoveCouponFromCart($code: String!) {
    removeCoupons(input: {codes: [$code]}) {
      cart {
        ...CartContent
      }
    }
  }
`;

export const RemoveCouponsFromCart = gql`
  mutation RemoveCouponsFromCart($codes: [String!]) {
    removeCoupons(input: {codes: $codes}) {
      cart {
        ...CartContent
      }
    }
  }
`;

export const SetShippingLocale = gql`
  mutation SetShippingLocale($zip: String!, $state: String, $city: String, $country: CountriesEnum) {
    updateCustomer(
      input: {shipping: {postcode: $zip, country: $country, state: $state, city: $city}}
    ) {
      customer {
        ...CustomerContent
      }
    }
  }
`;

export const SetShippingMethod = gql`
  mutation SetShippingMethod($shippingMethod: String!) {
    updateShippingMethod(input: {shippingMethods: [$shippingMethod]}) {
      cart {
        ...CartContent
      }
    }
  }
`;

```

## Step 1: Update useCartMutations.js

Update the `useCartMutations` hook as follows:

```javascript
import { useMutation } from '@apollo/client';
import { useSession } from "./SessionProvider";
import {
  AddToCart,
  UpdateCartItemQuantities,
  RemoveItemsFromCart,
  ApplyCouponToCart,
  RemoveCouponFromCart,
  SetShippingLocale,
  SetShippingMethod,
} from './graphql';

const useCartMutations = (
  productId,
  variationId,
  extraData,
) => {
  // ... rest of useCartMutations code.

  const removeCartItemHelper = () => {
    const item = findInCart(productId, variationId, extraData);

    if (!item) {
      throw new Error('Failed to find item in cart.');
    }

    removeCartItem({ variables: { keys: [item.key] } });
  };

  const updateQuantityHelper = (quantity) => {
    const item = findInCart(productId, variationId, extraData);

    if (!item) {
      throw new Error('Failed to find item in cart.');
    }

    updateQuantity({ variables: { items: [{ key: item.key, quantity }] } });
  };

  const addToCartHelper = (pId, quantity, vId, e) => {
    addToCart({
      variables: {
        productId: pId,
        quantity,
        variationId: vId,
        extraData: e,
      },
    });
  };

  const store = {
    adding,
    updating,
    removing,
    quantityFound,
    mutate,
    addToCart: addToCartHelper,
    removeCartItem: removeCartItemHelper,
    updateQuantity: updateQuantityHelper,
  };

  return store;
};
```

This new code adds more helper functions like `removeCartItemHelper`, `updateQuantityHelper`, `addToCartHelper`, and updates the store to include these functions. With these helpers, we get access to the `add`, `update`, and `remove` cart mutations without the need to provide product details to the hook.

And now let's implement the `useOtherCartMutations` hook:

```javascript
export const useOtherCartMutations = () => {
  const {
    setCart,
    setCustomer,
  } = useSession();

  const [applyCouponToCart, { loading: applyingCoupon }] = useMutation({
    mutation: ApplyCouponToCart,
    onCompleted({ applyCoupon: data }) {
      if (data?.cart) {
        setCart(data.cart as Cart);
      }
    },
  });

  const [removeCouponFromCart, { loading: removingCoupon }] = useMutation({
    mutation: RemoveCouponFromCart,
    onCompleted({ removeCoupons: data }) {
      if (data?.cart) {
        setCart(data.cart as Cart);
      }
    },
  });

  const [setShippingLocale, { loading: savingShippingLocale }] = useMutation({
    mutation: SetShippingLocale,
    onCompleted({ updateCustomer: data }) {
      if (data?.customer) {
        setCustomer(data.customer);
      }
    },
  });

  const [setShippingMethod, { loading: savingShippingMethod }] = useMutation({
    mutation: SetShippingMethod,
    onCompleted({ updateShippingMethod: data }) {
      if (data?.cart) {
        setCart(data.cart as Cart);
      }
    },
  });

  const applyCouponHelper = (code) => applyCouponToCart({ variables: { code } });
  const removeCouponHelper = (code) => removeCouponFromCart({ variables: { code } });
  const setShippingLocaleHelper = (input) => setShippingLocale({
    variables: {
      ...input,
    },
  });

  const setShippingMethodHelper = (shippingMethod) => setShippingMethod({
    variables: { shippingMethod },
  });

  const store = {
    applyingCoupon,
    removingCoupon,
    savingShippingInfo: savingShippingLocale || savingShippingMethod,
    applyCoupon: applyCouponHelper,
    removeCoupon: removeCouponHelper,
    setShippingLocale: setShippingLocaleHelper,
    setShippingMethod: setShippingMethodHelper,
  };

  return store;
};
```

This hook provides the helper callbacks for the other cart mutations. These mutations are the ones that affect the cart and not cart items, at least not directly, like `applyCoupon`.

## Step 2: Create the `/cart` page

After making the necessary updates to `useCartMutations` and `useOtherCartMutations`, we can now create the `/cart` page. First, import the necessary components and hooks.

```javascript
import React, { useState } from 'react';
import { useSession } from './SessionProvider';
import { useCartMutations, useOtherCartMutations } from './useCartMutations';
```

Now, let's create the `ShippingLocaleForm` component that will be used later in the `CartPage` component:

```jsx
import React, { useState } from 'react';
import { useOtherCartMutations } from './useOtherCartMutations';

const ShippingLocaleForm = () => {
  const [country, setCountry] = useState('');
  const [postalCode, setPostalCode] = useState('');
  const { updateShippingLocale } = useOtherCartMutations();

  const handleSubmit = async (e) => {
    e.preventDefault();
    await updateShippingLocale({ country, postalCode });
  };

  return (
    <form onSubmit={handleSubmit}>
      <h2>Shipping Locale</h2>
      <div>
        <label htmlFor="country">Country:</label>
        <input
          type="text"
          id="country"
          name="country"
          value={country}
          onChange={(e) => setCountry(e.target.value)}
        />
      </div>
      <div>
        <label htmlFor="postalCode">Postal Code:</label>
        <input
          type="text"
          id="postalCode"
          name="postalCode"
          value={postalCode}
          onChange={(e) => setPostalCode(e.target.value)}
        />
      </div>
      <button type="submit">Update Shipping Locale</button>
    </form>
  );
};

export default ShippingLocaleForm;
```

Next, create the `CartPage` component:

```jsx
const CartPage = () => {
  const CartPage = () => {
  const { cart } = useSession();
  const { applyCoupon } = useOtherCartMutations();

  if (!cart) {
    return <div>Loading...</div>;
  }

  const cartItems = cart.contents.nodes;

  return (
    <div>
      <h2>Cart</h2>
      <table>
        <thead>
          <tr>
            <th>Product</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Total</th>
          </tr>
        </thead>
        <tbody>
          {cartItems.map((item) => {
            const { key, product, variation, quantity, subtotal, total } = item;
            const { node: productNode } = product;
            const { node: variationNode } = variation || {};

            const {
              id: productId,
              image,
              name,
            } = productNode;
            const { id: variationId } = variationNode || {};
            const cartMutations = useCartMutations(productId, variationId);

            return (
              <tr key={key}>
                <td>
                  <button onClick={() => cartMutations.removeCartItem()}>Remove</button>
                  <img src={image.sourceUrl} alt={image.altText} />
                  <span>{name}</span>
                </td>
                <td>{subtotal}</td>
                <td>
                  <input
                    type="number"
                    value={quantity}
                    onChange={(e) => cartMutations.updateQuantity(parseInt(e.target.value))}
                  />
                </td>
                <td>{total}</td>
              </tr>
            );
          })}
        </tbody>
        <tfoot>
          <tr>
            <td colSpan={4}>
              <form
                onSubmit={(e) => {
                  e.preventDefault();
                  const couponCode = e.target.elements.coupon.value;
                  applyCoupon(couponCode);
                }}
              >
                <label>
                  Apply Coupon:
                  <input type="text" name="coupon" />
                </label>
                <button type="submit">Apply</button>
              </form>
            </td>
          </tr>
        </tfoot>
      </table>

      <div>
        <h3>Cart Totals</h3>
        {cart.needsShipping && !cart.needsShippingAddress ? (
          <div>
            <h4>Shipping</h4>
            {cart.availableShippingMethods.map((shippingMethod) => (
              <div key={shippingMethod.id}>
                <input type="radio" name="shipping_method" value={shippingMethod.id} />
                <label>{shippingMethod.label}</label>
              </div>
            ))}
            <p>Shipping Tax: {cart.shippingTax}</p>
            <p>Shipping Total: {cart.shippingTotal}</p>
          </div>
        ) : cart.needsShipping ? (
          <ShippingLocaleForm />
        ) : null}
        <p>Subtotal: {cart.subtotal}</p>
        {cart.appliedCoupons.map(({ code, discountAmount }) => (
          <div key={code}>
            <p>
              Coupon: {code} - Discount: {discountAmount}
            </p>
          </div>
        ))}
        <p>Total: {cart.total}</p>
      </div>
    </div>
  );
};

export default CartPage;
```

In the `CartPage` component, we first fetch the `cart` from the `SessionProvider`. If the cart is not available, we show a loading message. Once the cart is loaded, we display the cart items in a table format, allowing users to remove items or update the quantity.

We also provide a form to apply a coupon code, which calls the `applyCoupon` function from the `useOtherCartMutations` hook.

If the cart requires shipping, we display the available shipping methods and shipping-related costs. If the cart needs a shipping address, we render the `ShippingLocaleForm` component.

Lastly, we display the cart's subtotal, applied coupons with their respective discounts, and the cart's total.

Now, you can use the `CartPage` component in your app, allowing users to interact with the cart, apply coupons, and manage shipping options.
