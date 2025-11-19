<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt - Sale #{{ $sale->id }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.2;
            margin: 0;
            padding: 10px;
        }
        .receipt-header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
        }
        .receipt-header strong {
            font-size: 14px;
        }
        .receipt-line {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        .items-table th {
            text-align: left;
            border-bottom: 1px solid #ddd;
            padding: 5px;
            font-weight: bold;
        }
        .items-table td {
            padding: 4px 5px;
            border-bottom: 1px solid #eee;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .amount-section {
            margin: 10px 0;
        }
        .amount-row {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
        }
        .total-section {
            margin-top: 15px;
            border-top: 2px solid #000;
            padding-top: 10px;
            font-weight: bold;
        }
        .payment-info {
            margin-top: 15px;
            border-top: 1px dashed #000;
            padding-top: 10px;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="receipt-header">
        <strong>ATIN Industrial Hardware Supply Incorporated</strong><br>
        Door 3 Corner Guerrero St., Ramon Magsaysay Ave.<br>
        Poblacion District, Davao City, 8000 Davao del Sur<br>
        (082) 286 6300
    </div>
    
    <div class="receipt-line"></div>
    <div class="text-center" style="margin: 8px 0;">
        <strong>S A L E S &nbsp; R E C E I P T</strong>
    </div>
    <div class="receipt-line"></div>
    
    <div>
        <strong>Transaction #:</strong> {{ $sale->id }}<br>
        <strong>Date:</strong> {{ $sale->sale_date ? $sale->sale_date->format('M d, Y') : now()->format('M d, Y') }}<br>
        <strong>Time:</strong> {{ $sale->sale_date ? $sale->sale_date->format('h:i A') : now()->format('h:i A') }}<br>
        <strong>Cashier:</strong> {{ $sale->user->full_name ?? 'N/A' }}<br>
        @if($sale->customer_name)
        <strong>Customer:</strong> {{ $sale->customer_name }}<br>
        @endif
        @if($sale->customer_contact)
        <strong>Contact:</strong> {{ $sale->customer_contact }}<br>
        @endif
    </div>
    
    <div class="receipt-line"></div>
    
    <table class="items-table">
        <thead>
            <tr>
                <th>QTY</th>
                <th>DESCRIPTION</th>
                <th class="text-right">PRICE</th>
                <th class="text-right">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $item)
            <tr>
                <td>{{ $item->quantity_sold }}</td>
                <td>{{ $item->product->name ?? 'N/A' }}</td>
                <td class="text-right">₱{{ number_format($item->unit_price, 2) }}</td>
                <td class="text-right">₱{{ number_format($item->quantity_sold * $item->unit_price, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="receipt-line"></div>
    
    @php
        $total = $sale->items->sum(function($item) {
            return $item->quantity_sold * $item->unit_price;
        });
        
        // VAT Reverse Calculation (12% VAT)
        $vatRate = 0.12;
        $vatableSales = $total / (1 + $vatRate);
        $vatAmount = $total - $vatableSales;
    @endphp
    
    <!-- Amount Breakdown -->
    <div class="amount-section">
        <div class="amount-row">
            <span>AMOUNT DUE:</span>
            <span>₱{{ number_format($total, 2) }}</span>
        </div>
        <div class="amount-row">
            <span>VAT SALES:</span>
            <span>₱{{ number_format($vatableSales, 2) }}</span>
        </div>
        <div class="amount-row">
            <span>VAT 12.00%:</span>
            <span>₱{{ number_format($vatAmount, 2) }}</span>
        </div>
    </div>
    
    <div class="total-section text-right">
        GRAND TOTAL: ₱{{ number_format($total, 2) }}
    </div>
    
    <div class="receipt-line"></div>
    <div class="text-center" style="margin: 8px 0;">
        <strong>P A Y M E N T &nbsp; I N F O</strong>
    </div>
    <div class="receipt-line"></div>
    
    <div class="payment-info">
        @if($sale->payment)
            @php $payment = $sale->payment; @endphp

            <strong>Method:</strong> {{ $payment->payment_method ?? 'N/A' }}<br>
            <strong>Amount Tendered:</strong> ₱{{ number_format($payment->amount_tendered ?? 0, 2) }}<br>
            <strong>Change Given:</strong> ₱{{ number_format($payment->change_given ?? 0, 2) }}<br>

            @if(!empty($payment->reference_no))
                <strong>Reference #:</strong> {{ $payment->reference_no }}<br>
            @endif

        @else
            <em>No payment data recorded.</em>
        @endif
    </div>
    
    <div class="receipt-line"></div>
    
    <div class="footer">
        Thank You For Shopping With Us!<br>
        Please Come Again.
    </div>

    <!-- Return/Exchange Policy -->
    <div class="return-policy">
        Keep this invoice for item return/exchange.<br>
        Return/exchange is acceptable within 7 days from purchase date.
    </div>
</body>
</html>