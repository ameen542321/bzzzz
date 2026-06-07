<!DOCTYPE html>
<html lang="ar" dir="rtl" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>
    if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
</script>
        {{-- Tailwind --}}
    <!-- <script src="https://cdn.tailwindcss.com"></script> -->

    {{-- Alpine.js + Collapse --}}
   {{-- Alpine.js + Collapse --}}

<!-- <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script> -->

<!-- Alpine Core -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- AlpineJS --}}

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">

<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"> -->

    <!-- ملفك الأساسي - المسار الصحيح لـ public/css -->
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">

    <!-- ملف الوضع الفاتح - المسار الصحيح -->
    <link id="light-fix" href="{{ asset('css/light-mode-fix.css') }}" rel="stylesheet">

    {{-- <script>
        // التحكم في الثيم
        (function() {
            const savedTheme = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const isDark = savedTheme === 'dark' || (!savedTheme && prefersDark);

            if (isDark) {
                document.documentElement.classList.add('dark');
            }

            // تعطيل ملف الفاتح إذا كان الوضع مظلم
            document.addEventListener('DOMContentLoaded', function() {
                const lightFix = document.getElementById('light-fix');
                if (lightFix && isDark) {
                    lightFix.disabled = true;
                }
            });
        })();
    </script> --}}


{{-- <style>
    /* 1. تعريف المتغيرات للتحكم السريع */
    :root {
        --light-bg: #f1f5f9;
        --light-card: #ffffff;
        --light-text: #1e293b;
        --light-border: #e2e8f0;
    }

    /* 2. تفعيل الوضع الفاتح - إعادة برمجة كلاسات Tailwind */
    html:not(.dark) body {
        background-color: var(--light-bg) !important;
        color: var(--light-text) !important;
    }

    /* معالجة كلاسات الرمادي والـ Slate (الخلفيات) */
    html:not(.dark) .bg-gray-900,
    html:not(.dark) .bg-gray-800,
    html:not(.dark) .bg-slate-900,
    html:not(.dark) .bg-slate-800,
    html:not(.dark) .bg-slate-950,
    html:not(.dark) .bg-slate-800\/40,
    html:not(.dark) .bg-slate-900\/60,
    html:not(.dark) .bg-slate-900\/40 {
        background-color: var(--light-card) !important;
        background-image: none !important;
        border-color: var(--light-border) !important;
        backdrop-filter: none !important;
    }

    /* معالجة النصوص الفاتحة لتصبح داكنة */
    html:not(.dark) .text-white,
    html:not(.dark) .text-gray-100,
    html:not(.dark) .text-gray-300,
    html:not(.dark) .text-slate-200,
    html:not(.dark) .text-slate-300,
    html:not(.dark) .text-slate-100 {
        color: var(--light-text) !important;
    }

    /* معالجة النصوص الباهتة (Muted) */
    html:not(.dark) .text-gray-400,
    html:not(.dark) .text-gray-500,
    html:not(.dark) .text-slate-400,
    html:not(.dark) .text-slate-500 {
        color: #64748b !important;
    }

    /* معالجة الحدود (Borders) */
    html:not(.dark) .border-gray-800,
    html:not(.dark) .border-gray-700,
    html:not(.dark) .border-slate-800,
    html:not(.dark) .border-slate-700,
    html:not(.dark) .border-slate-700\/50 {
        border-color: var(--light-border) !important;
    }

    /* معالجة المدخلات (Inputs & Textareas) */
    html:not(.dark) input,
    html:not(.dark) textarea,
    html:not(.dark) select {
        background-color: #f8fafc !important;
        color: #1e293b !important;
        border-color: #cbd5e1 !important;
    }

    /* معالجة المودالات (Modals) */
    html:not(.dark) #withdrawalModal > div:last-child,
    html:not(.dark) .modal-content {
        background-color: #ffffff !important;
        border: 1px solid var(--light-border) !important;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1) !important;
    }

    /* معالجة الكروت الملونة بتدرج (Gradients) */
    html:not(.dark) .bg-gradient-to-br:not(.from-blue-600) {
        background-image: none !important;
        background-color: #f8fafc !important;
        border: 1px solid var(--light-border) !important;
    }

    /* معالجة شريط البحث المخصص */
    html:not(.dark) #employeeSearch {
        background-color: #ffffff !important;
    }

    /* تحسين شكل السكرول بار في الوضع الفاتح */
    html:not(.dark) .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
    }

    /* --- الإضافة الجديدة لصفحة الاستهلاك وما شابهها دون تغيير ما سبق --- */

    /* معالجة الحاويات الشفافة (مثل الفورم الرئيسي) */
    html:not(.dark) .bg-gray-900\/50,
    html:not(.dark) .bg-gray-800\/30,
    html:not(.dark) .bg-gradient-to-r.from-gray-800 {
        background-color: var(--light-card) !important;
        background-image: none !important;
        border-color: var(--light-border) !important;
    }

    /* معالجة صناديق التنبيهات الملونة (Success/Error) */
    html:not(.dark) .bg-green-900\/20 {
        background-color: #f0fdf4 !important;
        border-color: #bbf7d0 !important;
        color: #166534 !important;
    }

    html:not(.dark) .bg-red-900\/20 {
        background-color: #fef2f2 !important;
        border-color: #fecaca !important;
        color: #991b1b !important;
    }

    html:not(.dark) .bg-yellow-900\/20 {
        background-color: #fffbeb !important;
        border-color: #fef3c7 !important;
        color: #92400e !important;
    }

    /* معالجة خلفية خيارات القائمة المنسدلة */
    html:not(.dark) select option {
        background-color: #ffffff !important;
        color: var(--light-text) !important;
    }
    /* --- إضافة لتوضيح الحدود (تباين غامق على خلفية فاتحة) --- */

