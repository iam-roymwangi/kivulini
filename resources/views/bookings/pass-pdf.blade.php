<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Digital Pass — {{ $booking->booking_reference }}</title>
    <style>
        body {
            background-color: #0f172a;
            color: #f1f5f9;
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 40px 0;
            text-align: center;
        }

        .pass-container {
            width: 450px;
            margin: 0 auto;
            background-color: #1e293b;
            border: 2px solid #fbbf24;
            border-radius: 20px;
            overflow: hidden;
            text-align: left;
        }

        .pass-header {
            background-color: #d97706;
            padding: 30px;
            color: #ffffff;
            border-bottom: 2px solid #fbbf24;
        }

        .pass-label {
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 5px;
        }

        .pass-title {
            font-size: 24px;
            font-weight: bold;
            color: #ffffff;
            line-height: 1.2;
        }

        .pass-body {
            padding: 30px;
        }

        .badge-container {
            text-align: center;
            margin-bottom: 20px;
        }

        .badge {
            display: inline-block;
            background-color: rgba(251, 191, 36, 0.1);
            border: 1px solid rgba(251, 191, 36, 0.4);
            color: #fbbf24;
            border-radius: 9999px;
            padding: 5px 15px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .pass-reference {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px dashed rgba(251, 191, 36, 0.3);
        }

        .ref-label {
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #94a3b8;
            margin-bottom: 5px;
        }

        .ref-value {
            font-size: 28px;
            font-weight: bold;
            letter-spacing: 1px;
            color: #fbbf24;
        }

        .pass-field {
            margin-bottom: 18px;
        }

        .field-label {
            font-size: 9px;
            font-weight: bold;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #64748b;
            display: block;
            margin-bottom: 4px;
        }

        .field-value {
            font-size: 14px;
            color: #e2e8f0;
            line-height: 1.4;
        }

        .pass-divider {
            height: 1px;
            background-color: rgba(251, 191, 36, 0.15);
            margin: 20px 0;
        }

        .pass-footer {
            padding: 20px;
            background-color: #0f172a;
            border-top: 1px dashed rgba(251, 191, 36, 0.2);
            text-align: center;
            font-size: 10px;
            color: #475569;
            letter-spacing: 0.5px;
        }
    </style>
</head>
<body>
    <div class="pass-container">
        <div class="pass-header">
            <div class="pass-label">Digital Pass</div>
            <div class="pass-title">{{ $booking->event->title }}</div>
        </div>

        <div class="pass-body">
            <div class="badge-container">
                <span class="badge">✓ Confirmed</span>
            </div>

            <div class="pass-reference">
                <div class="ref-label">Booking Reference</div>
                <div class="ref-value">{{ $booking->booking_reference }}</div>
            </div>

            <div class="pass-field">
                <span class="field-label">Event</span>
                <span class="field-value">{{ $booking->event->title }}</span>
            </div>

            <div class="pass-field">
                <span class="field-label">Date</span>
                <span class="field-value">{{ $booking->event->start_date->format('D, M j Y') }}</span>
            </div>

            <div class="pass-field">
                <span class="field-label">Location</span>
                <span class="field-value">{{ $booking->event->location }}</span>
            </div>

            <div class="pass-divider"></div>

            <div class="pass-field">
                <span class="field-label">Attendee</span>
                <span class="field-value">{{ $booking->contact_name }}</span>
            </div>

            <div class="pass-field">
                <span class="field-label">Email</span>
                <span class="field-value">{{ $booking->contact_email }}</span>
            </div>
        </div>

        <div class="pass-footer">
            Present this pass at the event entrance &bull; Non-transferable
        </div>
    </div>
</body>
</html>
