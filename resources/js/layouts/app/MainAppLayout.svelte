<script lang="ts">
    import type { Snippet } from 'svelte';
    import DesktopTopNav from '@/components/DesktopTopNav.svelte';
    import MobileBottomNav from '@/components/MobileBottomNav.svelte';
    import { Toaster } from '@/components/ui/sonner';

    let {
        children,
    }: {
        children?: Snippet;
    } = $props();
</script>

<div class="flex min-h-svh flex-col bg-background">
    <DesktopTopNav />

    <main class="flex-1 pb-20 pt-0 lg:pb-0 lg:pt-16">
        {@render children?.()}
    </main>

    <MobileBottomNav />
    <Toaster />
</div>
