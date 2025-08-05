<?php

namespace App\Services;

use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Config;

class ThemeService
{
    /**
     * Get the current active theme
     */
    public function getCurrentTheme(): string
    {
        if (Config::get('theme.settings.allow_user_theme_selection')) {
            return Cookie::get(
                Config::get('theme.settings.theme_cookie_name'),
                Config::get('theme.default')
            );
        }

        return Config::get('theme.default');
    }

    /**
     * Set the current theme (if user selection is allowed)
     */
    public function setTheme(string $theme): bool
    {
        if (!Config::get('theme.settings.allow_user_theme_selection')) {
            return false;
        }

        if (!$this->isValidTheme($theme)) {
            return false;
        }

        if (Config::get('theme.settings.persist_theme_choice')) {
            Cookie::queue(
                Config::get('theme.settings.theme_cookie_name'),
                $theme,
                Config::get('theme.settings.theme_cookie_duration')
            );
        }

        return true;
    }

    /**
     * Check if a theme is valid
     */
    public function isValidTheme(string $theme): bool
    {
        return array_key_exists($theme, Config::get('theme.themes', []));
    }

    /**
     * Get all available themes
     */
    public function getAvailableThemes(): array
    {
        return Config::get('theme.themes', []);
    }

    /**
     * Get theme configuration
     */
    public function getThemeConfig(string $theme): ?array
    {
        return Config::get("theme.themes.{$theme}");
    }

    /**
     * Generate CSS custom properties for a theme
     */
    public function getThemeCssProperties(string $theme): string
    {
        $config = $this->getThemeConfig($theme);
        
        if (!$config) {
            return '';
        }

        $css = ":root {\n";

        // Primary colors
        if (isset($config['primary'])) {
            foreach ($config['primary'] as $shade => $rgb) {
                $css .= "  --color-primary-{$shade}: rgb({$rgb});\n";
            }
        }

        // Secondary colors
        if (isset($config['secondary'])) {
            foreach ($config['secondary'] as $shade => $rgb) {
                $css .= "  --color-secondary-{$shade}: rgb({$rgb});\n";
            }
        }

        // Accent colors
        if (isset($config['accent'])) {
            foreach ($config['accent'] as $shade => $rgb) {
                $css .= "  --color-accent-{$shade}: rgb({$rgb});\n";
            }
        }

        // Dynamic background and text colors based on primary colors
        if (isset($config['primary'])) {
            $css .= "  --color-bg-primary: rgb({$config['primary']['50']});\n";
            $css .= "  --color-bg-secondary: rgb(255 255 255);\n";
            $css .= "  --color-bg-tertiary: rgb({$config['primary']['100']});\n";
            $css .= "  --color-text-primary: rgb({$config['primary']['900']});\n";
            $css .= "  --color-text-secondary: rgb({$config['primary']['700']});\n";
            $css .= "  --color-text-tertiary: rgb({$config['primary']['400']});\n";
        }

        $css .= "}\n";

        // Dark mode overrides
        $css .= ".dark {\n";
        if (isset($config['primary'])) {
            $css .= "  --color-bg-primary: rgb({$config['primary']['900']});\n";
            $css .= "  --color-bg-secondary: rgb({$config['primary']['800']});\n";
            $css .= "  --color-bg-tertiary: rgb({$config['primary']['700']});\n";
            $css .= "  --color-text-primary: rgb({$config['primary']['50']});\n";
            $css .= "  --color-text-secondary: rgb({$config['primary']['200']});\n";
            $css .= "  --color-text-tertiary: rgb({$config['primary']['400']});\n";
        }
        $css .= "}\n";

        return $css;
    }

    /**
     * Generate inline CSS for current theme
     */
    public function getCurrentThemeCss(): string
    {
        return $this->getThemeCssProperties($this->getCurrentTheme());
    }
}