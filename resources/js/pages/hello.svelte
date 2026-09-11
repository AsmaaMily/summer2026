<script module lang="ts">
    import { ArrowLeft } from 'lucide-svelte';
    export const layout = {
        breadcrumbs: [
            {
                title: 'مرحبا',
                href: '/hello',
            },
        ],
        hideBottomNav: true
    };
</script>

<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
</script>

<AppHead title="مرحبا" />

<div class="relative flex h-full min-h-[100dvh] w-full" dir="rtl">
    
    <img
        src="/images/hello1234.png"
        alt="مرحبا"
        class="h-full w-full object-cover [-webkit-mask-image:linear-gradient(to_bottom,black_0%,black_75%,transparent_100%)] [mask-image:linear-gradient(to_bottom,black_0%,black_75%,transparent_100%)]"
    />

    <!-- استخدمنا حاوية مرنة (flex-col) لترتيب العناصر عمودياً -->
    <div class="absolute bottom-32 right-0 flex w-full flex-col items-start gap-8 px-8 sm:px-12 md:bottom-24">
        
        <h1 class="text-6xl font-extrabold leading-tight text-white drop-shadow-lg sm:text-5xl md:text-6xl">
            الطريقة الأفضل<br/> لإدارة أموالك
        </h1>

        <!-- زر مع أيقونة ونص عربي -->
        <button class="btn btn-xl w-full border-none text-white text-xl bg-gradient-to-r from-[#2B3070] via-[#6b6ec6] to-[#8C8DD8] transition-all duration-500 hover:bg-[right_center] hover:shadow-lg">
         ابدأ الآن
        <ArrowLeft class="size-6" />
        </button>

    </div>
    
</div>
