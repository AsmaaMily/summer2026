<script lang="ts">
    import { cn } from '@/lib/utils';

    let {
        size = 'md',
        class: className = '',
    }: {
        size?: 'sm' | 'md' | 'lg';
        class?: string;
    } = $props();

    const box = {
        sm: 'size-7 rounded-lg',
        md: 'size-8 rounded-xl',
        lg: 'size-12 rounded-2xl',
    } as const;

    const glyph = {
        sm: 'size-4',
        md: 'size-5',
        lg: 'size-7',
    } as const;
</script>

<span
    class={cn(
        'flex shrink-0 items-center justify-center bg-primary text-primary-foreground shadow-soft',
        box[size],
        className,
    )}
    aria-hidden="true"
>
    <svg class={glyph[size]} viewBox="0 0 32 32" fill="none">
        <rect
            x="6"
            y="17"
            width="4.5"
            height="9"
            rx="2.25"
            fill="currentColor"
            opacity="0.55"
        />
        <rect
            x="13.75"
            y="11"
            width="4.5"
            height="15"
            rx="2.25"
            fill="currentColor"
            opacity="0.8"
        />
        <rect
            x="21.5"
            y="6"
            width="4.5"
            height="20"
            rx="2.25"
            fill="currentColor"
        />
    </svg>
</span>
