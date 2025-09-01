# Conversation Thread Settings

## Environment Variables

### HIDE_SYSTEM_MESSAGES
Controls whether system messages are hidden in the conversation thread.

**Default:** `false`

**Values:**
- `true` - Hide system messages (except close/reopen messages)
- `false` - Show all system messages

**Example:**
```env
HIDE_SYSTEM_MESSAGES=true
```

## What Gets Hidden

When `HIDE_SYSTEM_MESSAGES=true`, the following system messages are hidden:
- Ticket status changes
- Merge operations
- Split operations (when implemented)
- Other system-generated messages

## What Stays Visible

The following messages are always shown regardless of the setting:
- Close messages
- Reopen messages
- User messages
- Public notes (with correct sender name)

## UI/UX Improvements

1. **Hover Effects**: Conversation items now have subtle hover effects that lighten/darken based on the theme
2. **Simplified Styling**: Removed dashed borders for cleaner appearance
3. **Correct Sender Names**: Public notes now show the actual user who created them instead of "System"
4. **Gray Close Icon**: Close messages now use a neutral gray color instead of red
5. **Smooth Transitions**: Added transition effects for better user experience
