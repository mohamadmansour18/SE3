<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>تنبيه أمني</title>
</head>
<body style="margin:0; padding:0; background:#f6f7fb; font-family:Arial, sans-serif; direction:rtl;">

@php
    // كلاس/ستايل لجزء LTR داخل نص عربي
    $ltrStyle = "direction:ltr; unicode-bidi:isolate; display:inline-block;";
@endphp

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f6f7fb; padding:20px;">
    <tr>
        <td align="center">
            <table width="600" cellpadding="0" cellspacing="0"
                   style="background:#ffffff; border-radius:10px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.06);">

                <!-- Header -->
                <tr>
                    <td style="background:#0f172a; color:#ffffff; padding:20px 24px; font-size:20px; font-weight:bold;">
                        تنبيه أمني
                    </td>
                </tr>

                <!-- Body -->
                <tr>
                    <td style="padding:24px; color:#111827; font-size:15px; line-height:1.8;">
                        <p style="margin-top:0;">
                            مرحبًا <strong>{{ $user->name }}</strong>،
                        </p>

                        <p>
                            لاحظنا <strong>محاولة/محاولات تسجيل دخول فاشلة</strong> إلى حسابك.
                            إذا كانت هذه المحاولات منك، يمكنك تجاهل هذه الرسالة.
                            وإذا لم تكن منك، فقد يكون هناك شخص يحاول الوصول إلى حسابك.
                        </p>

                        <div style="background:#f9fafb; border:1px solid #e5e7eb; border-radius:8px; padding:14px 16px; margin:16px 0;">
                            <h3 style="margin:0 0 10px; font-size:16px; color:#111827;">تفاصيل المحاولة</h3>

                            <p style="margin:6px 0;">
                                <strong>البريد المستخدم:</strong>
                                <span dir="ltr" style="{{ $ltrStyle }}">
                                    {{ $details['email'] ?? $user->email }}
                                </span>
                            </p>

                            <p style="margin:6px 0;">
                                <strong>عنوان IP:</strong>
                                <span dir="ltr" style="{{ $ltrStyle }}">
                                    {{ $details['ip_address'] ?? 'N/A' }}
                                </span>
                            </p>

                            <p style="margin:6px 0;">
                                <strong>User Agent:</strong>
                                <span dir="ltr" style="{{ $ltrStyle }}">
                                    {{ $details['user_agent'] ?? 'N/A' }}
                                </span>
                            </p>

                            <p style="margin:6px 0;">
                                <strong>الوقت:</strong>
                                <span dir="ltr" style="{{ $ltrStyle }}">
                                    {{ $details['occurred_at'] ?? now()->toDateTimeString() }}
                                </span>
                            </p>

                            @if(!empty($details['location']))
                                <p style="margin:6px 0;">
                                    <strong>الموقع التقريبي:</strong>
                                    <span dir="ltr" style="{{ $ltrStyle }}">
                                        {{ $details['location'] }}
                                    </span>
                                </p>
                            @endif
                        </div>

                        <p><strong>إجراءات مقترحة:</strong></p>
                        <ul style="padding-right:18px; margin-top:6px;">
                            <li>غيّر كلمة المرور فورًا إذا لم تكن هذه المحاولة منك.</li>
                            <li>استخدم كلمة مرور قوية وغير مستخدمة في مواقع أخرى.</li>
                            <li>إذا استمرت المحاولات، تواصل مع فريق الدعم.</li>
                        </ul>

                        @php
                            $resetUrl = (config('app.frontend_url') ?: config('app.url')) . '/reset-password';
                        @endphp

                        <div style="text-align:center; margin:22px 0;">
                            <a href="{{ $resetUrl }}"
                               style="background:#2563eb; color:#ffffff; text-decoration:none; padding:12px 20px; border-radius:8px; font-weight:bold; display:inline-block;">
                                تغيير كلمة المرور
                            </a>
                        </div>

                        <p style="color:#6b7280; font-size:13px;">
                            إذا لم يعمل الزر يمكنك نسخ الرابط التالي:
                            <br>
                            <span dir="ltr" style="{{ $ltrStyle }}">
                                {{ $resetUrl }}
                            </span>
                        </p>

                        <p style="margin-bottom:0;">
                            مع تحياتنا،<br>
                            فريق الدعم الأمني – {{ config('app.name') }}
                        </p>
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="background:#f3f4f6; padding:14px 20px; color:#6b7280; font-size:12px; text-align:center;">
                        هذه رسالة تلقائية، الرجاء عدم الرد عليها.
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>

</body>
</html>
