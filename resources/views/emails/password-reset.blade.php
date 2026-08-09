@extends('emails.layouts.transactional')

@section('title', 'איפוס סיסמה - '.$appName)

@section('content')
    <h1 style="margin:0 0 16px;font-size:18px;color:#1A1A1E;text-align:center;direction:rtl;">איפוס סיסמה</h1>
    <p style="margin:0 0 12px;font-size:14px;line-height:1.7;color:#4B5563;text-align:right;direction:rtl;">
        שלום <span style="unicode-bidi:isolate;font-weight:600;color:#1A1A1E;">{{ $userName }}</span>,
    </p>
    <p style="margin:0 0 20px;font-size:14px;line-height:1.7;color:#4B5563;text-align:right;direction:rtl;">
        קיבלנו בקשה לאיפוס הסיסמה של החשבון
        <span dir="ltr" style="unicode-bidi:isolate;">{{ $email }}</span>
        ב<span style="unicode-bidi:isolate;">{{ $appName }}</span>. לחצו על הכפתור למטה כדי לבחור סיסמה חדשה.
    </p>
    <p style="margin:0 0 24px;text-align:center;direction:rtl;">
        <a href="{{ $resetUrl }}" style="display:inline-block;background:#4F46E5;color:#FFFFFF;text-decoration:none;font-size:15px;font-weight:700;padding:12px 32px;border-radius:8px;text-align:center;direction:rtl;">
            איפוס סיסמה
        </a>
    </p>
    <p style="margin:0 0 20px;text-align:center;direction:rtl;">
        <a href="{{ $resetUrl }}" dir="ltr" style="font-size:12px;color:#6B7280;word-break:break-all;unicode-bidi:isolate;">{{ $resetUrl }}</a>
    </p>
    <p style="margin:0;font-size:12px;line-height:1.7;color:#6B7280;text-align:right;direction:rtl;">
        קישור זה תקף למשך 60 דקות בלבד. אם לא ביקשת איפוס סיסמה, ניתן להתעלם מהודעה זו.
    </p>
@endsection
