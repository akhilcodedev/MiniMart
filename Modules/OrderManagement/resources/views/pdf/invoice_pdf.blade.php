<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Invoice</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: #374151;
            margin: 0;
            padding: 20px;
            background-color: #f8fafc;
            /* Light background for better readability */
        }

        .container {
            width: 100%;
            max-width: 1280px;
            margin: 0 auto;
        }

        .flex {
            display: flex;
        }

        .flex-col {
            flex-direction: column;
        }

        .xl\:flex-row {
            flex-direction: row;
        }

        .flex-1 {
            flex: 1;
        }

        .justify-between {
            justify-content: space-between;
        }

        .items-center {
            align-items: center;
        }

        .pb-4 {
            padding-bottom: 1rem;
        }

        .border-b {
            border-bottom: 1px solid #e5e7eb;
        }

        .h-20 {
            height: 5rem;
        }

        .w-40 {
            width: 10rem;
        }

        .object-contain {
            object-fit: contain;
        }

        .mr-4 {
            margin-right: 1rem;
        }

        .mt-6 {
            margin-top: 1.5rem;
        }

        .mb-8 {
            margin-bottom: 2rem;
        }

        .text-3xl {
            font-size: 1.875rem;
            font-weight: 700;
            /* Make the invoice title bolder */
        }

        .font-bold {
            font-weight: bold;
        }

        .text-gray-800 {
            color: #1f2937;
        }

        .text-gray-600 {
            color: #4b5563;
        }

        .text-sm {
            font-size: 0.875rem;
        }

        .text-base {
            font-size: 1rem;
        }

        .text-red-500 {
            color: #ef4444;
        }

        .text-xs {
            font-size: 0.75rem;
        }

        .bg-red-500 {
            background-color: #ef4444;
        }

        .rounded-full {
            border-radius: 9999px;
        }

        .w-2 {
            width: 0.5rem;
        }

        .h-2 {
            height: 0.5rem;
        }

        .mr-1 {
            margin-right: 0.25rem;
        }

        .mb-4 {
            margin-bottom: 1rem;
        }

        .w-96 {
            width: 24rem;
        }

        .rounded-md {
            border-radius: 0.375rem;
        }

        .p-6 {
            padding: 1.5rem;
        }

        .bg-gray-50 {
            background-color: #f9fafb;
        }

        .bg-green-100 {
            background-color: #f0fdf4;
        }

        .text-green-800 {
            color: #15803d;
        }

        .text-yellow-800 {
            color: #854d0e;
        }

        .bg-yellow-100 {
            background-color: #fefce8;
        }

        .px-2\.5 {
            padding-left: 0.625rem;
            padding-right: 0.625rem;
        }

        .py-0\.5 {
            padding-top: 0.125rem;
            padding-bottom: 0.125rem;
        }

        .inline-block {
            display: inline-block;
        }

        .text-lg {
            font-size: 1.125rem;
        }

        .text-blue-500 {
            color: #3b82f6;
        }

        .text-green-500 {
            color: #22c55e;
        }

        .ml-1 {
            margin-left: 0.25rem;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            /* Subtle shadow for table */
        }

        .table th,
        .table td {
            border: 1px solid #e5e7eb;
            padding: 0.75rem;
        }

        .table th {
            background-color: #f3f4f6;
            font-weight: 600;
            text-align: left;
        }

        .text-left {
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .object-cover {
            object-fit: cover;
        }

        .w-12 {
            width: 3rem;
        }

        .h-12 {
            height: 3rem;
        }

        .mr-3 {
            margin-right: 0.75rem;
        }

        .overflow-hidden {
            overflow: hidden;
        }

        .font-medium {
            font-weight: 500;
        }

        .w-72 {
            width: 18rem;
        }

        .align-top {
            vertical-align: top;
        }

        .w-auto {
            width: auto;
        }

        .logo-container {
            display: inline-flex;
            align-items: center;
            width: auto;
        }

        .logo-content {
            width: 100%;
            padding-right: 2rem;

        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 5px;
            display: inline-block;
            font-size: 0.8rem;
            font-weight: 600;
            margin-right: 5px;
        }

        .status-badge.approved {
            background-color: #e6f7ed;
            color: #3e8865;
        }

        .status-badge.pending {
            background-color: #fff0e8;
            color: #c27c3d;
        }

        .payment-details {
            margin-bottom: 2rem;
            background-color: #fff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.08);
        }

        .payment-details h6 {
            margin-bottom: 1rem;
            font-weight: bold;
            font-size: 1.2rem;
            color: #4b5563;
        }

        .payment-details .detail-item {
            margin-bottom: 1rem;
            display: flex;
            flex-direction: column;
        }

        .payment-details .detail-item>div:first-child {
            font-size: 0.9rem;
            color: #777;
        }

        .payment-details .detail-item>div:last-child {
            font-size: 1rem;
            font-weight: 600;
            color: #333;
        }

        .project-details {
            margin-bottom: 2rem;
            background-color: #fff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.08);
        }

        .project-details h6 {
            margin-bottom: 1rem;
            font-weight: bold;
            font-size: 1.2rem;
            color: #4b5563;
        }

        .project-details .detail-item {
            margin-bottom: 1rem;
            display: flex;
            flex-direction: column;
        }

        .project-details .detail-item>div:first-child {
            font-size: 0.9rem;
            color: #777;
        }

        .project-details .detail-item>div:last-child {
            font-size: 1rem;
            font-weight: 600;
            color: #333;
        }
    </style>
</head>

<body class="p-8">
    <div class="invoice-header">
        <div class="flex items-center pb-4 border-b">
            <div class="flex items-center  w-auto logo-container justify-start">
                <img alt="Logo"
                    src="data:image/svg+xml;base64,{{ base64_encode(file_get_contents(public_path('logo.svg'))) }}"
                    class="h-20 w-40 object-contain mr-4">
            </div>
        </div>

        <h2>Invoice #{{ $invoiceData['invoice_number'] }}</h2>
    </div>

    <div class="customer-details">
        <p><strong>Customer:</strong> {{ $invoiceData['first_name'] }} {{ $invoiceData['last_name'] }}</p>
        <p><strong>Order Number:</strong> {{ $invoiceData['order_number'] }}</p>
        <p><strong>Date:</strong> {{ $invoiceData['created_at'] }}</p>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Product</th>
                <th>SKU</th>
                <th>Qty</th>
                <th>Unit Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoiceData['items'] as $item)
                <tr>
                    <td>{{ $item['product_name'] }}</td>
                    <td class="text-end">{{ $item['product_sku'] }}</td>
                    <td class="text-end">{{ $item['quantity'] }}</td>
                    <td class="text-end">{{ number_format($item['price'], 2) }}</td>
                    <td class="text-end">{{ number_format((float) $item['total'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="invoice-summary">
        <p><strong>Subtotal:</strong> {{ number_format($invoiceData['subtotal'], 2) }}</p>

        @if($invoiceData['tax_type'] === 'exclusive')
            <p><strong>VAT :</strong> {{ $invoiceData['vat_percentage'] }}%</p>
        @elseif($invoiceData['tax_type'] === 'inclusive')
            <p><strong>VAT :</strong> {{ number_format($invoiceData['vat_amount'], 2) }}</p>
        @endif

        <p><strong>Shipping Cost:</strong> {{ number_format($invoiceData['shipping_cost'], 2) }}</p>
        <p><strong>Discount:</strong> {{ number_format($invoiceData['discount_amount'], 2) }}</p>
        <p><strong>Grand Total:</strong> {{ number_format($invoiceData['grand_total'], 2) }}</p>
    </div>


</body>

</html>