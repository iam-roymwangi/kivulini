<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Pass — {{ $booking->booking_reference }}</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background: #f1f5f9;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .ticket {
            width: 100%;
            max-width: 520px;
            border-radius: 1.5rem;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15), 0 4px 16px rgba(0,0,0,0.08);
        }

        /* ── Header ── */
        .ticket-header {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #292524 100%);
            padding: 2rem 2rem 3rem;
            position: relative;
            overflow: hidden;
        }

        .ticket-header::before {
            content: '';
            position: absolute;
            top: -60px; right: -60px;
            width: 200px; height: 200px;
            background: rgba(251, 191, 36, 0.07);
            border-radius: 50%;
        }

        .ticket-header::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 32px;
            background: #ffffff;
            border-radius: 50% 50% 0 0 / 100% 100% 0 0;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            margin-bottom: 1.75rem;
        }

        .brand-logo {
            width: 36px;
            height: 36px;
            object-fit: contain;
            filter: brightness(0) invert(1);
        }

        .brand-name {
            font-size: 1rem;
            font-weight: 800;
            color: #f8fafc;
            letter-spacing: 0.02em;
        }

        .event-label {
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #fbbf24;
            margin-bottom: 0.5rem;
        }

        .event-title {
            font-size: 1.625rem;
            font-weight: 800;
            color: #ffffff;
            line-height: 1.25;
        }

        /* ── Body ── */
        .ticket-body {
            background: #ffffff;
            padding: 1.75rem 2rem;
        }

        .confirmed-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            background: #dcfce7;
            border: 1px solid #bbf7d0;
            color: #15803d;
            border-radius: 9999px;
            padding: 0.3rem 0.875rem;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            margin-bottom: 1.5rem;
        }

        .ref-block {
            background: #f8fafc;
            border: 1.5px dashed #e2e8f0;
            border-radius: 0.875rem;
            padding: 1.25rem 1.5rem;
            text-align: center;
            margin-bottom: 1.75rem;
        }

        .ref-label {
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: #94a3b8;
            margin-bottom: 0.375rem;
        }

        .ref-value {
            font-size: 1.75rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            color: #0f172a;
            font-family: 'Courier New', Courier, monospace;
        }

        /* ── Detail grid ── */
        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.25rem 1.5rem;
        }

        .detail-grid .full-width {
            grid-column: 1 / -1;
        }

        .detail-item {}

        .detail-label {
            font-size: 0.625rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #94a3b8;
            margin-bottom: 0.25rem;
        }

        .detail-value {
            font-size: 0.9rem;
            font-weight: 600;
            color: #1e293b;
            line-height: 1.4;
        }

        .divider {
            height: 1px;
            background: #f1f5f9;
            margin: 1.5rem 0;
            grid-column: 1 / -1;
        }

        /* ── Footer ── */
        .ticket-footer {
            background: #f8fafc;
            border-top: 1.5px dashed #e2e8f0;
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .footer-text {
            font-size: 0.7rem;
            color: #94a3b8;
            letter-spacing: 0.04em;
        }

        .footer-brand {
            font-size: 0.7rem;
            font-weight: 700;
            color: #64748b;
            letter-spacing: 0.06em;
        }
    </style>
</head>
<body>
    <div class="ticket">
        <!-- Header -->
        <div class="ticket-header">
            <div class="brand">
                <img src="{{ asset('assets/images/kivulini_logo.png') }}" alt="Kivulini" class="brand-logo" />
                <span class="brand-name">Kivulini Adventures</span>
            </div>
            <div class="event-label">Your Event Pass</div>
            <div class="event-title">{{ $booking->event->title }}</div>
        </div>

        <!-- Body -->
        <div class="ticket-body">
            <div>
                <span class="confirmed-badge">&#10003; Booking Confirmed</span>
            </div>

            <div class="ref-block">
                <div class="ref-label">Booking Reference</div>
                <div class="ref-value">{{ $booking->booking_reference }}</div>
            </div>

            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">Date</div>
                    <div class="detail-value">{{ $booking->event->start_date->format('D, M j Y') }}</div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Slots</div>
                    <div class="detail-value">{{ $booking->quantity }} {{ Str::plural('ticket', $booking->quantity) }}</div>
                </div>

                <div class="detail-item full-width">
                    <div class="detail-label">Location</div>
                    <div class="detail-value">{{ $booking->event->location }}</div>
                </div>

                <div class="divider"></div>

                <div class="detail-item">
                    <div class="detail-label">Attendee</div>
                    <div class="detail-value">{{ $booking->contact_name }}</div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Phone</div>
                    <div class="detail-value">{{ $booking->contact_phone }}</div>
                </div>

                <div class="detail-item full-width">
                    <div class="detail-label">Email</div>
                    <div class="detail-value">{{ $booking->contact_email }}</div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="ticket-footer">
            <span class="footer-text">Present this pass at the event entrance &bull; Non-transferable</span>
            <span class="footer-brand">Kivulini Adventures</span>
        </div>
    </div>
</body>
</html>