html:not(.dark) input,
html:not(.dark) select,
html:not(.dark) textarea,
html:not(.dark) .border,
html:not(.dark) .border-gray-800,
html:not(.dark) .border-gray-700,
html:not(.dark) table,
html:not(.dark) th,
html:not(.dark) td {
    /* استخدام لون رمادي متوسط لضمان ظهور الحدود بوضوح فوق الخلفية البيضاء */
    border-color: #94a3b8 !important;
    border-width: 1px !important;
    border-style: solid !important;
}

/* إبراز حدود الحقول عند الكتابة داخلها */
html:not(.dark) input:focus,
html:not(.dark) select:focus {
    border-color: #4f46e5 !important; /* لون أزرق/بنفسجي عند التركيز */
    box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.1) !important;
}

/* معالجة زر التأكيد البنفسجي ليكون ساطعاً */
html:not(.dark) .from-purple-600 {
    background-color: #7c3aed !important;
    color: #ffffff !important;
}
/* --- إضافة خاصة لزر الرجوع (Back Button) في الوضع الفاتح --- */

html:not(.dark) a.bg-gray-800,
html:not(.dark) .bg-gray-800.hover\:bg-gray-700 {
    /* تحويل خلفية الزر للون رمادي فاتح جداً ليظهر كزر منفصل عن الخلفية */
    background-color: #e2e8f0 !important;
    /* إجبار النص والأيقونة داخل الزر على اللون الداكن للوضوح */
    color: #1e293b !important;
    border: 1px solid #cbd5e1 !important;
}

/* معالجة الأيقونة (SVG) داخل زر الرجوع */
html:not(.dark) a.bg-gray-800 svg {
    stroke: #1e293b !important;
}

/* تأثير عند تمرير الماوس على الزر */
html:not(.dark) a.bg-gray-800:hover {
    background-color: #d1d5db !important;
}
/* --- إصلاحات صفحة البيع السريع والحدود في الوضع الفاتح --- */

