## Task: Add Division Chief Role

- [x] Add `division-chief` role entry to `config/inventory.php` with sidebar access: Dashboard, Inventory (all sub-categories), Employees, Calendar, Leave Credits (CTO, Leave Credits)
- [x] Run `SidebarAccessSeeder` to create the Division Chief role and assign proper sidebar items
- [x] Add `hr` role entry to `config/inventory.php` with sidebar access: Employees, Calendar, Leave Credits (CTO + Leave Credits)
- [x] Run `SidebarAccessSeeder` to create the HR role in the database

## Task: Create Laralournie Artajo User

- [x] Created user: **Laralournie Artajo**
  - emp_no: `4`
  - email: `artajo.laralournie@mgb.gov.ph`
  - password: (hashed) `artajo123`
  - Linked to existing employee record with emp_no 4

## Task: Add Regional Director Role + Create Cecilia Ochavo-Saycon User

- [x] Added `rd` role to `config/inventory.php` (name: `Regional Director`, slug: `rd`)
- [x] Ran `SidebarAccessSeeder` to create the Regional Director role in the database
- [x] Created user: **Cecilia Ochavo-Saycon**
  - username: `clos`
  - emp_no: `1`
  - email: `ochavosaycon.cecilia@mgb.gov.ph`
  - password: (hashed) `ochavosaycon`
  - Linked to existing employee record with emp_no 1

---

## Previous Tasks (from branch)

- [ ] Add `co_mooe` enum column to `other_ppes` table (values: `RPCSP`, `PPE`).
- [ ] Populate/derive `co_mooe` on create/update for Other PPE based on `unit_value` cutoff: <= 49999 => `RPCSP`, >= 50000 => `PPE`.
- [ ] Update `OtherPpe` model `$fillable` to include `co_mooe` and cast if needed.
- [ ] Update Other PPE create modal to show/auto-set `co_mooe` (or compute server-side only).
- [ ] Update Other PPE edit modal and table to display `co_mooe` (optional if not requested).
- [ ] Update destroy/store/update methods in `InventoryItemController` for Other PPE to compute `co_mooe`.
- [ ] Run migrations and sanity-check.

