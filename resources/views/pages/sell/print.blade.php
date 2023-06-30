<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Struk</title>
    {{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/paper-css/0.3.0/paper.css"> --}}
    <style>
        @page {
            size: A5
        }

        .logo {
            width: 80px;
            height: 80px;
        }

        .total {
            font-weight: bold;
        }

        .note-list {
            font-size: 12px;
        }

        .store-name,
        .address,
        .detail {
            text-align: center;
        }

        .alert p {
            font-size: 12px;
        }

        .address p {
            font-size: 12px;
        }

        .detail p {
            font-size: 14px
        }

        table p {
            font-size: 12px;
        }

        .dotted-hr {
            border: none;
            border-top: 2px dotted #000;
            background-color: transparent;
            height: 1px;
        }

        * {
            margin-top: 0;
            margin-bottom: 0;
            padding-right: 0px;
            padding-left: 0px;
        }
    </style>
</head>

<body class="A5 sheet">
    <div class="p-3">
        <div class="header">
            <div class="store-name">
                <h3>SETIA KULAKAN</h3>
            </div>
            <div class="alert" style="margin-top: 10px">
                <p style="font-weight: bold">Perhatian:</p>
                <p>Barang yang sudah dibeli tidak dapat dikembalikan</p>
            </div>
            <div class="address" style="margin-top: 24px">
                <p>{{ $sell->warehouse->address }}</p>
                <p>Telepon: {{ $sell->warehouse->phone }}</p>
            </div>
            <div class="detail" style="margin-top: 24px">
                <p>No: {{ $sell->order_number }}, Tanggal: {{ $sell->transaction_date }}</p>
                <p>Customer: {{ $sell->customer->name }}</p>
            </div>
        </div>
    </div>
    <hr  class="dotted-hr">
    <table width="100%">
        <tr>
            <td width="15%">
                <p>Qty</p>
            </td>
            <td width="40%">
                <p>Barang</p>
            </td>
            <td width="15%">
                <p>Harga</p>
            </td>
            <td width="10%">
                <p>Diskon</p>
            </td>
            <td width="20%" style="text-align:right">
                <p>Jumlah</p>
            </td>
        </tr>
    </table>
    <hr class="dotted-hr">
    <div class="p-3">
        <table width="100%">
            @foreach ($details as $detail)
                <tr>
                    <td width="15%">
                        <p>{{ $detail->quantity }}{{ $detail->unit->name }} </p>
                    </td>
                    <td width="40%">
                        <p>{{ $detail->product->name }}</p>
                    </td>
                    <td width="15%">
                        <p>{{ number_format($detail->price) }}</p>
                    </td>
                    <td width="10%">
                        <p>{{ $detail->diskon }}</p>
                    </td>
                    <td width="20%" style="text-align:right">
                        <p>{{ number_format($detail->price * $detail->quantity) }}</p>
                    </td>
                </tr>
            @endforeach
            <tr>
                <td colspan=3 style="text-align:right">
                    <p class="total">Total harga: Rp{{ number_format($sell->grand_total) }}</p>
                    <p class="total">Bayar: Rp{{ number_format($sell->pay) }}</p>
                    <p class="total">Kembali: Rp{{ number_format($sell->change) ?? 0 }}</p>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>
