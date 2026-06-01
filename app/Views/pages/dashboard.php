<!-- Welcome Row -->
<div class="row">
    <div class="col-md-12 grid-margin">
        <div class="card bg-white">
            <div class="card-body d-flex align-items-center justify-content-between">
                <h4 class="mt-1 mb-1"><?= lang('App.dashboard.welcome', ['name' => session()->get('auth_name')]) ?></h4>
                <div
                    id="dashboardRealtimeControls"
                    data-generated-at="<?= esc((string) ($dashboardGeneratedAt ?? ''), 'attr') ?>"
                    data-last-updated-label="<?= esc(lang('App.dashboard.lastUpdated'), 'attr') ?>"
                    class="d-flex flex-wrap align-items-center"
                >
                    <span id="dashboardLastUpdated" class="badge badge-light text-dark mr-2 mb-1"></span>
                    <label for="dashboardAutoRefresh" class="mb-1 mr-2 text-muted small"><?= lang('App.dashboard.autoRefresh') ?></label>
                    <select id="dashboardAutoRefresh" class="form-control form-control-sm mr-2 mb-1" style="width:auto; min-width:100px;">
                        <option value="0"><?= lang('App.dashboard.off') ?></option>
                        <option value="30">30s</option>
                        <option value="60">60s</option>
                    </select>
                    <button type="button" id="dashboardRefreshNow" class="btn btn-sm btn-outline-primary mb-1"><?= lang('App.dashboard.refreshNow') ?></button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- KPI Row 1: Financial Totals -->
<div class="row">
    <div class="col-md-3 grid-margin stretch-card">
        <div class="card border-0 border-radius-2 bg-success">
            <div class="card-body">
                <div class="d-flex flex-md-column flex-xl-row flex-wrap align-items-center justify-content-between">
                    <div class="icon-rounded-inverse-success icon-rounded-lg"><i class="mdi mdi-arrow-top-right"></i></div>
                    <div class="text-white">
                        <p class="fw-medium mt-md-2 mt-xl-0 text-md-center text-xl-left"><?= lang('App.dashboard.totalPayable') ?></p>
                        <h3 class="mb-0">&#x20B9;<?= number_format((float) ($totalOwnerPayableToMerchants ?? 0), 2) ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 grid-margin stretch-card">
        <div class="card border-0 border-radius-2 bg-info">
            <div class="card-body">
                <div class="d-flex flex-md-column flex-xl-row flex-wrap align-items-center justify-content-between">
                    <div class="icon-rounded-inverse-info icon-rounded-lg"><i class="mdi mdi-basket"></i></div>
                    <div class="text-white">
                        <p class="fw-medium mt-md-2 mt-xl-0 text-md-center text-xl-left"><?= lang('App.dashboard.totalReceivable') ?></p>
                        <h3 class="mb-0">&#x20B9;<?= number_format((float) ($totalOwnerReceivableFromMerchants ?? 0), 2) ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 grid-margin stretch-card">
        <div class="card border-0 border-radius-2 bg-danger">
            <div class="card-body">
                <div class="d-flex flex-md-column flex-xl-row flex-wrap align-items-center justify-content-between">
                    <div class="icon-rounded-inverse-danger icon-rounded-lg"><i class="mdi mdi-chart-donut-variant"></i></div>
                    <div class="text-white">
                        <p class="fw-medium mt-md-2 mt-xl-0 text-md-center text-xl-left"><?= lang('App.dashboard.totalActiveMerchants') ?></p>
                        <h3 class="mb-0 me-1"><?= (int) ($totalActiveMerchants ?? 0) ?> <small><?= lang('App.common.current') ?></small></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 grid-margin stretch-card">
        <div class="card border-0 border-radius-2 bg-warning">
            <div class="card-body">
                <div class="d-flex flex-md-column flex-xl-row flex-wrap align-items-center justify-content-between">
                    <div class="icon-rounded-inverse-warning icon-rounded-lg"><i class="mdi mdi-chart-multiline"></i></div>
                    <div class="text-white">
                        <p class="fw-medium mt-md-2 mt-xl-0 text-md-center text-xl-left"><?= lang('App.dashboard.totalActiveEmployees') ?></p>
                        <h3 class="mb-0 me-1"><?= (int) ($totalActiveEmployees ?? 0) ?> <small><?= lang('App.common.current') ?></small></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- KPI Row 2: Today + Net Position -->
