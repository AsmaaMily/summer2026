<script lang="ts">
    import { renderMarkdown } from '@/lib/markdown';

    let { source }: { source: string } = $props();

    let html = $derived(renderMarkdown(source));
</script>

<div
    class="min-w-0 text-sm leading-relaxed tabular-nums
        [&>*:first-child]:mt-0 [&>*:last-child]:mb-0
        [&_a]:font-medium [&_a]:text-primary [&_a]:underline [&_a]:underline-offset-2 [&_a:hover]:opacity-80
        [&_blockquote]:my-2 [&_blockquote]:border-s-2 [&_blockquote]:border-primary/40 [&_blockquote]:ps-3 [&_blockquote]:text-muted-foreground
        [&_code]:rounded-md [&_code]:bg-muted [&_code]:px-1.5 [&_code]:py-0.5 [&_code]:font-mono [&_code]:text-[0.8125rem] [&_code]:text-foreground
        [&_del]:text-muted-foreground
        [&_h1]:mb-1.5 [&_h1]:mt-3 [&_h1]:text-base [&_h1]:font-semibold [&_h1]:tracking-tight
        [&_h2]:mb-1.5 [&_h2]:mt-3 [&_h2]:text-[0.95rem] [&_h2]:font-semibold [&_h2]:tracking-tight
        [&_h3]:mb-1 [&_h3]:mt-2.5 [&_h3]:text-sm [&_h3]:font-semibold
        [&_hr]:my-3 [&_hr]:border-border
        [&_li]:my-1 [&_li]:marker:text-muted-foreground
        [&_li>ul]:mt-1 [&_li>ol]:mt-1
        [&_ol]:my-2 [&_ol]:list-decimal [&_ol]:ps-5
        [&_p]:my-2
        [&_pre]:my-2.5 [&_pre]:overflow-x-auto [&_pre]:rounded-xl [&_pre]:border [&_pre]:border-border/70 [&_pre]:bg-muted/70 [&_pre]:p-3
        [&_pre_code]:bg-transparent [&_pre_code]:p-0 [&_pre_code]:text-xs [&_pre_code]:text-foreground
        [&_strong]:font-semibold [&_strong]:text-foreground
        [&_table]:my-2.5 [&_table]:block [&_table]:w-full [&_table]:overflow-x-auto [&_table]:rounded-lg [&_table]:border [&_table]:border-border
        [&_table]:border-separate [&_table]:border-spacing-0
        [&_td]:border-t [&_td]:border-border [&_td]:px-2.5 [&_td]:py-1.5 [&_td]:text-start
        [&_th]:bg-muted/60 [&_th]:px-2.5 [&_th]:py-1.5 [&_th]:text-start [&_th]:text-xs [&_th]:font-semibold [&_th]:text-muted-foreground
        [&_ul]:my-2 [&_ul]:list-disc [&_ul]:ps-5"
    dir="auto"
>
    <!-- eslint-disable-next-line svelte/no-at-html-tags -- sanitized with DOMPurify in renderMarkdown -->
    {@html html}
</div>
