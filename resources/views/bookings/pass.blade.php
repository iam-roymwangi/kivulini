<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digital Pass — {{ $booking->booking_reference }}</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background-color: #0f172a;
            color: #f1f5f9;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .pass {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border: 1px solid #fbbf24;
            border-radius: 1.25rem;
            max-width: 480px;
            width: 100%;
            overflow: hidden;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.6), 0 0 0 1px rgba(251, 191, 36, 0.15);
        }

        .pass-header {
            background: linear-gradient(135deg, #b45309 0%, #d97706 50%, #f59e0b 100%);
            padding: 1.75rem 2rem;
            position: relative;
        }

        .pass-header::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            right: 0;
            height: 24px;
            background: #1e293b;
            border-radius: 50% 50% 0 0 / 0 0 100% 100%;
        }

        .pass-label {
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.75);
            margin-bottom: 0.375rem;
        }

        .pass-title {
            font-size: 1.75rem;
            font-weight: 800;
            color: #fff;
            line-height: 1.2;
        }

        .pass-body {
            padding: 2rem;
        }

        .pass-reference {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px dashed rgba(251, 191, 36, 0.3);
        }

        .pass-reference .ref-label {
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #94a3b8;
            margin-bottom: 0.5rem;
        }

        .pass-reference .ref-value {
            font-size: 1.75rem;
            font-weight: 800;
            letter-spacing: 0.1em;
            color: #fbbf24;
            font-family: 'Courier New', Courier, monospace;
        }

        .pass-fields {
            display: grid;
            gap: 1.25rem;
        }

        .pass-field {
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
        }

        .field-label {
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: #64748b;
        }

        .field-value {
            font-size: 0.95rem;
            font-weight: 500;
            color: #e2e8f0;
            line-height: 1.4;
        }

        .pass-divider {
            height: 1px;
            background: rgba(251, 191, 36, 0.15);
            margin: 1.25rem 0;
        }

        .pass-footer {
            padding: 1rem 2rem;
            background: rgba(15, 23, 42, 0.6);
            border-top: 1px dashed rgba(251, 191, 36, 0.2);
            text-align: center;
            font-size: 0.7rem;
            color: #475569;
            letter-spacing: 0.05em;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            background: rgba(251, 191, 36, 0.1);
            border: 1px solid rgba(251, 191, 36, 0.3);
            color: #fbbf24;
            border-radius: 9999px;
            padding: 0.25rem 0.75rem;
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <div class="pass">
        <div class="pass-header">
            <div class="pass-label">Digital Pass</div>
            <div class="pass-title">{{ $booking->event->title }}</div>
        </div>

        <div class="pass-body">
            <div style="text-align:center;">
                <span class="badge">✓ Confirmed</span>
            </div>

            <div class="pass-reference">
                <div class="ref-label">Booking Reference</div>
                <div class="ref-value">{{ $booking->booking_reference }}</div>
            </div>

            <div class="pass-fields">
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
        </div>

        <div class="pass-footer">
            Present this pass at the event entrance &bull; Non-transferable
        </div>
    </div>
</body>
</html>
