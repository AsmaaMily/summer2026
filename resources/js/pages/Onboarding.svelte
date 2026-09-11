<script lang="ts">
    import {
        Wallet,
        ArrowLeft,
        Sparkles,
        Shield,
    } from 'lucide-svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import {
        Card,
        CardContent,
        CardDescription,
        CardHeader,
        CardTitle,
    } from '@/components/ui/card';
    import { Badge } from '@/components/ui/badge';
    import AppLogoIcon from '@/components/AppLogoIcon.svelte';

    let step = $state<'welcome' | 'register' | 'login' | 'setup'>('welcome');
    let name = $state('');
    let email = $state('');
    let password = $state('');
    let monthlySalary = $state('');
</script>

<AppHead title="مرحباً بك" />

<div class="flex min-h-svh flex-col items-center justify-center bg-gradient-to-b from-background to-muted/30 p-4">
    <div class="w-full max-w-md space-y-6">
        {#if step === 'welcome'}
            <div class="flex flex-col items-center gap-4 text-center">
                <div class="flex size-16 items-center justify-center rounded-2xl bg-primary text-primary-foreground">
                    <Wallet class="size-8" />
                </div>
                <div class="space-y-2">
                    <h1 class="text-2xl font-bold">مرحباً بك في تطبيق تتبع المصاريف</h1>
                    <p class="text-sm text-muted-foreground">
                        تتبع مصاريفك بذكاء مع مساعد ذكي يحلل أنماط صرفك ويقدم لك نصائح مخصصة
                    </p>
                </div>
            </div>

            <div class="space-y-3">
                <Button class="w-full" size="lg" onclick={() => step = 'register'}>
                    إنشاء حساب جديد
                    <ArrowLeft class="mr-2 size-4" />
                </Button>
                <Button class="w-full" variant="outline" size="lg" onclick={() => step = 'login'}>
                    تسجيل الدخول
                </Button>
            </div>

            <div class="flex items-center justify-center gap-4 text-xs text-muted-foreground">
                <span class="flex items-center gap-1">
                    <Shield class="size-3" />
                    آمن ومشفر
                </span>
                <span class="flex items-center gap-1">
                    <Sparkles class="size-3" />
                    مدعوم بالذكاء الاصطناعي
                </span>
            </div>

        {:else if step === 'register'}
            <Card>
                <CardHeader class="text-center">
                    <div class="mx-auto flex size-12 items-center justify-center rounded-xl bg-primary/10">
                        <AppLogoIcon class="size-6 text-primary" />
                    </div>
                    <CardTitle class="mt-4 text-lg">إنشاء حساب جديد</CardTitle>
                    <CardDescription>أدخل بياناتك للبدء</CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="space-y-2">
                        <Label for="reg-name">الاسم</Label>
                        <Input id="reg-name" bind:value={name} placeholder="أدخل اسمك" />
                    </div>
                    <div class="space-y-2">
                        <Label for="reg-email">البريد الإلكتروني</Label>
                        <Input id="reg-email" type="email" bind:value={email} placeholder="example@email.com" dir="ltr" class="text-right" />
                    </div>
                    <div class="space-y-2">
                        <Label for="reg-password">كلمة المرور</Label>
                        <Input id="reg-password" type="password" bind:value={password} placeholder="••••••••" dir="ltr" class="text-right" />
                    </div>
                    <Button class="w-full" size="lg">
                        إنشاء الحساب
                        <ArrowLeft class="mr-2 size-4" />
                    </Button>
                    <div class="text-center text-sm text-muted-foreground">
                        لديك حساب بالفعل؟
                        <button class="font-medium text-primary hover:underline" onclick={() => step = 'login'}>
                            تسجيل الدخول
                        </button>
                    </div>
                </CardContent>
            </Card>

        {:else if step === 'login'}
            <Card>
                <CardHeader class="text-center">
                    <div class="mx-auto flex size-12 items-center justify-center rounded-xl bg-primary/10">
                        <AppLogoIcon class="size-6 text-primary" />
                    </div>
                    <CardTitle class="mt-4 text-lg">تسجيل الدخول</CardTitle>
                    <CardDescription>أدخل بياناتك للدخول إلى حسابك</CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="space-y-2">
                        <Label for="login-email">البريد الإلكتروني</Label>
                        <Input id="login-email" type="email" bind:value={email} placeholder="example@email.com" dir="ltr" class="text-right" />
                    </div>
                    <div class="space-y-2">
                        <Label for="login-password">كلمة المرور</Label>
                        <Input id="login-password" type="password" bind:value={password} placeholder="••••••••" dir="ltr" class="text-right" />
                    </div>
                    <Button class="w-full" size="lg">
                        تسجيل الدخول
                    </Button>
                    <div class="text-center text-sm text-muted-foreground">
                        ليس لديك حساب؟
                        <button class="font-medium text-primary hover:underline" onclick={() => step = 'register'}>
                            إنشاء حساب جديد
                        </button>
                    </div>
                </CardContent>
            </Card>

        {:else if step === 'setup'}
            <Card>
                <CardHeader class="text-center">
                    <div class="mx-auto flex size-12 items-center justify-center rounded-xl bg-primary/10">
                        <Sparkles class="size-6 text-primary" />
                    </div>
                    <CardTitle class="mt-4 text-lg">إعداد حسابك</CardTitle>
                    <CardDescription>أدخل بعض البيانات الأساسية للبدء</CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="space-y-2">
                        <Label for="setup-salary">الراتب الشهري (ر.س)</Label>
                        <Input id="setup-salary" type="number" bind:value={monthlySalary} placeholder="0" />
                        <p class="text-xs text-muted-foreground">
                            يمكنك تعديل الراتب لاحقاً من الصفحة الشخصية
                        </p>
                    </div>
                    <div class="rounded-lg bg-muted p-3">
                        <div class="flex items-center gap-2 text-sm">
                            <span class="font-medium">العملة:</span>
                            <Badge variant="secondary">ريال سعودي (ر.س)</Badge>
                        </div>
                    </div>
                    <Button class="w-full" size="lg">
                        ابدأ الاستخدام
                        <ArrowLeft class="mr-2 size-4" />
                    </Button>
                </CardContent>
            </Card>
        {/if}

        {#if step !== 'welcome'}
            <div class="text-center">
                <button
                    class="text-sm text-muted-foreground hover:text-foreground"
                    onclick={() => step = 'welcome'}
                >
                    العودة
                </button>
            </div>
        {/if}
    </div>
</div>
