---
title: "1. Creating the Product List"
metaTitle: "CRA WooGraphQL Product List Tutorial"
metaDescription: "A tutorial on implementing a product list in with WooGraphQL + Apollo + React."
---

import Link from '../../../src/components/link'

# Creating a product list
A product list is the most common element found on any online shop/marketplace regardless of how it’s built, but it is also the hardest to element to create without some any flaws. There are serious guidelines that a developer must follow or the performance of very common component, that may appear multiple times on one page, could tank your application’s speed and UX.

# What are the guidelines?
1. Never query all your products at once, use pagination instead. Specifically, cursor-based pagination, it’s following the principles of setting some filters on what type products you want and what order they are, and ignoring everything else. We don’t care how many products there are in total because with could take a long time to count them. Just continue to send enough to feel the view as user browses the list. If the user wants a specific product from the list allow the user to update the filters to be more specific and refetch the products with the new filters.
2. Only query the data you need when you need it. The means only query the data you need to display on the product list. Typically this is the “title”, “short description”, “price”, and the “thumbnail”. You’ll also need the “id” and “productId” for caching or an “Add to cart” button, but that just about it. No need to get all the product information for the product page when the user might not even view that product.

# Before we begin.
We are going to skip setting up a React/Apollo application because there are a ton of great tutorials on the web as well as the Apollo Docs[1]. Instead, we will do three simple step to prepare.

1. Make a list of the NPM dependencies we will require for reference.
    - react
    - react-dom
    - apollo-boost
    - @apollo/react-hooks
    - graphql
    - graphql-anywhere
    - styled-components
    - lodash
    - prop-types
2. Make a quick note of the project “src” structure
    - assets/ – Will contain non-js project files like fonts or images,
    - components/ – Will contain reusable components
        - app.js – Application root, we will be staging our product list component for testing with the Webpack “devServer” here.
        - grid.js – Simple reusable style-component.
        - products/ – Will contain our product list component files
    - index.js – Application entry, we will setup our “ApolloClient” and render our “App” wrapped in an “ApolloProvider” here.
3. Create our index.js, components/app.jsx, components/grid.js files

```       
// index.js
import React from 'react';
import ReactDOM from 'react-dom';
import ApolloClient from 'apollo-boost';
import { ApolloProvider } from '@apollo/react-hooks';
import App from './components/app';

const client = new ApolloClient({ uri: process.env.REACT_APP_ENDPOINT });
ReactDOM.render(
  <ApolloProvider client={client}>
    <App />
  </ApolloProvider>,
document.getElementById('root'));

Nothing to complex here if you have basic familiarity with React and Apollo.

import styled from 'styled-components';

const Grid = styled.div.attrs((props) => ({
 	columns: props.columns || 'auto-fit',
 	inline: props.inline || false,
 	rows: props.rows || '1fr',
 	itemWidth: props.itemWidth || '375px',
 	min: typeof props.columns === 'number' ? `${100 / props.columns}%` : props.itemWidth,
}))`
  	padding: 12px 16px;
  	width: ${({ width }) => (width || '100%')};
  	height: ${({ height }) => (height || 'auto')};
  	display: ${({ inline }) => (inline ? 'inline-grid' : 'grid')};
  	grid-template-columns: repeat(${(props) => props.columns}, minmax(${(props) => props.min}, 1fr));
  	grid-template-rows: ${(props) => props.rows};
  	${({ columnGap }) => columnGap && `grid-column-gap: ${columnGap};`}
  	${({ rowGap }) => rowGap && `grid-row-gap: ${rowGap};`}
  	${({ justifyItems }) => justifyItems && `justify-items: ${justifyItems};`}
  	${({ justifyContent }) => justifyContent && `justify-content: ${justifyContent};`}
  	${({ alignItems }) => alignItems && `align-items: ${alignItems};`}
  	${({ alignContent }) => alignContent && `align-content: ${alignContent};`}
  	${({ autoFlow }) => autoFlow && `grid-auto-flow: ${autoFlow};`}
  	${({ maxWidth }) => maxWidth && `max-width: ${maxWidth};`}
`;

export default Grid;
```

A quick, versatile, and reusable Grid styled component.

```
// app.js
import React from 'react';
import ProductsList from './products';

const App = () => {
  return <ProductsList />
};

export default App;
```

We are just importing our `<ProductsList />` component and rendering it.

# Creating the component.
Now start the development server based upon however you setup your application and React should throw an compile error because `./products/index.js` doesn’t exist yet. So let’s jump right into developing our simple-in-design and reusable product list component. It will be made up of two components, a list component for rendering items and the controls for filtering them, and an item component for defining who the individual product data should be rendered. So let’s start with `components/products/index.jsx`.

```
// products/index.jsx
import React from 'react';
import { useQuery } from '@apollo/react-hooks';
import { gql } from 'apollo-boost';
import PropTypes from 'prop-types';

import Grid from '../grid';
import ProductsItem from './item';

export const GET_PRODUCTS = gql`
	query ($first: Int, $after: String) {
    	products(first: $first, after: $after, where: { supportedTypesOnly: true }) {
			edges {
				cursor
				node {
					id
					slug
					name
					type
					shortDescription
					image {
						id
						sourceUrl
						altText
					}
					galleryImages {
						nodes {
							id
							sourceUrl
							altText
						}
					}
					... on SimpleProduct {
						onSale
						price
						regularPrice
					}
					... on VariableProduct {
						onSale
						price
						regularPrice
					}
				}
			}
		}
	}
