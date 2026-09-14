# Search Functionality Enhancement

## Overview
Enhanced the global search functionality in the RMinder application to provide interactive dropdown suggestions that connect to all pages and resources.

## Changes Made

### 1. Enhanced Search Backend (`actions/search.php`)
**Before**: Searched only events and announcements
**After**: Comprehensive search across all major resources

#### Security Improvements:
- ✅ **Replaced `real_escape_string()` with prepared statements** - Prevents SQL injection attacks
- ✅ All queries now use parameterized `bind_param()` for safety

#### New Search Coverage:
1. **Events** - Searches event titles and venues
2. **Announcements** - Searches announcement titles and messages
3. **Forum Threads** - Searches forum thread titles and content (excludes viewers)
4. **Donations** - Searches donor names and remarks (admin/imam/leader only)
5. **Appointments** - Searches full names and reference numbers (admin only)
6. **Quick Page Links** - Navigation shortcuts to main pages

#### Role-Based Access Control:
```php
- Admins: See all resources from all communities
- Imams/Leaders: See community-specific resources + forum
- Viewers: See only announcements and events
```

### 2. Updated Search UI (`includes/header.php`)

#### Styling Enhancements:
```css
.sd-icon.forum        { background: rgba(74,222,128,.2); }     /* Green */
.sd-icon.donation     { background: rgba(59,130,246,.2); }     /* Blue */
.sd-icon.appointment  { background: rgba(236,72,153,.2); }     /* Pink */
.sd-icon.page         { background: rgba(168,85,247,.2); }     /* Purple */
```

#### Icon Mapping:
| Type | Icon | Color |
|------|------|-------|
| Event | 📅 | Purple |
| Announcement | 📢 | Yellow |
| Forum | 💬 | Green |
| Donation | ❤️ | Blue |
| Appointment | 📝 | Pink |
| Page | 📄 | Purple |

#### JavaScript Enhancements:
```javascript
// Dynamic icon handling with fallback
var icon = icons[r.icon_name] || icons[r.type] || '🔍';

// Support for custom icon names from search results
// Direct link handling with query parameters
// e.g., forum_thread.php?id=123, events.php?id=456
```

#### Placeholder Text Update:
- Before: "Search events, announcements..."
- After: "Search events, forum, donations..."

### 3. Test Coverage

**Test File**: `test_search.ps1`

Tests include:
- ✅ Login flow with CSRF token extraction
- ✅ Search queries for all resource types
- ✅ Minimum query length validation (2 characters)
- ✅ Unauthenticated search rejection
- ✅ Role-based access control

**Test Results**:
```
Query: 'event' → 2 results (announcements, Events page)
Query: 'ann'   → 1 result  (Announcements page)
Query: 'for'   → 8 results (announcements with "for", forum matches)
Query: 'don'   → 2 results (donation announcements, Donations page)
Query: 'app'   → 1 result  (Appointments page)
Query: 'dash'  → 1 result  (Dashboard page)
```

## Usage

### For Users:
1. Click the search bar at the top of any page
2. Type at least 2 characters
3. Interactive dropdown appears showing:
   - Matching events, announcements, forum threads, donations, appointments
   - Quick navigation links to main pages (role-appropriate)
4. Click any result to navigate directly to it

### For Developers:

To add a new resource type to search:

1. **Add search query in `search.php`:**
```php
if ($role === 'admin') {
    $stmt = $conn->prepare("SELECT id, title FROM your_table
            WHERE title LIKE ?
            ORDER BY created_at DESC LIMIT 5");
    $stmt->bind_param("s", $like);
} else {
    $stmt = $conn->prepare("SELECT id, title FROM your_table
            WHERE title LIKE ? AND community_id = ?
            ORDER BY created_at DESC LIMIT 5");
    $stmt->bind_param("si", $like, $community_id);
}
$stmt->execute();
$r = $stmt->get_result();
while ($row = $r->fetch_assoc()) {
    $results[] = [
        'type'  => 'mytype',
        'id'    => $row['id'],
        'title' => $row['title'],
        'meta'  => 'Metadata here',
        'url'   => 'page.php?id=' . $row['id'],
    ];
}
$stmt->close();
```

2. **Add icon and styling in `header.php`:**
```javascript
// In icons object:
mytype: '🎯'

// In CSS:
.sd-icon.mytype { background: rgba(r,g,b,.2); }
```

## Performance Considerations

- **Query Limits**: Each resource type limited to 5 results
- **Debouncing**: 280ms delay before fetching (prevents excessive requests)
- **Lazy Rendering**: Results only rendered when needed
- **Prepared Statements**: More efficient than string concatenation

## Security Notes

- ✅ All queries use parameterized statements
- ✅ CSRF token required (inherited from session)
- ✅ Authentication check before any results returned
- ✅ Role-based filtering prevents data exposure
- ✅ HTML escaping in results via `escHtml()` function
- ✅ Minimum 2-character query prevents noise

## Future Enhancements

Potential improvements:
1. Search analytics (track popular searches)
2. Fuzzy matching (handle typos better)
3. Recent searches history
4. Saved searches/bookmarks
5. Advanced filter options (date range, category, etc.)
6. Search result pagination
7. Full-text search (MySQL fulltext index)

## Files Modified

1. `actions/search.php` - Enhanced backend search handler
2. `includes/header.php` - Updated UI and JavaScript
3. `test_search.ps1` - New test suite (testing only)

## Backward Compatibility

✅ **Fully backward compatible**
- Existing search functionality maintained
- CSS class names unchanged
- URL structure unchanged
- HTML element IDs unchanged
