<script lang="ts">
    import { Link, page, router } from '@inertiajs/svelte';
    import {
        Home,
        ReceiptText,
        Bot,
        Target,
        User,
        LogOut,
    } from 'lucide-svelte';
    import {
        Avatar,
        AvatarFallback,
        AvatarImage,
    } from '@/components/ui/avatar';
    import {
        DropdownMenu,
        DropdownMenuContent,
        DropdownMenuItem,
        DropdownMenuTrigger,
    } from '@/components/ui/dropdown-menu';
    import { getInitials } from '@/lib/initials';
    import { toUrl } from '@/lib/utils';
    import {
        dashboard,
        transactions,
        aiAssistant,
        budget,
        profile,
        logout,
    } from '@/routes';

    const currentPath = $derived(page.url);

    function isActive(href: string): boolean {
        return currentPath === href;
    }

    const auth = $derived(page.props.auth);
    const user = $derived(auth.user);

    function handleLogout() {
        router.flushAll();
    }
    const navItems = [
        { href: toUrl(transactions()), icon: ReceiptText, label: 'المعاملات' },
        { href: toUrl(aiAssistant()), icon: Bot, label: 'المساعد' },
        { href: toUrl(dashboard()), icon: Home, label: 'الرئيسية', isCenter: true },
        { href: toUrl(budget()), icon: Target, label: 'الميزانية' },
    ];
</script>

<nav class="fixed inset-x-0 bottom-0 z-50 border-t border-border bg-background/95 backdrop-blur-lg lg:hidden">
    <div class="flex items-center justify-around px-2" style="height: 4rem;">
        {#each navItems as item (item.href)}
            {#if item.isCenter}
                <Link
                    href={item.href}
                    class="flex flex-col items-center justify-center"
                >
                    <div
                        class="flex size-12 -mt-5 items-center justify-center rounded-full bg-primary text-primary-foreground shadow-lg transition-transform {isActive(item.href)
                            ? 'scale-110 ring-2 ring-primary/30'
                            : ''}"
                    >
                        <item.icon class="size-7" />
                    </div>
                    <span class="mt-0.5 text-[10px] font-medium {isActive(item.href) ? 'text-primary' : 'text-muted-foreground'}">
                        {item.label}
                    </span>
                </Link>
            {:else}
                <Link
                    href={item.href}
                    class="flex flex-col items-center justify-center gap-0.5 px-3 py-1"
                >
                    <item.icon
                        class="size-7 {isActive(item.href) ? 'text-primary' : 'text-muted-foreground'}"
                    />
                    <span class="text-[10px] font-medium {isActive(item.href) ? 'text-primary' : 'text-muted-foreground'}">
                        {item.label}
                    </span>
                </Link>
            {/if}
        {/each}

        <DropdownMenu>
            <DropdownMenuTrigger>
                <button
                    class="flex flex-col items-center justify-center gap-0.5 px-3 py-1"
                >
                    <Avatar class="size-7 rounded-full">
                        {#if user?.avatar}
                            <AvatarImage src={user.avatar} alt={user?.name} />
                        {/if}
                        <AvatarFallback class="rounded-full bg-primary/10 text-[10px] font-semibold text-primary">
                            {getInitials(user?.name ?? '')}
                        </AvatarFallback>
                    </Avatar>
                    <span class="text-[10px] font-medium text-muted-foreground"></span>
                </button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" side="top" class="mb-2 w-48">
                <DropdownMenuItem>
                    <Link href={toUrl(profile())}>
                        <User class="ml-2 size-4" />
                        الصفحة الشخصية
                    </Link>
                </DropdownMenuItem>
                <DropdownMenuItem onclick={handleLogout}>
                    <LogOut class="ml-2 size-4" />
                    تسجيل الخروج
                </DropdownMenuItem>
            </DropdownMenuContent>
        </DropdownMenu>
    </div>
</nav>
