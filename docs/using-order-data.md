---
title: "Using Order Data with WooGraphQL"
description: "Learn how to utilize the Order queries and type by building a basic order status page in React.js that works by taking an `email` and returning a list of all the orders connected to that `email`."
keywords: "WooGraphQL, WPGraphQL, WooCommerce, GraphQL, Order queries, React.js, order status page"
author: "Geoff Taylor"
---

# Using Order Data

In this section, we will delve into how to utilize the Order queries and types provided by WooGraphQL. This tutorial assumes that you have already gone through the previous documentation pages and have a basic understanding of how to use GraphQL with WooCommerce.

Our objective here is to demonstrate how to build a basic order status page using React.js. This page will allow users to input an email address and, in return, receive a list of all orders associated with that email. This demonstration aims to highlight both the possible and recommended ways of retrieving and utilizing order data.

Before we start, it's important to note that while we will first show you how to fetch orders directly from the client-side, this is not the recommended approach due to privacy concerns. Instead, we suggest using a serverless function, such as a Next API route, with admin access provided by a WordPress Application Password. This method allows for secure querying of orders using the root-level `orders` queries with the `where.billingEmail` argument set.

With these prerequisites and objectives in mind, let's dive into creating our order status page.

## Creating the Order Status Page

First, let's create a simple form that takes an email as input. When the form is submitted, a list of orders associated with the entered email will be displayed.

```jsx
import React, { useState } from 'react';
import { useSession } from './SessionProvider';

function OrderStatusPage() {
  const { customer, updateCustomer, fetching } = useSession();
  const [email, setEmail] = useState('');
  const [selectedOrder, setSelectedOrder] = useState(null);

  const handleSubmit = (event) => {
    event.preventDefault();
    
    updateCustomer({
      billing: { email }
    })
  };

  if (!customer) {
    return null;
  }

  if (!customer?.billing?.email) {
    return (
      <form onSubmit={handleSubmit}>
        <label>
          Email:
          <input type="email" value={email} onChange={e => setEmail(e.target.value)} />
        </label>
        <input type="submit" disabled={fetching} value="Submit" />
      </form>
    );
  }

  const orders = customer?.orders?.nodes || [];

  return (
    <div>
      <ul>
        {orders.map((order) => (
          <li
            key={order.id}
            onClick={() => setSelectedOrder(order)}
          >
            Order #{order.databaseId} - {order.status} - {order.total}
          </li>
        ))}
      </ul>
      <hr />
      {selectedOrder && (
        <div>
          <h2>Order Details</h2>
          <p>Order ID: {selectedOrder.databaseId}</p>
          <p>Status: {selectedOrder.status}</p>
          <p>Total: {selectedOrder.total}</p>
          <p>Date: {new Date(selectedOrder.date).toLocaleDateString()}</p>
        </div>
      )}
    </div>
  );
}

export default OrderStatusPage;
```

After the email has been submitted, we will use the `updateCustomer` callback from the `SessionProvider` to set the current viewer's `billingEmail` as the provided email address. Then, we will the `orders` field from the resulting `customer` object after it's saved in the `SessionProvider`.

![Order status page states](images/order-status-page-states.gif)

As mentioned above, this is not the ideal approach due to privacy concerns.

Let's create a Next.js API route page that takes a billingEmail address and runs the query against the endpoint using `GraphQLClient` from the `graphql-request` library, and returns the orders.

```jsx
import { GraphQLClient } from 'graphql-request';

export default async function handler(req, res) {
  const { billingEmail } = req.body;

  const endpoint = 'YOUR_GRAPHQL_ENDPOINT';
  const graphQLClient = new GraphQLClient(endpoint, {
    headers: {
      authorization: 'Bearer YOUR_WORDPRESS_APPLICATION_PASSWORD',
    },
  });

  const query = `
    query ($billingEmail: String) {
      orders(where: { billingEmail: $billingEmail }) {
        nodes {
          ... OrderFields
        }
      }
    }
  `;

  const variables = {
    billingEmail,
  };

  const data = await graphQLClient.request(query, variables);

  res.status(200).json(data);
}
```

Replace `'YOUR_GRAPHQL_ENDPOINT'` with your GraphQL endpoint and `'Bearer YOUR_WORDPRESS_APPLICATION_PASSWORD'` with your WordPress Application Password.

For more information on WordPress Application Passwords, refer to the [official WordPress Application Password documentation](https://developer.wordpress.org/rest-api/using-the-rest-api/authentication/application-passwords/).

## Conclusion

Well done! You've successfully navigated through the process of using order data in a WooGraphQL context. We've explored how to build a basic order status page in React.js, which takes an email address and returns a list of all the orders associated with that email. We've also discussed the recommended method of retrieving and utilizing order data, which involves using a serverless function like a Next API route.

Remember, while the method we initially demonstrated works, it's not the most secure or efficient way to handle order data. We recommend using a serverless function with admin access, provided by a WordPress Application Password, to query for the orders. This method is more secure and efficient, and it allows for greater flexibility and control over the data.

As you continue to build your headless WooCommerce application, keep these principles in mind. Understanding how to effectively and securely handle order data is crucial for providing a smooth and secure user experience.

In the next sections, we will delve deeper into the capabilities of WooGraphQL, exploring topics like customer data and mutations, subscription data and mutations, and more. Stay tuned!
