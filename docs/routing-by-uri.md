# Routing By URI

In this guide, we will create a simple app that demonstrates routing with WPGraphQL's `nodeByUri` query. We will use this query to fetch data for a shop page that displays a list of products with their "name", "shortDescription", "price", and "image". The shop page will use the uri parameter to fetch the data and render the page accordingly.

## Prerequisites

- Basic knowledge of React and React Router.
- Familiarity with GraphQL and WPGraphQL.
- A setup WPGraphQL/WooGraphQL backend.

## Getting Started

Let's start by setting up a simple React application with React Router. We will create a basic shop page that fetches the products data based on the `uri`.

```jsx
import React from 'react';
import { BrowserRouter as Router, Route, Switch } from 'react-router-dom';
import ShopPage from './ShopPage';

function App() {
  return (
    <Router>
      <Switch>
        <Route path="/shop" component={ShopPage} />
      </Switch>
    </Router>
  );
}

export default App;
```

## ShopPage Component

In the `ShopPage` component, we will use the `nodeByUri` query to fetch the data for the shop page. Based on the data received, we will render a product listing for either a collection or a single data object.

```jsx
import React, { useEffect, useState } from 'react';
import { useQuery } from '@apollo/client';
import gql from 'graphql-tag';
import ProductListing from './ProductListing';

// Import the NodeByUri query here
import { NodeByUri } from './graphql';

const ShopPage = () => {
  const [products, setProducts] = useState([]);
  const { loading, error, data } = useQuery(NodeByUri, {
    variables: { uri: '/shop' },
  });

  useEffect(() => {
    if (data && data.nodeByUri) {
      const { nodeByUri } = data;

      if (nodeByUri.contentNodes) {
        setProducts(nodeByUri.contentNodes.nodes);
      } else {
        setProducts([nodeByUri]);
      }
    }
  }, [data]);

  if (loading) return <p>Loading...</p>;
  if (error) return <p>Error: {error.message}</p>;

  return <ProductListing products={products} />;
};

export default ShopPage;
```

The `ShopPage` component fetches the data using the `nodeByUri` query and updates the state with the received data. It then passes the products data to the `ProductListing` component for rendering.

## ProductListing Component

The `ProductListing` component takes the products data and renders a list of products.

_Notice that we are not checking to see if the fields that are nullable aren't empty values before rendering them and remember you should always do so, we just skipped it here to for readability._

```jsx
import React from 'react';

const ProductListing = ({ products }) => {
  return (
    <div>
      <h2>Shop</h2>
      <ul>
        {products.map((product) => (
          <li key={product.id}>
            <h3>{product.name}</h3>
            <p>{product.shortDescription}</p>
            <p>Price: {product.price}</p>
            <img src={product.image.sourceUrl} alt={product.image.altText} />
          </li>
        ))}
      </ul>
    </div>
  );
};

export default ProductListing;
```

With the `ProductListing` component, we can display the product listing for both collection and single data object. This approach can also be applied to other pages such as `/product-category/*` or `/product-tag/*` pages, with the ability to change there slug names as well in the WP Dashboard.

In the next part, we will focused further on rendering a product listing using the `nodeByUri` query by exploring adding features like pagination, sorting, and filtering to our shop page.

## Pagination

To add pagination to our shop page, we will need to update the `NodeByUri` operation to include the `after` and `first` variables. This will allow us to fetch a specific number of products and control the starting point for the fetched data.

_Notice we are not applying the `first` and `after` variables to the query but instead a connection within._

First, update the `NodeByUri` query in `graphql.js`:

```graphql
query NodeByUri($uri: ID!, $first: Int, $after: String) {
  nodeByUri(uri: $uri) {
    ... on Product {
      id
      name
      shortDescription
      price
      image {
        sourceUrl
        altText
      }
    }
      contentNodes(first: $first, after: $after) {
        nodes {
          ... on Product {
            id
            name
            shortDescription
            ... on SimpleProduct {
                price
            }
            ... on VariableProduct {
                price
            }
            image {
              sourceUrl
              altText
            }
          }
        }
        pageInfo {
          hasNextPage
          endCursor
        }
      }
  }
}
```

