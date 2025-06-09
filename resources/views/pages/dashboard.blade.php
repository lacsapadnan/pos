@extends('layouts.dashboard')

@section('title', 'Dashboard')

@section('menu-title', 'Dashboard')
@section('content')
    @include('components.alert')
    <div class="mt-5 border-0 card card-p-0 card-flush">
        <h2>Selamat datang, {{ auth()->user()->name }}</h2>
        <h3>Cabang {{ auth()->user()->warehouse->name }}</h3>
    </div>

    <div class="mt-5">
        <canvas id="kt_chartjs_1" class="mh-400px"></canvas>
    </div>
@endsection

@push('addon-script')
    <script>
        var ctx = document.getElementById('kt_chartjs_1');

        // Define fonts
        var fontFamily = KTUtil.getCssVariableValue('--bs-font-sans-serif');

        // Get PHP data into JS
        const topProducts = @json($topProducts);

        // Prepare labels and data
        const labels = topProducts.map(item => item.product ? item.product.name : 'Unknown');
        const dataValues = topProducts.map(item => item.total_sold);

        // Chart data
        const data = {
            labels: labels,
            datasets: [{
                label: 'Top 10 Produk Terlaris',
                data: dataValues,
                backgroundColor: '#50cd89',
            }],
        };

        // Chart config
        const config = {
            type: 'bar',
            data: data,
            options: {
                plugins: {
                    title: {
                        display: true,
                        text: 'Top 10 Produk Terlaris'
                    }
                },
                responsive: true,
                interaction: {
                    intersect: false,
                },
                scales: {
                    x: {
                        stacked: false,
                    },
                    y: {
                        stacked: false
                    }
                }
            },
            defaults: {
                global: {
                    defaultFont: fontFamily
                }
            }
        };

        var myChart = new Chart(ctx, config);
    </script>
@endpush
