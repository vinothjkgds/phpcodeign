<?php

namespace App\Controllers;

use App\Models\SalePurchaseModel;
use Dompdf\Dompdf;
use Dompdf\Options;

class Salepurchase extends BaseController
{
    protected $salePurchaseModel;

    public function __construct()
    {
        $this->salePurchaseModel = new SalePurchaseModel();
        helper(['form', 'url']);
    }

    public function index()
    {
        $shopId = $this->getCurrentShopId();
        if ($shopId === null) {
            return redirect()->to(site_url('dashboard'))->with('error', 'Unable to identify current shop.');
        }

        return view('index', [
            'body_content' => 'salepurchase/list',
            'merchants' => $this->salePurchaseModel->getActiveMerchants($shopId),
        ]);
    }

    public function add()
    {
        $shopId = $this->getCurrentShopId();
        if ($shopId === null) {
            return redirect()->to(site_url('dashboard'))->with('error', 'Unable to identify current shop.');
        }

        return view('index', [
            'body_content' => 'salepurchase/add',
            'merchants' => $this->salePurchaseModel->getActiveMerchants($shopId),
            'products' => $this->salePurchaseModel->getActiveProducts($shopId),
        ]);
    }

    public function save()
    {
        try {
            $shopId = $this->getCurrentShopId();
            if ($shopId === null) {
                return $this->respondSave(false, 'Unable to identify current shop.', null, [], 403);
            }

            $entryType = trim((string) $this->request->getPost('entry_type'));
            $requiresWeight = in_array($entryType, ['sale', 'purchase'], true);
            $weightRule = $requiresWeight ? 'required|numeric|greater_than[0]' : 'permit_empty|numeric|greater_than[0]';
            $weightUnitRule = $requiresWeight ? 'required|in_list[gram,kilogram,milligram,tola,ounce,other]' : 'permit_empty|in_list[gram,kilogram,milligram,tola,ounce,other]';

            $validation = \Config\Services::validation();
            $validation->setRules([
                'entry_date' => [
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'Entry date is required.',
                    ],
                ],
                'merchant_id' => [
                    'rules' => 'required|integer',
                    'errors' => [
                        'required' => 'Please select merchant.',
                        'integer' => 'Invalid merchant selected.',
                    ],
                ],
                'entry_type' => [
                    'rules' => 'required|in_list[opening,sale,purchase,payment_received,payment_paid]',
                    'errors' => [
                        'required' => 'Entry type is required.',
                        'in_list' => 'Entry type must be opening, sale, purchase, payment received, or payment paid.',
                    ],
                ],
                'opening_balance_type' => [
                    'rules' => 'permit_empty|in_list[receivable,payable]',
                    'errors' => [
                        'in_list' => 'Opening balance type must be receivable or payable.',
                    ],
                ],
                'product_id' => [
                    'rules' => 'permit_empty|integer',
                ],
                'weight' => [
                    'rules' => $weightRule,
                    'errors' => [
                        'required' => 'Weight is required.',
                        'numeric' => 'Weight must be numeric.',
                        'greater_than' => 'Weight must be greater than 0.',
                    ],
                ],
                'weight_unit' => [
                    'rules' => $weightUnitRule,
                    'errors' => [
                        'required' => 'Weight unit is required.',
                        'in_list' => 'Please select a valid weight unit.',
                    ],
                ],
                'amount' => [
                    'rules' => 'required|numeric|greater_than[0]',
                    'errors' => [
                        'required' => 'Amount is required.',
                        'numeric' => 'Amount must be numeric.',
                        'greater_than' => 'Amount must be greater than 0.',
                    ],
                ],
            ]);

            if (!$validation->run($this->request->getPost())) {
                $errors = $validation->getErrors();
                return $this->respondSave(false, implode(' | ', $errors), null, $errors, 422);
            }

            $merchantId = (int) $this->request->getPost('merchant_id');
            $amount = (float) $this->request->getPost('amount');
            $entryDateInput = trim((string) $this->request->getPost('entry_date'));
            $entryDate = str_replace('T', ' ', $entryDateInput);
            $openingBalanceType = trim((string) $this->request->getPost('opening_balance_type'));

            $allowedMerchants = $this->salePurchaseModel->getActiveMerchants($shopId);
            $merchantIds = array_map(static fn($item) => (int) $item->merchant_id, $allowedMerchants);
            if (!in_array($merchantId, $merchantIds, true)) {
                return $this->respondSave(false, 'Selected merchant is invalid.', null, ['merchant_id' => 'Selected merchant is invalid.'], 422);
            }

            $receivableDelta = 0;
            $payableDelta = 0;
            if ($entryType === 'opening') {
                if (!in_array($openingBalanceType, ['receivable', 'payable'], true)) {
                    return $this->respondSave(false, 'Please select opening balance type.', null, ['opening_balance_type' => 'Please select opening balance type.'], 422);
                }

                if ($openingBalanceType === 'receivable') {
                    $receivableDelta = $amount;
                }

                if ($openingBalanceType === 'payable') {
                    $receivableDelta = -$amount;
                }
            }
            if ($entryType === 'sale') {
                $receivableDelta = $amount;
            }
            if ($entryType === 'purchase') {
                $receivableDelta = -$amount;
            }
            if ($entryType === 'payment_received') {
                $receivableDelta = -$amount;
            }
            if ($entryType === 'payment_paid') {
                $receivableDelta = $amount;
            }

            $weightInput = trim((string) $this->request->getPost('weight'));
            $weightUnitInput = trim((string) $this->request->getPost('weight_unit'));

            $data = [
                'shop_id' => $shopId,
                'merchant_id' => $merchantId,
                'entry_date' => $entryDate,
                'entry_type' => $entryType,
                'txn_ref' => trim((string) $this->request->getPost('txn_ref')) ?: null,
                'product_id' => (int) ($this->request->getPost('product_id') ?: 0) ?: null,
                'description' => trim((string) $this->request->getPost('description')) ?: null,
                'weight' => $weightInput !== '' ? (float) $weightInput : null,
                'weight_unit' => $weightUnitInput !== '' ? $weightUnitInput : null,
                'purity' => trim((string) $this->request->getPost('purity')) ?: null,
                'amount' => $amount,
                'receivable_delta' => $receivableDelta,
                'payable_delta' => $payableDelta,
            ];

            $this->salePurchaseModel->addEntry($data);
            return $this->respondSave(true, 'Sale/Purchase entry added successfully.', site_url('salepurchase'));
        } catch (\Throwable $e) {
            return $this->respondSave(false, $e->getMessage(), null, [], 500);
        }
    }

    public function getSalePurchaseListJson()
    {
        $shopId = $this->getCurrentShopId();
        if ($shopId === null) {
            return $this->response->setStatusCode(403)->setJSON([
                'draw' => (int) ($this->request->getPost('draw') ?? 0),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'message' => 'Unable to identify current shop.',
            ]);
        }

        $response = $this->salePurchaseModel->getSalePurchaseListDT($this->request->getPost(), $shopId);
        return $this->response->setJSON($response);
    }

    public function invoice($ledgerId)
    {
        $shopId = $this->getCurrentShopId();
        if ($shopId === null) {
            return redirect()->to(site_url('dashboard'))->with('error', 'Unable to identify current shop.');
        }

        $invoice = $this->salePurchaseModel->getInvoiceByLedgerId($shopId, (int) $ledgerId);
        if (!$invoice) {
            return redirect()->to(site_url('salepurchase'))->with('error', 'Invoice not found.');
        }

        return view('pages/salepurchase/invoice', [
            'invoice' => $invoice,
            'downloadMode' => false,
        ]);
    }

    public function downloadInvoice($ledgerId)
    {
        $shopId = $this->getCurrentShopId();
        if ($shopId === null) {
            return redirect()->to(site_url('dashboard'))->with('error', 'Unable to identify current shop.');
        }

        $invoice = $this->salePurchaseModel->getInvoiceByLedgerId($shopId, (int) $ledgerId);
        if (!$invoice) {
            return redirect()->to(site_url('salepurchase'))->with('error', 'Invoice not found.');
        }

        $html = view('pages/salepurchase/invoice', [
            'invoice' => $invoice,
            'downloadMode' => true,
        ]);

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $pdfContent = $dompdf->output();

        $refNo = trim((string) ($invoice['txn_ref'] ?? ''));
        $fileSuffix = $refNo !== '' ? preg_replace('/[^a-z0-9\-]+/i', '-', strtolower($refNo)) : 'ledger-' . (int) $invoice['ledger_id'];
        $fileName = 'invoice-' . trim((string) $fileSuffix, '-') . '.pdf';

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $fileName . '"')
            ->setBody($pdfContent);
    }

    public function exportCsv()
    {
        $shopId = $this->getCurrentShopId();
        if ($shopId === null) {
            return redirect()->to(site_url('dashboard'))->with('error', 'Unable to identify current shop.');
        }

        $rows = $this->salePurchaseModel->getSalePurchaseExportRows($this->getExportFilters(), $shopId);

        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, ['S.No', 'Date', 'Type', 'Merchant', 'Product', 'Weight', 'Purity', 'Amount', 'Balance Change', 'Pending Balance', 'Ref No', 'Description']);

        foreach ($rows as $row) {
            fputcsv($handle, $this->formatExportRow($row));
        }

        rewind($handle);
        $csvContent = stream_get_contents($handle);
        fclose($handle);

        $fileName = 'sale_purchase_' . date('Ymd_His') . '.csv';

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $fileName . '"')
            ->setBody("\xEF\xBB\xBF" . $csvContent);
    }

    public function exportExcel()
    {
        $shopId = $this->getCurrentShopId();
        if ($shopId === null) {
            return redirect()->to(site_url('dashboard'))->with('error', 'Unable to identify current shop.');
        }

        $rows = $this->salePurchaseModel->getSalePurchaseExportRows($this->getExportFilters(), $shopId);

        $html = '<table border="1"><thead><tr>'
            . '<th>S.No</th><th>Date</th><th>Type</th><th>Merchant</th><th>Product</th><th>Weight</th><th>Purity</th><th>Amount</th><th>Balance Change</th><th>Pending Balance</th><th>Ref No</th><th>Description</th>'
            . '</tr></thead><tbody>';

        foreach ($rows as $row) {
            $formatted = $this->formatExportRow($row);
            $html .= '<tr>';
            foreach ($formatted as $cell) {
                $html .= '<td>' . esc((string) $cell) . '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';

        $fileName = 'sale_purchase_' . date('Ymd_His') . '.xls';

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $fileName . '"')
            ->setBody("\xEF\xBB\xBF" . $html);
    }

    public function importCsv()
    {
        $shopId = $this->getCurrentShopId();
        if ($shopId === null) {
            return redirect()->to(site_url('dashboard'))->with('error', 'Unable to identify current shop.');
        }

        $db = db_connect();

        $file = $this->request->getFile('import_file');
        if (!$file || $file->getError() !== UPLOAD_ERR_OK) {
            return redirect()->to(site_url('salepurchase'))->with('error', 'Please choose a valid CSV file.');
        }

        $handle = fopen($file->getTempName(), 'r');
        if ($handle === false) {
            return redirect()->to(site_url('salepurchase'))->with('error', 'Unable to read uploaded CSV file.');
        }

        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return redirect()->to(site_url('salepurchase'))->with('error', 'CSV file is empty.');
        }

        $normalizedHeader = array_map(fn($value) => $this->normalizeHeader((string) $value), $header);
        $columns = array_flip($normalizedHeader);

        $requiredHeaders = ['date', 'type', 'merchant', 'amount'];
        foreach ($requiredHeaders as $requiredHeader) {
            if (!array_key_exists($requiredHeader, $columns)) {
                fclose($handle);
                return redirect()->to(site_url('salepurchase'))->with('error', 'Invalid CSV format. Missing required column: ' . ucfirst($requiredHeader));
            }
        }

        $merchantMap = [];
        $merchantRows = $db->table('merchants')
            ->select('merchant_id, merchant_name')
            ->where('shop_id', $shopId)
            ->get()
            ->getResultArray();
        foreach ($merchantRows as $merchantRow) {
            $merchantMap[strtolower(trim((string) $merchantRow['merchant_name']))] = (int) $merchantRow['merchant_id'];
        }

        $productMap = [];
        $productRows = $db->table('products')
            ->select('product_id, product_name')
            ->where('shop_id', $shopId)
            ->get()
            ->getResultArray();
        foreach ($productRows as $productRow) {
            $productMap[strtolower(trim((string) $productRow['product_name']))] = (int) $productRow['product_id'];
        }

        $importedCount = 0;
        $skippedCount = 0;
        $errorCount = 0;
        $errors = [];
        $lineNumber = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $lineNumber++;
            if ($this->isCsvRowEmpty($row)) {
                continue;
            }

            $dateRaw = $this->getCsvValue($row, $columns, 'date');
            $typeRaw = $this->getCsvValue($row, $columns, 'type');
            $merchantRaw = $this->getCsvValue($row, $columns, 'merchant');
            $productRaw = $this->getCsvValue($row, $columns, 'product');
            $weightRaw = $this->getCsvValue($row, $columns, 'weight');
            $purityRaw = $this->getCsvValue($row, $columns, 'purity');
            $amountRaw = $this->getCsvValue($row, $columns, 'amount');
            $balanceChangeRaw = $this->getCsvValue($row, $columns, 'balance_change');
            $refRaw = $this->getCsvValue($row, $columns, 'ref_no');
            $descriptionRaw = $this->getCsvValue($row, $columns, 'description');

            $entryDateTs = strtotime($dateRaw);
            if ($entryDateTs === false) {
                $errorCount++;
                $errors[] = 'Line ' . $lineNumber . ': invalid date.';
                continue;
            }
            $entryDate = date('Y-m-d H:i:s', $entryDateTs);

            $entryType = strtolower(str_replace(' ', '_', trim($typeRaw)));
            $allowedEntryTypes = ['opening', 'sale', 'purchase', 'payment_received', 'payment_paid'];
            if (!in_array($entryType, $allowedEntryTypes, true)) {
                $errorCount++;
                $errors[] = 'Line ' . $lineNumber . ': invalid entry type.';
                continue;
            }

            $merchantKey = strtolower(trim($merchantRaw));
            if ($merchantKey === '' || !isset($merchantMap[$merchantKey])) {
                $errorCount++;
                $errors[] = 'Line ' . $lineNumber . ': merchant not found.';
                continue;
            }
            $merchantId = $merchantMap[$merchantKey];

            $amount = $this->parseNumber($amountRaw);
            if ($amount <= 0) {
                $errorCount++;
                $errors[] = 'Line ' . $lineNumber . ': invalid amount.';
                continue;
            }

            $receivableDelta = 0.0;
            $balanceChange = $this->parseNumber($balanceChangeRaw);
            if ($balanceChangeRaw !== '') {
                $receivableDelta = $balanceChange;
            } else {
                if ($entryType === 'opening' || $entryType === 'sale' || $entryType === 'payment_paid') {
                    $receivableDelta = $amount;
                }
                if ($entryType === 'purchase' || $entryType === 'payment_received') {
                    $receivableDelta = -$amount;
                }
            }

            $productId = null;
            $productName = trim($productRaw);
            if ($productName !== '' && $productName !== '-') {
                $productKey = strtolower($productName);
                if (!isset($productMap[$productKey])) {
                    $errorCount++;
                    $errors[] = 'Line ' . $lineNumber . ': product not found.';
                    continue;
                }
                $productId = $productMap[$productKey];
            }

            [$weight, $weightUnit] = $this->parseWeightAndUnit($weightRaw);

            $txnRef = trim($refRaw);
            $description = trim($descriptionRaw);

            $duplicateQuery = $db->table('merchant_ledger');
            $duplicateQuery->where('shop_id', $shopId)
                ->where('merchant_id', $merchantId)
                ->where('entry_date', $entryDate)
                ->where('entry_type', $entryType)
                ->where('amount', $amount)
                ->where('receivable_delta', $receivableDelta);

            if ($productId === null) {
                $duplicateQuery->where('product_id', null);
            } else {
                $duplicateQuery->where('product_id', $productId);
            }

            if ($txnRef === '') {
                $duplicateQuery->where('txn_ref', null);
            } else {
                $duplicateQuery->where('txn_ref', $txnRef);
            }

            if ($description === '') {
                $duplicateQuery->where('description', null);
            } else {
                $duplicateQuery->where('description', $description);
            }

            if ($duplicateQuery->countAllResults() > 0) {
                $skippedCount++;
                continue;
            }

            $insertData = [
                'shop_id' => $shopId,
                'merchant_id' => $merchantId,
                'entry_date' => $entryDate,
                'entry_type' => $entryType,
                'txn_ref' => $txnRef !== '' ? $txnRef : null,
                'product_id' => $productId,
                'description' => $description !== '' ? $description : null,
                'weight' => $weight,
                'weight_unit' => $weightUnit,
                'purity' => trim($purityRaw) !== '' && trim($purityRaw) !== '-' ? trim($purityRaw) : null,
                'amount' => $amount,
                'receivable_delta' => $receivableDelta,
                'payable_delta' => 0,
            ];

            $this->salePurchaseModel->addEntry($insertData);
            $importedCount++;
        }

        fclose($handle);

        $message = 'Import completed. Added: ' . $importedCount . ', Skipped duplicates: ' . $skippedCount . ', Errors: ' . $errorCount . '.';
        if (!empty($errors)) {
            $message .= ' ' . implode(' ', array_slice($errors, 0, 5));
        }

        if ($errorCount > 0) {
            return redirect()->to(site_url('salepurchase'))->with('error', $message);
        }

        return redirect()->to(site_url('salepurchase'))->with('success', $message);
    }

    private function getExportFilters(): array
    {
        return [
            'filter_entry_type' => trim((string) $this->request->getGet('filter_entry_type')),
            'filter_merchant_id' => (int) ($this->request->getGet('filter_merchant_id') ?? 0),
            'filter_from_date' => trim((string) $this->request->getGet('filter_from_date')),
            'filter_to_date' => trim((string) $this->request->getGet('filter_to_date')),
        ];
    }

    private function formatExportRow(array $row): array
    {
        $weightText = '-';
        if ($row['weight'] !== null) {
            $weightText = rtrim(rtrim(number_format((float) $row['weight'], 3, '.', ''), '0'), '.');
            if (!empty($row['weight_unit'])) {
                $weightText .= ' ' . ucfirst((string) $row['weight_unit']);
            }
        }

        return [
            (int) ($row['ledger_id'] ?? 0),
            !empty($row['entry_date']) ? date('Y-m-d H:i', strtotime((string) $row['entry_date'])) : '-',
            ucwords(str_replace('_', ' ', (string) ($row['entry_type'] ?? ''))),
            (string) ($row['merchant_name'] ?? '-'),
            !empty($row['product_name']) ? (string) $row['product_name'] : '-',
            $weightText,
            !empty($row['purity']) ? (string) $row['purity'] : '-',
            number_format((float) ($row['amount'] ?? 0), 2),
            number_format((float) ($row['receivable_delta'] ?? 0), 2),
            number_format((float) ($row['current_receivable_balance'] ?? 0), 2),
            !empty($row['txn_ref']) ? (string) $row['txn_ref'] : '-',
            !empty($row['description']) ? (string) $row['description'] : '-',
        ];
    }

    private function normalizeHeader(string $header): string
    {
        $header = preg_replace('/^\xEF\xBB\xBF/', '', $header);
        $header = strtolower(trim($header));
        $header = str_replace(['(', ')', '+'], '', $header);
        $header = preg_replace('/\s+/', ' ', $header ?? '');

        return match ($header) {
            's.no' => 's_no',
            'date' => 'date',
            'type' => 'type',
            'merchant' => 'merchant',
            'product' => 'product',
            'weight' => 'weight',
            'purity' => 'purity',
            'amount' => 'amount',
            'balance change',
            'receivable' => 'balance_change',
            'pending balance',
            'pending receivable' => 'pending_balance',
            'ref no' => 'ref_no',
            'description' => 'description',
            default => str_replace(' ', '_', $header),
        };
    }

    private function getCsvValue(array $row, array $columns, string $column): string
    {
        if (!isset($columns[$column])) {
            return '';
        }

        $index = $columns[$column];
        return isset($row[$index]) ? trim((string) $row[$index]) : '';
    }

    private function parseNumber(string $value): float
    {
        $normalized = str_replace([',', '₹', '$', ' '], '', trim($value));
        if ($normalized === '' || $normalized === '-') {
            return 0.0;
        }

        return (float) $normalized;
    }

    private function parseWeightAndUnit(string $weightRaw): array
    {
        $value = trim($weightRaw);
        if ($value === '' || $value === '-') {
            return [null, null];
        }

        $parts = preg_split('/\s+/', $value, 2);
        $weight = isset($parts[0]) ? (float) $parts[0] : null;
        if (!$weight || $weight <= 0) {
            return [null, null];
        }

        $unit = isset($parts[1]) ? strtolower(trim((string) $parts[1])) : null;
        $allowedUnits = ['gram', 'kilogram', 'milligram', 'tola', 'ounce', 'other'];
        if ($unit !== null && $unit !== '' && !in_array($unit, $allowedUnits, true)) {
            $unit = null;
        }

        return [$weight, $unit ?: null];
    }

    private function isCsvRowEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function respondSave(bool $status, string $message, ?string $redirect = null, array $errors = [], int $statusCode = 200)
    {
        if ($this->request->isAJAX()) {
            $payload = [
                'status' => $status,
                'message' => $message,
            ];

            if (!empty($redirect)) {
                $payload['redirect'] = $redirect;
            }

            if (!empty($errors)) {
                $payload['errors'] = $errors;
            }

            return $this->response->setStatusCode($statusCode)->setJSON($payload);
        }

        if ($status) {
            return redirect()->to($redirect ?: site_url('salepurchase'))->with('success', $message);
        }

        return redirect()->back()->withInput()->with('error', $message)->with('errors', $errors);
    }

    private function getCurrentShopId(): ?int
    {
        $shopId = session()->get('auth_shop_id');
        if (!empty($shopId)) {
            return (int) $shopId;
        }

        $referenceCode = (string) session()->get('auth_reference');
        if ($referenceCode === '') {
            return null;
        }

        $employee = $this->salePurchaseModel->getUserByRefCode($referenceCode);
        if (!$employee || empty($employee->shop_id)) {
            return null;
        }

        session()->set('auth_shop_id', (int) $employee->shop_id);
        return (int) $employee->shop_id;
    }
}
