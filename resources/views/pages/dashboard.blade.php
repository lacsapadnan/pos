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
    var ctx = document.getElementById('kt_chartjs_1').getContext('2d');

    // Init chart but empty
    var myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: 'Top 10 Produk Terlaris',
                data: [],
                backgroundColor: '#50cd89',
            }]
        },
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
                x: { stacked: false },
                y: { stacked: false }
            }
        }
    });

    // Load data via AJAX
    fetch('{{ route('api.top-products') }}')
        .then(response => response.json())
        .then(data => {
            const labels = data.map(item => item.product ? item.product.name : 'Unknown');
            const values = data.map(item => item.total_sold);

            myChart.data.labels = labels;
            myChart.data.datasets[0].data = values;
            myChart.update();
        })
        .catch(error => console.error('Error fetching chart data:', error));
</script>
@endpush
