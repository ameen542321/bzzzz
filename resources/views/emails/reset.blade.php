<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إعادة تعيين كلمة المرور - CARLED</title>
</head>

<body style="margin:0; padding:0; background:#0f172a; font-family: 'Tahoma', sans-serif; direction: rtl; text-align:right;">

    <table width="100%" cellpadding="0" cellspacing="0" style="background:#0f172a; padding:40px 0;">
        <tr>
            <td align="center">

                <!-- Container -->
                <table width="600" cellpadding="0" cellspacing="0" style="background:#1e293b; border-radius:12px; padding:40px; color:#fff;">

                    <!-- Logo -->
                    <tr>
                        <td align="center" style="padding-bottom:20px;">
                            <h1 style="margin:0; font-size:28px; color:#38bdf8;">CARLED</h1>
                        </td>
                    </tr>

                    <!-- Title -->
                    <tr>
                        <td style="font-size:20px; font-weight:bold; padding-bottom:10px;">
                            إعادة تعيين كلمة المرور
                        </td>
                    </tr>

                    <!-- Message -->
                    <tr>
                        <td style="font-size:16px; line-height:1.8; padding-bottom:25px; color:#cbd5e1;">
                            لقد تلقينا طلبًا لإعادة تعيين كلمة المرور الخاصة بحسابك.
                            إذا لم تقم بهذا الطلب، يمكنك تجاهل هذه الرسالة.
                        </td>
                    </tr>

                    <!-- Button -->
                    <tr>
                        <td align="center" style="padding-bottom:30px;">
                            <a href="{{ $resetUrl }}"
                               style="background:#38bdf8; color:#0f172a; padding:14px 28px; border-radius:8px; text-decoration:none; font-size:16px; font-weight:bold;">
                                إعادة تعيين كلمة المرور
                            </a>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="font-size:14px; color:#64748b; line-height:1.6;">
                            إذا لم يعمل الزر أعلاه، يمكنك نسخ الرابط التالي ولصقه في المتصفح:
                            <br>
                            <span style="color:#94a3b8;">{{ $resetUrl }}</span>
                        </td>
                    </tr>

                </table>
                <!-- End Container -->

            </td>
        </tr>
    </table>

</body>
</html>
