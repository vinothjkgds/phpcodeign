<?php
/**
 * Merchant Profile + Transactions View
 */

$merchant = $merchantInfo ?? null;
$transactionRows = $transactions ?? [];
$receivable = (float) ($receivableAmount ?? 0);
$payable = (float) ($payableAmount ?? 0);

$logoPath = null;
if ($merchant) {
    $logoPath = $merchant->merchant_type === 'shop' ? ($merchant->shop_logo ?? null) : ($merchant->profile_logo ?? null);
}
?>

<div class="row grid-margin">
    <div class="col-12">
        <div class="card">
            <div class="card-body d-flex justify-content-between align-items-start flex-wrap">
                <div class="d-flex align-items-center">
                    <?php if (!empty($logoPath)): ?>
                        <img src="<?= base_url(ltrim((string) $logoPath, '/')) ?>" alt="Merchant Logo" style="width:64px;height:64px;object-fit:cover;border-radius:50%;margin-right:16px;">
                    <?php else: ?>
                        <div style="width:64px;height:64px;border-radius:50%;background:#e9ecef;display:flex;align-items:center;justify-content:center;margin-right:16px;">
                            <i class="mdi mdi-account" style="font-size:28px;"></i>
                        </div>
                    <?php endif; ?>

                    <div>
                        <h4 class="card-title mb-1"><?= esc((string) ($merchant->merchant_name ?? 'Merchant')) ?></h4>
                        <p class="mb-1 text-muted"><?= ucfirst((string) ($merchant->merchant_type ?? '-')) ?></p>
                        <p class="mb-0 text-muted"><?= esc((string) ($merchant->phone ?? '-')) ?><?= !empty($merchant->email) ? ' | ' . esc((string) $merchant->email) : '' ?></p>
                    </div>
                </div>

                <div class="text-end mt-3 mt-md-0">
                    <div class="mb-2"><span class="badge badge-success">Receivable: ₹<?= number_format($receivable, 2) ?></span></div>
                    <div><span class="badge badge-warning">Payable: ₹<?= number_format($payable, 2) ?></span></div>
                </div>
            </div>

            <div class="card-body pt-0">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Address:</strong> <?= esc((string) ($merchant->personal_address ?? $merchant->shop_address ?? '-')) ?></p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1"><strong>GSTIN:</strong> <?= esc((string) ($merchant->gstin ?? '-')) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="card-title mb-0">Merchant Transactions</h4>
                    <a href="<?= base_url('merchant') ?>" class="btn btn-secondary">Back to Merchant List</a>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>S.No</th>
                                <th>Date</th>
                                <th>Type</th>
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
                        <tbody>
                            <?php if (empty($transactionRows)): ?>
                                <tr>
                                    <td colspan="11" class="text-center text-muted">No transactions found for this merchant.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($transactionRows as $row): ?>
                                    <?php
                                    $entryTypeRaw = (string) ($row['entry_type'] ?? '');
                                    $entryTypeLabel = ucwords(str_replace('_', ' ', $entryTypeRaw));
                                    $badgeClass = match ($entryTypeRaw) {
                                        'sale', 'payment_received' => 'badge-success',
                                        'purchase' => 'badge-info',
                                        'payment_paid' => 'badge-warning',
                                        'opening' => 'badge-secondary',
                                        default => 'badge-dark',
                                    };

                                    $weightText = '-';
                                    if (!empty($row['weight'])) {
                                        $weightText = rtrim(rtrim(number_format((float) $row['weight'], 3, '.', ''), '0'), '.');
                                        if (!empty($row['weight_unit'])) {
                                            $weightText .= ' ' . ucfirst((string) $row['weight_unit']);
                                        }
                                    }
                                    ?>
                                    <tr>
                                        <td><?= (int) ($row['ledger_id'] ?? 0) ?></td>
                                        <td><?= !empty($row['entry_date']) ? esc(date('Y-m-d H:i', strtotime((string) $row['entry_date']))) : '-' ?></td>
                                        <td><span class="badge <?= esc($badgeClass) ?> badge-pill"><?= esc($entryTypeLabel) ?></span></td>
                                        <td><?= !empty($row['product_name']) ? esc((string) $row['product_name']) : '-' ?></td>
                                        <td><?= esc($weightText) ?></td>
                                        <td><?= !empty($row['purity']) ? esc((string) $row['purity']) : '-' ?></td>
                                        <td>₹<?= number_format((float) ($row['amount'] ?? 0), 2) ?></td>
                                        <td><?= number_format((float) ($row['receivable_delta'] ?? 0), 2) ?></td>
                                        <td><?= number_format((float) ($row['current_receivable_balance'] ?? 0), 2) ?></td>
                                        <td><?= !empty($row['txn_ref']) ? esc((string) $row['txn_ref']) : '-' ?></td>
                                        <td><?= !empty($row['description']) ? esc((string) $row['description']) : '-' ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