/* 1. توضيح حدود الحقول والبطاقات بلون رمادي متباين (وليس أبيض) */
html:not(.dark) .bg-gray-900,
html:not(.dark) .bg-gray-800,
html:not(.dark) .bg-gray-800\/40,
html:not(.dark) .border-gray-800,
html:not(.dark) .border-gray-700 {
    background-color: #ffffff !important;
    border-color: #94a3b8 !important; /* رمادي متباين وواضح */
    border-width: 1px !important;
}

/* 2. تصحيح زر الرجوع (كان النص يختفي) */
html:not(.dark) a.bg-gray-800 {
    background-color: #f1f5f9 !important;
    color: #1e293b !important; /* نص داكن */
    border: 1px solid #94a3b8 !important;
}

/* 3. تصحيح زر "تأكيد العملية" (اللون الأزرق) ليكون ساطعاً */
html:not(.dark) .bg-blue-600 {
    background-color: #2563eb !important;
    color: #ffffff !important;
}

/* 4. معالجة النصوص الرمادية (مثل "ابحث عن منتج" و "المحاسب") */
html:not(.dark) .text-gray-400,
html:not(.dark) .text-gray-500,
html:not(.dark) .text-gray-600 {
    color: #475569 !important; /* رمادي غامق مقروء */
}

/* 5. معالجة حقول الإدخال (Input) لتكون بارزة جداً */
html:not(.dark) input,
html:not(.dark) select,
html:not(.dark) textarea {
    background-color: #ffffff !important;
    color: #0f172a !important;
    border: 1.5px solid #64748b !important; /* حدود أكثر سمكاً للتركيز */
}

/* 6. معالجة النصوص داخل البطاقات (اسم المنتج والسعر) */
html:not(.dark) .text-white {
    color: #1e293b !important;
}

/* 7. معالجة قائمة نتائج البحث المنسدلة */
html:not(.dark) .absolute.z-50.bg-gray-800 {
    background-color: #ffffff !important;
    border: 2px solid #94a3b8 !important;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.2) !important;
}

/* 8. معالجة صفوف المنتجات في السلة */
html:not(.dark) .bg-gray-800\/40 {
    background-color: #f8fafc !important;
    border-bottom: 2px solid #e2e8f0 !important;
}
/* --- إضافة شاملة لتحويل تدرجات الألوان الداكنة إلى فاتحة متباينة --- */

/* 1. تحويل كافة الخلفيات الشفافة (Opacity) التي تُستخدم في البطاقات */
html:not(.dark) .bg-gray-800\/50,
html:not(.dark) .bg-gray-900\/50,
html:not(.dark) .bg-slate-800\/50,
html:not(.dark) .bg-slate-900\/50,
html:not(.dark) .bg-white\/5,
html:not(.dark) .bg-white\/10 {
    background-color: rgba(0, 0, 0, 0.03) !important; /* خلفية رمادية خفيفة جداً */
    border: 1px solid #d1d5db !important;
}

/* 2. تحويل ألوان التنبيهات والحالات (Alerts & Status) */