<div class="row">
    <div class="col-md-3 grid-margin stretch-card">
        <div class="card border-0 border-radius-2" style="background:#3f51b5;">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div style="background:rgba(255,255,255,.15);border-radius:50%;width:52px;height:52px;display:flex;align-items:center;justify-content:center;">
                        <i class="mdi mdi-trending-up text-white" style="font-size:1.6rem;"></i>
                    </div>
                    <div class="text-white text-right">
                        <p class="fw-medium mb-1"><?= lang('App.dashboard.todaysSales') ?></p>
                        <h3 class="mb-0">&#x20B9;<?= number_format((float) ($todaysSalesAmount ?? 0), 2) ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 grid-margin stretch-card">
        <div class="card border-0 border-radius-2" style="background:#6d4c41;">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div style="background:rgba(255,255,255,.15);border-radius:50%;width:52px;height:52px;display:flex;align-items:center;justify-content:center;">
                        <i class="mdi mdi-receipt text-white" style="font-size:1.6rem;"></i>
                    </div>
                    <div class="text-white text-right">
                        <p class="fw-medium mb-1"><?= lang('App.dashboard.todaysSalesCount') ?></p>
                        <h3 class="mb-0"><?= number_format((int) ($todaysSalesCount ?? 0)) ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 grid-margin stretch-card">
        <div class="card border-0 border-radius-2" style="background:#00897b;">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div style="background:rgba(255,255,255,.15);border-radius:50%;width:52px;height:52px;display:flex;align-items:center;justify-content:center;">
                        <i class="mdi mdi-cash-multiple text-white" style="font-size:1.6rem;"></i>
                    </div>
                    <div class="text-white text-right">
                        <p class="fw-medium mb-1"><?= lang('App.dashboard.todaysCollections') ?></p>
                        <h3 class="mb-0">&#x20B9;<?= number_format((float) ($todaysCollectionsAmount ?? 0), 2) ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 grid-margin stretch-card">
        <?php $netPos = (float) ($netPosition ?? 0); ?>
        <div class="card border-0 border-radius-2" style="background:<?= $netPos >= 0 ? '#388e3c' : '#c62828' ?>;">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div style="background:rgba(255,255,255,.15);border-radius:50%;width:52px;height:52px;display:flex;align-items:center;justify-content:center;">
                        <i class="mdi mdi-scale-balance text-white" style="font-size:1.6rem;"></i>
                    </div>
                    <div class="text-white text-right">
                        <p class="fw-medium mb-1"><?= lang('App.dashboard.netPosition') ?></p>
                        <h3 class="mb-0"><?= $netPos >= 0 ? '+' : '' ?>&#x20B9;<?= number_format(abs($netPos), 2) ?></h3>
                        <small><?= $netPos >= 0 ? 'Receivable surplus' : 'Payable surplus' ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Inventory & Efficiency Alerts -->
<div class="row">
    <div class="col-md-4 grid-margin stretch-card">
        <div class="card border-0 border-radius-2" style="background:#d32f2f;">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div style="background:rgba(255,255,255,.15);border-radius:50%;width:52px;height:52px;display:flex;align-items:center;justify-content:center;">
                        <i class="mdi mdi-alert-outline text-white" style="font-size:1.6rem;"></i>
                    </div>
                    <div class="text-white text-right">
                        <p class="fw-medium mb-1"><?= lang('App.dashboard.outOfStockProducts') ?></p>
                        <h3 class="mb-0"><?= number_format((int) ($outOfStockProductsCount ?? 0)) ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 grid-margin stretch-card">
        <div class="card border-0 border-radius-2" style="background:#ef6c00;">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div style="background:rgba(255,255,255,.15);border-radius:50%;width:52px;height:52px;display:flex;align-items:center;justify-content:center;">
                        <i class="mdi mdi-bell-alert text-white" style="font-size:1.6rem;"></i>
                    </div>
                    <div class="text-white text-right">
                        <p class="fw-medium mb-1"><?= lang('App.dashboard.lowStockProducts') ?></p>
                        <h3 class="mb-0"><?= number_format((int) ($lowStockProductsCount ?? 0)) ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 grid-margin stretch-card">
        <div class="card border-0 border-radius-2" style="background:#3949ab;">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div style="background:rgba(255,255,255,.15);border-radius:50%;width:52px;height:52px;display:flex;align-items:center;justify-content:center;">
                        <i class="mdi mdi-calculator text-white" style="font-size:1.6rem;"></i>
                    </div>
                    <div class="text-white text-right">
                        <p class="fw-medium mb-1"><?= lang('App.dashboard.avgSaleValueToday') ?></p>
                        <h3 class="mb-0">&#x20B9;<?= number_format((float) ($avgSaleValueToday ?? 0), 2) ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Live Transactions -->
