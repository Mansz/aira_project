@extends('admin.layouts.stisla')
@section('title', 'Dashboard')




@section('content')
<div class="row page-titles mx-0">
    <div class="col p-md-0">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="javascript:void(0)">Dashboard</a></li>
            <li class="breadcrumb-item active"><a href="javascript:void(0)">Home</a></li>
        </ol>
    </div>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-3 col-sm-6">
            <div class="card gradient-1">
                <div class="card-body">
                    <h3 class="card-title text-white">Products</h3>
                    <div class="d-inline-block">
                        <h2 class="text-white">{{ $stats['total_products'] ?? 0 }}</h2>
                        <p class="text-white mb-0">Total Products</p>
                    </div>
                    <span class="float-right display-5 opacity-5"><i class="fa fa-shopping-cart"></i></span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="card gradient-2">
                <div class="card-body">
                    <h3 class="card-title text-white">Revenue</h3>
                    <div class="d-inline-block">
                        <h2 class="text-white">Rp {{ number_format($stats['total_revenue'] ?? 0, 0, ',', '.') }}</h2>
                        <p class="text-white mb-0">Total Revenue</p>
                    </div>
                    <span class="float-right display-5 opacity-5"><i class="fa fa-money"></i></span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="card gradient-3">
                <div class="card-body">
                    <h3 class="card-title text-white">Orders</h3>
                    <div class="d-inline-block">
                        <h2 class="text-white">{{ $stats['active_orders'] ?? 0 }}</h2>
                        <p class="text-white mb-0">Active Orders</p>
                    </div>
                    <span class="float-right display-5 opacity-5"><i class="fa fa-shopping-bag"></i></span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="card gradient-4">
                <div class="card-body">
                    <h3 class="card-title text-white">Live Streams</h3>
                    <div class="d-inline-block">
                        <h2 class="text-white">{{ $stats['live_streams'] ?? 0 }}</h2>
                        <p class="text-white mb-0">Active Streams</p>
                    </div>
                    <span class="float-right display-5 opacity-5"><i class="fa fa-video-camera"></i></span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title">Monthly Orders ({{ date('Y') }})</h4>
                    </div>
                    <canvas id="monthlyOrdersChart" width="500" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Best Selling Products</h4>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Total Sold</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stats['best_selling_products'] ?? [] as $product)
                                <tr>
                                    <td>{{ $product->product->name }}</td>
                                    <td>{{ $product->total_sold }}</td>
                                    <td>Rp {{ number_format($product->total_sold * $product->product->price, 0, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center">No products found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Recent Transactions</h4>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stats['recent_transactions'] ?? [] as $order)
                                <tr>
                                    <td><a href="{{ route('admin.orders.show', $order->id) }}">#{{ $order->id }}</a></td>
                                    <td>{{ $order->user->name }}</td>
                                    <td>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                                    <td>
                                        <span class="label gradient-{{ $order->status == 'completed' ? '1' : '2' }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">No transactions found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(isset($activeStream))
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Live Stream Status</h4>
                    @if($activeStream)
                        <div class="alert alert-success">
                            <h4><i class="icon-camera"></i> Live Stream Active!</h4>
                            <p>Title: {{ $activeStream->title }}</p>
                            <p>Viewers: <span id="viewer-count">{{ $activeStream->viewer_count }}</span></p>
                            <p>Duration: <span id="stream-duration">{{ $activeStream->created_at->diffForHumans() }}</span></p>
                        </div>
                        <a href="{{ route('admin.streaming.dashboard') }}" class="btn btn-primary">Go to Streaming Dashboard</a>
                    @else
                        <div class="alert alert-info">
                            <h4><i class="icon-info"></i> No Active Stream</h4>
                            <p>Start a new live stream to engage with your customers!</p>
                        </div>
                        <a href="{{ route('admin.streaming.create') }}" class="btn btn-success">Start New Stream</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Monthly Orders Chart
const monthlyOrdersCtx = document.getElementById('monthlyOrdersChart').getContext('2d');
const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
const monthlyData = @json($stats['monthly_orders']);

window.monthlyOrdersChart = new Chart(monthlyOrdersCtx, {
    type: 'bar',
    data: {
        labels: monthNames,
        datasets: [{
            label: 'Orders',
            data: monthNames.map((_, index) => monthlyData[index + 1] || 0),
            backgroundColor: 'rgba(60, 141, 188, 0.8)',
            borderColor: 'rgba(60, 141, 188, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});

// Real-time updates using AJAX
function updateDashboardStats() {
    $.ajax({
        url: '{{ route("admin.dashboard.stats") }}',
        method: 'GET',
        success: function(response) {
            // Update stats
            $('.active-orders-count').text(response.active_orders);
            $('.live-viewers-count').text(response.live_viewers);
            $('.total-transactions-count').text(response.total_transactions);
            
            // Update recent orders table
            let ordersHtml = '';
            response.recent_orders.forEach(function(order) {
                ordersHtml += `
                    <tr>
                        <td><a href="/admin/orders/${order.id}">#${order.id}</a></td>
                        <td>${order.user.name}</td>
                        <td>Rp ${number_format(order.total_amount)}</td>
                        <td><span class="badge badge-${order.status == 'completed' ? 'success' : 'warning'}">${order.status}</span></td>
                    </tr>
                `;
            });
            $('#recent-orders-table tbody').html(ordersHtml);

            // Update monthly orders chart
            if (window.monthlyOrdersChart) {
                window.monthlyOrdersChart.data.datasets[0].data = monthNames.map((_, index) => 
                    response.monthly_orders[index + 1] || 0
                );
                window.monthlyOrdersChart.update();
            }

            // Update best selling products table
            let productsHtml = '';
            response.best_selling_products.forEach(function(product) {
                productsHtml += `
                    <tr>
                        <td>${product.product.name}</td>
                        <td>${product.total_sold}</td>
                        <td>Rp ${number_format(product.total_sold * product.product.price)}</td>
                    </tr>
                `;
            });
            $('#best-selling-products-table tbody').html(productsHtml);
        }
    });
}

// Update stats every 30 seconds
setInterval(updateDashboardStats, 30000);

function number_format(number) {
    return new Intl.NumberFormat('id-ID').format(number);
}
</script>
@endpush