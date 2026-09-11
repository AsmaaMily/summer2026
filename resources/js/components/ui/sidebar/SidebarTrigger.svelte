<script lang="ts">
    import { PanelLeftClose, PanelLeftOpen } from 'lucide-svelte';
    import { Button } from '@/components/ui/button';
    import { cn } from '@/lib/utils';
    import { useSidebar } from './context.svelte.js';

    let { class: className = '' } = $props();

    const sidebar = useSidebar();
</script>

<Button
    data-sidebar="trigger"
    data-slot="sidebar-trigger"
    variant="ghost"
    size="icon"
    class={cn('h-7 w-7', className)}
    onclick={sidebar.toggle}
>
    {#if sidebar.isMobile || sidebar.state === 'collapsed'}
        <PanelLeftOpen class="size-4" />
    {:else}
        <PanelLeftClose class="size-4" />
    {/if}
    <span class="sr-only">Toggle sidebar</span>
</Button>
