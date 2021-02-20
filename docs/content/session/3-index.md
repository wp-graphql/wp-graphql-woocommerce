---
title: "Why does WooGraphQL use a custom session handler in the first place?"
metaTitle: "Why does WooGraphQL use a custom session handler in the first place? | WooGraphQL Docs | AxisTaylor"
metaDescription: "Learn the reasoning for the custom session handler, the advantages of using it, and the caveats"
---

If you're familiar with WooCommerce, you may be wondering why using a custom session handler at all instead of the WooCommerce default session handler? A number of reasons but the ones that really matter are.

- The default session handler only supports cookies.
- The default session handler only saves changes at the end of the request in the `shutdown` hook.
- The default session handler hass no support for concurrent requests.
- More consistent with modern web.
