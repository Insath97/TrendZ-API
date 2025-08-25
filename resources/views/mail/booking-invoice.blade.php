<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Invoice</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }

        body {
            background-color: #f5f7fa;
            padding: 16px;
            line-height: 1.4;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
        }

        .container {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
            position: relative;
        }

        .header {
            background: linear-gradient(135deg, #4a6cf7 0%, #82b8ff 100%);
            color: white;
            padding: 20px;
            text-align: center;
        }

        .logo {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .invoice-title {
            font-size: 16px;
            opacity: 0.9;
        }

        .content {
            padding: 20px;
        }

        .section {
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px solid #eaeaea;
        }

        .section:last-child {
            border-bottom: none;
        }

        .section-title {
            color: #4a6cf7;
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
        }

        .section-title i {
            margin-right: 8px;
            font-style: normal;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
        }

        .info-label {
            font-size: 11px;
            color: #888;
            text-transform: uppercase;
            font-weight: 500;
            margin-bottom: 2px;
        }

        .info-value {
            font-size: 14px;
            font-weight: 500;
            color: #333;
        }

        .services-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            font-size: 13px;
        }

        .services-table th {
            background: #f8f9fa;
            color: #555;
            padding: 8px;
            text-align: left;
            font-weight: 600;
            border-bottom: 1px solid #eaeaea;
        }

        .services-table td {
            padding: 8px;
            border-bottom: 1px solid #f0f0f0;
        }

        .services-table tr:last-child td {
            border-bottom: none;
        }

        .amount-section {
            background: #f8f9fa;
            padding: 16px;
            border-radius: 6px;
            margin-top: 16px;
            position: relative;
            overflow: hidden;
        }

        .amount-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 13px;
        }

        .total-row {
            font-weight: bold;
            color: #2e7d32;
            border-top: 1px solid #e0e0e0;
            margin-top: 6px;
            padding-top: 10px;
        }

        .footer {
            background: #2c3e50;
            color: white;
            padding: 20px;
            text-align: center;
            font-size: 12px;
        }

        .footer-links {
            margin: 12px 0;
            display: flex;
            justify-content: center;
            gap: 16px;
        }

        .footer-link {
            color: #82b8ff;
            text-decoration: none;
        }

        .thank-you {
            margin-top: 12px;
            color: #adb5bd;
            line-height: 1.5;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
            background: #e9f5e9;
            color: #2e7d32;
        }

        /* Paid Stamp Styles */
        .paid-stamp {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid #800020;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.9);
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            transform: rotate(15deg);
            z-index: 10;
        }

        .paid-stamp-content {
            text-align: center;
            color: #800020;
            font-weight: bold;
        }

        .paid-stamp-text {
            font-size: 16px;
            line-height: 1.2;
        }

        .paid-stamp-shop {
            font-size: 8px;
            margin-top: 4px;
            text-transform: uppercase;
        }

        .paid-stamp-date {
            font-size: 7px;
            margin-top: 2px;
        }

        /* Maroon color scheme adjustments */
        .maroon-text {
            color: #800020;
        }

        .maroon-bg {
            background-color: #800020;
        }

        .header {
            background: linear-gradient(135deg, #800020 0%, #600010 100%);
        }

        .section-title {
            color: #800020;
        }

        .services-table th {
            background: #f8e9e9;
            color: #800020;
        }

        @media (max-width: 480px) {
            .info-grid {
                grid-template-columns: 1fr;
            }

            .footer-links {
                flex-direction: column;
                gap: 8px;
            }

            .paid-stamp {
                width: 70px;
                height: 70px;
                top: 15px;
                right: 15px;
            }

            .paid-stamp-text {
                font-size: 14px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Paid Stamp -->
        <div class="paid-stamp">
            <div class="paid-stamp-content">
                <div class="paid-stamp-text">PAID</div>
                <div class="paid-stamp-shop">{{ $shop->name }}</div>
                <div class="paid-stamp-date">{{ \Carbon\Carbon::parse($booking->booking_date)->format('M d, Y') }}</div>
            </div>
        </div>

        <!-- Header -->
        <div class="header">
            <div class="logo">{{ $shop->name }}</div>
            <div class="invoice-title">Booking Confirmation #{{ $booking->booking_number }}</div>
        </div>

        <!-- Content -->
        <div class="content">
            <!-- Customer Information -->
            <div class="section">
                <div class="section-title">
                    <i>👤</i> Customer
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Name</span>
                        <span class="info-value">{{ $customer->name }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Contact</span>
                        <span class="info-value">{{ $customer->phone_number ?? $customer->email }}</span>
                    </div>
                </div>
            </div>

            <!-- Booking Details -->
            <div class="section">
                <div class="section-title">
                    <i>📅</i> Appointment Details
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Date & Time</span>
                        <span class="info-value">
                            {{ \Carbon\Carbon::parse($booking->booking_date)->format('M d, Y') }}
                            @if ($booking->slots->count() > 0)
                                at {{ \Carbon\Carbon::parse($booking->slots->first()->start_time)->format('h:i A') }}
                            @endif
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Barber</span>
                        <span class="info-value">{{ $booking->barber->name ?? 'Not Assigned' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Status</span>
                        <span class="info-value"><span class="status-badge">Completed</span></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Reference</span>
                        <span class="info-value">#{{ $booking->unique_reference }}</span>
                    </div>
                </div>
            </div>

            <!-- Services -->
            <div class="section">
                <div class="section-title">
                    <i>✂️</i> Services
                </div>
                <table class="services-table">
                    <thead>
                        <tr>
                            <th>Service</th>
                            <th>Duration</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($services as $service)
                            <tr>
                                <td>{{ $service->name }}</td>
                                <td>{{ $service->duration }}m</td>
                                <td>Rs. {{ number_format($service->price, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Payment Summary -->
            <div class="amount-section">
                <div class="section-title">
                    <i>💰</i> Payment Summary
                </div>

                <div class="amount-row">
                    <span>Subtotal:</span>
                    <span>Rs. {{ number_format($services->sum('price'), 2) }}</span>
                </div>

                @if ($shop->booking_fees > 0)
                    <div class="amount-row">
                        <span>Booking Fee:</span>
                        <span>Rs. {{ number_format($shop->booking_fees, 2) }}</span>
                    </div>
                @endif

                @if ($booking->tax_amount > 0)
                    <div class="amount-row">
                        <span>Tax ({{ $booking->tax_rate ?? 0 }}%):</span>
                        <span>Rs. {{ number_format($booking->tax_amount, 2) }}</span>
                    </div>
                @endif

                @if ($booking->discount_amount > 0)
                    <div class="amount-row" style="color: #d32f2f;">
                        <span>Discount:</span>
                        <span>- Rs. {{ number_format($booking->discount_amount, 2) }}</span>
                    </div>
                @endif

                <div class="amount-row total-row">
                    <span>Total Paid:</span>
                    <span>Rs. {{ number_format($booking->total_amount, 2) }}</span>
                </div>

                <div class="amount-row">
                    <span>Payment Status:</span>
                    <span style="color: #28a745; font-weight: 600;">Paid</span>
                </div>
            </div>

            <!-- Additional Notes -->
            @if ($booking->notes)
                <div class="section">
                    <div class="section-title">
                        <i>📝</i> Notes
                    </div>
                    <p style="color: #666; font-size: 13px; margin-top: 8px;">
                        {{ $booking->notes }}
                    </p>
                </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="footer">
            <p class="thank-you">
                Thank you for choosing {{ $shop->name }}! We appreciate your business.
            </p>
            <div class="footer-links">
                <a href="#" class="footer-link">Website</a>
                <a href="#" class="footer-link">Support</a>
                <a href="#" class="footer-link">Privacy</a>
            </div>
            <p style="margin-top: 12px; color: #adb5bd;">
                &copy; {{ date('Y') }} {{ $shop->name }}<br>
                {{ $shop->location->name }} | {{ $shop->phone_number }}
            </p>
        </div>
    </div>
</body>

</html>
