# Support Portal Styling Guide

This document explains the consistent styling system implemented in the Support Portal application.

## Overview

The application uses a consistent CSS framework with:
- **CSS Custom Properties** for dynamic theming
- **Reusable Component Classes** for consistency
- **Tailwind CSS** as the base framework
- **Theme Configuration System** for easy customization

## CSS Custom Properties

### Color Variables

The system uses CSS custom properties that can be dynamically changed:

```css
/* Primary colors (main brand colors) */
--color-primary-50 through --color-primary-900

/* Secondary colors (accents and secondary actions) */
--color-secondary-50 through --color-secondary-900

/* Accent colors (interactive elements) */
--color-accent-50 through --color-accent-900

/* Semantic colors */
--color-success-50, --color-success-500, etc.
--color-warning-50, --color-warning-500, etc.
--color-danger-50, --color-danger-500, etc.

/* Dynamic colors that change based on theme */
--color-bg-primary, --color-bg-secondary, --color-bg-tertiary
--color-text-primary, --color-text-secondary, --color-text-tertiary
```

## Reusable Component Classes

### Buttons

```html
<!-- Primary button (main actions) -->
<button class="btn-primary">Save</button>

<!-- Secondary button (secondary actions) -->
<button class="btn-secondary">Cancel</button> 

<!-- Danger button (destructive actions) -->
<button class="btn-danger">Delete</button>

<!-- Success button (positive actions) -->
<button class="btn-success">Approve</button>
```

### Form Elements

```html
<!-- Text input -->
<input type="text" class="form-input" />

<!-- Select dropdown -->
<select class="form-select">...</select>

<!-- Textarea -->
<textarea class="form-textarea"></textarea>

<!-- Label -->
<label class="form-label">Field Name</label>

<!-- Error message -->
<p class="form-error">Error message</p>
```

### Typography

```html
<!-- Headings -->
<h1 class="text-heading-1">Main Title</h1>
<h2 class="text-heading-2">Section Title</h2>
<h3 class="text-heading-3">Subsection</h3>
<h4 class="text-heading-4">Card Title</h4>

<!-- Body text -->
<p class="text-body">Primary text content</p>
<p class="text-body-secondary">Secondary text</p>
<p class="text-body-tertiary">Tertiary/helper text</p>
```

### Layout Components

```html
<!-- Page header with title and action -->
<div class="page-header">
    <h1 class="page-title">Page Title</h1>
    <button class="btn-primary">Action</button>
</div>

<!-- Content sections -->
<div class="content-section">
    <h2>Section Content</h2>
    <!-- Content here -->
</div>

<!-- Cards -->
<div class="card">
    <div class="card-header">
        <h3>Card Title</h3>
        <span>Extra info</span>
    </div>
    <!-- Card content -->
</div>
```

### Status & Priority Badges

```html
<!-- Status badge (uses model methods for colors) -->
<span class="status-badge {{ $ticket->getStatusCssClass() }}">
    {{ $ticket->status }}
</span>

<!-- Priority badge (uses model methods for colors) -->
<span class="priority-badge {{ $meta['class'] }}">
    <x-dynamic-component :component="$meta['icon']" />
    {{ $meta['text'] }}
</span>
```

### Utility Classes

```html
<!-- Responsive grid -->
<div class="grid-responsive">
    <!-- 1 col mobile, 2 col tablet, 3 col desktop -->
</div>

<!-- Flex utilities -->
<div class="flex-between"><!-- space-between --></div>
<div class="flex-center"><!-- center items --></div>

<!-- Interactive effects -->
<div class="hover-lift"><!-- hover lift effect --></div>
<button class="focus-ring"><!-- consistent focus ring --></button>

<!-- Alerts -->
<div class="alert-success">Success message</div>
<div class="alert-warning">Warning message</div>
<div class="alert-danger">Error message</div>
```

## Theme System

### Configuration

Themes are configured in `config/theme.php`. Available themes:

- **neutral** (default) - Clean gray theme
- **blue** - Corporate blue theme  
- **green** - Nature green theme
- **purple** - Royal purple theme

### Using the Theme Service

```php
// Get current theme
$theme = app(\App\Services\ThemeService::class)->getCurrentTheme();

// Set theme (if user selection enabled)
app(\App\Services\ThemeService::class)->setTheme('blue');

// Get theme CSS properties
$css = app(\App\Services\ThemeService::class)->getCurrentThemeCss();

// Get available themes
$themes = app(\App\Services\ThemeService::class)->getAvailableThemes();
```

### Enabling User Theme Selection

1. Set `ALLOW_USER_THEMES=true` in your `.env` file
2. Add the theme selector component to your layout:

```html
<x-theme-selector />
```

### Creating Custom Themes

Add new themes to `config/theme.php`:

```php
'themes' => [
    'custom' => [
        'name' => 'Custom Theme',
        'description' => 'My custom theme',
        'primary' => [
            '50' => '255 255 255',  // RGB values
            '100' => '240 240 240',
            // ... more shades
        ],
        'secondary' => [
            // Secondary color shades
        ],
        'accent' => [
            // Accent color shades
        ],
    ],
]
```

## Best Practices

### DO ✅

- Use the predefined component classes (`btn-primary`, `form-input`, etc.)
- Use semantic color variables (`--color-text-primary`, `--color-accent-600`)
- Keep status and priority colors separate from theme colors
- Use the typography classes for consistent text styling
- Use layout utility classes (`page-header`, `content-section`)

### DON'T ❌

- Don't use hardcoded Tailwind colors for theme-dependent elements
- Don't mix inline styles with the component system
- Don't override status/priority colors with theme colors
- Don't create one-off classes when component classes exist
- Don't use deprecated glass-card class (use `card` instead)

## Migration from Old Styles

If updating existing views, replace:

```html
<!-- OLD -->
<div class="glass-card">
<h1 class="text-2xl font-semibold text-neutral-800 dark:text-neutral-100">
<button class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-md">
<input class="w-full px-3 py-2 rounded-md border border-white/30 bg-white/60">

<!-- NEW -->
<div class="card">
<h1 class="text-heading-2">
<button class="btn-primary">
<input class="form-input">
```

## File Structure

```
resources/
├── css/
│   └── app.css                 # Main CSS with component classes
├── views/
│   └── components/
│       └── theme-selector.blade.php  # Theme selection component
config/
└── theme.php                  # Theme configuration
app/
└── Services/
    └── ThemeService.php       # Theme management service
```

This system provides consistency, maintainability, and easy customization for future development.