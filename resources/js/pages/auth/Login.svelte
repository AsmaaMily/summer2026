<script module lang="ts">
    import BlankLayout from '@/layouts/BlankLayout.svelte';
    export const layout = BlankLayout;
</script>

<script lang="ts">
    import { Form } from '@inertiajs/svelte';
    import LineSquiggle from 'lucide-svelte/icons/line-squiggle';
    import AppHead from '@/components/AppHead.svelte';
    import InputError from '@/components/InputError.svelte';
    import PasswordInput from '@/components/PasswordInput.svelte';
    import TextLink from '@/components/TextLink.svelte';
    import { Button } from '@/components/ui/button';
    import { Checkbox } from '@/components/ui/checkbox';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Spinner } from '@/components/ui/spinner';
    import { register } from '@/routes';
    import { store } from '@/routes/login';
    import { request } from '@/routes/password';

    let {
        status = '',
        canResetPassword,
    }: {
        status?: string;
        canResetPassword: boolean;
    } = $props();
</script>

<AppHead title="تسجيل الدخول" />

<div class="flex min-h-[100dvh] w-full items-center justify-center bg-cover bg-center bg-no-repeat p-4 sm:p-8" style="background-image: url('/images/login.png')" dir="rtl">

    <!-- تم تغيير max-w-md إلى max-w-lg لتكبير البطاقة، وزيادة الحشوة الداخلية sm:p-12 -->
    <div class="w-full max-w-lg rounded-2xl border border-white/30 bg-white/50 p-6 shadow-2xl backdrop-blur-xl dark:border-slate-700/50 dark:bg-slate-900/60 sm:p-12">

        <!-- حاوية الشعار واسم المشروع -->
        <div class="mb-10 flex flex-col items-center justify-center gap-3">
            <LineSquiggle class="h-16 w-16 text-[#ffffff] drop-shadow-md" strokeWidth="2.5" />
            <h1 class="text-4xl font-extrabold tracking-wide text-[#ffffff] drop-shadow-sm">مَسار</h1>
        </div>

        <Form
            {...store.form()}
            resetOnSuccess={['password']}
            class="flex flex-col gap-6"
        >
            {#snippet children({ errors, processing })}
                <div class="grid gap-6">
                    <div class="grid gap-2">
                        <Label for="email">البريد الإلكتروني</Label>
                        <Input
                            id="email"
                            type="email"
                            name="email"
                            required
                            autocomplete="email"
                            placeholder="example@email.com"
                            dir="ltr"
                            class="bg-white/60 text-right focus:bg-white dark:bg-slate-800/60 dark:focus:bg-slate-800"
                        />
                        <InputError message={errors.email} />
                    </div>

                    <div class="grid gap-2">
                        <div class="flex items-center justify-between">
                            <Label for="password">كلمة المرور</Label>
                            {#if canResetPassword}
                                <TextLink href={request()} class="text-sm">
                                    نسيت كلمة المرور؟
                                </TextLink>
                            {/if}
                        </div>
                        <PasswordInput
                            id="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            placeholder="كلمة المرور"
                            dir="ltr"
                            class="bg-white/60 text-right focus:bg-white dark:bg-slate-800/60 dark:focus:bg-slate-800"
                        />
                        <InputError message={errors.password} />
                    </div>

                    <div class="flex items-center justify-between">
                        <Label for="remember" class="flex items-center space-x-3 space-x-reverse">
                            <Checkbox id="remember" name="remember" />
                            <span>تذكرني</span>
                        </Label>
                    </div>

                    <!-- تم تكبير الزر قليلاً ليتناسب مع الحجم الجديد للبطاقة -->
                    <Button
                        type="submit"
                        class="mt-4 h-10 w-full border-none bg-gradient-to-r from-[#3c3f98] via-[#6b6ec6] to-[#8C8DD8] text-white transition-opacity hover:opacity-90"
                        data-test="login-button"
                    >
                        {#if processing}<Spinner />{/if}
                        تسجيل الدخول
                    </Button>
                </div>

                <div class="text-center text-sm font-medium">
                    ليس لديك حساب؟
                    <TextLink href={register.url()}>إنشاء حساب</TextLink>
                </div>
            {/snippet}
        </Form>
    </div>
</div>