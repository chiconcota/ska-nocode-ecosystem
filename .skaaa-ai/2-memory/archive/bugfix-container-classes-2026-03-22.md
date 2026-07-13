# Bug Fix: Tailwind Classes Not Applying - Container Block
**Date:** 2026-03-22 (Late Evening)  
**Status:** ✅ Fixed
**Severity:** High - Core functionality affected

## Summary
Fixed a critical bug in the Container block where Tailwind classes added by users were not being applied due to an erroneous `setAttributes` call that was undoing the class split logic.

## The Problem
When users added Tailwind classes via TailwindPanel in a Container block (e.g., `bg-red-500`, `absolute inset-0`), the classes would not appear in the rendered output, neither in Editor nor on frontend.

## Root Cause
In `src/skaaa-container/index.js` line 26, after TailwindPanel had properly split classes into:
- `tailwindClasses: styling` (design classes like bg-, text-, etc.)
- `className: layout` (structural classes like absolute, flex, grid, etc.)

The `useEffect` hook was executing:
```javascript
setAttributes({ className: tailwindClasses });
```

This was **overwriting the properly split `className`** with **all tailwindClasses**, breaking the split logic.

## How It Should Work
1. User adds classes in TailwindPanel: e.g., `absolute inset-0 bg-red-500`
2. TailwindPanel splits them:
   - `tailwindClasses`: `bg-red-500` (design/styling)
   - `className`: `absolute inset-0` (layout/structure)
3. Both attributes are saved to block
4. On render:
   - Frontend: render.php reads `tailwindClasses ?? className ?? ''`
   - JIT Compiler: scans both and generates CSS

## The Buggy Behavior (Before Fix)
1. User adds classes: `absolute inset-0 bg-red-500`
2. TailwindPanel splits correctly ✓
3. `useEffect` overwrites: `className: "bg-red-500"` ✗
4. Block saves with mixed/corrupted attributes
5. Frontend gets wrong classes
6. No CSS generated or applied

## The Fix
**File:** `src/skaaa-container/index.js`

**Removed:** The problematic line 26
```javascript
setAttributes({ className: tailwindClasses });  // ← DELETE
```

**Kept:** Only auto-migration for backward compatibility
```javascript
if (className && !tailwindClasses) {
    setAttributes({ tailwindClasses: className, className: '' });
}
```

**Added:** Comment explaining why we don't override className
```javascript
// NOTE: Do NOT set className here - TailwindPanel callback handles proper split
```

## Why This Works
- TailwindPanel's `setClassName` callback (line 95) properly splits classes
- We now let it do its job without interference
- Auto-migration (old blocks) still works for backward compatibility
- Other blocks (Text, Button, Image, Icon, Video, List) already use this correct pattern

## Testing Checklist
- [ ] Add class `bg-red-500` to Container → should show red background ✓
- [ ] Add class `absolute inset-0` to block inside Container → should cover entire parent ✓
- [ ] Add multiple classes: `flex items-center justify-center gap-4 p-8` → should all apply ✓
- [ ] Add responsive classes: `md:bg-blue-500 lg:flex-row` → should work on breakpoints ✓
- [ ] Add old block (before fix) to page → auto-migration should work ✓
- [ ] Check Database → block should have both `tailwindClasses` and `className` attributes ✓

## Files Modified
- `src/skaaa-container/index.js` (lines 19-27)

## Blocks Verified (Not Affected)
- ✅ Skaaa Text - Uses correct pattern
- ✅ Skaaa Button - Uses correct pattern
- ✅ Skaaa Image - Uses correct pattern
- ✅ Skaaa Icon - Uses correct pattern
- ✅ Skaaa Video - Uses correct pattern
- ✅ Skaaa List - Uses correct pattern

## Impact
- **Severity:** HIGH - Affects core functionality
- **Scope:** Container block only
- **Breaking:** NO - Only fixes broken behavior
- **Build Required:** NO - JavaScript file change
- **Performance:** None - same logic, just correct order

## Technical Details
The Container block differs from other blocks because it uses:
```javascript
setClassName={(styling, layout) => setAttributes({ tailwindClasses: styling, className: layout })}
```

This means it receives the split classes from TailwindPanel and should NOT re-set them. The erroneous line 26 was conflicting with this callback logic.

## Related Decision Log
See `decision-log.md` entry for 2026-03-22 (Late Evening) for full technical analysis.
