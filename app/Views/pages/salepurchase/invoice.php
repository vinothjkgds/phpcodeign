<?php
$invoiceData = $invoice ?? [];
$isDownloadMode = (bool) ($downloadMode ?? false);

$entryTypeRaw = (string) ($invoiceData['entry_type'] ?? '');
$entryTypeLabel = ucwords(str_replace('_', ' ', $entryTypeRaw));
$invoiceNumber = !empty($invoiceData['txn_ref']) ? (string) $invoiceData['txn_ref'] : 'INV-' . str_pad((string) ((int) ($invoiceData['ledger_id'] ?? 0)), 6, '0', STR_PAD_LEFT);

$shopAddress = (string) ($invoiceData['shop_address_full'] ?? '-');
$merchantAddress = (string) ($invoiceData['personal_address'] ?? $invoiceData['shop_address'] ?? '-');

$weightText = '-';
if (isset($invoiceData['weight']) && $invoiceData['weight'] !== null) {
    $weightText = rtrim(rtrim(number_format((float) $invoiceData['weight'], 3, '.', ''), '0'), '.');
    if (!empty($invoiceData['weight_unit'])) {
        $weightText .= ' ' . ucfirst((string) $invoiceData['weight_unit']);
    }
}

$balanceChange = (float) ($invoiceData['receivable_delta'] ?? 0);
$pendingBalance = (float) ($invoiceData['current_receivable_balance'] ?? 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice - <?= esc($invoiceNumber) ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; color: #212529; background: #f4f6f9; }
        .invoice-card { max-width: 980px; margin: 0 auto; background: #fff; border: 1px solid #e5e7eb; padding: 26px; }
        .invoice-toolbar { margin-bottom: 16px; text-align: right; }
        .btn { text-decoration: none; padding: 8px 12px; border-radius: 4px; border: 1px solid #0d6efd; color: #0d6efd; margin-left: 8px; display: inline-block; font-size: 14px; background: #fff; }
        .btn-primary { background: #0d6efd; color: #fff; }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-table td { vertical-align: top; }
        .brand-name { margin: 0; font-size: 20px; font-weight: 700; }
        .brand-sub { margin: 6px 0 0 0; color: #6b7280; font-size: 13px; line-height: 1.5; }
        .invoice-title { margin: 0; font-size: 28px; font-weight: 700; text-align: right; }
        .invoice-no { margin: 8px 0 0 0; color: #6b7280; font-size: 14px; text-align: right; }
        .section-spacer { height: 20px; }
        .row-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .row-table td { vertical-align: top; padding: 0; }
        .col-left { width: 50%; padding-right: 12px !important; }
        .col-right { width: 50%; padding-left: 12px !important; }
        .block-title { margin: 0 0 8px 0; font-size: 13px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.2px; }
        .block-main { margin: 0 0 4px 0; font-size: 16px; font-weight: 700; }
        .block-line { margin: 2px 0; font-size: 14px; color: #374151; }
        .date-table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        .date-table td { border: 1px solid #e5e7eb; padding: 10px; font-size: 14px; }
        .items { width: 100%; border-collapse: collapse; margin-top: 14px; }
        .items th, .items td { border: 1px solid #e5e7eb; padding: 10px; font-size: 14px; }
        .items th { background: #f3f5f7; text-transform: uppercase; font-size: 12px; letter-spacing: 0.3px; }
        .text-end { text-align: right; }
        .totals-wrap { width: 100%; margin-top: 14px; }
        .totals-table { width: 360px; margin-left: auto; border-collapse: collapse; }
        .totals-table td { border: 1px solid #e5e7eb; padding: 10px; font-size: 14px; }
        .total-row td { font-weight: 700; background: #f3f5f7; }
        .foot-note { color: #6b7280; font-size: 12px; margin-top: 14px; }
        @media print {
            body { margin: 0; background: #fff; }
            .no-print { display: none !important; }
            .invoice-card { border: none; max-width: none; margin: 0; padding: 12px; }
        }
    </style>
</head>
<body>
    <div class="invoice-card">
        <?php if (!$isDownloadMode): ?>
            <div class="invoice-toolbar no-print">
                <a href="<?= site_url('salepurchase') ?>" class="btn">Back</a>
                <a href="<?= site_url('salepurchase/invoice/download/' . (int) ($invoiceData['ledger_id'] ?? 0)) ?>" class="btn">Download PDF</a>
                <button type="button" onclick="window.print()" class="btn btn-primary">Print</button>
            </div>
        <?php endif; ?>

        <table class="header-table">
            <tr>
                <td>
                    <h3 class="brand-name"><?= esc((string) ($invoiceData['shop_name'] ?? 'Jewellery Shop')) ?></h3>
                    <p class="brand-sub"><?= esc($shopAddress) ?></p>
                </td>
                <td>
                    <h2 class="invoice-title">Invoice</h2>
                    <p class="invoice-no">#<?= esc($invoiceNumber) ?></p>
                </td>
            </tr>
        </table>

        <div class="section-spacer"></div>

        <table class="row-table">
            <tr>
                <td class="col-left">
                    <p class="block-title">From</p>
                    <p class="block-main"><?= esc((string) ($invoiceData['shop_name'] ?? '-')) ?></p>
                    <p class="block-line">Owner: <?= esc((string) ($invoiceData['owner_name'] ?? '-')) ?></p>
                    <p class="block-line">Phone: <?= esc((string) ($invoiceData['shop_mobile_no'] ?? '-')) ?></p>
                    <p class="block-line">Email: <?= esc((string) ($invoiceData['shop_email'] ?? '-')) ?></p>
                    <p class="block-line">GSTIN: <?= esc((string) ($invoiceData['shop_gstin'] ?? '-')) ?></p>
                    <p class="block-line">Address: <?= esc($shopAddress) ?></p>
                </td>
                <td class="col-right">
                    <p class="block-title">Invoice To</p>
                    <p class="block-main"><?= esc((string) ($invoiceData['merchant_name'] ?? '-')) ?></p>
                    <p class="block-line">Phone: <?= esc((string) ($invoiceData['merchant_phone'] ?? '-')) ?></p>
                    <p class="block-line">Email: <?= esc((string) ($invoiceData['merchant_email'] ?? '-')) ?></p>
                    <p class="block-line">GSTIN: <?= esc((string) ($invoiceData['merchant_gstin'] ?? '-')) ?></p>
                    <p class="block-line">Address: <?= esc($merchantAddress) ?></p>
                </td>
            </tr>
        </table>

        <table class="date-table">
            <tr>
                <td><strong>Invoice Date:</strong> <?= !empty($invoiceData['entry_date']) ? esc(date('d M Y, H:i', strtotime((string) $invoiceData['entry_date']))) : '-' ?></td>
                <td><strong>Type:</strong> <?= esc($entryTypeLabel) ?> | <strong>Reference:</strong> <?= esc((string) ($invoiceData['txn_ref'] ?? '-')) ?></td>
            </tr>
        </table>

        <table class="items">
            <thead>
                <tr>
                    <th style="width:60px;">#</th>
                    <th>Description</th>
                    <th style="width:120px;">Weight</th>
                    <th style="width:100px;">Purity</th>
                    <th class="text-end">Amount (₹)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?= (int) ($invoiceData['ledger_id'] ?? 0) ?></td>
                    <td>
                        <strong><?= esc((string) ($invoiceData['product_name'] ?? '-')) ?></strong><br>
                        <span style="color:#6b7280;"><?= esc((string) ($invoiceData['description'] ?? '-')) ?></span>
                    </td>
                    <td><?= esc($weightText) ?></td>
                    <td><?= esc((string) ($invoiceData['purity'] ?? '-')) ?></td>
                    <td class="text-end"><?= number_format((float) ($invoiceData['amount'] ?? 0), 2) ?></td>
                </tr>
            </tbody>
        </table>

        <div class="totals-wrap">
            <table class="totals-table">
                <tr>
                    <td>Sub Total</td>
                    <td class="text-end"><?= number_format((float) ($invoiceData['amount'] ?? 0), 2) ?></td>
                </tr>
                <tr>
                    <td>Balance Change</td>
                    <td class="text-end"><?= number_format($balanceChange, 2) ?></td>
                </tr>
                <tr>
                    <td>Pending Balance</td>
                    <td class="text-end"><?= number_format($pendingBalance, 2) ?></td>
                </tr>
                <tr class="total-row">
                    <td>Total</td>
                    <td class="text-end"><?= number_format((float) ($invoiceData['amount'] ?? 0), 2) ?></td>
                </tr>
            </table>
        </div>

        <p class="foot-note">Generated on <?= esc(date('Y-m-d H:i:s')) ?></p>
    </div>
</body>
</html>
