@extends('layouts.app')

@section('title', 'dboard - ATIN Admin')

@section('content')
<style>
    .dashboard-title {
        color: #002B7F;
        font-weight: 700;
    }

    .card-outline {
        border: 3px solid #002B7F;
        border-radius: 18px;
        padding: 20px;
        height: 240px;
        cursor: pointer;
        transition: 0.25s ease;
    }

    .card-outline:hover {
        background: rgba(0, 43, 127, 0.05);
        transform: scale(1.02);
    }

    .chart-card {
        border-radius: 20px;
        padding: 25px;
        border: 2px solid #eee;
    }

    .filter-option {
        font-size: 14px;
        cursor: pointer;
        padding: 5px 12px;
        border-radius: 6px;
        transition: 0.2s ease;
    }

    .filter-active {
        background: #002B7F;
        color: white;
    }

    .loading-box {
        display: flex;
        height: 100%;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        color: #002B7F;
        font-weight: 600;
    }
</style>


{{-- PAGE HEADER --}}
<div class="page-header">
    <div class="row align-items-center">

        <div class="col-md-6">
            <h2 class="mb-0 dashboard-title">
                <i class="bi bi-speedometer2 me-2"></i>
                Dashboard
            </h2>
            <p class="text-muted mb-0 mt-1">
                Welcome back, {{ session('user_name') }}! ({{ session('user_role') }})
            </p>
        </div>

        <div class="col-md-6 text-end">
            <div class="text-muted">
                Logged in as: <strong>{{ session('username') }}</strong>
            </div>
        </div>

    </div>
</div>



{{-- SALES OVERVIEW --}}
<div class="chart-card mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold m-0">Sales Overview</h5>

        <div class="d-flex gap-2">
            <div class="filter-option filter-active" data-period="day">Day</div>
            <div class="filter-option" data-period="week">Week</div>
            <div class="filter-option" data-period="month">Month</div>
        </div>
    </div>

    <canvas id="salesChart" height="120"></canvas>
</div>



{{-- INTERACTIVE BOXES --}}
<div class="row mt-4">

    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card-outline interactive-card" data-type="top">
            <h5 class="fw-bold mb-3">Top Selling Products</h5>
            <div id="top-products-box" class="loading-box">Click to load</div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card-outline interactive-card" data-type="low">
            <h5 class="fw-bold mb-3">Low Stock Products</h5>
            <div id="low-stock-box" class="loading-box">Click to load</div>
        </div>
    </div>

    <div class="col-lg-4 col-md-12 mb-4">
        <div class="card-outline interactive-card" data-type="recent">
            <h5 class="fw-bold mb-3">Recent Transactions</h5>
            <div id="recent-trans-box" class="loading-box">Click to load</div>
        </div>
    </div>

</div>

@endsection



{{-- JAVASCRIPT --}}
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
console.log("Dashboard script loaded!");

/* -----------------------------
   SALES CHART (Dynamic Filter)
--------------------------------*/
let chart;
function loadSales(period = "day") {

    const dataSets = {
        day:  [35, 52, 48, 60, 58, 74, 90],
        week: [320, 410, 385, 450, 470, 520],
        month:[1200, 1500, 1350, 1800, 1950, 2200, 2100, 2300, 2500, 2700, 3000, 3200]
    };

    if (chart) chart.destroy();

    const ctx = document.getElementById('salesChart').getContext('2d');
    chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: period === "day"
                ? ["Mon","Tue","Wed","Thu","Fri","Sat","Sun"]
                : period === "week"
                ? ["W1","W2","W3","W4","W5","W6"]
                : ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"],
            datasets: [{
                label: "Sales",
                data: dataSets[period],
                borderColor: "#002B7F",
                backgroundColor: "#002B7F",
                tension: 0.4,
                pointRadius: 5,
                pointHoverRadius: 7
            }]
        },
        options: { plugins: { legend: { display:false } }, responsive:true }
    });
}

loadSales();

/* -----------------------------
   CLICKABLE FILTER BUTTONS
--------------------------------*/
document.querySelectorAll(".filter-option").forEach(btn => {
    btn.addEventListener("click", () => {
        document.querySelectorAll(".filter-option").forEach(b => b.classList.remove("filter-active"));
        btn.classList.add("filter-active");

        loadSales(btn.dataset.period);
    });
});

/* -----------------------------
   INTERACTIVE BOXES
--------------------------------*/
function loadBox(type) {
    let target = {
        top: "#top-products-box",
        low: "#low-stock-box",
        recent: "#recent-trans-box"
    }[type];

    document.querySelector(target).innerHTML = "<div class='loading-box'>Loading...</div>";

    setTimeout(() => {
        if (type === "top") {
            document.querySelector(target).innerHTML = `
                <ul>
                    <li>Cement 40kg — 312 sold</li>
                    <li>GI Pipe 1" — 255 sold</li>
                    <li>Concrete Nails — 188 sold</li>
                    <li>LED Bulb 9W — 176 sold</li>
                    <li>Marine Plywood ¼ — 144 sold</li>
                </ul>`;
        }

        if (type === "low") {
            document.querySelector(target).innerHTML = `
                <ul>
                    <li>10mm Rebar — 6 left</li>
                    <li>½" PVC Tee — 4 left</li>
                    <li>Roof Sealant — 2 left</li>
                    <li>Concrete Hollow Blocks — 12 left</li>
                    <li>2x3 Lumber — 3 left</li>
                </ul>`;
        }

        if (type === "recent") {
            document.querySelector(target).innerHTML = `
                <ul>
                    <li>Order #2031 — ₱2,150</li>
                    <li>Order #2030 — ₱980</li>
                    <li>Order #2029 — ₱640</li>
                    <li>Order #2028 — ₱5,320</li>
                    <li>Order #2027 — ₱720</li>
                </ul>`;
        }
    }, 600);
}

document.querySelectorAll(".interactive-card").forEach(card => {
    card.addEventListener("click", () => {
        loadBox(card.dataset.type);
    });
});
</script>
@endpush
