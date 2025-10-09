# TODO List for Inventory System Fixes

## Completed Tasks
- [x] Fix badge color for status NEW to green in inventory tab (changed to condition and bg-success)
- [x] Ensure inventory export only shows inventory items (removed IPM columns from export-pdf.blade.php)
- [x] Create separate export-ipm-pdf.blade.php for IPM tab with different export button/param
- [x] Update controller to use different views and filenames based on tab
- [x] Update JS in IPM tab to add tab=ipm param to export URL
- [x] Update all references from status to condition in controller and views
- [x] Update dashboard method to use condition instead of status

## Pending Tasks
- [ ] Test the exports to ensure they work correctly
- [ ] Verify the badge displays correctly
- [ ] Check if any other inconsistencies remain

## Notes
- Inventory export now uses export-pdf.blade.php with only inventory columns
- IPM export uses export-ipm-pdf.blade.php with IPM columns
- Filenames are prefixed with 'inventory_' or 'ipm_' accordingly
- All status references changed to condition for consistency
