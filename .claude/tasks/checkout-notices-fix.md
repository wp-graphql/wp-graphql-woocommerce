# Checkout Mutation Notices Fix - Implementation Plan

## Issue Summary
**GitHub Issue**: #666 - Checkout mutation does not return WC Session Notices

**Problem**:
- When checkout mutation fails (e.g., payment gateway failure), WooCommerce session notices are not returned to the client
- Notices persist in the WC session until manually cleared, causing them to appear in subsequent requests
- Unlike other mutations (e.g., `updateSession`), the checkout mutation doesn't expose a `notices` field

## Root Cause Analysis

### Current Implementation Issues
1. **Missing Notices Field**: The checkout mutation output fields in `/includes/mutation/class-checkout.php:96-123` do not include a `notices` field
2. **No Notice Handling**: The mutation doesn't check for or return WC session notices like other mutations do
3. **No Notice Clearing**: Failed checkouts don't clear notices, causing them to persist

### Working Example
The `updateSession` mutation in `/includes/mutation/class-session-update.php:123-127` properly handles notices:
```php
$notices = $session->get( 'wc_notices' );
if ( ! empty( $notices['error'] ) ) {
    $error_messages = implode( ' ', array_column( $notices['error'], 'notice' ) );
    \wc_clear_notices();
    throw new UserError( $error_messages );
}
```

## Solution Design

We need a **hybrid approach** that handles both error and non-error notices:

1. **Error notices**: Captured in catch block and included in UserError message (for failed checkouts)
2. **Non-error notices**: Exposed via new `notices` field in successful checkout responses

### 1. Enhanced Error Handling in Checkout Mutation
**File**: `/includes/mutation/class-checkout.php`
**Location**: Catch block around line 169-176

Capture WC error notices and include them in the error message before throwing UserError. This handles failed checkouts with payment gateway errors.

### 2. Add Notices Field for Successful Checkouts
**File**: `/includes/mutation/class-checkout.php`
**Location**: `get_output_fields()` method (around line 96)

Add a new `notices` field to expose non-error notices (success, info) that occur during successful checkouts.

### 3. Create Notice GraphQL Types
**File**: `/includes/type/object/class-cart-notice.php` (new file)

Create a new GraphQL object type for notices:
```php
<?php
namespace WPGraphQL\WooCommerce\Type\WPObject;

class Cart_Notice {
    public static function register_type() {
        register_graphql_object_type(
            'CartNotice',
            [
                'description' => __( 'A WooCommerce notice', 'wp-graphql-woocommerce' ),
                'fields'      => [
                    'type' => [
                        'type'        => 'CartNoticeTypeEnum',
                        'description' => __( 'Notice type', 'wp-graphql-woocommerce' ),
                    ],
                    'message' => [
                        'type'        => 'String',
                        'description' => __( 'Notice message', 'wp-graphql-woocommerce' ),
                    ],
                ],
            ]
        );
    }
}
```

### 4. Create Notice Type Enum
**File**: `/includes/type/enum/class-cart-notice-type.php` (new file)

Create an enum for notice types:
```php
<?php
namespace WPGraphQL\WooCommerce\Type\WPEnum;

class Cart_Notice_Type {
    public static function register_enum() {
        register_graphql_enum_type(
            'CartNoticeTypeEnum',
            [
                'description' => __( 'WooCommerce notice types', 'wp-graphql-woocommerce' ),
                'values'      => [
                    'ERROR'   => [ 'value' => 'error' ],
                    'SUCCESS' => [ 'value' => 'success' ],
                    'NOTICE'  => [ 'value' => 'notice' ],
                ],
            ]
        );
    }
}
```

### 5. Add Notices Field to Output
**File**: `/includes/mutation/class-checkout.php`
**Location**: `get_output_fields()` method (around line 96)

Add notices field to checkout mutation output:
```php
'notices' => [
    'type'    => [ 'list_of' => 'CartNotice' ],
    'resolve' => static function ( $payload ) {
        return $payload['notices'] ?? [];
    },
],
```

### 6. Update Catch Block Logic
**File**: `/includes/mutation/class-checkout.php`
**Location**: Line 169-176 in `mutate_and_get_payload()` method

Modify the catch block to capture and include WC notices:

