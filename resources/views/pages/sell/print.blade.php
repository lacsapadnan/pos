<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Struk</title>
    <style>
        @page {
            size: letter;
            margin: 0;
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
        .detail,
        .thankyou {
            text-align: center;
        }

        .alert,
        .address,
        table p {
            font-size: 12px;
        }

        .detail p {
            font-size: 14px
        }

        .footer .note p {
            font-size: 10px;
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

        body {
            width: 3.9in;
            height: 5.5in;
            margin: 0;
            padding: 0;
        }
    </style>
</head>

<body class="A5 sheet">
    <div class="p-3">
        <div class="header">
            <div class="store-name">
                <h3>{{ $sell->warehouse->name }}</h3>
            </div>
            <div class="address" style="margin-top: 24px">
                <p>{{ $sell->warehouse->address }}</p>
                <p>Telepon: {{ $sell->warehouse->phone }}</p>
            </div>
            <div class="detail" style="margin-top: 24px">
                <p>No: {{ $sell->order_number }}, Tanggal: {{ $sell->updated_at }}</p>
                <p>Customer: {{ $sell->customer->name }}</p>
            </div>
        </div>
    </div>
    <hr class="dotted-hr">
    <table width="100%">
        <tr>
            <td width="20%">
                <p>Qty</p>
            </td>
            <td width="35%">
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
                        <p>{{ $detail->quantity }}  {{ $detail->unit->name }} </p>
                    </td>
                    <td width="40%">
                        <p>{{ $detail->product->name }}</p>
                        @if($detail->product->hadiah != null)
                            <p>*{{ $detail->product->hadiah }}</p>
                        @endif
                    </td>
                    <td width="15%">
                        <p>{{ number_format($detail->price) }}</p>
                    </td>
                    <td width="10%">
                        <p>{{ $detail->diskon }}</p>
                    </td>
                    <td width="20%" style="text-align:right">
                        <p>{{ number_format($detail->price * $detail->quantity - $detail->diskon) }}</p>
                    </td>
                </tr>
            @endforeach
            <tr>
                <td colspan="4">
                    <p>{{ $totalQuantity }} macam</p>
                </td>
            </tr>
        </table>
        <hr class="dotted-hr">
        <table width="100%" style="margin: 8px 0px;">
            <tr>
                <td width="30%">
                    <p>Tanda Terima:</p>
                </td>
                <td width="30%" style="text-align:center">
                    <p>Kasir:</p>
                </td>
                <td width="50%" style="text-align:right">
                    <p>Total: {{ number_format($sell->grand_total) }}</p>
                </td>
            </tr>
            <tr>
                <td width="30%"></td>
                <td width="30%"></td>
                <td width="50%" style="text-align:right">
                    @if($sell->payment_method == 'transfer')
                        <p>Transfer: {{ number_format($sell->transfer) }}</p>
                    @elseif($sell->payment_method == 'cash')
                        <p>Cash: {{ number_format($sell->cash) }}</p>
                    @else
                        <p>Cash: {{ number_format($sell->cash) }}</p>
                        <p>Transfer: {{ number_format($sell->transfer) }}</p>
                    @endif
                </td>
            </tr>
            <tr>
                <td width="30%">
                    (...............)
                </td>
                <td width="30%" style="text-align:center">
                    <p>{{ $sell->cashier->name }}</p>
                </td>
                <td width="50%" style="text-align:right">
                    <p>Kembali: {{ number_format($sell->change) ?? 0 }}</p>
                </td>
            </tr>
        </table>
        <hr class="dotted-hr">
        <div class="footer">
            <div class="note">
                <p>Cara bayar: {{ $sell->payment_method }} *Nota: Copy, Tanggal cetak: {{ now() }}</p>
                <br>
                <p>Pembayaran transfer ke rekening <span style="font-weight: bold">BCA</span></p>
                <p style="font-weight: bold">7285132827 a/n Andreas Jati Perkasa.</p>
                <p>Selain No. Rek tersebut dianggap belum bayar.</p>
            </div>
            <div class="alert">
                <div class="alert" style="margin-top: 6px">
                    <p style="font-weight: bold">Perhatian:</p>
                    <p>Barang yang sudah dibeli tidak dapat dikembalikan</p>
                </div>
            </div>
            <div class="thankyou">
                <p style="font-weight: bold">TERIMA KASIH</p>
            </div>
        </div>
    </div>
</body>

</html>
