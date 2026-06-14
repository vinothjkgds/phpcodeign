<div class="row mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">SaaS Dashboard</h4>
                    <p class="text-muted mb-0">Welcome, <?= esc((string) (session()->get('auth_name') ?? 'Admin')) ?></p>
                </div>
                <a href="<?= site_url('saas/onboarding/add') ?>" class="btn btn-primary">New Onboarding</a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4 grid-margin stretch-card">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h6 class="mb-1">Total Shops</h6>
                <h3 class="mb-0"><?= (int) ($totalShops ?? 0) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-4 grid-margin stretch-card">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h6 class="mb-1">Active Shops</h6>
                <h3 class="mb-0"><?= (int) ($activeShops ?? 0) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-4 grid-margin stretch-card">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h6 class="mb-1">Pending Onboarding</h6>
                <h3 class="mb-0"><?= (int) ($pendingOnboarding ?? 0) ?></h3>
            </div>
        </div>
    </div>
</div>