`;

const ProductsList = (props) => {
	const { columns, itemWidth, ...rest } = props;
	const { data, loading, error } = useQuery(GET_PRODUCTS);

	if (loading) {
		return <div>Fetching products...</div>
	}

	if (error) {
		return <div>{error.message}</div>
	}

	const products = data.products.edges || [];

	return (
		<Grid maxWidth="100%" columns={columns} itemWidth={itemWidth} {...rest}>
			{products.map(({ cursor, node, }) => <ProductsItem key={cursor} data={node} width={itemWidth} />)}
		</Grid>
	);
};

ProductsList.propTypes = {
	products: PropTypes.arrayOf(PropTypes.shape({})),
	columns: PropTypes.oneOfType([PropTypes.number, PropTypes.string]),
	itemWidth: PropTypes.string,
};

ProductsList.defaultProps = {
	products: [],
	columns: 'auto-fit',
	itemWidth: '375px',
};

export default ProductsList;
```

This is our list component. It’ll do the job of querying our products and mapping our product data to the list of `<ProductsItem>` components, however if you stick this in the application and use it you get an error because `components/products/item.jsx` doesn’t exist so let’s make that too.

```
// products/item.jsx
import React from 'react';
import parse, { domToReact } from 'html-react-parser';
import PropTypes from 'prop-types';

import Rail, { ProductRail } from '../rail';
import Image from './image';
import Price from './price';

const ProductsItem = ({ data, ...rest }) => {
	const {
		name,
		onSale,
		regularPrice,
		price,
		image,
		galleryImages,
		type,
		shortDescription: description,
		link,
	} = data;

	return (
		<Rail justifyContent="center" direction="column" {...rest}>
			<Image data={{ image, galleryImages }} width="175px" squared noUI />
			<ProductRail
				direction="column"
				height="175px"
				width="175px"
				alignItems="center"
				inline
				shrink
			>
				<a className="product-name" href={link}>
					{onSale && (
						<>
							<span className="badge">On Sale</span>
							<br />
						</>
					)}
					{name}
					{description && (
						<>
							<br />
							{parse(description, {
								replace({ name, children }) {
									if (name === 'p') {
										return <small>{domToReact(children)}</small>;
									}
								}
							})}
						</>
					)}
					<Price
						type={type}
						onSale={onSale}
						price={price}
						regularPrice={regularPrice}
					/>
				</a>
			</ProductRail>
		</Rail>
	);
};

ProductsItem.propTypes = {
	data: PropTypes.shape({
		name: PropTypes.string,
		slug: PropTypes.string,
		onSale: PropTypes.bool,
		regularPrice: PropTypes.string,
		price: PropTypes.string,
		image: PropTypes.shape({}),
		galleryImages: PropTypes.shape({
			nodes: PropTypes.arrayOf(PropTypes.shape({}))
		}),
		type: PropTypes.string,
		shortDescription: PropTypes.string,
	}),
};

ProductsItem.defaultProps = {
	data: {},
	type: 'normal',
};

export default ProductsItem;
```

```
// products/image.jsx
import React from 'react';
import PropTypes from 'prop-types';
import styled from 'styled-components';

/**
* Returns the proper width for the image.
* 
* @param {*} props 
*/
function getWidth(props, raw = false) {
	const {
		thumbnail,
		small,
		medium,
		large,
		width,
	} = props;

	let output;
	switch (true) {
		case thumbnail:
			output = '128px';
			break;
		case small:
			output = '240px';
			break;
		case medium:
			output = '512px';
			break;
		case large:
			output = '764px';
			break;
		case width && typeof width === 'string':
			output = width;
			break;
		default:
			return false;
	}

	return raw ? parseFloat(output) : output;
}

const Image = styled.img`
	max-width: 100%;
	width: ${(props) => getWidth(props) ? getWidth(props) : '100%'};
	${({ rounded }) => rounded && `border-radius: 50%;`}
`;

const ProductImage = ({ data, ...rest }) => {
	const { image } = data;

	return image 
		? <Image src={data.image.sourceUrl} alt={data.image.altText} {...rest} />
		: <Image src="http://place-puppy.com/640x640" alt="no product image" {...rest} />;
};

const imagePropType = PropTypes.shape({
	sourceUrl: PropTypes.string,
	altText: PropTypes.string,
});

ProductImage.propTypes = {
	data: PropTypes.shape({
		image: imagePropType,
	}),
};

ProductImage.defaultProps = {
	data: {
		image: {
			sourceUrl: 'http://place-puppy.com/640x640',
			altText: 'product image',
		},
	},
};

export default ProductImage;
```

