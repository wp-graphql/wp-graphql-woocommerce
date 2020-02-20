const config = {
	"gatsby": {
		"pathPrefix": "/",
		"siteUrl": "https://woographql.axistaylor.com",
		"gaTrackingId": null
	},
	"header": {
		"logo": "https://docs.axistaylor.com/wp-content/uploads/2020/02/woographql-v3.svg.png",
		"logoLink": "https://woographql.axistaylor.com",
		"title": "WooGraphQL Documentation",
		"githubUrl": "https://github.com/wp-graphql/wp-graphql-woocommerce",
		"helpUrl": "https://github.com/wp-graphql/wp-graphql-woocommerce/issues",
		"tweetText": "Take a look at the amazing Docs Site for @woographql https://woographql.axistaylor.com",
		"links": [
			{
				"text": "AxisTaylor",
				"link": "https://axistaylor.com"
			}
		],
		"search": {
			"enabled": false,
			"indexName": "",
			"algoliaAppId": process.env.GATSBY_ALGOLIA_APP_ID,
			"algoliaSearchKey": process.env.GATSBY_ALGOLIA_SEARCH_KEY,
			"algoliaAdminKey": process.env.ALGOLIA_ADMIN_KEY
		}
	},
	"sidebar": {
		"forcedNavOrder": [
			"/introduction",
			"/playground",
			"/guides",
			"/contributing"
		],
    	"collapsedNav": [
      		"/guides"
    	],
		"links": [
			{ "text": "AxisTaylor", "link": "https://axistaylor.com"},
			{ "text": "WPGraphQL", "link": "https://wpgraphql.com"},
			{ "text": "WPGraphQL Documentation", "link": "https://wpgraphql.com/docs"},
		],
		"frontline": true,
		"ignoreIndex": true,
	},
	"siteMetadata": {
		"title": "WPGraphQL WooCommerce Docs | AxisTaylor",
		"description": "The official WPGraphQL WooCommerce (WooGraphQL) documentation",
		"ogImage": null,
		"docsLocation": "https://github.com/kidunot89/woographql-docs/tree/master/content",
		"favicon": "https://docs.axistaylor.com/wp-content/uploads/2020/02/woographql-v3.svg.png",
		"twitterHandle": "woographql"
	},
	"pwa": {
		"enabled": false, // disabling this will also remove the existing service worker.
		"manifest": {
			"name": "WPGraphQL WooCommerce Documentation",
			"short_name": "WooGraphQLDocs",
			"start_url": "/",
			"background_color": "#6b37bf",
			"theme_color": "#6b37bf",
			"display": "standalone",
			"crossOrigin": "use-credentials",
			icons: [
				{
					src: "src/pwa-512.png",
					sizes: `512x512`,
					type: `image/png`,
				},
			],
		},
	}
};

module.exports = config;
