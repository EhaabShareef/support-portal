# UI/UX Improvement Plan for Support Portal

## 1. Current UI/UX Patterns and Pain Points
- **Inconsistent component usage** – many Blade views include verbose Tailwind utility strings instead of the design system classes defined in `resources/css/app.css` (e.g., buttons in `resources/views/livewire/manage-organizations.blade.php`).
- **Duplicated flash and form markup** – success/error alerts and forms are hand-written in every view with slight variations, leading to inconsistent spacing and colours.
- **Sparse loading feedback** – Livewire pages change state without loaders or skeletons, leaving the user unsure during long requests.
- **Navigation & layout gaps** – navigation is desktop‑first and relies on custom classes; mobile transitions and keyboard interactions are minimal, and sidebars / headers are repeated across pages.
- **Theme handling is basic** – the theme toggle only switches classes locally; there is no persistence per user or smooth transition between themes.
- **Route‑level behaviour** – routes are grouped but lack intermediate progress indications and consistent naming for nested resources.

## 2. Proposed UX Enhancements
1. **Introduce unified component library** – create Blade components for buttons, inputs, selects, textareas, cards, modals and flash messages so pages rely on a shared API.
2. **Loading states & skeletons** – add `wire:loading` indicators and skeleton components for lists/tables. Use Alpine transitions for smoother entry/exit animations.
3. **Improved navigation** – implement a reusable responsive navigation component with keyboard access, focus traps for dropdowns, and better mobile slide‑over behaviour.
4. **Accessible modals and dialogs** – centralise modal markup with ARIA attributes, ESC/overlay closing and focus management.
5. **Responsive table patterns** – switch to stackable cards or collapsible rows on small screens for ticket and hardware lists.
6. **Theme system polish** – animate theme changes, remember preference server‑side, and expose a theme selector for multiple palettes.
7. **Progress and feedback** – add top‑bar progress (e.g., NProgress) for route changes and file uploads; show confirmation toasts rather than page‑level flash boxes.
8. **Keyboard & screen‑reader support** – ensure all interactive elements have tabindex, aria‑labels and logical order; allow keyboard navigation for menus and modals.
9. **Mobile first tweaks** – audit every page for small‑screen spacing, add off‑canvas menu for filters, and ensure buttons have touch‑friendly sizes.

## 3. Code‑Level Design Improvements
- **Blade components**: create `resources/views/components/button.blade.php`, `input.blade.php`, `select.blade.php`, `modal.blade.php`, `flash.blade.php`, `card.blade.php` leveraging classes from `app.css`.
- **Layout refactor**: consolidate headers into a `page-header` component; move repeated navigation/sidebar markup into one place.
- **JavaScript utilities**: extend `resources/js/app.js` with a central event bus (Alpine store) for theme toggling, global loading bar and modal control.
- **CSS**: expand `resources/css/app.css` with utility classes for skeletons, transitions and dark‑mode friendly shadows.
- **Routes**: ensure routes in `routes/web.php` follow RESTful patterns and group nested resources consistently.

## 4. Step‑by‑Step Update Guide
1. **Design tokens & utilities**
   - File: `resources/css/app.css`
   - Add skeleton animations, transition helpers and a `.btn-link` variant. Consider CSS variables for spacing and shadow depth.
2. **JavaScript infrastructure**
   - File: `resources/js/app.js`
   - Create an Alpine store for theme state, expose `toggleTheme`, add NProgress integration and emit events for route/page loads.
3. **Reusable Blade components**
   - Files to add under `resources/views/components/`
     - `button.blade.php` (`variant` prop)
     - `input.blade.php`, `select.blade.php`, `textarea.blade.php`
     - `modal.blade.php` (ARIA roles, transitions, close actions)
     - `flash.blade.php` (success/error/info variants)
     - `card.blade.php` (header/body slots)
4. **Navigation & layout**
   - File: `resources/views/components/navigation.blade.php`
   - Swap hard‑coded anchors for `x-nav-link` components, add skip‑to‑content link, ensure dropdown traps focus and closes on ESC.
   - Consider collapsing desktop nav to sidebar on smaller breakpoints.
5. **Page headers & structure**
   - Files: all main Livewire views in `resources/views/livewire/`
   - Replace ad‑hoc headers with `<x-page-header>` component; ensure consistent spacing and actions slot.
6. **Forms**
   - Files: `resources/views/livewire/manage-organizations.blade.php`, `manage-hardware.blade.php`, `manage-tickets.blade.php`, `admin/manage-users.blade.php`, `admin/manage-settings.blade.php`, etc.
   - Replace inline inputs/selects with new components. Use `wire:model.defer` consistently, add `wire:loading.attr="disabled"` on submit buttons and show inline validation via `<x-input-error>`.
7. **Lists & tables**
   - Files: ticket/user/hardware lists.
   - Extract row/card views into partials, add skeleton placeholders, and convert tables to responsive stacks at `sm` breakpoint.
8. **Flash messages & toasts**
   - Replace repeated `@if (session()->has('message'))` blocks with `<x-flash />` component. Optionally integrate a JS toast library for non-blocking feedback.
9. **Theme selection**
   - File: `resources/views/components/theme-selector.blade.php` & `theme-toggle.blade.php`
   - Add smooth fade between themes, allow persistence via user profile settings, and expose more theme choices if configured.
10. **Route polish**
    - File: `routes/web.php`
    - Review route names for consistency (`tickets.manage` vs `tickets.index`), use resourceful controllers where possible, and add breadcrumbs via route metadata.

### Execution Phases
- **Phase 1**: Establish component library and CSS utilities.
- **Phase 2**: Refactor navigation/layout and migrate forms to components.
- **Phase 3**: Enhance interactions (loading, toasts, responsive tables).
- **Phase 4**: Theme persistence and accessibility pass (ARIA/keyboard).

This plan focuses on quality-of-life, visual coherence and maintainable UI patterns without addressing existing bugs.
