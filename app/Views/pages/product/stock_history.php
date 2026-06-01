<?php
/**
 * Product Stock History View
 */
?>

<style>
#stockHistoryAddStockForm select.form-select {
    height: calc(2.625rem + 2px);
}
#sh_existing_stock {
    height: calc(2.625rem + 2px);
    display: flex;
    align-items: center;
    margin: 0;
    padding: 0 0.5rem;
    font-weight: 600;
    background-color: var(--bs-body-bg);
    border: var(--bs-border-width) solid var(--bs-border-color);
    border-radius: 2px;
    font-size: 0.875rem;
}
</style>

<div class="card mb-3">
    <div class="card-body">
        <h5 class="card-title">Adjust Stock (Manual)</h5>
        <form id="stockHistoryAddStockForm">
            <div class="row g-2">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="sh_product_id">Product</label>
                        <select class="form-select rounded-0" id="sh_product_id" name="sh_product_id" required>
                            <option value="">-- Select Product --</option>
                            <?php foreach (($products ?? []) as $product): ?>
                                <option value="<?= (int) ($product['product_id'] ?? 0) ?>" data-unit="<?= esc((string) ($product['stock_unit'] ?? 'gram')) ?>">
                                    <?= esc((string) ($product['product_name'] ?? '')) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label class="d-block">Existing Stock</label>
                        <div id="sh_existing_stock">--</div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="sh_quantity">Adjust Quantity (+/-)</label>
                        <input type="number" step="0.001" class="form-control" id="sh_quantity" name="sh_quantity" required placeholder="Use + to add, - to reduce">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="sh_unit">Input Unit</label>
                        <select class="form-select rounded-0" id="sh_unit" name="sh_unit" required>
                            <option value="gram">Gram (gm)</option>
                            <option value="kilogram">Kilogram (kg)</option>
                            <option value="milligram">Milligram (mg)</option>
                            <option value="tola">Tola</option>
                            <option value="ounce">Ounce (oz)</option>
                            <option value="piece">Piece (pc)</option>
                            <option value="liter">Liter (ltr)</option>
                            <option value="other">Other</option>
                        </select>
                        <small class="form-text text-muted" id="sh_unit_hint"></small>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="sh_notes">Notes</label>
                        <input type="text" maxlength="500" class="form-control" id="sh_notes" name="sh_notes" placeholder="Reason">
                    </div>
                </div>
                <div class="col-md-1">
                    <div class="form-group">
                        <label class="d-block">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">Save</button>
                    </div>
                </div>
            </div>
        </form>
        <div id="shAddStockMessage" class="mt-3" style="display:none;"></div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="card-title mb-0">Product Stock History</h4>
            <a href="<?= base_url('product') ?>" class="btn btn-secondary">Back to Product List</a>
        </div>

        <div class="row mb-3">
            <div class="col-md-3">
                <div class="form-group">
                    <label for="filter_product_id">Product</label>
                    <select id="filter_product_id" class="form-select rounded-0">
                        <option value="">All Products</option>
                        <?php foreach (($products ?? []) as $product): ?>
                            <option value="<?= (int) ($product['product_id'] ?? 0) ?>"><?= esc((string) ($product['product_name'] ?? '')) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="filter_movement_type">Movement Type</label>
                    <select id="filter_movement_type" class="form-select rounded-0">
                        <option value="">All Types</option>
                        <option value="opening">Opening</option>
                        <option value="sale">Sale</option>
                        <option value="purchase">Purchase</option>
                        <option value="adjustment">Adjustment</option>
                    </select>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label for="filter_from_date">From Date</label>
                    <input type="date" id="filter_from_date" class="form-control">
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label for="filter_to_date">To Date</label>
                    <input type="date" id="filter_to_date" class="form-control">
                </div>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <div class="form-group w-100 d-flex">
                    <button type="button" id="applyStockHistoryFilter" class="btn btn-primary me-2">Apply</button>
                    <button type="button" id="resetStockHistoryFilter" class="btn btn-light">Reset</button>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table id="stockHistoryTable" class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Product</th>
                        <th>Type</th>
                        <th>Quantity</th>
                        <th>Before</th>
                        <th>After</th>
                        <th>Ref No</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
