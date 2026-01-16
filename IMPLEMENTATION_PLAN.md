# Implementation Plan - Register Company Subscription Routes

## Goal
Enable Company Admins to access the Subscription Purchase page and submit payment proofs.

## Proposed Changes
### Routes
#### [MODIFY] [routes/company.php](file:///d:/Cashier/routes/company.php)
- Add GET route `/company/{company}/subscriptions/purchase` -> `Company\SubscriptionController@purchase`
- Add POST route `/company/{company}/subscriptions/purchase` -> `Company\SubscriptionController@processPurchase`

## Verification Plan
### Manual Verification
1.  **Code Review**: Verify routes are added correctly inside the `subscriptions` group or parallel to it.
2.  **Route Check**: I will use `php artisan route:list` to verify the new routes are registered and pointing to the correct controller methods.
