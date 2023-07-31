---
title: "Using Product Data with WooGraphQL"
description: "Learn how to efficiently fetch and use product data in your headless WooCommerce application using WooGraphQL and WPGraphQL for a seamless shopping experience."
keywords: "WooGraphQL, WPGraphQL, WooCommerce, GraphQL, product data, headless, shopping experience"
author: "Geoff Taylor"
---

# Using Product Data

In this section, we will implement the Single Product page using the provided GraphQL query and the JSON result. We will display the product's `name`, `description`, `price`, `regularPrice`, `attributes`, `width`, `height`, `length`, and `weight`. Additionally, we will prepare a section for cart options like desired quantity and an Add to Cart button.

## Prerequisites

- Basic knowledge of React and React Router.
- Familiarity with GraphQL and WPGraphQL.
- A setup WPGraphQL/WooGraphQL backend.
- Read previous sections on [Routing By URI](routing-by-uri.md)

## Step 0: Create our `graphql.js` file.

```javascript
import { gql } from '@apollo/client';

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

export const GetProduct = gql`
  query GetProduct($id: ID!, $idType: ProductIdTypeEnum) {
    product(id: $id, idType: $idType) {
      ...ProductContentFull
    }
  }
`;
```

We've included the `GetProduct` query we'll be utilizing going forward and leveraging some fragments. Now we can move onto implementing the components sourcing this query.

## Step 1: Set up the Single Product component

First, create a new component for the Single Product page. You can call it `SingleProduct.js`. In this component, we will use the `GetProduct` query from the list of provided queries.

```jsx
import React from 'react';
import { useQuery } from '@apollo/client';
import { GetProduct } from './graphql';

const SingleProduct = ({ productId }) => {
  const { data, loading, error } = useQuery(GetProduct, {
    variables: { id: productId, idType: 'DATABASE_ID' },
  });

  if (loading) return <p>Loading...</p>;
  if (error) return <p>Error: {error.message}</p>;

  const product = data.product;

  return (
    <div className="single-product">
      {/* Render product information here */}
    </div>
  );
};

export default SingleProduct;
```

## Step 2: Display product information

Now, we will render the product information using the fetched data.

```javascript
// Inside the SingleProduct component, after defining the `product` constant

return (
  <div className="single-product">
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

    {/* Add the width, height, length, and weight information */}
    {/* Add the cart options section */}
  </div>
);
```

## Step 3: Add width, height, length, and weight information

You need to modify the `GetProduct` query to include the dimensions and weight fields for simple and variable products. Then, display the dimensions and weight in the Single Product component. Add the following fields to the SimpleProduct and VariableProduct fragments in the `graphql.js` file.

```graphql
dimensions {
  width
  height
  length
}
weight
```

Inside the SingleProduct component, render the attributes.

```jsx
<div className="dimensions">
  <strong>Dimensions:</strong> {product.dimensions.width} x {product.dimensions.height} x {product.dimensions.length}
</div>
<div className="weight">
  <strong>Weight:</strong> {product.weight}
</div>
```

## Step 4: Add cart options section

Finally, add a section for cart options like desired quantity and the Add to Cart button. Use the `soldIndividually` and `stockStatus` fields to control the state of the cart controls. Add this inside the SingleProduct component after rendering the weight information.

```jsx
  <div className="cart-options">
    {!product.soldIndividually && (
      <div className="quantity">
        <label htmlFor="quantity">Quantity:</label>
        <input type="number" id="quantity" name="quantity" min="1" defaultValue="1" />
      </div>
    )}
    {product.stockStatus === 'IN_STOCK' ? (
      <button type="button" className="add-to-cart">
        Add to Cart
      </button>
    ) : (
      <p>Out of stock</p>
    )}
  </div>
);
```

With this implementation, the Single Product page displays the product information, dimensions, and weight. Additionally, it includes a section for cart options, such as the desired quantity and an Add to Cart button. The availability of the cart options is dictated by the `soldIndividually` and `stockStatus` fields. If the product is not sold individually, the user can select a quantity. The Add to Cart button is only shown if the product is in stock; otherwise, an "Out of stock" message is displayed. We could also go a step further and use the product's `stockQuantity` to set a hard max quantity limit, but that's out of the scope of this section.

## Conclusion

In this section, you learned how to implement a Single Product page using the provided GraphQL queries and the example JSON response. The Single Product page displays essential product information such as the name, description, price, attributes, dimensions, and weight. The Add to Cart controls are conditionally rendered based on the `soldIndividually` and `stockStatus` fields.

In the next section, we will dive into implementing the functionality for adding a product to the cart and updating the cart's contents. We will explore how to manage the cart state and interact with the WooCommerce API to handle cart-related actions.

By continuing to follow the documentation, you'll be well on your way to building a complete, functional e-commerce website using React and WooCommerce with GraphQL.