Next, update the `ShopPage` component to manage the pagination state and fetch more products using the `fetchMore` function from the `useQuery` hook:

```jsx
import React, { useEffect, useState } from 'react';
import { useQuery } from '@apollo/client';
import gql from 'graphql-tag';
import ProductListing from './ProductListing';

// Import the NodeByUri query here
import { NodeByUri } from './graphql';

const ShopPage = () => {
  const [products, setProducts] = useState([]);
  const { loading, error, data, fetchMore } = useQuery(NodeByUri, {
    variables: { uri: '/shop', first: 10 },
  });

  useEffect(() => {
    if (data && data.nodeByUri) {
      const { nodeByUri } = data;

      if (nodeByUri.contentNodes) {
        setProducts(nodeByUri.contentNodes.nodes);
      } else {
        setProducts([nodeByUri]);
      }
    }
  }, [data]);

  const loadMoreProducts = () => {
    if (data.nodeByUri.contentNodes.pageInfo.hasNextPage) {
      fetchMore({
        variables: {
          after: data.nodeByUri.contentNodes.pageInfo.endCursor,
        },
      });
    }
  };

  if (loading) return <p>Loading...</p>;
  if (error) return <p>Error: {error.message}</p>;

  return (
    <>
      <ProductListing products={products} />
      <button onClick={loadMoreProducts}>Load More</button>
    </>
  );
};

export default ShopPage;
```

Now, when you click the "Load More" button, it will fetch more products and add them to the list.

## Sorting

To add sorting functionality, we will update the `NodeByUri` query again to include a `where` argument in the `contentNodes` field. This will allow us to control the order in which the products are fetched.

Update the `NodeByUri` query in `graphql.js`:

```graphql
query NodeByUri($uri: ID!, $first: Int, $after: String, $where: RootQueryToProductConnectionWhereArgs) {
    nodeByUri(uri: $uri) {
        ... on Product {
            id
            name
            shortDescription
            price
            image {
                sourceUrl
                altText
            }
        }
        contentNodes(first: $first, after: $after, where: $where) {
            nodes {
                ... on Product {
                    id
                    name
                    shortDescription
                    ... on SimpleProduct {
                        price
                    }
                    ... on VariableProduct {
                        price
                    }
                    image {
                        sourceUrl
                        altText
                    }
                }
            }
            pageInfo {
                hasNextPage
                endCursor
            }
        }
    }
}
```

Next, add a sorting dropdown component to the `ShopPage` component, and update the state and the `useQuery` hook to handle sorting:

```jsx
import React, { useEffect, useState } from 'react';
import { useQuery } from '@apollo/client';
import gql from 'graphql-tag';
import ProductListing from './ProductListing';

// Import the NodeByUri query here
import { NodeByUri } from './graphql';

const ShopPage = () => {
  const [products, setProducts] = useState([]);
  const [sort, setSort] = useState(null);
  const { loading, error, data, fetchMore } = useQuery(NodeByUri, {
    variables: { uri: '/shop', first: 10, where: sort },
  });

  useEffect(() => {
    if (data && data.nodeByUri) {
      const { nodeByUri } = data;

      if (nodeByUri.contentNodes) {
        setProducts(nodeByUri.contentNodes.nodes);
      } else {
        setProducts([nodeByUri]);
      }
    }
  }, [data]);

  const loadMoreProducts = () => {
    if (data.nodeByUri.contentNodes.pageInfo.hasNextPage) {
      fetchMore({
        variables: {
          after: data.nodeByUri.contentNodes.pageInfo.endCursor,
        },
      });
    }
  };

  const handleSortChange = (e) => {
    setSort({ orderby: e.target.value });
  };

  if (loading) return <p>Loading...</p>;
  if (error) return <p>Error: {error.message}</p>;

  return (
    <>
      <select onChange={handleSortChange}>
        <option value="DATE">Newest</option>
        <option value="PRICE_ASC">Price: Low to High</option>
        <option value="PRICE_DESC">Price: High to Low</option>
      </select>
      <ProductListing products={products} />
      <button onClick={loadMoreProducts}>Load More</button>
    </>
  );
};

export default ShopPage;
```

