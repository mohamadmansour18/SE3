<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>رمز التحقق - {{ config('app.name') }}</title>
    <meta name="color-scheme" content="light dark">
    <meta name="supported-color-schemes" content="light dark">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700&display=swap');

        body {
            font-family: 'Tajawal', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f2f4f7;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.06);
        }

        h2 {
            color: #0d6efd;
            text-align: center;
            margin-bottom: 10px;
        }

        p {
            font-size: 16px;
            line-height: 1.7;
            text-align: center;
            margin: 10px 0;
        }

        .otp {
            font-size: 28px;
            font-weight: bold;
            letter-spacing: 10px;
            text-align: center;
            color: #0d6efd;
            background-color: #e9f2ff;
            border: 2px dashed #0d6efd;
            border-radius: 8px;
            padding: 15px;
            width: fit-content;
            margin: 20px auto;
            user-select: all;
        }

        .signature {
            text-align: center;
            margin-top: 40px;
            font-size: 15px;
            color: #666;
        }

        .footer {
            text-align: center;
            font-size: 13px;
            color: #999;
            margin-top: 30px;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }

        @media (prefers-color-scheme: dark) {
            body {
                background-color: #1e1e1e;
                color: #f1f1f1;
            }

            .container {
                background-color: #2c2c2c;
                color: #f1f1f1;
            }

            .otp {
                background-color: #112244;
                color: #91caff;
                border-color: #91caff;
            }

            p {
                color: #f1f1f1 !important;
            }

            .signature {
                color: #aaa;
            }

            .footer {
                color: #777;
                border-top: 1px solid #444;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <h2>مرحبًا {{ $name }}</h2>
    <p>: رمز التحقق الخاص بك</p>
    <div class="otp">{{ $code }}</div>
    <p>يرجى استخدام هذا الرمز خلال <strong>5 دقائق</strong> لضمان الأمان</p>
    <p>نحن سعداء بانضمامك إلى {{ config('app.name') }}</p>

    <div class="signature">
        مع تحيات<br>
        <strong>فريق {{ config('app.name') }}</strong>
    </div>

    <div class="footer">
        تم إرسال هذه الرسالة تلقائيًا – الرجاء عدم الرد.
    </div>
</div>
</body>
</html>
