# ICM Personnel Display Fix - TODO

## Steps:
- [x] Edit `resources/views/inventory/tabs/icm.blade.php`: Replace raw `{{ $item->requesting_personnel ?? 'N/A' }}` with preg_replace stripping `\s*\(\d+\)$`.
- [x] Verify the file content after edit.
- [ ] Test ICM table display (reload page).
- [ ] [DONE] Mark complete and cleanup TODO.md.

**Current status:** Starting implementation.