<div class="row">
    <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><?= lang('App.dashboard.recentTransactions') ?></h5>
                <?php if (!empty($recentTransactions)): ?>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Merchant</th>
                                <th class="text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $badgeMap = [
                                'sale'             => 'primary',
                                'purchase'         => 'info',
                                'payment_received' => 'success',
                                'payment_paid'     => 'warning',
                                'opening'          => 'danger',
                                'adjustment'       => 'dark',
                            ];
                            foreach ($recentTransactions as $rt):
                                $etRaw   = (string) $rt->entry_type;
                                $etLabel = ucwords(str_replace('_', ' ', $etRaw));
                                $badge   = $badgeMap[$etRaw] ?? 'secondary';
                            ?>
                            <tr>
                                <td><?= date('d M', strtotime($rt->entry_date)) ?></td>
                                <td><span class="badge badge-<?= $badge ?> badge-pill"><?= $etLabel ?></span></td>
                                <td><?= esc($rt->merchant_name) ?></td>
                                <td class="text-right">&#x20B9;<?= number_format((float) $rt->amount, 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-muted mt-2"><?= lang('App.dashboard.noData') ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Secondary Analytics (Collapsible) -->
<div class="row">
    <div class="col-md-12 grid-margin">
        <div class="d-flex justify-content-end mb-2">
            <button class="btn btn-sm btn-outline-primary" type="button" data-toggle="collapse" data-target="#secondaryAnalytics" aria-expanded="false" aria-controls="secondaryAnalytics">
                <?= lang('App.dashboard.toggleAnalytics') ?>
            </button>
        </div>
        <div class="collapse" id="secondaryAnalytics">
            <div class="row">
                <div class="col-md-8 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?= lang('App.dashboard.monthlyTrend') ?></h5>
                            <?php if (!empty($monthlyTrendLabels)): ?>
                            <canvas id="monthlyTrendChart" height="100"></canvas>
                            <?php else: ?>
                            <p class="text-muted mt-3"><?= lang('App.dashboard.noData') ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?= lang('App.dashboard.categorySplit') ?></h5>
                            <?php if (!empty($categoryChartLabels)): ?>
                            <canvas id="categorySplitChart" height="180"></canvas>
                            <?php else: ?>
                            <p class="text-muted mt-3"><?= lang('App.dashboard.noData') ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?= lang('App.dashboard.topMerchants') ?></h5>
                            <?php if (!empty($topMerchants)): ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>#</th>
                                            <th><?= lang('App.dashboard.merchantName') ?></th>
                                            <th><?= lang('App.dashboard.type') ?></th>
                                            <th class="text-right"><?= lang('App.dashboard.salesTotal') ?></th>
                                            <th class="text-right"><?= lang('App.dashboard.purchasesTotal') ?></th>
                                            <th class="text-right"><?= lang('App.dashboard.grandTotal') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($topMerchants as $i => $tm): ?>
                                        <tr>
                                            <td><?= $i + 1 ?></td>
                                            <td><?= esc($tm->merchant_name) ?></td>
                                            <td><span class="badge badge-<?= $tm->merchant_type === 'shop' ? 'primary' : 'info' ?> badge-pill"><?= ucfirst(esc($tm->merchant_type)) ?></span></td>
                                            <td class="text-right">&#x20B9;<?= number_format((float) $tm->sales_total, 2) ?></td>
                                            <td class="text-right">&#x20B9;<?= number_format((float) $tm->purchases_total, 2) ?></td>
                                            <td class="text-right font-weight-bold">&#x20B9;<?= number_format((float) $tm->grand_total, 2) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <p class="text-muted mt-2"><?= lang('App.dashboard.noData') ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


