<script lang="ts">
    let { class: className = '' }: { class?: string } = $props();
</script>

<span class="flex items-center gap-1.5 {className}" aria-label="جارٍ الكتابة">
    <span
        class="size-2 animate-bounce rounded-full bg-primary/70 [animation-delay:-0.3s] [animation-duration:1s]"
    ></span>
    <span
        class="size-2 animate-bounce rounded-full bg-primary/70 [animation-delay:-0.15s] [animation-duration:1s]"
    ></span>
    <span
        class="size-2 animate-bounce rounded-full bg-primary/70 [animation-duration:1s]"
    ></span>
</span>