/* الأخضر (النجاح / الوفرة) */
html:not(.dark) .text-green-400,
html:not(.dark) .text-green-500 { color: #15803d !important; }
html:not(.dark) .bg-green-900\/20 { background-color: #f0fdf4 !important; border-color: #bbf7d0 !important; }

/* الأحمر (النقص / الحذف / التنبيه) */
html:not(.dark) .text-red-400,
html:not(.dark) .text-red-500 { color: #b91c1c !important; }
html:not(.dark) .bg-red-900\/20 { background-color: #fef2f2 !important; border-color: #fecaca !important; }

/* الأصفر (التحذير / الآجل) */
html:not(.dark) .text-yellow-400,
html:not(.dark) .text-yellow-500 { color: #a16207 !important; }
html:not(.dark) .bg-yellow-900\/20 { background-color: #fffbeb !important; border-color: #fef3c7 !important; }

/* الأزرق (المعلومات / الروابط) */
html:not(.dark) .text-blue-400,
html:not(.dark) .text-blue-500 { color: #1d4ed8 !important; }
html:not(.dark) .bg-blue-900\/20 { background-color: #eff6ff !important; border-color: #bfdbfe !important; }

/* 3. معالجة اللون الرمادي المتوسط (المتسبب في المشاكل عادةً) */
html:not(.dark) .text-gray-500,
html:not(.dark) .text-slate-500,
html:not(.dark) .text-gray-600 {
    color: #64748b !important; /* رمادي صريح ومقروء */
}

/* 4. توضيح فواصل الجداول والقوائم (Dividers) */
html:not(.dark) .divide-gray-800 > * + *,
html:not(.dark) .border-t.border-gray-700\/50,
html:not(.dark) .border-b.border-gray-700\/50 {
    border-color: #e2e8f0 !important;
}

/* 5. معالجة الظلال (Shadows) لتظهر في الوضع الفاتح */
html:not(.dark) .shadow-xl,
html:not(.dark) .shadow-2xl {
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05) !important;
}

/* 6. إجبار أي نص باللون الأبيض داخل بطاقة فاتحة على التحول للأسود */
html:not(.dark) .bg-white .text-white,
html:not(.dark) .bg-gray-50 .text-white {
    color: #1e293b !important;
}
/* --- الإغلاق النهائي لثغرات الألوان في الوضع الفاتح --- */

/* 1. معالجة الحاويات فائقة التعتيم (slate-950 وما يشابهها) */
html:not(.dark) .bg-slate-950,
html:not(.dark) .bg-gray-950 {
    background-color: #f8fafc !important;
    border: 1px solid #cbd5e1 !important;
}

/* 2. معالجة النصوص التي تستخدم ألوان Slate الزرقاء الداكنة */
html:not(.dark) .text-slate-200,
html:not(.dark) .text-slate-300,
html:not(.dark) .text-slate-400 {
    color: #334155 !important;
}

/* 3. معالجة كلاسات الشفافية العالية (Opacity 80/ و 50/) */
/* هذه الكلاسات تجعل العنصر شبه شفاف، في الفاتح يجب أن تكون صلبة (Solid) */
html:not(.dark) .bg-gray-800\/80,
html:not(.dark) .bg-gray-900\/80,
html:not(.dark) .bg-slate-800\/80 {
    background-color: #ffffff !important;
    backdrop-filter: none !important;
    border: 1px solid #d1d5db !important;
}

/* 4. معالجة التدرجات اللونية للأزرار (Indigo / Purple / Blue) */
/* نضمن بقاء الألوان قوية وواضحة وليست باهتة */
html:not(.dark) .from-blue-600,
html:not(.dark) .from-indigo-600,
html:not(.dark) .from-purple-600 {
    --tw-gradient-from: #4f46e5 !important;
    --tw-gradient-to: #7c3aed !important;
    color: #ffffff !important;
}

/* 5. معالجة نصوص الـ Placeholder (النص المؤقت داخل الحقول) */
html:not(.dark) input::placeholder,
html:not(.dark) textarea::placeholder {
    color: #94a3b8 !important;
}

/* 6. معالجة أيقونات الـ SVG التي قد تظل بيضاء */
html:not(.dark) svg:not(.text-white) {
    stroke: currentColor;
}

/* 7. معالجة كلاسات التباين المنخفض جداً (gray-600/700) */
html:not(.dark) .text-gray-600,
html:not(.dark) .text-gray-700 {
    color: #1e293b !important; /* تحويلها لأسود تقريباً */
}

/* 8. معالجة حواف الجداول (Table Borders) المفقودة */
html:not(.dark) table,
html:not(.dark) tr,
html:not(.dark) td,
html:not(.dark) th {
    border-color: #e2e8f0 !important;
}
/* --- تحسينات صفحة المصاريف للوضع الفاتح --- */

html:not(.dark) {
    /* 1. الحاويات الرئيسية والخلفيات */
    .bg-gray-800,
    .bg-gray-750,
    .bg-gradient-to-br.from-gray-800 {
        background: #ffffff !important;
        background-color: #ffffff !important;
        border-color: #e2e8f0 !important;
    }

    /* 2. صناديق الإحصائيات (تعديل التدرجات لتصبح باستيل مريحة) */
    .from-blue-900\/30, .from-green-900\/30, .from-purple-900\/30, .from-red-900\/30, .from-yellow-900\/30 {
        --tw-gradient-from: rgba(0, 0, 0, 0.02) !important;
        --tw-gradient-to: rgba(0, 0, 0, 0.05) !important;
        border-color: #e2e8f0 !important;
    }

    /* 3. النصوص والعناوين */
    .text-white, h1.text-white, h2.text-white {
        color: #0f172a !important; /* أسود كحلي عميق */
    }

    .text-gray-400, .text-gray-500 {
        color: #64748b !important; /* رمادي متوسط واضح */
    }

    .text-gray-300 {
        color: #334155 !important; /* نصوص الجداول */
    }

    /* 4. الجدول والحقول */
    .bg-gray-900 {
        background-color: #f1f5f9 !important; /* رأس الجدول */
    }

    .divide-gray-700, .border-gray-700, .border-gray-600 {
        border-color: #e2e8f0 !important;
    }

    input, textarea, .bg-gray-700\/50 {
        background-color: #ffffff !important;
        color: #1e293b !important;
        border-color: #cbd5e1 !important;
    }

    /* 5. الأزرار (إبقاء اللون الداكن للرجوع) */
    .bg-gray-700 {
        background-color: #334155 !important;
    }

    /* 6. المودال (الخلفية الضبابية) */
    #expenseModal, #editModal {
        background-color: rgba(0, 0, 0, 0.4) !important;
    }

    /* 7. كلاسات خاصة (مثل شارة المبلغ ر.س) */
    .bg-gradient-to-r.from-yellow-900\/30 {
        background: #fefce8 !important; /* خلفية صفراء فاتحة */
        color: #a16207 !important;
        border-color: #fef3c7 !important;
    }

    /* 8. أيقونات الحالات */
    .bg-blue-600\/20 { background-color: #eff6ff !important; }
    .bg-green-600\/20 { background-color: #f0fdf4 !important; }
    .bg-purple-600\/20 { background-color: #faf5ff !important; }
    .bg-red-600\/20 { background-color: #fef2f2 !important; }
}
/* معالجة أزرار الرجوع الرمادية في الوضع الفاتح */
html:not(.dark) .bg-gray-700 {
    background-color: #f1f5f9 !important; /* رمادي فاتح جداً */
    color: #1e293b !important; /* نص كحلي داكن */
    border: 1px solid #e2e8f0 !important;
}

html:not(.dark) .bg-gray-700:hover {
    background-color: #e2e8f0 !important;
}

/* التأكد من تلون الأيقونة داخل زر الرجوع */
html:not(.dark) .bg-gray-700 svg {
    color: #1e293b !important;
}
/* --- تحسينات صفحة البيع الآجل للوضع الفاتح --- */

html:not(.dark) {
    /* 1. الخلفيات والحاويات (تغيير من Slate الداكن إلى أبيض/رمادي ناعم) */
    .bg-slate-800\/40,
    .bg-slate-900\/60,
    .bg-slate-900,
    .employee-card.bg-slate-900\/40 {
        background-color: #ffffff !important;
        border-color: #e2e8f0 !important;
        backdrop-filter: none !important;
    }

    /* 2. الأيقونات والخلفيات الدائرية الصغيرة */
    .bg-slate-800,
    .bg-slate-900\/50,
    .bg-slate-950 {
        background-color: #f8fafc !important; /* خلفية رمادية فاتحة جداً */
        border-color: #e2e8f0 !important;
    }

    /* 3. النصوص (تحويل من Slate الباهت إلى نصوص صريحة) */
    .text-white,
    h1.text-white,
    h2.text-white,
    h3.text-white {
        color: #0f172a !important; /* أسود كحلي */
    }

    .text-slate-400,
    .text-slate-500 {
        color: #64748b !important; /* رمادي متزن للقراءة */
    }

    .text-slate-200,
    .text-slate-300 {
        color: #334155 !important; /* نصوص فرعية داكنة */
    }

    /* 4. الحقول والمدخلات (Inputs) */
    input#employeeSearch,
    input[type="date"],
    input[type="number"],
    textarea {
        background-color: #ffffff !important;
        color: #1e293b !important;
        border-color: #cbd5e1 !important;
        box-shadow: inset 0 1px 2px rgba(0,0,0,0.05) !important;
    }

    /* 5. زر الرجوع الخاص بهذه الصفحة (bg-slate-800) */
    .bg-slate-800.text-slate-200 {
        background-color: #f1f5f9 !important;
        color: #1e293b !important;
        border: 1px solid #e2e8f0 !important;
    }

    .bg-slate-800.text-slate-200:hover {
        background-color: #e2e8f0 !important;
    }

    /* 6. سجل العمليات الجانبي (Timeline) */
    .border-slate-800 {
        border-color: #f1f5f9 !important;
    }

    .group:hover.border-r-2 {
        border-color: #4f46e5 !important; /* لون Indigo عند التمرير */
    }

    /* 7. المودال (Modal) */
    .fixed.inset-0.bg-slate-950\/90 {
        background-color: rgba(15, 23, 42, 0.5) !important; /* طبقة شفافة ناعمة */
    }

    #creditSaleModal .bg-slate-900 {
        background-color: #ffffff !important;
        border-color: #e2e8f0 !important;
    }

    #creditSaleModal .sticky.top-0 {
        background-color: rgba(255, 255, 255, 0.95) !important;
    }

    /* 8. شريط التمرير (Scrollbar) */
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1 !important;
    }
}
/* --- تحسينات صفحة السحب النقدي للوضع الفاتح --- */
html:not(.dark) {
    /* 1. الحاويات الرئيسية والبطاقات */
    .bg-slate-800\/40,
    .bg-slate-900\/60,
    .employee-card.bg-slate-900\/40,
    #withdrawalModal .bg-slate-900 {
        background-color: #ffffff !important;
        border-color: #e2e8f0 !important;
        backdrop-filter: none !important;
    }

    /* 2. النصوص والعناوين */
    .text-white, h1.text-white, h2.text-white, h3.text-white {
        color: #0f172a !important;
    }

    .text-slate-400, .text-slate-500 {
        color: #64748b !important;
    }

    .text-slate-200, .text-slate-300 {
        color: #334155 !important;
    }

    /* 3. زر الرجوع والعناصر الرمادية (Slate 800) */
    .bg-slate-800 {
        background-color: #f1f5f9 !important;
        color: #1e293b !important;
        border-color: #e2e8f0 !important;
    }

    .bg-slate-800.text-blue-400 { /* دائرة الحرف الأول للموظف */
        background-color: #eff6ff !important;
        border-color: #dbeafe !important;
    }

    /* 4. المدخلات (Inputs) والحقول */
    input#employeeSearch,
    input[type="date"],
    input[type="number"],
    textarea,
    .bg-slate-950 {
        background-color: #ffffff !important;
        color: #1e293b !important;
        border-color: #cbd5e1 !important;
    }

    /* 5. أيقونة التاريخ والخطوط الفاصلة داخل الحقول */
    .bg-slate-800.mr-2 { /* الخط الصغير بجانب العملة أو التاريخ */
        background-color: #e2e8f0 !important;
    }

    /* 6. سجل العمليات الجانبي */
    .border-slate-800 {
        border-color: #f1f5f9 !important;
    }

    /* 7. خلفية المودال المظلمة */
    .bg-slate-950\/90 {
        background-color: rgba(15, 23, 42, 0.4) !important;
    }

    #withdrawalModal .sticky.top-0 {
        background-color: rgba(255, 255, 255, 0.9) !important;
    }

    /* 8. شريط التمرير */
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1 !important;
    }
}

</style>

<style>
    /* تطبيق الخط العربي على كامل الصفحة */
    body {
        font-family: 'Cairo', sans-serif;
    }

    /* منع ظهور العناصر قبل تحميل الجافاسكريبت (الحل لمشكلة الرمش) */
    [x-cloak] {
        display: none !important;
    }

    /* تحسين شكل التمرير (Scrollbar) ليتناسب مع الثيم المظلم */
    ::-webkit-scrollbar {
        width: 8px;
    }
    ::-webkit-scrollbar-track {
        background: #0f172a;
    }
    ::-webkit-scrollbar-thumb {
        background: #334155;
        border-radius: 10px;
    }
    ::-webkit-scrollbar-thumb:hover {
        background: #475569;
    }
    /* --- تحسينات صفحة المديونيات للوضع الفاتح --- */
html:not(.dark) {

    /* 1. الحاويات الرئيسية والخلفيات */
    .bg-slate-800\/40,
    .bg-slate-900\/60,
    .bg-slate-900\/40,
    .employee-card,
    #debtModal .bg-slate-900,
    #collectModal .bg-slate-900 {
        background-color: #ffffff !important;
        border-color: #e2e8f0 !important;
        backdrop-filter: none !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05) !important;
    }

    /* 2. النصوص والعناوين */
    .text-white,
    h1, h2, h3, h4,
    #modalTitle,
    .group-hover\:text-white {
        color: #0f172a !important;
    }

    .text-slate-400,
    .text-slate-500,
    #empNameDisplay,
    #collectEmpName {
        color: #64748b !important;
    }

    .text-slate-200,
    .text-slate-300 {
        color: #334155 !important;
    }

    /* 3. عناصر الموظفين (دائرة الحروف والأدوار) */
    .bg-slate-800 {
        background-color: #f1f5f9 !important;
        border-color: #e2e8f0 !important;
        color: #475569 !important;
    }

    .bg-blue-900\/40 { /* دائرة المحاسب */
        background-color: #eff6ff !important;
        border-color: #dbeafe !important;
        color: #2563eb !important;
    }

    /* 4. الحقول والمدخلات (Inputs) */
    input,
    textarea,
    .bg-slate-950 {
        background-color: #f8fafc !important;
        color: #0f172a !important;
        border-color: #e2e8f0 !important;
    }

    input:focus, textarea:focus {
        background-color: #ffffff !important;
        border-color: #ec4899 !important; /* لون وردي للتركيز */
    }

    /* 5. سجل العمليات الجانبي والوصف */
    .border-pink-800 { border-color: #f9a8d4 !important; }
    .border-blue-800 { border-color: #93c5fd !important; }

    .bg-slate-800\/40.p-2.rounded-lg.italic {
        background-color: #f8fafc !important;
        border-color: #e2e8f0 !important;
        color: #64748b !important;
    }

    /* 6. مودال التحصيل وقوائم المديونيات */
    #debtsList .bg-slate-800\/40 {
        background-color: #f8fafc !important;
        border-color: #e2e8f0 !important;
    }

    .bg-blue-900\/20 { /* أيقونة المبلغ في التحصيل */
        background-color: #eff6ff !important;
        color: #2563eb !important;
    }

    /* 7. الأجزاء الثابتة والخلفيات المظلمة */
    .bg-slate-950\/90 {
        background-color: rgba(15, 23, 42, 0.4) !important;
    }

    .sticky.top-0 {
        background-color: rgba(255, 255, 255, 0.9) !important;
    }

    /* 8. شريط التمرير (Scrollbar) */
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1 !important;
    }
}
</style> --}}
    {{-- SweetAlert --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Font Awesome --}}
    <!-- <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
          crossorigin="anonymous" referrerpolicy="no-referrer" /> -->

   <title>@yield('title', 'CARLED Dashboard')</title>


    {{-- Tailwind + Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>

    [x-cloak]{display:none!important}
    </style>



{{-- <script src="https://cdn.onesignal.com/sdks/OneSignalSDK.js" async=""></script>
<script>
    window.OneSignal = window.OneSignal || [];
    OneSignal.push(function() {
        OneSignal.init({
            appId: "{{ $settings->app_id }}",
        });
    });
</script> --}}

</head>

<body class="bg-gray-900 text-gray-100 min-h-screen flex">

    {{-- ========================= --}}
    {{--        SIDEBAR           --}}
    {{-- ========================= --}}
   {{-- Sidebar حسب الدور --}}
@if(auth('accountant')->check())



@elseif(auth('web')->check())

    {{-- هنا لدينا نوعان داخل نفس الجدول: admin أو user --}}
    @php
        $role = auth('web')->user()->role ?? 'user';
    @endphp

    @if($role === 'admin')
        @include('dashboard.sidebars.admin')
    @else
        {{-- @include('dashboard.sidebars.user') --}}
    @endif

@endif




    <div class="flex-1 flex flex-col">

        {{-- ========================= --}}
        {{--          NAVBAR          --}}
        {{-- ========================= --}}
        <header>
          {{-- ========================= --}}
{{--         NAVBAR            --}}
{{-- ========================= --}}

@if(auth('accountant')->check())

    {{-- المحاسب --}}
    @include('dashboard.navbars.accountant')

@elseif(auth('web')->check())

    {{-- هنا لدينا نوعان داخل نفس جدول users: admin أو user --}}
    @php
        $role = auth('web')->user()->role ?? 'user';
    @endphp

    @if($role === 'admin')
        @include('dashboard.navbars.admin')
    @else
        @include('dashboard.navbars.user')
    @endif

@endif

        </header>
{{-- في layouts/app.blade.php أو أي مكان مناسب --}}
{{-- @if(session('subscription_warning'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle"></i>
        {{ session('subscription_warning.message') }}

        @if(session('subscription_warning.days_left') <= 3)
            <a href="{{ route('user.subscription') }}" class="alert-link">
                تجديد الاشتراك الآن
            </a>
        @endif

        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif --}}
        {{-- ========================= --}}
        {{--         CONTENT           --}}
        {{-- ========================= --}}
        <main class="flex-1 p-6">
            @yield('content')
        </main>

        {{-- ========================= --}}
        {{--          FOOTER           --}}
        {{-- ========================= --}}
        <footer class="mt-6">
            @include('dashboard.footer')
        </footer>

    </div>
    <script>
    @if (session('success'))
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: "{{ session('success') }}",
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        });
    @endif

    @if (session('error'))
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'error',
            title: "{{ session('error') }}",
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        });
    @endif
</script>


{{-- <script src="https://cdn.onesignal.com/sdks/OneSignalSDK.js" async=""></script>
<script>
    window.OneSignal = window.OneSignal || [];
    OneSignal.push(function() {
        OneSignal.init({
            appId: "{{ config('services.onesignal.app_id') }}",
            allowLocalhostAsSecureOrigin: true,
        });

        OneSignal.on('subscriptionChange', function (isSubscribed) {
            if (isSubscribed) {
                OneSignal.getUserId(function(playerId) {
                    if (playerId) {
                        fetch("{{ route('device.token.store') }}", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": "{{ csrf_token() }}"
                            },
                            body: JSON.stringify({
                                token: playerId
                            })
                        });
                    }
                });
            }
        });
    });
</script> --}}
@yield('scripts')

</body>
</html>