```php
} catch ( \Throwable $e ) {
    // Delete order if it was created.
    if ( is_object( $order ) ) {
        Order_Mutation::purge( $order );
    }

    // Capture any WC notices that were added during checkout process
    $notices = wc_get_notices();
    $error_message = $e->getMessage();

    // If there are error notices, include them in the error message
    if ( ! empty( $notices['error'] ) ) {
        $notice_messages = array_column( $notices['error'], 'notice' );
        $error_message = implode( ' ', $notice_messages );
    }

    // Clear notices to prevent them from persisting to next request
    wc_clear_notices();

    // Throw error with enhanced message
    throw new UserError( $error_message );
}
```

### 3. Alternative: Add Notice Formatting Helper (Optional)
**File**: `/includes/mutation/class-checkout.php`

Add a helper method to format notices more elegantly:
```php
/**
 * Format WC notices for error reporting
 *
 * @param array $notices WC notices array
 * @return string Formatted error message
 */
private static function format_notices_for_error( $notices ) {
    $error_messages = [];

    // Prioritize error notices
    if ( ! empty( $notices['error'] ) ) {
        foreach ( $notices['error'] as $notice ) {
            $error_messages[] = $notice['notice'] ?? $notice;
        }
    }

    // Include other notice types if no errors
    if ( empty( $error_messages ) ) {
        foreach ( [ 'notice', 'success' ] as $type ) {
            if ( ! empty( $notices[ $type ] ) ) {
                foreach ( $notices[ $type ] as $notice ) {
                    $error_messages[] = $notice['notice'] ?? $notice;
                }
            }
        }
    }

    return implode( ' ', $error_messages );
}
```

### 8. Enhanced Catch Block with Helper
```php
} catch ( \Throwable $e ) {
    // Delete order if it was created.
    if ( is_object( $order ) ) {
        Order_Mutation::purge( $order );
    }

    // Capture any WC notices that were added during checkout process
    $notices = wc_get_notices();
    $error_message = $e->getMessage();

    // If there are notices, use them instead of the original error
    if ( ! empty( $notices ) ) {
        $formatted_notices = self::format_notices_for_error( $notices );
        if ( ! empty( $formatted_notices ) ) {
            $error_message = $formatted_notices;
        }
    }

    // Clear notices to prevent them from persisting to next request
    wc_clear_notices();

    // Throw error with enhanced message
    throw new UserError( $error_message );
}
```

### 9. Update Success Case to Include Notices
**File**: `/includes/mutation/class-checkout.php`
**Location**: Around line 168 in `mutate_and_get_payload()` method

Modify the success return to include non-error notices:
```php
// Capture any non-error notices for successful checkouts
$notices = wc_get_notices();
$formatted_notices = self::format_notices_for_response( $notices );

// Clear notices to prevent persistence
wc_clear_notices();

do_action( 'graphql_woocommerce_after_checkout', $order, $input, $context, $info );

return array_merge( [ 'id' => $order_id ], $results, [ 'notices' => $formatted_notices ] );
```

### 10. Add Notice Formatting Helper for Response
**File**: `/includes/mutation/class-checkout.php`

Add helper method to format notices for GraphQL response:
```php
/**
 * Format WC notices for GraphQL response
 *
 * @param array $notices WC notices array
 * @return array Formatted notices for GraphQL
 */
private static function format_notices_for_response( $notices ) {
    $formatted_notices = [];

    // Include non-error notices (success, notice)
    foreach ( [ 'success', 'notice' ] as $type ) {
        if ( ! empty( $notices[ $type ] ) ) {
            foreach ( $notices[ $type ] as $notice ) {
                $formatted_notices[] = [
                    'type'    => strtoupper( $type ),
                    'message' => $notice['notice'] ?? $notice,
                ];
            }
        }
    }

    return $formatted_notices;
}
```

### 11. Register New Types
**File**: `/includes/class-type-registry.php`
**Location**: Around line where other types are registered

Add the new types to the type registry:
```php
Cart_Notice::register_type();
Cart_Notice_Type::register_enum();
```

### 5. Update Tests
**File**: `/tests/wpunit/CheckoutNoticesTest.php`

