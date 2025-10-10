# Soft Delete Implementation for Inventory Items

## Completed Tasks
- [x] Update `destroy` method in `InventoryItemController.php` to set 'x' => 'INACTIVE' instead of hard delete
- [x] Remove `deactivate` method from `InventoryItemController.php`
- [x] Update `scopeActive` in `InventoryItem.php` to exclude 'INACTIVE' items
- [x] Update delete button in `resources/views/inventory/table-data.blade.php` to use `route('inventory.destroy')` with DELETE method
- [x] Update delete button in `resources/views/inventory/table-data-ipm.blade.php` to use `route('inventory.destroy')` with DELETE method

## Followup Steps
- [ ] Test deleting an item: verify it's removed from UI and 'x' is set to 'INACTIVE' in DB
- [ ] Ensure active items still display correctly in all views (index, ipm, dashboard, export)
