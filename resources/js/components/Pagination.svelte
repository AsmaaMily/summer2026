<script lang="ts">
    import { ChevronRight, ChevronLeft } from 'lucide-svelte';
    import { Button } from '@/components/ui/button';

    let {
        currentPage,
        lastPage,
        onPageChange,
    }: {
        currentPage: number;
        lastPage: number;
        onPageChange: (page: number) => void;
    } = $props();

    const pages = $derived(() => {
        const items: (number | '...')[] = [];

        if (lastPage <= 7) {
            for (let i = 1; i <= lastPage; i++) {
items.push(i);
}
        } else {
            items.push(1);

            if (currentPage > 3) {
items.push('...');
}

            const start = Math.max(2, currentPage - 1);
            const end = Math.min(lastPage - 1, currentPage + 1);

            for (let i = start; i <= end; i++) {
items.push(i);
}

            if (currentPage < lastPage - 2) {
items.push('...');
}

            items.push(lastPage);
        }

        return items;
    });
</script>

{#if lastPage > 1}
    <div class="flex items-center justify-center gap-1 pt-4" dir="rtl">
        <Button
            variant="outline"
            size="icon-sm"
            disabled={currentPage <= 1}
            onclick={() => onPageChange(currentPage - 1)}
            class="border-white/40 bg-white/50 dark:border-slate-600/50 dark:bg-slate-800/50 dark:text-white"
        >
            <ChevronRight class="size-4" />
        </Button>

        {#each pages() as page, i (i)}
            {#if page === '...'}
                <span class="px-2 text-sm text-slate-500 dark:text-slate-400">...</span>
            {:else}
                <Button
                    variant={page === currentPage ? 'default' : 'outline'}
                    size="icon-sm"
                    onclick={() => onPageChange(page as number)}
                    class={page === currentPage
                        ? 'bg-gradient-to-r from-[#3c3f98] via-[#6b6ec6] to-[#6b6ec6] text-white shadow-lg'
                        : 'border-white/40 bg-white/50 dark:border-slate-600/50 dark:bg-slate-800/50 dark:text-white'}
                >
                    {page}
                </Button>
            {/if}
        {/each}

        <Button
            variant="outline"
            size="icon-sm"
            disabled={currentPage >= lastPage}
            onclick={() => onPageChange(currentPage + 1)}
            class="border-white/40 bg-white/50 dark:border-slate-600/50 dark:bg-slate-800/50 dark:text-white"
        >
            <ChevronLeft class="size-4" />
        </Button>
    </div>
{/if}
