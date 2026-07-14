# Domain extraction guidance

**Do not thin controllers for line-count.** Extract Domain / Support only when it improves design:

| Extract when… | Leave alone when… |
|---------------|-------------------|
| Same business rule duplicated in multiple places | One working flow, no reuse yet |
| Logic is hard to unit-test behind HTTP | Fat but coherent HTTP orchestration |
| Clear cohesion (e.g. stock math, shared search) | Moving code would only rename layers |
| Correctness risk (transactions, eligibility) | No performance or bug pressure |

Working, stable controller methods (booking `index`, installments, refunds, liveform step UI, medicine location CRUD) stay put until a real need appears.

## Already extracted (for a reason)

| Domain service | Why |
|----------------|-----|
| `Support\LikeSearch` + `Person\PersonSearchService` | Stop reinventing search per controller |
| `Medicine\MedicineInventoryService` | Stock/lock/dispense invariants in one place |
| `Person\PersonProfileService` | Multi-table profile update as one use-case |
| `EventFinance\SeasonEventBookingService` | Booking create + eligibility rules |
| `Enrolment\LiveFormLegacyService` | Waiting-list move / answer upsert |
| Existing Enrolment/Auth/OrgTree services | Capacity, submit, password reset, group tree |

Controllers keep: validation, Auth, redirects, views, JSON responses. Domain keeps: rules + persistence use-cases. Prefer constructor DI over `app()`.
