<script lang="ts">
    import type { Snippet } from 'svelte';
    import AppLayout from '@/layouts/app/MainAppLayout.svelte';

    let {
        children,
    }: {
        children?: Snippet;
    } = $props();
</script>

<AppLayout>
    {@render children?.()}
</AppLayout>
