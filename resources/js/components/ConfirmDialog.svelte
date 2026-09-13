<script lang="ts">
    import {
        Dialog,
        DialogContent,
        DialogHeader,
        DialogTitle,
        DialogDescription,
        DialogFooter,
    } from '@/components/ui/dialog';
    import { Button } from '@/components/ui/button';

    let {
        open = $bindable(false),
        title = '',
        message = '',
        onConfirm,
        onCancel,
    }: {
        open: boolean;
        title: string;
        message: string;
        onConfirm: () => void;
        onCancel: () => void;
    } = $props();

    function handleConfirm() {
        onConfirm();
        open = false;
    }

    function handleCancel() {
        onCancel();
        open = false;
    }
</script>

<Dialog bind:open>
    <DialogContent class="sm:max-w-md border-white/30 bg-white/80 backdrop-blur-xl dark:border-slate-700/50 dark:bg-slate-900/80" dir="rtl">
        <DialogHeader>
            <DialogTitle class="text-slate-800 dark:text-white">{title}</DialogTitle>
            <DialogDescription class="text-slate-500 dark:text-slate-400">{message}</DialogDescription>
        </DialogHeader>
        <DialogFooter class="gap-2 sm:gap-0">
            <Button variant="outline" onclick={handleCancel} class="border-white/40 bg-white/50 dark:border-slate-600/50 dark:bg-slate-800/50 dark:text-white">
                إلغاء
            </Button>
            <Button variant="destructive" onclick={handleConfirm}>
                تأكيد
            </Button>
        </DialogFooter>
    </DialogContent>
</Dialog>
