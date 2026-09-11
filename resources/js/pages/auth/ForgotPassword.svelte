<script module lang="ts">
    export const layout = {
        title: 'نسيت كلمة المرور',
        description: 'أدخل بريدك الإلكتروني لاستلام رابط إعادة تعيين كلمة المرور',
    };
</script>

<script lang="ts">
    import { Form } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import InputError from '@/components/InputError.svelte';
    import TextLink from '@/components/TextLink.svelte';
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Spinner } from '@/components/ui/spinner';
    import { login } from '@/routes';
    import { email } from '@/routes/password';

    let {
        status = '',
    }: {
        status?: string;
    } = $props();
</script>

<AppHead title="نسيت كلمة المرور" />

{#if status}
    <div class="mb-4 text-center text-sm font-medium text-green-600">
        {status}
    </div>
{/if}

<div class="space-y-6">
    <Form {...email.form()}>
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
                    class="text-right"
                />
                <InputError message={errors.email} />
            </div>

            <div class="my-6 flex items-center justify-start">
                <Button
                    type="submit"
                    class="w-full"
                    disabled={processing}
                    data-test="email-password-reset-link-button"
                >
                    {#if processing}<Spinner />{/if}
                    إرسال رابط إعادة التعيين
                </Button>
            </div>
        {/snippet}
    </Form>

    <div class="text-center text-sm text-muted-foreground">
        <span>أو </span>
        <TextLink href={login()}>العودة لتسجيل الدخول</TextLink>
    </div>
</div>