Update the test to verify the fix works correctly:
```php
public function testCheckoutMutationReturnsNoticesInError() {
    // ... existing setup code ...

    // Hook into checkout process to simulate payment gateway failure
    add_action( 'woocommerce_checkout_process', function() {
        wc_add_notice( 'Payment failed: Test card declined', 'error' );
    }, 10 );

    $response = $this->graphql( compact( 'query', 'variables' ) );

    // Should still fail, but with the WC notice message
    $this->assertQueryError( $response );
    $this->assertStringContainsString( 'Payment failed: Test card declined', $response['errors'][0]['message'] );

    // Verify notices are cleared and don't persist
    $remaining_notices = wc_get_notices();
    $this->assertEmpty( $remaining_notices, 'Notices should be cleared after checkout failure' );
}
```

## Implementation Steps

1. **Create Notice GraphQL types** (CartNotice object and CartNoticeTypeEnum)
2. **Register new types** in TypeRegistry
3. **Add notices field** to checkout mutation output fields
4. **Add notice formatting helper methods** to checkout mutation class
5. **Update success case** to capture and return non-error notices
6. **Update catch block** to capture and include error notices in UserError message
7. **Ensure notices are cleared** in both success and error cases
8. **Update tests** to verify both error and success notice handling
9. **Test with various payment gateways** (especially Stripe test scenarios)
10. **Verify notices don't persist** across requests

## Expected Behavior After Fix

### On Successful Checkout
```json
{
  "data": {
    "checkout": {
      "order": { ... },
      "notices": [
        {
          "type": "SUCCESS",
          "message": "Order received successfully"
        }
      ],
      "result": "success",
      "redirect": "..."
    }
  }
}
```

### On Successful Checkout (No Notices)
```json
{
  "data": {
    "checkout": {
      "order": { ... },
      "notices": [],
      "result": "success",
      "redirect": "..."
    }
  }
}
```

### On Failed Checkout
```json
{
  "errors": [
    {
      "message": "Payment failed: Test card declined",
      "locations": [{"line": 3, "column": 5}],
      "path": ["checkout"]
    }
  ],
  "data": {
    "checkout": null
  }
}
```

### Benefits
1. **Comprehensive Notice Handling**: Both error and non-error notices are captured
2. **Consistent API**: Error notices in GraphQL errors, success notices in response data
3. **Better UX**: Frontend can display specific messages to users
4. **No Notice Pollution**: Notices are properly cleared after each request
5. **Debugging**: Easier to troubleshoot payment gateway issues

## Considerations

### Backward Compatibility
- Adding new optional fields doesn't break existing implementations
- Existing error handling via GraphQL errors remains unchanged
- New notices field provides additional information

### Performance
- Minimal performance impact (just reading/formatting existing notices)
- No additional database queries required

### Testing
- Test with various payment gateways
- Verify notice clearing in all scenarios
- Test both guest and logged-in user flows

## Success Criteria

1. ✅ Checkout mutation returns notices on successful checkouts
2. ✅ Error notices are included in GraphQL error messages
3. ✅ Notices are properly cleared after each checkout attempt
4. ✅ No notice pollution across requests
5. ✅ Backward compatibility maintained
6. ✅ Tests pass for all scenarios
7. ✅ Works with Stripe and other payment gateways

### Benefits
1. **Consistent API**: Checkout mutation behavior aligns with other mutations
2. **Better UX**: Frontend can display specific error messages to users
3. **No Notice Pollution**: Notices are properly cleared after each request
4. **Debugging**: Easier to troubleshoot payment gateway issues

## Considerations

### Backward Compatibility
- Adding new optional fields doesn't break existing implementations
- Existing error handling via GraphQL errors remains unchanged
- New notices field provides additional information

### Performance
- Minimal performance impact (just reading/formatting existing notices)
- No additional database queries required

### Testing
- Test with various payment gateways
- Verify notice clearing in all scenarios
- Test both guest and logged-in user flows

## Risks & Mitigation

### Risk: Breaking Changes
- **Mitigation**: New field is optional, doesn't change existing behavior

### Risk: Notice Format Changes
- **Mitigation**: Use WC core notice structure, handle edge cases

### Risk: Memory/Performance Issues
- **Mitigation**: Format notices efficiently, clear them promptly

## Success Criteria

1. ✅ Checkout mutation returns notices on both success and failure
2. ✅ Notices are properly cleared after each checkout attempt
3. ✅ No notice pollution across requests
4. ✅ Backward compatibility maintained
5. ✅ Tests pass for all scenarios
6. ✅ Works with Stripe and other payment gateways