```
// products/price.jsx
import React from 'react';
import PropTypes from 'prop-types';

const ProductPrice = ({ onSale, regularPrice, price, type }) => {
	if (onSale) {
		return type === 'VARIABLE'
			? (<p className="product-price">{price}</p>)
			: (<p className="product-price"><span className="regular-price">{regularPrice}</span> {price}</p>);
	}

	return <p className="product-price">{price}</p>;
};

ProductPrice.propTypes = {
	price: PropTypes.string,
	regularPrice: PropTypes.string,
	salePrice: PropTypes.string,
	onSale: PropTypes.bool,
};

ProductPrice.defaultProps = {
	price: 'Free',
	regularPrice: 'Free',
	salePrice: 'Free',
	onSale: false,
};

export default ProductPrice;
```

This is our item component. Right now, it just displays the most basic product data, simple enough. It’ll do much more later in the tutorial, but this will do for now.

[![Edit 2-add-pagination-start](https://codesandbox.io/static/img/play-codesandbox.svg)](https://codesandbox.io/s/2-add-pagination-start-ib7ed?fontsize=14&hidenavigation=1&theme=dark)
If you run in an application now you should see a list of 10 products. If you don’t see any product check your browser console for error messages, and follow accordingly. If you don’t see any because there are no product in store, I suggest importing the sample product data included in the WooCommerce plugin while developing[2].

# Adding some pagination
Well, this fine all we ever plan to sell is 10 items, but since that’s probably not the case let’s implement some pagination. First we’ll create a callback for executing “fetchMore” request. This will tell Apollo to grab the next group of products and add them to the “data” object essentially. To do this, we’ll have to make some changes to `products/index.jsx`

```
// products/index.jsx
...
import InfiniteLoader from 'react-infinite-loader';

...

const ProductsList = (props) => {
	const containerRef = useRef(null);
	const {
		columns,
		itemWidth,
		width,
		...variables
	} = props;
	const { data, loading, error, fetchMore } = useQuery(GET_PRODUCTS, { variables });

	if (loading) {
		return <div>Fetching products...</div>
	}

	if (error) {
		return <div>{error.message}</div>
	}

	const hasMore = () => {
		if (variables.last) {
			return data.products.pageInfo.hasPreviousPage;
		}
		return data.products.pageInfo.hasNextPage;
	};

	const loadMore = () => {
		// eslint-disable-next-line
		console.log('fetching more items.');

		return hasMore() && fetchMore({
			variables: variables.last
				? { before: data.products.pageInfo.startCursor }
				: { after: data.products.pageInfo.endCursor },
			updateQuery(prev, { fetchMoreResult }) {
				if (fetchMoreResult) {
					const next = {
						...fetchMoreResult,
						products: {
							...fetchMoreResult.products,
							edges: uniqBy([...prev.products.edges, ...fetchMoreResult.products.edges], 'cursor'),
						},
					};
					return next;
				}
				return prev;
			},
		});
	};

	const products = data.products.edges || [];

	return (
		<Grid ref={containerRef} maxWidth="100%" columns={columns} itemWidth={itemWidth} width={width}>
			{products.map(({ cursor, node, }) => (<ProductsItem key={cursor} data={node} width={itemWidth} />))}
			<InfiniteLoader onVisited={loadMore} containerElement={containerRef && containerRef.current} />
		</Grid>
	);
};

ProductsList.propTypes = {
	columns: PropTypes.oneOfType([PropTypes.number, PropTypes.string]),
	itemWidth: PropTypes.string,
	width: PropTypes.string
};

ProductsList.defaultProps = {
	columns: 'auto-fit',
	itemWidth: '375px',
	width: undefined,
};

export default ProductsList;
```

This implements a loadMore callback that is executed when the `<InfinteLoader />` is scrolled passed. This callback queries the next set of products and updates the `GET_PRODUCTS` query results that are stored in Apollo's cache. This new products are this added to the `data` object in `ProductsList` component.
```
const { data, loading, error, fetchMore } = useQuery(GET_PRODUCTS, { variables });
```

This implemention of allows for controlling the number of products queried at one time be setting the `first` or `last` props on the `ProductsList`

```
<ProductsList first={10} />
```

[![Edit 3-add-product-page-start](https://codesandbox.io/static/img/play-codesandbox.svg)](https://codesandbox.io/s/3-add-product-page-start-goomf?fontsize=14&hidenavigation=1&theme=dark)

And there you have it. A product list with pagination. Next, we'll be implementing a product page with a heavy focus on what each piece of data provided by the GraphQL Interface type `Product`, and how that data should be used.

> ### Quick Tip
> You can actually implements some complex filtering capabilities by making some small changes to the `GET_PRODUCTS` query
```javascript
- query ($first: Int, $after: String) {
-   products(first: $first, after: $after, where: { supportedTypesOnly: true }) {
+ query ($first: Int, $after: String, $where: RootQueryToProductConnectionWhereArgs) {
+   products(first: $first, after: $after, where: $where) {
```
> With these changes it's now possible to filtering using the `where` prop.
```
<ProductsList first={10} where={{ category: "Clothing" }} />
```
> To what properties are available on the GraphQL Input type `RootQueryToProductConnectionWhereArgs`. You can search this type and more using the **Docs** panel on the right side of <Link to="/playground">Playground</Link>.