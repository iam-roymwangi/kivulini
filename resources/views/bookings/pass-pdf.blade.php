<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Digital Pass — {{ $booking->booking_reference }}</title>
    <style>
        @page {
            margin: 20px;
        }

        body {
            background-color: #0b0f19;
            color: #f1f5f9;
            font-family: 'DejaVu Sans', Arial, sans-serif;
            margin: 0;
            padding: 20px 0;
            text-align: center;
        }

        .ticket-wrapper {
            width: 480px;
            margin: 0 auto;
            background-color: #111827;
            border: 1px solid #374151;
            border-radius: 16px;
            overflow: hidden;
            text-align: left;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
        }

        .ticket-header {
            background: #1e293b;
            padding: 24px 28px;
            border-bottom: 2px solid #f59e0b;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-logo-cell {
            width: 60px;
            vertical-align: middle;
        }

        .company-logo {
            width: 48px;
            height: 48px;
        }

        .header-text-cell {
            vertical-align: middle;
            padding-left: 12px;
        }

        .company-name {
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #f59e0b;
        }

        .ticket-subtitle {
            font-size: 10px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #9ca3af;
            margin-top: 2px;
        }

        .ticket-body {
            padding: 28px;
        }

        .status-badge-container {
            text-align: right;
        }

        .status-badge {
            display: inline-block;
            background-color: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.4);
            color: #10b981;
            border-radius: 20px;
            padding: 4px 14px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .event-title {
            font-size: 22px;
            font-weight: bold;
            color: #ffffff;
            line-height: 1.3;
            margin-top: 10px;
            margin-bottom: 20px;
        }

        .ref-box {
            background-color: #1f2937;
            border: 1px dashed #f59e0b;
            border-radius: 12px;
            padding: 16px;
            text-align: center;
            margin-bottom: 24px;
        }

        .ref-label {
            font-size: 9px;
            font-weight: bold;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #9ca3af;
            margin-bottom: 4px;
        }

        .ref-value {
            font-size: 26px;
            font-weight: bold;
            letter-spacing: 3px;
            color: #f59e0b;
        }

        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .details-table td {
            padding: 8px 0;
            vertical-align: top;
        }

        .field-label {
            font-size: 9px;
            font-weight: bold;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #6b7280;
            display: block;
            margin-bottom: 3px;
        }

        .field-value {
            font-size: 13px;
            font-weight: 500;
            color: #e5e7eb;
        }

        .divider {
            height: 1px;
            background-color: #374151;
            margin: 16px 0;
        }

        .ticket-footer {
            padding: 16px 24px;
            background-color: #0f172a;
            border-top: 1px solid #1f2937;
            text-align: center;
            font-size: 9px;
            color: #6b7280;
            letter-spacing: 0.5px;
        }
    </style>
</head>
<body>
    @php
        $logoPath = public_path('assets/images/kivulini_logo.png');
        $logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;
    @endphp

    <div class="ticket-wrapper">
        <!-- Header -->
        <div class="ticket-header">
            <table class="header-table">
                <tr>
                    @if($logoBase64)
                    <td class="header-logo-cell">
                        <img src="{{ $logoBase64 }}" alt="Kivulini" class="company-logo">
                    </td>
                    @endif
                    <td class="header-text-cell">
                        <div class="company-name">Kivulini Adventures</div>
                        <div class="ticket-subtitle">Official Event Pass</div>
                    </td>
                    <td class="status-badge-container">
                        <span class="status-badge">✓ {{ strtoupper($booking->payment_status === 'paid' ? 'Confirmed' : $booking->payment_status) }}</span>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Body -->
        <div class="ticket-body">
            <!-- Event Title -->
            <div class="event-title">{{ $booking->event->title }}</div>

            <!-- Booking Reference Box -->
            <div class="ref-box">
                <div class="ref-label">Booking Reference</div>
                <div class="ref-value">{{ $booking->booking_reference }}</div>
            </div>

            <!-- Details -->
            <table class="details-table">
                <tr>
                    <td style="width: 50%;">
                        <span class="field-label">Date & Time</span>
                        <span class="field-value">{{ $booking->event->start_date->format('D, M j Y @ g:i A') }}</span>
                    </td>
                    <td style="width: 50%;">
                        <span class="field-label">Location</span>
                        <span class="field-value">{{ $booking->event->location }}</span>
                    </td>
                </tr>
                @if($booking->event->pickup_location)
                <tr>
                    <td colspan="2">
                        <span class="field-label">Pickup Point</span>
                        <span class="field-value">{{ $booking->event->pickup_location }}</span>
                    </td>
                </tr>
                @endif
            </table>

            <div class="divider"></div>

            <table class="details-table">
                <tr>
                    <td style="width: 50%;">
                        <span class="field-label">Attendee</span>
                        <span class="field-value">{{ $booking->contact_name }}</span>
                    </td>
                    <td style="width: 50%;">
                        <span class="field-label">Reserved Slots</span>
                        <span class="field-value">{{ $booking->quantity }} {{ Str::plural('Ticket', $booking->quantity) }}</span>
                    </td>
                </tr>
                <tr>
                    <td style="width: 50%;">
                        <span class="field-label">Email</span>
                        <span class="field-value">{{ $booking->contact_email }}</span>
                    </td>
                    <td style="width: 50%;">
                        <span class="field-label">Phone</span>
                        <span class="field-value">{{ $booking->contact_phone }}</span>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Footer -->
        <div class="ticket-footer">
            Present this pass at entry &bull; Non-transferable &bull; Kivulini Adventures
        </div>
    </div>
</body>
</html>
