<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Form Message</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f8fafc; margin: 0; padding: 24px; color: #1e293b; }
        .card { background: #ffffff; border-radius: 12px; max-width: 580px; margin: 0 auto; padding: 40px; border: 1px solid #e2e8f0; }
        .badge { display: inline-block; background: #fbbf24; color: #1e293b; font-size: 11px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; padding: 4px 10px; border-radius: 999px; margin-bottom: 16px; }
        h1 { font-size: 22px; font-weight: 800; margin: 0 0 24px; color: #0f172a; }
        .field { margin-bottom: 20px; }
        .label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #94a3b8; margin-bottom: 4px; }
        .value { font-size: 15px; color: #1e293b; }
        .message-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; white-space: pre-wrap; font-size: 15px; line-height: 1.6; color: #334155; }
        .footer { margin-top: 32px; padding-top: 20px; border-top: 1px solid #e2e8f0; font-size: 12px; color: #94a3b8; text-align: center; }
    </style>
</head>
<body>
    <div class="card">
        <div class="badge">Contact Form</div>
        <h1>New Message from {{ $senderName }}</h1>

        <div class="field">
            <div class="label">From</div>
            <div class="value">{{ $senderName }} &lt;{{ $senderEmail }}&gt;</div>
        </div>

        @if($contactSubject)
        <div class="field">
            <div class="label">Subject</div>
            <div class="value">{{ $contactSubject }}</div>
        </div>
        @endif

        <div class="field">
            <div class="label">Message</div>
            <div class="message-box">{{ $body }}</div>
        </div>

        <div class="footer">
            Sent via the Kivulini Adventures contact form &mdash; {{ now()->format('d M Y, H:i') }}
        </div>
    </div>
</body>
</html>
