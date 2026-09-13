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
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Spinner } from '@/components/ui/spinner';
    import { store as loginStore } from '@/routes/login';
    import { store as registerStore } from '@/routes/register';

    let { passwordRules }: { passwordRules: string } = $props();
</script>

<AppHead title="إنشاء حساب" />

<div class="flex min-h-[100dvh] w-full items-center justify-center bg-cover bg-center bg-no-repeat p-4 sm:p-8" style="background-image: url('/images/login.png')" dir="rtl">

    <div class="w-full max-w-md rounded-2xl border border-white/30 bg-white/50 p-6 shadow-2xl backdrop-blur-xl dark:border-slate-700/50 dark:bg-slate-900/60 sm:p-10">

        <!-- حاوية الشعار واسم المشروع -->
        <div class="mb-10 flex flex-col items-center justify-center gap-3">
            <LineSquiggle class="h-16 w-16 text-[#ffffff] drop-shadow-md" strokeWidth="2.5" />
            <h1 class="text-4xl font-extrabold tracking-wide text-[#ffffff] drop-shadow-sm">مَسار</h1>
        </div>

        <Form
            {...registerStore.form()}
            resetOnSuccess={['password', 'password_confirmation']}
            class="flex flex-col gap-6"
        >
            {#snippet children({ errors, processing })}
                <div class="grid gap-6">
                    <div class="grid gap-2">
                        <Label for="name">الاسم</Label>
                        <Input
                            id="name"
                            type="text"
                            required
                            autocomplete="name"
                            name="name"
                            placeholder=" اسم المستخدم"
                            class="bg-white/60 text-right focus:bg-white dark:bg-slate-800/60 dark:focus:bg-slate-800"
                        />
                        <InputError message={errors.name} />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">البريد الإلكتروني</Label>
                        <Input
                            id="email"
                            type="email"
                            required
                            autocomplete="email"
                            name="email"
                            placeholder="example@email.com"
                            dir="ltr"
                            class="bg-white/60 text-right focus:bg-white dark:bg-slate-800/60 dark:focus:bg-slate-800"
                        />
                        <InputError message={errors.email} />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password">كلمة المرور</Label>
                        <PasswordInput
                            id="password"
                            required
                            autocomplete="new-password"
                            name="password"
                            placeholder="كلمة المرور"
                            passwordrules={passwordRules}
                            dir="ltr"
                            class="bg-white/60 text-right focus:bg-white dark:bg-slate-800/60 dark:focus:bg-slate-800"
                        />
                        <InputError message={errors.password} />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password_confirmation">تأكيد كلمة المرور</Label>
                        <PasswordInput
                            id="password_confirmation"
                            required
                            autocomplete="new-password"
                            name="password_confirmation"
                            placeholder="أعد إدخال كلمة المرور"
                            passwordrules={passwordRules}
                            dir="ltr"
                            class="bg-white/60 text-right focus:bg-white dark:bg-slate-800/60 dark:focus:bg-slate-800"
                        />
                        <InputError message={errors.password_confirmation} />
                    </div>

                    <Button
                        type="submit"
                        class="mt-4 h-10 w-full border-none bg-gradient-to-r from-[#3c3f98] via-[#6b6ec6] to-[#8C8DD8] text-white transition-opacity hover:opacity-90"
                        disabled={processing}
                        data-test="register-user-button"
                    >
                        {#if processing}<Spinner />{/if}
                        إنشاء حساب
                    </Button>
                </div>

                <div class="text-center text-sm font-medium">
                    لديك حساب بالفعل؟
                    <TextLink href={loginStore.url()}>تسجيل الدخول</TextLink>
                </div>
            {/snippet}
        </Form>

    </div>
</div>
