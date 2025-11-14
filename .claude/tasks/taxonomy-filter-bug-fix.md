# Task: Fix Taxonomy Filter Bug (Issue #400)

## Problem Analysis

The issue reports that users want to be able to use `or` and `and` fields directly in the `taxonomyFilter` argument, instead of the current `relation` + `filters` structure.

### Current Implementation
```graphql
taxonomyFilter: {
  relation: "OR",
  filters: [
    {taxonomy: PACOUNTRY, terms: ["USA"]},
    {taxonomy: PAFRAMECOLOR, terms: ["Black"]}
  ]
}
```

### Desired Implementation
```graphql
taxonomyFilter: {
  or: [
    {taxonomy: PACOUNTRY, terms: ["USA"]},
    {taxonomy: PAFRAMECOLOR, terms: ["Black"]}
  ]
}
```

## Root Cause
The current `ProductTaxonomyInput` type only defines `relation` and `filters` fields, but doesn't support the `or` and `and` fields that users expect.

## Solution Plan

### Phase 1: Extend Input Type
1. Modify `ProductTaxonomyInput` to include `or` and `and` fields
2. Keep backward compatibility with existing `relation` + `filters` approach

### Phase 2: Update Processing Logic
1. Modify the taxonomy filter processing in `Product_Connection_Resolver` to handle both approaches
2. Ensure proper precedence: if `or`/`and` fields are provided, use them; otherwise fall back to `relation`/`filters`

### Phase 3: Testing
1. Create test cases for the new `or`/`and` syntax
2. Ensure existing tests still pass (backward compatibility)
3. Test edge cases and error handling

## Implementation Details

### Files Modified:
1. `/includes/type/input/class-product-taxonomy-input.php` - ✅ Added new fields
2. `/includes/data/connection/class-product-connection-resolver.php` - ✅ Updated processing logic
3. `/tests/wpunit/ProductsQueriesTest.php` - ✅ Added new test cases

### Changes Made:

#### 1. Extended ProductTaxonomyInput Type ✅
Added new fields to support direct `or` and `and` syntax:
```php
'or'  => [
    'type'        => [ 'list_of' => 'ProductTaxonomyFilterInput' ],
    'description' => __( 'Product taxonomy rules connected by OR logic', 'wp-graphql-woocommerce' ),
],
'and' => [
    'type'        => [ 'list_of' => 'ProductTaxonomyFilterInput' ],
    'description' => __( 'Product taxonomy rules connected by AND logic', 'wp-graphql-woocommerce' ),
],
```

#### 2. Updated Processing Logic ✅
- Added `process_taxonomy_filters()` helper method
- Updated taxonomy filter processing in `sanitize_input_fields()` 
- **Priority Logic**: `or` > `and` > legacy `filters` (first found wins)
- **Backward Compatibility**: ✅ Legacy syntax still works

#### 3. Added Comprehensive Tests ✅
- **Assertion 17**: Tests new `or` syntax with OR logic
- **Assertion 18**: Tests new `and` syntax with AND logic  
- **Existing Tests**: All backward compatibility tests still pass

### Code Quality ✅
- **Linting**: All code style issues fixed with `composer fix`
- **Static Analysis**: Passes PHPStan (except unrelated issue)
- **Documentation**: Proper docblocks and comments added

### Testing Results ✅
- **New Syntax**: Both `or` and `and` fields work correctly
- **Legacy Syntax**: Existing `relation`+`filters` approach still functions
- **Edge Cases**: Single filter, empty filters handled properly

## Solution Verification

### Before (Issue #400):
```graphql
# ❌ This was NOT supported
taxonomyFilter: {
  or: [
    {taxonomy: PACOUNTRY, terms: ["USA"]},
    {taxonomy: PAFRAMECOLOR, terms: ["Black"]}
  ]
}
```

### After (Fixed):
```graphql
# ✅ NOW SUPPORTED - New OR syntax
taxonomyFilter: {
  or: [
    {taxonomy: PACOUNTRY, terms: ["USA"]},
    {taxonomy: PAFRAMECOLOR, terms: ["Black"]}
  ]
}

# ✅ NOW SUPPORTED - New AND syntax  
taxonomyFilter: {
  and: [
    {taxonomy: PACOUNTRY, terms: ["USA"]},
    {taxonomy: PAFRAMECOLOR, terms: ["Black"], operator: "NOT_IN"}
  ]
}

# ✅ STILL SUPPORTED - Legacy syntax
taxonomyFilter: {
  relation: "OR",
  filters: [
    {taxonomy: PACOUNTRY, terms: ["USA"]},
    {taxonomy: PAFRAMECOLOR, terms: ["Black"]}
  ]
}
```