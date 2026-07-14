# Thinning fat controllers

Domain services extracted so HTTP controllers keep validation / redirects / views only.

| Domain service | From controller | Owns |
|----------------|-----------------|------|
| `Medicine\MedicineInventoryService` | `MedicineInventoryController` | stock, locks, dispense, restock, decorate |
| `Person\PersonProfileService` | `PersonDirectoryController` | multi-table profile update |
| `Person\PersonSearchService` | directory lists / typeaheads | search + pagination |
| `EventFinance\SeasonEventBookingService` | `SeasonEventBookingFinanceController` | create booking, eligibility |
| `Enrolment\LiveFormLegacyService` | `LiveFormEnrolmentController` | waiting-list move + answer upsert |

Still large (next pass): booking `index`/installments/refunds, liveform step2/submit prep, medicine location CRUD UI.
