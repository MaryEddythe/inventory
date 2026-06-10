- [ ] Add `co_mooe` enum column to `other_ppes` table (values: `RPCSP`, `PPE`).
- [ ] Populate/derive `co_mooe` on create/update for Other PPE based on `unit_value` cutoff: <= 49999 => `RPCSP`, >= 50000 => `PPE`.
- [ ] Update `OtherPpe` model `$fillable` to include `co_mooe` and cast if needed.
- [ ] Update Other PPE create modal to show/auto-set `co_mooe` (or compute server-side only).
- [ ] Update Other PPE edit modal and table to display `co_mooe` (optional if not requested).
- [ ] Update destroy/store/update methods in `InventoryItemController` for Other PPE to compute `co_mooe`.
- [ ] Run migrations and sanity-check.

