# 07 — Theme System & Primary Color Customization

**Laravel-adminX** features a dynamic theme engine built on Tailwind CSS v4 custom properties.

---

## 🎨 Changing Primary Theme Color

Administrators can switch the primary brand color from `/adminx/settings/system`.

### Preset Color Palettes:
- 🟢 **Emerald** (`#059669`) — *Default*
- 🔵 **Indigo** (`#4f46e5`)
- 🔴 **Rose** (`#e11d48`)
- 🟠 **Amber** (`#d97706`)
- 🟣 **Violet** (`#7c3aed`)
- 🌊 **Teal** (`#0d9488`)

---

## 💡 How Dynamic Color Works

1. `resources/css/app.css` binds brand utility classes (`bg-brand`, `text-brand`, `border-brand`) to CSS custom properties:
   ```css
   --color-brand: var(--theme-brand, #059669);
   --color-brand-hover: var(--theme-brand-hover, #047857);
   ```
2. `resources/views/partials/head.blade.php` reads `theme_color` setting from `system_settings` table and injects the corresponding CSS variables into `<style id="system-theme-style">`.
3. The UI updates instantly across all buttons, badges, navigation highlights, and focus rings!
