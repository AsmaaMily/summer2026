<script module lang="ts">
    export const layout = {
        breadcrumbs: [
            {
                title: 'مرحباً',
                href: '/hello',
            },
        ],
    };
</script>

<script lang="ts">
    import { Sparkles } from 'lucide-svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { Button } from '@/components/ui/button';
    import {
        Card,
        CardContent,
        CardHeader,
        CardTitle,
    } from '@/components/ui/card';
</script>

<AppHead title="مرحباً" />

<div class="flex h-full flex-1 flex-col items-center justify-center gap-6 p-4 md:p-6">
    <div class="flex size-16 items-center justify-center rounded-2xl bg-primary/10">
        <Sparkles class="size-8 text-primary" />
    </div>

    <div class="space-y-2 text-center">
        <h1 class="text-2xl font-bold">مرحباً بك</h1>
        <p class="text-sm text-muted-foreground">
            هذه صفحة تجريبية يمكنك تخصيصها حسب احتياجك
        </p>
    </div>

    <Card class="w-full max-w-md">
        <CardHeader>
            <CardTitle class="text-base">محتوى الصفحة</CardTitle>
        </CardHeader>
        <CardContent>
            <p class="text-sm text-muted-foreground">
                ابدأ بتعديل هذا المحتوى وإضافة العناصر التي تحتاجها
            </p>
        </CardContent>
    </Card>

    <Button>
        <Sparkles class="ml-2 size-4" />
        ابدأ الآن
    </Button>
</div>