Now, when you change the sorting option in the dropdown, the products will be fetched and displayed in the selected order.

## Filtering

To add filtering functionality, you will need to make further updates to the `NodeByUri` query and the `ShopPage` component. In this example, we will add a simple search input to filter products based on their names.

Update the `NodeByUri` query in `graphql.js` to include a search argument in the where field:

```graphql
query NodeByUri($uri: ID!, $first: Int, $after: String, $where: RootQueryToProductConnectionWhereArgs) {
  nodeByUri(uri: $uri) {
    ... on Product {
      id
      name
      shortDescription
      price
      image {
        sourceUrl
        altText
      }
    }
    ... on Collection {
      contentNodes(first: $first, after: $after, where: $where) {
        nodes {
          ... on Product {
            id
            name
            shortDescription
            ... on SimpleProduct {
                price
            }
            ... on VariableProduct {
                price
            }
            image {
              sourceUrl
              altText
            }
          }
        }
        pageInfo {
          hasNextPage
          endCursor
        }
      }
    }
  }
}
```

Next, add a search input component to the `ShopPage` component, and update the state and the `useQuery` hook to handle filtering:

```jsx
import React, { useEffect, useState } from 'react';
import { useQuery } from '@apollo/client';
import gql from 'graphql-tag';
import ProductListing from './ProductListing';

// Import the NodeByUri query here
import { NodeByUri } from './graphql';

const ShopPage = () => {
  const [products, setProducts] = useState([]);
  const [sort, setSort] = useState(null);
  const [search, setSearch] = useState('');
  const { loading, error, data, fetchMore } = useQuery(NodeByUri, {
    variables: { uri: '/shop', first: 10, where: { ...sort, search } },
  });

  useEffect(() => {
    if (data && data.nodeByUri) {
      const { nodeByUri } = data;

      if (nodeByUri.contentNodes) {
        setProducts(nodeByUri.contentNodes.nodes);
      } else {
        setProducts([nodeByUri]);
      }
    }
  }, [data]);

  const loadMoreProducts = () => {
    if (data.nodeByUri.contentNodes.pageInfo.hasNextPage) {
      fetchMore({
        variables: {
          after: data.nodeByUri.contentNodes.pageInfo.endCursor,
        },
      });
    }
  };

  const handleSortChange = (e) => {
    setSort({ orderby: e.target.value });
  };

  const handleSearchChange = (e) => {
    setSearch(e.target.value);
  };

  if (loading) return <p>Loading...</p>;
  if (error) return <p>Error: {error.message}</p>;

  return (
    <>
      <input
        type="text"
        placeholder="Search products..."
        value={search}
        onChange={handleSearchChange}
      />
      <select onChange={handleSortChange}>
        <option value="DATE">Newest</option>
        <option value="PRICE_ASC">Price: Low to High</option>
        <option value="PRICE_DESC">Price: High to Low</option>
      </select>
      <ProductListing products={products} />
      <button onClick={loadMoreProducts}>Load More</button>
    </>
  );
};

export default ShopPage;
```

With these changes, you can now search for products by typing in the search input, and the products will be fetched and displayed based on the search query. This is far from complete it needs many more things, like CSS styling, field validation, and error handling to name few.

In summary, you've now implemented sorting and filtering functionality for products in a headless WordPress + React app using WooGraphQL. You can further customize the sorting and filtering options by modifying the `NodeByUri` query and the `ShopPage` component.
