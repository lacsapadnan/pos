<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Struk</title>
    {{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/paper-css/0.3.0/paper.css"> --}}
    <style>

        @page { size: A5 }
        .logo {
            width: 80px;
            height: 80px;
        }

        .total {
            font-weight: bold;
        }

        .note-list {
           font-size: 12px
        }

        table p {
            font-size: 12px
        }
        *{
            margin-top : 0;
            margin-bottom : 0;
            padding-right : 0px;
            padding-left : 0px;
        }

    </style>
</head>
<body class="A5 sheet">
    <div class="p-3">
        <table width="100%">
            <tr>
                <td rowspan="3" width="50%">
                    Nama Toko
                </td>
            </tr>
            <tr>
                <td>
                    <p>{{ $sell->warehouse->name }} </p>
                    <p>{{ $sell->warehouse->address }}</p>
                </td>
            </tr>
        </table>
    </div>
    <hr>
    <table width="100%">
        <tr>
            <td rowspan="3" width="65%">
                <p>Nama:  {{ $sell->customer->name }}</p>
            </td>
        </tr>
        <tr>
            <td>
                <p>Tanggal: {{ $sell->created_at }}</p>
                <p>Kasir: {{ $sell->cashier->name }}</p>
            </td>
        </tr>
    </table>
    <hr>
    <div class="p-3">
        <table width="100%">
            @foreach ($details as $detail)
            <tr>
                <td width="65%">
                    <p>Produk: {{ $detail->product->name }} </p>
                </td>
                <td width="10%">
                    <p>{{ $detail->quantity }}</p>
                </td>
                <td width="10%">
                    <p>{{ $detail->unit->name }}</p>
                </td>
                <td style="text-align:right">
                    <p>Rp{{ number_format($detail->price) }}</p>
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
