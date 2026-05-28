<div class="row">
    <div class="col-md-12 grid-margin">
        <div class="card bg-white">
        <div class="card-body d-flex align-items-center justify-content-between">
            <h4 class="mt-1 mb-1">Hi, Welcome back <?= session()->get('auth_name'); ?>!</h4>
            
            <button class="btn btn-info d-none d-md-block">Import</button>
        </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-3 grid-margin stretch-card">
        <div class="card border-0 border-radius-2 bg-success">
        <div class="card-body">
            <div class="d-flex flex-md-column flex-xl-row flex-wrap  align-items-center justify-content-between">
            <div class="icon-rounded-inverse-success icon-rounded-lg">
                <i class="mdi mdi-arrow-top-right"></i>
            </div>
            <div class="text-white">
                <p class="fw-medium mt-md-2 mt-xl-0 text-md-center text-xl-left">Total Payable</p>
                <div
                class="d-flex flex-md-column flex-xl-row flex-wrap align-items-baseline align-items-md-center align-items-xl-baseline">
                <h3 class="mb-0 mb-md-1 mb-lg-0 me-1">₹<?= number_format((float) ($totalOwnerPayableToMerchants ?? 0), 2) ?></h3>
                <!-- <small class="mb-0">Current outstanding</small> -->
                </div>
            </div>
            </div>
        </div>
        </div>
    </div>
    <div class="col-md-3 grid-margin stretch-card">
        <div class="card border-0 border-radius-2 bg-info">
        <div class="card-body">
            <div class="d-flex flex-md-column flex-xl-row flex-wrap  align-items-center justify-content-between">
            <div class="icon-rounded-inverse-info icon-rounded-lg">
                <i class="mdi mdi-basket"></i>
            </div>
            <div class="text-white">
                <p class="fw-medium mt-md-2 mt-xl-0 text-md-center text-xl-left">Total Receivable</p>
                <div
                class="d-flex flex-md-column flex-xl-row flex-wrap align-items-baseline align-items-md-center align-items-xl-baseline">
                <h3 class="mb-0 mb-md-1 mb-lg-0 me-1">₹<?= number_format((float) ($totalOwnerReceivableFromMerchants ?? 0), 2) ?></h3>
                </div>
            </div>
            </div>
        </div>
        </div>
    </div>
    <div class="col-md-3 grid-margin stretch-card">
        <div class="card border-0 border-radius-2 bg-danger">
        <div class="card-body">
            <div class="d-flex flex-md-column flex-xl-row flex-wrap  align-items-center justify-content-between">
            <div class="icon-rounded-inverse-danger icon-rounded-lg">
                <i class="mdi mdi-chart-donut-variant"></i>
            </div>
            <div class="text-white">
                <p class="fw-medium mt-md-2 mt-xl-0 text-md-center text-xl-left">Total Active Merchants</p>
                <div
                class="d-flex flex-md-column flex-xl-row flex-wrap align-items-baseline align-items-md-center align-items-xl-baseline">
                <h3 class="mb-0 mb-md-1 mb-lg-0 me-1"><?= (int) ($totalActiveMerchants ?? 0) ?></h3>
                <small class="mb-0">Current</small>
                </div>
            </div>
            </div>
        </div>
        </div>
    </div>
    <div class="col-md-3 grid-margin stretch-card">
        <div class="card border-0 border-radius-2 bg-warning">
        <div class="card-body">
            <div class="d-flex flex-md-column flex-xl-row flex-wrap  align-items-center justify-content-between">
            <div class="icon-rounded-inverse-warning icon-rounded-lg">
                <i class="mdi mdi-chart-multiline"></i>
            </div>
            <div class="text-white">
                <p class="fw-medium mt-md-2 mt-xl-0 text-md-center text-xl-left">Total Active Employees</p>
                <div
                class="d-flex flex-md-column flex-xl-row flex-wrap align-items-baseline align-items-md-center align-items-xl-baseline">
                <h3 class="mb-0 mb-md-1 mb-lg-0 me-1"><?= (int) ($totalActiveEmployees ?? 0) ?></h3>
                <small class="mb-0">Current</small>
                </div>
            </div>
            </div>
        </div>
        </div>
    </div>
</div>