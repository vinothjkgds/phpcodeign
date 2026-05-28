<?php
/**
 * Sale/Purchase List View
 */
?>

<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="card-title mb-0">Sale / Purchase Entries</h4>
            <a href="<?= base_url('salepurchase/add') ?>" class="btn btn-primary">Add Entry</a>
        </div>

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
