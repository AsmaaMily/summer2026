---
paths:
  - 'resources/js/components/ui/**'
---

# Ui

## UI kit has duplicate PascalCase/lowercase files; index.ts imports lowercase
In resources/js/components/ui/* there are two copies of each component, e.g. select-content.svelte and SelectContent.svelte, dialog-content.svelte and DialogContent.svelte. The folder index.ts imports the lowercase names (import Content from "./select-content.svelte"). Editing the PascalCase copy has NO effect on the running UI. Verify with: grep the built asset for a unique class (the used file's classes end up in the bundle). When changing Select/Dialog styling such as z-index, edit the lowercase file.
