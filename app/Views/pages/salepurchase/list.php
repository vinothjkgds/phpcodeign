<?php
/**
 * Sale/Purchase List View
 */
?>

<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="card-title mb-0">Sale / Purchase Entries</h4>
            <div>
                <button type="button" id="importSalePurchaseCsv" class="btn btn-outline-primary me-2">Import CSV</button>
                <button type="button" id="exportSalePurchaseCsvFiltered" class="btn btn-outline-secondary me-2">Export Filtered CSV</button>
                <button type="button" id="exportSalePurchaseExcelFiltered" class="btn btn-outline-success me-2">Export Filtered Excel</button>
                <button type="button" id="exportSalePurchaseCsvAll" class="btn btn-outline-secondary me-2">Export All CSV</button>
                <button type="button" id="exportSalePurchaseExcelAll" class="btn btn-outline-success me-2">Export All Excel</button>
                <a href="<?= base_url('salepurchase/add') ?>" class="btn btn-primary">Add Entry</a>
            </div>
        </div>
        <form id="importSalePurchaseCsvForm" action="<?= site_url('salepurchase/import/csv') ?>" method="post" enctype="multipart/form-data" style="display:none;">
            <?= csrf_field() ?>
            <input type="file" id="importSalePurchaseCsvFile" name="import_file" accept=".csv,text/csv">
        </form>

        <div class="row g-3">
            <div class="col-md-3">
                <label for="filter_entry_type" class="form-label">Entry Type</label>
                <select class="form-select rounded-0" id="filter_entry_type">
                    <option value="">All</option>
                    <option value="opening">Opening Balance</option>
                    <option value="sale">Sale</option>
                    <option value="purchase">Purchase</option>
                    <option value="payment_received">Payment Received</option>
                    <option value="payment_paid">Payment Paid</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="filter_merchant_id" class="form-label">Merchant</label>
                <select class="form-select rounded-0" id="filter_merchant_id">
                    <option value="">All</option>
                    <?php foreach (($merchants ?? []) as $merchant): ?>
                        <option value="<?= (int) $merchant->merchant_id ?>"><?= esc($merchant->merchant_name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label for="filter_from_date" class="form-label">From Date</label>
                <input type="date" class="form-control" id="filter_from_date">
            </div>
            <div class="col-md-2">
                <label for="filter_to_date" class="form-label">To Date</label>
                <input type="date" class="form-control" id="filter_to_date">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="button" id="applySalePurchaseFilter" class="btn btn-primary me-2">Filter</button>
                <button type="button" id="resetSalePurchaseFilter" class="btn btn-light">Reset</button>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="salePurchaseTable" class="table table-striped">
                <thead>
                    <tr>
                        <th>S.No</th>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Merchant</th>
                        <th>Product</th>
                        <th>Weight</th>
                        <th>Purity</th>
                        <th>Amount</th>
                        <th>Balance Change</th>
                        <th>Pending Balance</th>
                        <th>Ref No</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
