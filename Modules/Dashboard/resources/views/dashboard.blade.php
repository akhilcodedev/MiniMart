@extends('base::layouts.mt-main')

@section('content')

    <div class="post d-flex flex-column-fluid" id="kt_post">
        <div id="kt_content_container" class="container-xxl">
            <div class="row gy-5 g-xl-8">
                <div class="col-xl-4">
                    <div class="card card-flush card-hover animate__animated animate__fadeInUp">
                        <div class="card-body d-flex align-items-center">
                            <span class="svg-icon svg-icon-2hx svg-icon-primary me-5">
                                {{-- Replace with your actual user icon SVG --}}
                                <i class="bi bi-person"></i>
                            </span>
                            <div>
                                <div class="fw-bold fs-3">Total Users</div>
                                <div class="fw-semibold text-muted">{{ $totalUsers }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4">
                    <div class="card card-flush card-hover animate__animated animate__fadeInUp">
                        <div class="card-body d-flex align-items-center">
                            <span class="svg-icon svg-icon-2hx svg-icon-success me-5">
                                {{-- Replace with your actual product icon SVG --}}
                                <i class="bi bi-cart"></i>
                            </span>
                            <div>
                                <div class="fw-bold fs-3">Total Products</div>
                                <div class="fw-semibold text-muted">{{ $totalProducts }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4">
                    <div class="card card-flush card-hover animate__animated animate__fadeInUp">
                        <div class="card-body d-flex align-items-center">
                            <span class="svg-icon svg-icon-2hx svg-icon-warning me-5">
                                {{-- Replace with your actual order icon SVG --}}
                                <i class="bi bi-basket-fill"></i>
                            </span>
                            <div>
                                <div class="fw-bold fs-3">Total Orders</div>
                                <div class="fw-semibold text-muted">{{ $totalOrders }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Chart Card: Monthly Sales Overview --}}
            <div class="row mt-8">
                <div class="col-lg-12">
                    <div class="card card-flush overflow-hidden h-md-100 card-hover animate__animated animate__fadeInUp">
                        <!-- Header with title and toggle button -->
                        <div class="card-header py-5 d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="card-title m-0">
                                    <span class="card-label fw-bold text-dark">Orders Overview</span>
                                </h3>
                                <span class="text-gray-500 mt-1 fw-semibold fs-6">Toggle between Monthly & Daily</span>
                            </div>
                            <button id="toggleChart" class="btn btn-sm btn-primary">
                                Switch to Daily
                            </button>
                        </div>
                        <!-- Body with chart container -->
                        <div class="card-body d-flex flex-column pb-1 px-0">
                            <div id="orders_chart" style="height: 350px;"></div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Chart Card: Products Category Pie Chart --}}
            <div class="row mt-8">
                <div class="col-lg-6">
                     <div class="card card-flush overflow-hidden h-md-100 card-hover animate__animated animate__fadeInUp">
                         <div class="card-header py-5">
                             <h3 class="card-title align-items-start flex-column">
                                 <span class="card-label fw-bold text-dark">Products Category Distribution</span>
                                 <span class="text-gray-500 mt-1 fw-semibold fs-6">Products by Category</span>
                             </h3>
                         </div>
                         <div class="card-body d-flex justify-content-between flex-column pb-1 px-0">
                             <div id="products_category_chart" style="height: 350px;"></div>
                         </div>
                     </div>
                </div>
                
                <div class="col-lg-6">
                    <!--begin::Card-->
                    <div class="card card-flush overflow-hidden h-md-100 card-hover animate__animated animate__fadeInUp">
                        <!--begin::Card header-->
                        <div class="card-header py-5">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-gray-800">Recent Orders</span>
                                <span class="text-gray-500 mt-1 fw-semibold fs-6">Latest customer transactions</span>
                            </h3>
                            <div class="card-toolbar">
                                <button type="button" class="btn btn-sm btn-icon btn-color-primary btn-active-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                    <i class="ki-duotone ki-dotsquare fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                                </button>
                            </div>
                        </div>
                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <div class="card-body pt-0 px-0">
                            <div class="table-responsive">
                                <table class="table align-middle table-row-dashed fs-6 gy-4 mb-0" data-kt-table-sortable="true">
                                    <thead>
                                        <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                                            <th class="min-w-100px ps-6">Order #</th>
                                            <th class="min-w-150px">Customer</th>
                                            <th class="min-w-100px">Date</th>
                                            <th class="min-w-100px pe-6 text-end">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="fw-semibold text-gray-700">
                                        @foreach($recentOrders as $order)
                                        <tr class="hoverable">
                                            <td class="ps-6">
                                                <a href="#" class="text-gray-700 text-hover-primary fw-bold">{{ $order->order_number }}</a>
                                            </td>
                                            <td>
                                                @if($order->customerData)
                                                    <div class="d-flex align-items-center">
                                                        <div class="symbol symbol-35px symbol-circle me-3">
                                                            <span class="symbol-label bg-light-primary text-primary fw-bold">
                                                                {{ substr($order->customerData->first_name, 0, 1) }}{{ substr($order->customerData->last_name, 0, 1) }}
                                                            </span>
                                                        </div>
                                                        <span class="text-gray-700">{{ $order->customerData->first_name }} {{ $order->customerData->last_name }}</span>
                                                    </div>
                                                @else
                                                    <span class="text-muted">Guest Customer</span>
                                                @endif
                                            </td>
                                            <td>{{ $order->created_at->format('M d, Y') }}</td>
                                            <td class="pe-6 text-end">
                                                @php
                                                    $statusColor = match(strtolower($order->order_status)) {
                                                        'completed' => 'success',
                                                        'pending' => 'warning',
                                                        'processing' => 'primary',
                                                        'cancelled' => 'danger',
                                                        default => 'info'
                                                    };
                                                @endphp
                                                <span class="badge badge-lg badge-light-{{ $statusColor }} px-4 py-3">{{ $order->order_status }}</span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::Card-->
                </div>
                
                
            </div>
            

            {{-- Additional Section: Activity Feed and Announcements --}}
            <div class="row mt-8">
                {{-- Activity Feed --}}
                <div class="col-lg-6">
                    <div class="card card-flush h-lg-100 card-hover animate__animated animate__fadeInUp">
                        <div class="card-header py-5">
                            <h3 class="card-title fw-bold">Activity Feed</h3>
                        </div>
                        <div class="card-body">
                            <div class="timeline timeline-5">
                                
                                <!-- Clickable Heading for New Customers -->
                                <h6 class="fw-bold text-primary cursor-pointer" data-bs-toggle="collapse" data-bs-target="#newCustomersCollapse">
                                    New Customers
                                </h6>
                
                                <!-- Collapsible Content for New Customers -->
                                <div class="collapse" id="newCustomersCollapse">
                                    @foreach($newCustomers as $customer)
                                        <div class="timeline-item animate__animated animate__fadeIn">
                                            <div class="timeline-label">{{ $customer->created_at->format('H:i') }}</div>
                                            <div class="timeline-badge">
                                                <i class="fa fa-genderless text-success fs-1"></i>
                                            </div>
                                            <div class="timeline-content fw-semibold text-dark">
                                                New user registered: 
                                                <a href="#">{{ $customer->first_name }} {{ $customer->last_name }}</a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                
                                <!-- Clickable Heading for Recent Orders -->
                                <h6 class="fw-bold text-danger cursor-pointer mt-4" data-bs-toggle="collapse" data-bs-target="#recentOrdersCollapse">
                                    Recent Orders
                                </h6>
                
                                <!-- Collapsible Content for Recent Orders -->
                                <div class="collapse" id="recentOrdersCollapse">
                                    @foreach($recentOrders as $order)
                                        <div class="timeline-item animate__animated animate__fadeIn">
                                            <div class="timeline-label">{{ $order->created_at->format('H:i') }}</div>
                                            <div class="timeline-badge">
                                                <i class="fa fa-genderless text-danger fs-1"></i>
                                            </div>
                                            <div class="timeline-content fw-semibold text-dark">
                                                Order <strong>#{{ $order->order_number }}</strong> was placed by 
                                                <a href="#">
                                                    {{ optional($order->customerData)->first_name }} {{ optional($order->customerData)->last_name }}
                                                </a>
                                                <div class="small mt-1">
                                                    Shipping: {{ $order->shipping_method_name }}<br>
                                                    Payment: {{ $order->payment_status }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                
                   
                            </div>
                        </div>
                    </div>
                </div>
                
                
            
                {{-- Announcements --}}
                <div class="col-lg-6">
                    <div class="card card-flush h-lg-100 card-hover animate__animated animate__fadeInUp">
                        <div class="card-header py-5">
                            <h3 class="card-title fw-bold">Announcements</h3>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item animate__animated animate__fadeIn">
                                    <div class="d-flex align-items-center">
                                        <span class="bullet bullet-success me-3"></span>
                                        <span>System maintenance scheduled for 2025-03-01.</span>
                                    </div>
                                </li>
                                <li class="list-group-item animate__animated animate__fadeIn">
                                    <div class="d-flex align-items-center">
                                        <span class="bullet bullet-warning me-3"></span>
                                        <span>New feature release: Dashboard enhancements.</span>
                                    </div>
                                </li>
                                <li class="list-group-item animate__animated animate__fadeIn">
                                    <div class="d-flex align-items-center">
                                        <span class="bullet bullet-danger me-3"></span>
                                        <span>Alert: Unusual login activity detected.</span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            

        </div>
    </div>


<script>
    let monthlyOrdersData = @json($ordersByMonth);
    let dailyOrdersData   = @json($ordersByDate);

    function parseOrdersData(data) {
        let categories = data.map(item => item.month || item.date);
        let counts     = data.map(item => item.count);
        return { categories, counts };
    }

    function buildOrdersChartOptions(categories, data) {
        return {
            series: [{
                name: 'Orders',
                data: data
            }],
            chart: {
                height: 350,
                type: 'bar',
                toolbar: {
                    show: false
                },
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 800,
                    animateGradually: {
                        enabled: true,
                        delay: 150
                    },
                    dynamicAnimation: {
                        enabled: true,
                        speed: 350
                    }
                }
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '55%',
                    endingShape: 'rounded'
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                show: true,
                width: 2,
                colors: ['transparent']
            },
            xaxis: {
                categories: categories
            },
            yaxis: {
                title: {
                    text: 'Number of Orders'
                }
            },
            fill: {
                opacity: 1
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return val + " orders";
                    }
                }
            }
        };
    }

    let ordersChart;
    function renderOrdersChart(viewType) {
        if (ordersChart) {
            ordersChart.destroy();
        }

        let rawData = (viewType === 'monthly') ? monthlyOrdersData : dailyOrdersData;
        let parsed  = parseOrdersData(rawData);

        let options = buildOrdersChartOptions(parsed.categories, parsed.counts);

        ordersChart = new ApexCharts(document.querySelector("#orders_chart"), options);
        ordersChart.render();
    }

    let currentView = 'monthly';
    renderOrdersChart(currentView);

    document.getElementById('toggleChart').addEventListener('click', function() {
        if (currentView === 'monthly') {
            currentView = 'daily';
            this.innerText = 'Switch to Monthly';
        } else {
            currentView = 'monthly';
            this.innerText = 'Switch to Daily';
        }
        renderOrdersChart(currentView);
    });







    var productsCategoryData = @json($productsCategory);

    var labels = productsCategoryData.map(function(item) {
        return item.category;
    });
    var series = productsCategoryData.map(function(item) {
        return item.count;
    });

    var options = {
        chart: {
            type: 'pie',
            height: 350
        },
        labels: labels,
        series: series,
        tooltip: {
            y: {
                formatter: function(val) {
                    return val;
                }
            }
        }
    };

    var chart = new ApexCharts(document.querySelector("#products_category_chart"), options);
    chart.render();













</script>

@endsection