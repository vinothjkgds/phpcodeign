<?php
/**
 * Product View Page
 */

$productStockUnit = strtolower(trim((string) ($productInfo->stock_unit ?? 'gram')));
?>

<style>
#adjustProductStockForm .stock-unit-select {
    height: calc(2.625rem + 2px);
}
</style>

<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="card-title mb-0">Product View</h4>
            <div>
                <a href="<?= base_url('product/edit/' . (int) ($productInfo->product_id ?? 0)) ?>" class="btn btn-primary me-2">Edit Product</a>
                <a href="<?= base_url('product') ?>" class="btn btn-secondary">Back to Product List</a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <strong>Product Name:</strong><br>
                <?= esc((string) ($productInfo->product_name ?? '-')) ?>
            </div>
            <div class="col-md-2">
                <strong>Category:</strong><br>
                <?= esc((string) ($productInfo->category ?? '-')) ?>
            </div>
            <div class="col-md-2">
                <strong>Current Stock:</strong><br>
                <?= esc(number_format((float) ($productInfo->current_stock ?? 0), 3)) . ' ' . esc((string) ($stockChartUnit ?? '')) ?>
            </div>
            <div class="col-md-2">
                <strong>Reorder Level:</strong><br>
                <?= esc(number_format((float) ($productInfo->reorder_level ?? 0), 3)) . ' ' . esc((string) ($stockChartUnit ?? '')) ?>
            </div>
            <div class="col-md-2">
                <strong>Status:</strong><br>
                <?php if (!empty($productInfo->is_active)): ?>
                    <span class="badge badge-success">Active</span>
                <?php else: ?>
                    <span class="badge badge-danger">Inactive</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <h5 class="card-title">Add Stock (Manual)</h5>
        <p class="mb-3 text-muted">
            Existing Stock: <strong><?= esc(number_format((float) ($productInfo->current_stock ?? 0), 3)) . ' ' . esc((string) ($stockChartUnit ?? '')) ?></strong>
        </p>
        <form id="adjustProductStockForm" method="POST" action="<?= site_url('product/adjustStock/' . (int) ($productInfo->product_id ?? 0)) ?>">
            <div class="row">
                <div class="col-md-4">
                    <label for="adjustment_quantity">Add Stock</label>
                    <input type="number" step="0.001" min="0.001" class="form-control" id="adjustment_quantity" name="adjustment_quantity" required placeholder="Enter stock to add">
                </div>
                <div class="col-md-2">
                    <label for="adjustment_unit">Input Unit</label>
                    <select class="form-control stock-unit-select" id="adjustment_unit" name="adjustment_unit" required>
                        <option value="gram" <?= $productStockUnit === 'gram' ? 'selected' : '' ?>>Gram (gm)</option>
                        <option value="kilogram" <?= $productStockUnit === 'kilogram' ? 'selected' : '' ?>>Kilogram (kg)</option>
                        <option value="milligram" <?= $productStockUnit === 'milligram' ? 'selected' : '' ?>>Milligram (mg)</option>
                        <option value="tola" <?= $productStockUnit === 'tola' ? 'selected' : '' ?>>Tola</option>
                        <option value="ounce" <?= $productStockUnit === 'ounce' ? 'selected' : '' ?>>Ounce (oz)</option>
                        <option value="piece" <?= $productStockUnit === 'piece' ? 'selected' : '' ?>>Piece (pc)</option>
                        <option value="liter" <?= $productStockUnit === 'liter' ? 'selected' : '' ?>>Liter (ltr)</option>
                        <option value="other" <?= $productStockUnit === 'other' ? 'selected' : '' ?>>Other</option>
                    </select>
                    <small class="form-text text-muted">Product stock unit: <?= esc((string) ($stockChartUnit ?? '')) ?></small>
                </div>
                <div class="col-md-4">
                    <label for="adjustment_notes">Notes</label>
                    <input type="text" maxlength="500" class="form-control" id="adjustment_notes" name="adjustment_notes" placeholder="Reason for adjustment">
                </div>
                <div class="col-md-2">
                    <label class="d-block">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">Save</button>
                </div>
            </div>
            <small class="form-text text-muted mt-2">Example: if stock unit is gm, 2 kilogram becomes 2000 gm.</small>
            <small class="form-text text-muted d-block">Each add is saved in stock history.</small>
        </form>
        <div id="adjustStockMessage" class="mt-3" style="display: none;"></div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <h5 class="card-title">Stock Trend Chart</h5>
        <canvas id="productStockHistoryChart" height="100"></canvas>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h5 class="card-title">Stock History List</h5>
        <div class="table-responsive">
            <table id="productViewStockHistoryTable" class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Quantity</th>
                        <th>Before</th>
                        <th>After</th>
                        <th>Ref No</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($stockHistoryRows ?? []) as $row): ?>
                        <tr>
                            <td><?= (int) ($row['history_id'] ?? 0) ?></td>
                            <td><?= esc((string) ($row['created_at'] ?? '-')) ?></td>
                            <td><?= esc((string) ($row['movement_type'] ?? '-')) ?></td>
                            <td><?= esc((string) ($row['quantity'] ?? '-')) ?></td>
                            <td><?= esc((string) ($row['stock_before'] ?? '-')) ?></td>
                            <td><?= esc((string) ($row['stock_after'] ?? '-')) ?></td>
                            <td><?= esc((string) ($row['txn_ref'] ?? '-')) ?></td>
                            <td><?= esc((string) ($row['notes'] ?? '-')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
window.ProductStockViewChartData = {
    labels: <?= json_encode($stockChartLabels ?? []) ?>,
    values: <?= json_encode($stockChartValues ?? []) ?>,
    unit: <?= json_encode($stockChartUnit ?? '') ?>
};
</script>
