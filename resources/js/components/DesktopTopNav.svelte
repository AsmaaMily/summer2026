<script lang="ts">
    import { Link, page, router } from '@inertiajs/svelte';
    import {
        ReceiptText,
        Bot,
        Target,
        User,
        LogOut,
        Menu,
    } from 'lucide-svelte';
    import AppLogo from '@/components/AppLogo.svelte';
    import {
        Avatar,
        AvatarFallback,
        AvatarImage,
    } from '@/components/ui/avatar';
    import { Button } from '@/components/ui/button';
    import {
        DropdownMenu,
        DropdownMenuContent,
        DropdownMenuItem,
        DropdownMenuSeparator,
        DropdownMenuTrigger,
    } from '@/components/ui/dropdown-menu';
    import {
        Sheet,
        SheetContent,
        SheetTitle,
        SheetTrigger,
    } from '@/components/ui/sheet';
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
    import { currentUrlState } from '@/lib/currentUrl.svelte';
    import type { NavItem } from '@/types';

    const auth = $derived(page.props.auth);
    const user = $derived(auth.user);
    const url = currentUrlState();

    const activeItemStyles = 'text-primary font-semibold';

    const menuNavItems: NavItem[] = [
        {
            title: 'سجل المعاملات',
            href: transactions(),
            icon: ReceiptText,
        },
        {
            title: 'المساعد الذكي',
            href: aiAssistant(),
            icon: Bot,
        },
        {
            title: 'الميزانية والأهداف',
            href: budget(),
            icon: Target,
        },
    ];

    function handleLogout() {
        router.flushAll();
    }
</script>

<div class="fixed inset-x-0 top-0 z-50 border-b border-border bg-background/95 backdrop-blur-lg hidden lg:block">
    <div class="flex h-16 items-center justify-between px-6">
        <Link href={toUrl(dashboard())} class="flex items-center gap-2">
            <AppLogo />
        </Link>

        <div class="flex items-center gap-3">
            <Sheet>
                <SheetTrigger>
                    <Button variant="ghost" size="icon" class="size-9">
                        <Menu class="size-5" />
                    </Button>
                </SheetTrigger>
                <SheetContent side="left" class="w-[280px] p-6">
                    <SheetTitle class="sr-only">القائمة الرئيسية</SheetTitle>
                    <nav class="mt-8 flex flex-col gap-2">
                        {#each menuNavItems as item (toUrl(item.href))}
                            <Link
                                href={toUrl(item.href)}
                                class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors hover:bg-accent {url.whenCurrentUrl(
                                    item.href,
                                    url.currentUrl,
                                    activeItemStyles,
                                    'text-muted-foreground',
                                ) ?? ''}"
                            >
                                {#if item.icon}
                                    <item.icon class="size-5" />
                                {/if}
                                {item.title}
                            </Link>
                        {/each}
                    </nav>
                </SheetContent>
            </Sheet>

            <DropdownMenu>
                <DropdownMenuTrigger>
                    <button
                        class="flex items-center gap-2 rounded-full py-1 ps-2 pe-1 transition-colors hover:bg-accent"
                    >
                        <span class="text-sm font-medium">{user?.name}</span>
                        <Avatar class="size-8 rounded-full">
                            {#if user?.avatar}
                                <AvatarImage src={user.avatar} alt={user?.name} />
                            {/if}
                            <AvatarFallback class="rounded-full bg-primary/10 text-xs font-semibold text-primary">
                                {getInitials(user?.name ?? '')}
                            </AvatarFallback>
                        </Avatar>
                    </button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" class="w-56">
                    <div class="px-2 py-1.5">
                        <p class="text-sm font-medium">{user?.name}</p>
                        <p class="text-xs text-muted-foreground">{user?.email}</p>
                    </div>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem>
                        <Link href={toUrl(profile())}>
                            <User class="ml-2 size-4" />
                            الصفحة الشخصية
                        </Link>
                    </DropdownMenuItem>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem onclick={handleLogout}>
                        <LogOut class="ml-2 size-4" />
                        تسجيل الخروج
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>
        </div>
    </div>
</div>
