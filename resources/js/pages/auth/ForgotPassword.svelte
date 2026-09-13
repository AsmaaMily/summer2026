<script module lang="ts">
    import BlankLayout from '@/layouts/BlankLayout.svelte';
    export const layout = BlankLayout;
</script>

<script lang="ts">
    import { Form } from '@inertiajs/svelte';
    import LineSquiggle from 'lucide-svelte/icons/line-squiggle';
    import AppHead from '@/components/AppHead.svelte';
    import InputError from '@/components/InputError.svelte';
    import TextLink from '@/components/TextLink.svelte';
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Spinner } from '@/components/ui/spinner';
    import { store as loginStore } from '@/routes/login';
    import { email } from '@/routes/password';

    let {
        status = '',
    }: {
        status?: string;
    } = $props();
</script>

<AppHead title="نسيت كلمة المرور" />

<div class="flex min-h-[100dvh] w-full items-center justify-center bg-cover bg-center bg-no-repeat p-4 sm:p-8" style="background-image: url('/images/login.png')" dir="rtl">

    <div class="w-full max-w-md rounded-3xl border border-white/30 bg-white/50 p-6 shadow-2xl backdrop-blur-xl dark:border-slate-700/50 dark:bg-slate-900/60 sm:p-10">

        <div class="mb-8 flex justify-center">
            <LineSquiggle class="h-16 w-16 text-[#6669C6] drop-shadow-md" strokeWidth="2.5" />
        </div>

        {#if status}
            <div class="mb-4 rounded-lg bg-green-50 p-3 text-center text-sm font-medium text-green-600 dark:bg-green-900/20 dark:text-green-400">
                {status}
            </div>
        {/if}

        <Form {...email.form()} class="flex flex-col gap-6">
            {#snippet children({ errors, processing })}
                <div class="grid gap-2">
                    <Label for="email">البريد الإلكتروني</Label>
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        autocomplete="off"
                        placeholder="example@email.com"
                        dir="ltr"
                        class="bg-white/60 text-right focus:bg-white dark:bg-slate-800/60 dark:focus:bg-slate-800"
                    />
                    <InputError message={errors.email} />
                </div>

                <Button
                    type="submit"
                    class="h-10 w-full border-none bg-gradient-to-r from-[#2B3070] via-[#6b6ec6] to-[#8C8DD8] text-white transition-opacity hover:opacity-90"
                    disabled={processing}
                    data-test="email-password-reset-link-button"
                >
                    {#if processing}<Spinner />{/if}
                    إرسال رابط إعادة التعيين
                </Button>
            {/snippet}
        </Form>

        <div class="mt-6 text-center text-sm font-medium">
            <span>أو </span>
            <TextLink href={loginStore.url()}>العودة لتسجيل الدخول</TextLink>
        </div>

    </div>
</div>
