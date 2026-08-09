@extends('emails.layouts.transactional')

@section('title', 'ברוכים הבאים - '.$appName)

@section('content')
    <h1 style="margin:0 0 16px;font-size:20px;line-height:1.4;color:#1A1A1E;text-align:center;direction:rtl;">
        ברוכים הבאים ל<span style="unicode-bidi:isolate;">{{ $appName }}</span>!
    </h1>
    <p style="margin:0 0 12px;font-size:14px;line-height:1.7;color:#4B5563;text-align:right;direction:rtl;">
        שלום <span style="unicode-bidi:isolate;font-weight:600;color:#1A1A1E;">{{ $userName }}</span>,
    </p>
    <p style="margin:0 0 20px;font-size:14px;line-height:1.7;color:#4B5563;text-align:right;direction:rtl;">
        החשבון שלך נוצר בהצלחה. אנו שמחים שהצטרפת למשפחה שלנו, ומאחלים לכם שנה מלאה ברגעים משותפים.
    </p>
    <p style="margin:0 0 24px;text-align:center;direction:rtl;">
        <a href="{{ $dashboardUrl }}" style="display:inline-block;background:#4F46E5;color:#FFFFFF;text-decoration:none;font-size:15px;font-weight:700;padding:12px 32px;border-radius:8px;text-align:center;direction:rtl;">
            מעבר ללוח הבקרה
        </a>
    </p>
    <p style="margin:0 0 20px;text-align:center;direction:rtl;">
        <a href="{{ $dashboardUrl }}" dir="ltr" style="font-size:12px;color:#6B7280;word-break:break-all;unicode-bidi:isolate;">{{ $dashboardUrl }}</a>
    </p>
    <p style="margin:0 0 4px;font-size:14px;line-height:1.7;color:#1A1A1E;text-align:right;direction:rtl;">
        מה עכשיו?
    </p>
    <p style="margin:0 0 20px;font-size:13px;line-height:1.7;color:#4B5563;text-align:right;direction:rtl;">
        התחילו ביצירת לוח השנה המשפחתי הראשון שלכם, והוסיפו בני משפחה כדי לראות ימי הולדת וימי נישואין מופיעים אוטומטית.
    </p>
    <p style="margin:0;font-size:12px;line-height:1.7;color:#6B7280;text-align:right;direction:rtl;">
        אם קיבלתם הודעה זו בטעות, ניתן להתעלם ממנה.
    </p>
@endsection
