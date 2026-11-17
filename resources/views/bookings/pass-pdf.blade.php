<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Kivulini Pass — {{ $booking->booking_reference }}</title>
    <style>
        @page { margin: 0; }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 48px 40px;
            background-color: #efe6d6;
            color: #13261f;
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
        }

        table { border-collapse: collapse; width: 100%; }
        td { vertical-align: top; padding: 0; }

        .eyebrow {
            font-size: 7.5px;
            font-weight: bold;
            letter-spacing: 2.4px;
            text-transform: uppercase;
            color: #8a7a5e;
        }

        .mono { font-family: 'DejaVu Sans Mono', monospace; }
        .serif { font-family: 'DejaVu Serif', Georgia, serif; }

        /* ---------- Ticket shell ---------- */
        .ticket {
            position: relative;
            background-color: #fbf7ef;
            border-radius: 22px;
            overflow: hidden;
        }

        /* ---------- Header band ---------- */
        .band {
            background-color: #0f2e25;
            color: #f3ead8;
            padding: 22px 30px 20px;
        }

        .brand-name {
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 3.5px;
            text-transform: uppercase;
            color: #f3ead8;
        }

        .brand-sub {
            margin-top: 3px;
            font-size: 7.5px;
            letter-spacing: 2.4px;
            text-transform: uppercase;
            color: #9fb8a9;
        }

        .admit {
            display: inline-block;
            padding: 6px 14px;
            border: 1px solid #f5a524;
            border-radius: 30px;
            color: #f5a524;
            font-size: 8.5px;
            font-weight: bold;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .stripe td { height: 5px; }

        /* ---------- Hero ---------- */
        .hero { padding: 26px 30px 6px; }

        .category {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 4px;
            background-color: #f5a524;
            color: #0f2e25;
            font-size: 7.5px;
            font-weight: bold;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .title {
            margin-top: 12px;
            font-size: 30px;
            line-height: 1.12;
            font-weight: bold;
            color: #0f2e25;
        }

        /* ---------- Route (boarding-pass style) ---------- */
        .route { padding: 22px 30px 4px; }

        .place {
            margin-top: 5px;
            font-size: 15px;
            font-weight: bold;
            color: #13261f;
            line-height: 1.25;
        }

        .route-line {
            padding-top: 20px;
            text-align: center;
            color: #c98a1b;
            font-size: 11px;
            letter-spacing: 1px;
        }

        /* ---------- Facts grid ---------- */
        .facts { padding: 20px 30px 26px; }

        .fact {
            padding: 12px 14px;
            background-color: #f3ead8;
            border-radius: 10px;
        }

        .fact-value {
            margin-top: 5px;
            font-size: 12.5px;
            font-weight: bold;
            color: #0f2e25;
        }

        .fact-sub {
            margin-top: 2px;
            font-size: 9px;
            color: #6f6450;
        }

        /* ---------- Perforation ---------- */
        .perforation {
            position: relative;
            height: 0;
            border-top: 2px dashed #d9ccb2;
            margin: 0 26px;
        }

        .notch {
            position: absolute;
            top: -15px;
            width: 28px;
            height: 28px;
            border-radius: 14px;
            background-color: #efe6d6;
        }

        .notch-left { left: -42px; }
        .notch-right { right: -42px; }

        /* ---------- Stub ---------- */
        .stub { padding: 24px 30px 26px; }

        .ref {
            margin-top: 6px;
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 3px;
            color: #0f2e25;
        }

        .stub-value {
            margin-top: 5px;
            font-size: 11.5px;
            font-weight: bold;
            color: #13261f;
        }

        .stub-sub {
            margin-top: 2px;
            font-size: 9px;
            color: #6f6450;
        }

        .paid {
            display: inline-block;
            margin-top: 6px;
            padding: 5px 12px;
            border-radius: 30px;
            background-color: #0f2e25;
            color: #f5a524;
            font-size: 8px;
            font-weight: bold;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .amount {
            margin-top: 6px;
            font-size: 18px;
            font-weight: bold;
            color: #0f2e25;
        }

        .signature { margin-top: 20px; }
        .signature td { height: 6px; }

        /* ---------- Footer ---------- */
        .notes {
            padding: 16px 30px;
            background-color: #0f2e25;
            color: #b9cbbf;
            font-size: 8.5px;
            line-height: 1.6;
        }

        .notes strong { color: #f5a524; }

        .outside {
            margin-top: 16px;
            text-align: center;
            font-size: 8px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #8a7a5e;
        }
    </style>
</head>
<body>
    @php
        $event = $booking->event;
        $logoPath = public_path('assets/images/kivulini_logo.png');
        $logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,'.base64_encode(file_get_contents($logoPath)) : null;
        $category = $event->type ? \Illuminate\Support\Str::headline($event->type) : 'Adventure';
        $isPaid = $booking->payment_status === 'paid';

        // A colour band unique to each pass, derived from its reference.
        $palette = ['#0f2e25', '#f5a524', '#c98a1b', '#3f6b57', '#e2d3b4', '#7a4f1d'];
        $hash = md5($booking->booking_reference);
        $signature = [];
        for ($i = 0; $i < 24; $i++) {
            $signature[] = $palette[hexdec($hash[$i]) % count($palette)];
        }
    @endphp

    <div class="ticket">
        {{-- Header band --}}
        <div class="band">
            <table>
                <tr>
                    @if ($logoBase64)
                        <td style="width: 48px; vertical-align: middle;">
                            <img src="{{ $logoBase64 }}" alt="Kivulini" style="width: 38px; height: 38px;">
                        </td>
                    @endif
                    <td style="vertical-align: middle;">
                        <div class="brand-name">Kivulini Adventures</div>
                        <div class="brand-sub">Trip Pass &middot; Keep this with you</div>
                    </td>
                    <td style="vertical-align: middle; text-align: right;">
                        <span class="admit">Admit {{ $booking->quantity }}</span>
                    </td>
                </tr>
            </table>
        </div>

        <table class="stripe">
            <tr>
                <td style="background-color: #f5a524; width: 55%;"></td>
                <td style="background-color: #c98a1b; width: 25%;"></td>
                <td style="background-color: #3f6b57; width: 20%;"></td>
            </tr>
        </table>

        {{-- Hero --}}
        <div class="hero">
            <span class="category">{{ $category }}</span>
            <div class="title serif">{{ $event->title }}</div>
        </div>

        {{-- Route --}}
        <div class="route">
            <table>
                <tr>
                    <td style="width: 42%;">
                        <div class="eyebrow">Pickup</div>
                        <div class="place">{{ $event->pickup_location ?: 'Meet at venue' }}</div>
                    </td>
                    <td class="route-line" style="width: 16%;">&bull; &bull; &bull; &rarr;</td>
                    <td style="width: 42%; text-align: right;">
                        <div class="eyebrow">Destination</div>
                        <div class="place">{{ $event->location }}</div>
                    </td>
                </tr>
            </table>
        </div>

        {{-- Facts --}}
        <div class="facts">
            <table>
                <tr>
                    <td style="width: 33.33%; padding-right: 6px;">
                        <div class="fact">
                            <div class="eyebrow">Departs</div>
                            <div class="fact-value">{{ $event->start_date->format('D, M j Y') }}</div>
                            <div class="fact-sub">{{ $event->start_date->format('g:i A') }}</div>
                        </div>
                    </td>
                    <td style="width: 33.33%; padding: 0 3px;">
                        <div class="fact">
                            <div class="eyebrow">Returns</div>
                            <div class="fact-value">{{ $event->end_date ? $event->end_date->format('D, M j Y') : 'Same day' }}</div>
                            <div class="fact-sub">{{ $event->end_date ? $event->end_date->format('g:i A') : '—' }}</div>
                        </div>
                    </td>
                    <td style="width: 33.33%; padding-left: 6px;">
                        <div class="fact">
                            <div class="eyebrow">Guests</div>
                            <div class="fact-value">{{ $booking->quantity }} {{ \Illuminate\Support\Str::plural('Traveller', $booking->quantity) }}</div>
                            <div class="fact-sub">Lead: {{ \Illuminate\Support\Str::limit($booking->contact_name, 18) }}</div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        {{-- Perforation --}}
        <div class="perforation">
            <div class="notch notch-left"></div>
            <div class="notch notch-right"></div>
        </div>

        {{-- Stub --}}
        <div class="stub">
            <table>
                <tr>
                    <td style="width: 55%;">
                        <div class="eyebrow">Booking Reference</div>
                        <div class="ref mono">{{ $booking->booking_reference }}</div>

                        <table style="margin-top: 16px;">
                            <tr>
                                <td style="width: 50%; padding-right: 10px;">
                                    <div class="eyebrow">Lead Traveller</div>
                                    <div class="stub-value">{{ $booking->contact_name }}</div>
                                    <div class="stub-sub">{{ $booking->contact_phone }}</div>
                                </td>
                                <td style="width: 50%;">
                                    <div class="eyebrow">Email</div>
                                    <div class="stub-value" style="font-size: 10px;">{{ $booking->contact_email }}</div>
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td style="width: 45%; text-align: right;">
                        <div class="eyebrow">Amount Paid</div>
                        <div class="amount">KES {{ number_format((float) $booking->total_price) }}</div>
                        @if ($booking->payment_reference)
                            <div class="stub-sub mono" style="margin-top: 4px;">M-Pesa &middot; {{ $booking->payment_reference }}</div>
                        @endif
                        <span class="paid">{{ $isPaid ? '✓ Confirmed' : ucfirst($booking->payment_status) }}</span>
                    </td>
                </tr>
            </table>

            <table class="signature">
                <tr>
                    @foreach ($signature as $colour)
                        <td style="background-color: {{ $colour }};"></td>
                    @endforeach
                </tr>
            </table>
        </div>

        {{-- Notes --}}
        <div class="notes">
            <strong>Good to know:</strong>
            Arrive at the pickup point 30 minutes before departure &middot;
            Show this pass and a valid ID at check-in &middot;
            This pass is personal and non-transferable.
        </div>
    </div>

    <div class="outside">Kivulini Adventures &middot; Issued {{ now()->format('M j, Y') }}</div>
</body>
</html>
