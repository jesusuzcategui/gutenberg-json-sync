# Gutenberg JSON Sync

A small WordPress plugin that writes Site Editor templates and template parts to the active block theme as `.html` files. When a file actually changes, it asks Nodus Jet to commit it through the `commit_from_plugin` filter.

## Requirements

- WordPress 6.6 or newer with a block theme.
- Nodus Jet installed, active, and connected to the active theme repository.
- The site user saving the template must have `edit_theme_options`.

## Install

Upload the `gutenberg-json-sync` folder as a plugin in WordPress and activate it. You do not need a modified Gutenberg build.

Templates are written to `wp-content/themes/<active-theme>/templates/<slug>.html`; template parts are written to `wp-content/themes/<active-theme>/parts/<slug>.html`. The plugin does nothing when Nodus Jet's `commit_from_plugin` filter is unavailable, and it skips unchanged file content to avoid empty commits.

## Scope

This first version syncs Site Editor templates and template parts only. Page and post content remains in the WordPress database; assigning a template to a page does not copy the page content into the theme.
