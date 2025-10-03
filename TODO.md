# TODO: Remove Uniqueness Constraints from Property Number and Serial Number

## Completed Tasks
- [x] Created new migration to drop unique constraints on `serial_number` and `property_number` columns
- [x] Edited `InventoryItemController.php` to remove unique validation rules in `store` method
- [x] Edited `InventoryItemController.php` to remove unique validation rules in `update` method
- [x] Ran the migration to apply database changes (updated to use dropIndex with specific index names)

## Summary
The property number and serial number fields are now no longer unique, allowing duplicate values as required for routine inventory checks.
