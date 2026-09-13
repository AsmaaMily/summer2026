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
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Spinner } from '@/components/ui/spinner';
    import { update } from '@/routes/password';

    let {
        token,
        email,
        passwordRules,
    }: {
        token: string;
        email: string;
        passwordRules: string;
    } = $props();
</script>

<AppHead title="إعادة تعيين كلمة المرور" />

<div class="flex min-h-[100dvh] w-full items-center justify-center bg-cover bg-center bg-no-repeat p-4 sm:p-8" style="background-image: url('/images/login.png')" dir="rtl">

    <div class="w-full max-w-lg rounded-2xl border border-white/30 bg-white/50 p-6 shadow-2xl backdrop-blur-xl dark:border-slate-700/50 dark:bg-slate-900/60 sm:p-12">

        <!-- حاوية الشعار واسم المشروع -->
        <div class="mb-10 flex flex-col items-center justify-center gap-3">
            <LineSquiggle class="h-16 w-16 text-[#ffffff] drop-shadow-md" strokeWidth="2.5" />
            <h1 class="text-4xl font-extrabold tracking-wide text-[#ffffff] drop-shadow-sm">مَسار</h1>
        </div>

        <Form
            {...update.form()}
            transform={(data) => ({ ...data, token, email })}
            resetOnSuccess={['password', 'password_confirmation']}
        >
            {#snippet children({ errors, processing })}
                <div class="grid gap-6">
                    <div class="grid gap-2">
                        <Label for="email">البريد الإلكتروني</Label>
                        <Input
                            id="email"
                            type="email"
                            name="email"
                            autocomplete="email"
                            value={email}
                            dir="ltr"
                            class="bg-white/60 text-right focus:bg-white dark:bg-slate-800/60 dark:focus:bg-slate-800"
                            readonly
                        />
                        <InputError message={errors.email} />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password">كلمة المرور الجديدة</Label>
                        <PasswordInput
                            id="password"
                            name="password"
                            autocomplete="new-password"
                            dir="ltr"
                            class="bg-white/60 text-right focus:bg-white dark:bg-slate-800/60 dark:focus:bg-slate-800"
                            placeholder="كلمة المرور الجديدة"
                            passwordrules={passwordRules}
                        />
                        <InputError message={errors.password} />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password_confirmation">تأكيد كلمة المرور</Label>
                        <PasswordInput
                            id="password_confirmation"
                            name="password_confirmation"
                            autocomplete="new-password"
                            dir="ltr"
                            class="bg-white/60 text-right focus:bg-white dark:bg-slate-800/60 dark:focus:bg-slate-800"
                            placeholder="تأكيد كلمة المرور"
                            passwordrules={passwordRules}
                        />
                        <InputError message={errors.password_confirmation} />
                    </div>

                    <Button
                        type="submit"
                        class="mt-4 h-11 w-full border-none bg-gradient-to-r from-[#6b6ec6] via-[#6b6ec6] to-[#8C8DD8] text-lg font-semibold text-white transition-opacity hover:opacity-90"
                        disabled={processing}
                        data-test="reset-password-button"
                    >
                        {#if processing}<Spinner />{/if}
                        إعادة تعيين كلمة المرور
                    </Button>
                </div>
            {/snippet}
        </Form>
    </div>
</div>