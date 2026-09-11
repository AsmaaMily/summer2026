<script lang="ts">
	import { cn, type WithElementRef } from "@/lib/utils.js";
	import type { HTMLAttributes } from "svelte/elements";
	import type { PinInputCell } from "bits-ui";
	import Slot from "./input-otp-slot.svelte";

	let {
		ref = $bindable(null),
		class: className,
		children,
		cells,
		...restProps
	}: WithElementRef<HTMLAttributes<HTMLDivElement>> & {
		cells?: PinInputCell[];
	} = $props();
</script>

<div
	bind:this={ref}
	data-slot="input-otp-group"
	class={cn("rounded-lg has-aria-invalid:border-destructive has-aria-invalid:ring-3 has-aria-invalid:ring-destructive/20 dark:has-aria-invalid:ring-destructive/40 flex items-center", className)}
	{...restProps}
>
	{#if cells}
		{#each cells as cell}
			<Slot {cell} />
		{/each}
	{:else}
		{@render children?.()}
	{/if}
</div>
