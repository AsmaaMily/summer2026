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
    import DesktopTopNav from '@/components/DesktopTopNav.svelte';
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
    const shouldHide = $derived(currentPath === '/hello');

    function isActive(href: string): boolean {
        return currentPath === href;
    }

    const auth = $derived(page.props.auth);
    const user = $derived(auth.user);

    function handleLogout() {
        router.visit(logout(), { method: 'post' });
    }

    const navItems = [
        { href: toUrl(transactions()), icon: ReceiptText, label: '' },
        { href: toUrl(aiAssistant()), icon: Bot, label: '' },
        { href: toUrl(dashboard()), icon: Home, label: '', isCenter: true },
        { href: toUrl(budget()), icon: Target, label: '' },
    ];
</script>

<!-- الحاوية الأساسية للخلفية والضباب -->
<div class="relative min-h-[100dvh] w-full bg-cover bg-center bg-fixed bg-no-repeat" style="background-image: url('/images/1sa.png')" dir="rtl">

    <!-- طبقة الضباب -->
    <div class="absolute inset-0 backdrop-blur-md"></div>

    <!-- شريط التنقل العلوي على الديسكتوب -->
    <DesktopTopNav />

    <!-- حاوية المحتوى مع مساحة سفلية (pb-24) لكي لا يختفي المحتوى خلف الشريط الثابت -->
    <div class="relative z-10 flex min-h-[100dvh] w-full flex-col items-center justify-center p-4 pb-24 sm:p-8 sm:pb-24 lg:pt-20">
        <slot />
    </div>

    <!-- الشريط السفلي للجوال مدمج تلقائياً (بستايل متناسق مع الـ Dark Mode والزجاج) -->
{#if !shouldHide}
<nav class="fixed inset-x-0 bottom-0 z-50 border-t border-white/20 bg-slate-900/85 backdrop-blur-lg lg:hidden" dir="rtl">
    <div class="flex h-16 items-center justify-around px-2">
        
        {#each navItems as item (item.href)}
            {#if item.isCenter}
                <Link
                    href={item.href}
                    class="flex flex-col items-center justify-center outline-none"
                >
                    <div
                        class="flex size-12 -mt-5 items-center justify-center rounded-full bg-gradient-to-r from-[#3c3f98] via-[#6b6ec6] to-[#6b6ec6] text-white shadow-lg transition-transform hover:scale-105 {isActive(item.href)
                            ? 'scale-110 ring-2 ring-white/30'
                            : ''}"
                    >
                        <item.icon class="size-7" />
                    </div>
                    <span class="mt-1 text-[10px] font-medium {isActive(item.href) ? 'text-[#8C8DD8]' : 'text-slate-400'}">
                        {item.label}
                    </span>
                </Link>
            {:else}
                <Link
                    href={item.href}
                    class="flex flex-col items-center justify-center gap-1 px-3 py-1 outline-none transition-colors hover:text-[#8C8DD8]"
                >
                    <item.icon
                        class="size-7 {isActive(item.href) ? 'text-[#8C8DD8]' : 'text-slate-400'}"
                    />
                    <span class="text-[10px] font-medium {isActive(item.href) ? 'text-[#8C8DD8]' : 'text-slate-400'}">
                        {item.label}
                    </span>
                </Link>
            {/if}
        {/each}

        <DropdownMenu>
            <DropdownMenuTrigger class="outline-none">
                <button
                    class="flex flex-col items-center justify-center gap-1 px-3 py-1 outline-none"
                >
                    <Avatar class="size-7 rounded-full border border-white/20 transition-transform hover:scale-105">
                        {#if user?.avatar}
                            <AvatarImage src={user.avatar} alt={user?.name} />
                        {/if}
                        <AvatarFallback class="rounded-full bg-[#8C8DD8]/20 text-[10px] font-semibold text-[#8C8DD8]">
                            {getInitials(user?.name ?? '')}
                        </AvatarFallback>
                    </Avatar>
                    <span class="text-[10px] font-medium text-slate-400"></span>
                </button>
            </DropdownMenuTrigger>
            
            <DropdownMenuContent align="start" side="top" dir="rtl" class="mb-2 w-40 border-white/20 bg-slate-900/90 text-white backdrop-blur-xl text-right items-center justify-center">
                
                <DropdownMenuItem class="cursor-pointer focus:bg-white/10">
                    <Link href={toUrl(profile())} class="flex w-full items-center gap-2">
                        <span>الصفحة الشخصية</span>
                        <User class="size-4" />
                    </Link>
                </DropdownMenuItem>
                
                <DropdownMenuItem onclick={handleLogout} class="flex cursor-pointer items-center gap-2 text-rose-400 focus:bg-white/10 focus:text-rose-400">
                    <span>تسجيل الخروج</span>
                    <LogOut class="size-4" />
                    
                </DropdownMenuItem>
                
            </DropdownMenuContent>
        </DropdownMenu>

    </div>
</nav>
{/if}
</div>p