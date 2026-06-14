<?php

namespace App\Models;

use App\Traits\IsActiveTrait;
use CodeIgniter\Model;

class SalePurchaseModel extends Model
{
    use IsActiveTrait;

    protected $table = 'merchant_ledger';
    protected $primaryKey = 'ledger_id';
    protected $protectFields = false;
    protected $useTimestamps = true;

    public function addEntry(array $data)
    {
        return $this->insert($data);
    }

    public function getProductByIdForShop(int $shopId, int $productId): ?array
    {
        $row = $this->db->table('products')
            ->select('product_id, product_name, current_stock, stock_unit, reorder_level, is_active')
            ->where('shop_id', $shopId)
            ->where('product_id', $productId)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    public function addEntryWithStock(array $data, ?int $createdBy = null): array
    {
        $entryType = (string) ($data['entry_type'] ?? '');
        $shopId = (int) ($data['shop_id'] ?? 0);
        $productId = isset($data['product_id']) ? (int) $data['product_id'] : 0;
        $weight = isset($data['weight']) ? (float) $data['weight'] : 0.0;
        $weightUnit = (string) ($data['weight_unit'] ?? '');

        $isTradeEntry = in_array($entryType, ['sale', 'purchase'], true);

        $this->db->transBegin();

        try {
            $stockBefore = null;
            $stockAfter = null;
            $productStockUnit = null;
            $convertedWeightForStock = null;

            if ($isTradeEntry) {
                if ($productId <= 0 || $weight <= 0 || $weightUnit === '' || $shopId <= 0) {
                    throw new \RuntimeException('Product, weight and weight unit are required for sale/purchase.');
                }

                $product = $this->db->query(
                    'SELECT product_id, product_name, current_stock, stock_unit, is_active FROM products WHERE shop_id = ? AND product_id = ? LIMIT 1 FOR UPDATE',
                    [$shopId, $productId]
                )->getRowArray();

                if (!$product || !(int) ($product['is_active'] ?? 0)) {
                    throw new \RuntimeException('Selected product is invalid or inactive.');
                }

                $productStockUnit = (string) ($product['stock_unit'] ?? '');
                $convertedWeightForStock = $this->convertWeightToUnit($weight, $weightUnit, $productStockUnit);
                if ($convertedWeightForStock === null) {
                    throw new \RuntimeException('Cannot convert weight unit from ' . $weightUnit . ' to ' . $productStockUnit . '.');
                }

                $stockBefore = (float) ($product['current_stock'] ?? 0);
                if ($entryType === 'sale') {
                    if ($stockBefore < $convertedWeightForStock) {
                        throw new \RuntimeException('Insufficient stock. Available: ' . number_format($stockBefore, 3, '.', '') . ' ' . $productStockUnit . '.');
                    }
                    $stockAfter = $stockBefore - $convertedWeightForStock;
                } else {
                    $stockAfter = $stockBefore + $convertedWeightForStock;
                }

                $this->db->table('products')
                    ->where('shop_id', $shopId)
                    ->where('product_id', $productId)
                    ->update([
                        'current_stock' => $stockAfter,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
            }

            $this->db->table($this->table)->insert($data);
            $ledgerId = (int) $this->db->insertID();

            if ($isTradeEntry && $productId > 0 && $stockBefore !== null && $stockAfter !== null) {
                $this->db->table('product_stock_history')->insert([
                    'shop_id' => $shopId,
                    'product_id' => $productId,
                    'movement_type' => $entryType,
                    'quantity' => $convertedWeightForStock,
                    'stock_unit' => $productStockUnit,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'reference_type' => 'merchant_ledger',
                    'reference_id' => $ledgerId,
                    'txn_ref' => $data['txn_ref'] ?? null,
                    'notes' => $data['description'] ?? null,
                    'created_by' => $createdBy,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }

            if ($this->db->transStatus() === false) {
                throw new \RuntimeException('Failed to save transaction.');
            }

            $this->db->transCommit();

            return [
                'status' => true,
                'ledger_id' => $ledgerId,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
            ];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    private function convertWeightToUnit(float $value, string $fromUnit, string $toUnit): ?float
    {
        $from = strtolower(trim($fromUnit));
        $to = strtolower(trim($toUnit));

        if ($from === '' || $to === '') {
            return null;
        }

        if ($from === $to) {
            return $value;
        }

        $massUnitToGram = [
            'milligram' => 0.001,
            'gram' => 1,
            'kilogram' => 1000,
            'tola' => 11.6638038,
            'ounce' => 28.349523125,
        ];

        if (!isset($massUnitToGram[$from], $massUnitToGram[$to])) {
            return null;
        }

        $valueInGram = $value * $massUnitToGram[$from];
        $converted = $valueInGram / $massUnitToGram[$to];

        return round($converted, 6);
    }

    public function getSalePurchaseListDT(array $postData, int $shopId): array
    {
        $builder = $this->db->table($this->table . ' l');
        $builder->select("l.ledger_id, l.entry_date, l.entry_type, l.txn_ref, l.description, l.weight, l.weight_unit, l.purity, l.amount, l.receivable_delta, m.merchant_name, p.product_name,
            (
                SELECT COALESCE(SUM(ml.receivable_delta), 0)
                FROM merchant_ledger ml
                WHERE ml.shop_id = l.shop_id
                  AND ml.merchant_id = l.merchant_id
                  AND (
                      ml.entry_date < l.entry_date
                      OR (ml.entry_date = l.entry_date AND ml.ledger_id <= l.ledger_id)
                  )
            ) AS current_receivable_balance");
        $builder->join('merchants m', 'm.merchant_id = l.merchant_id', 'inner');
        $builder->join('products p', 'p.product_id = l.product_id', 'left');
        $builder->where('l.shop_id', $shopId);

        $this->applyFilters($builder, $postData);

        if (!empty($postData['search']['value'])) {
            $search = trim((string) $postData['search']['value']);
            $builder->groupStart()
                ->like('m.merchant_name', $search)
                ->orLike('p.product_name', $search)
                ->orLike('l.txn_ref', $search)
                ->orLike('l.description', $search)
                ->orLike('l.purity', $search)
                ->groupEnd();
        }

        $columns = [
            'l.ledger_id',
            'l.entry_date',
            'l.entry_type',
            'm.merchant_name',
            'p.product_name',
            'l.weight',
            'l.purity',
            'l.amount',
            'l.receivable_delta',
            'l.txn_ref',
            'l.description',
        ];

        if (isset($postData['order'][0]['column'], $postData['order'][0]['dir'])) {
            $colIndex = (int) $postData['order'][0]['column'];
            $direction = strtolower((string) $postData['order'][0]['dir']) === 'asc' ? 'ASC' : 'DESC';
            $orderColumn = $columns[$colIndex] ?? 'l.ledger_id';
            $builder->orderBy($orderColumn, $direction);
            if ($orderColumn !== 'l.ledger_id') {
                $builder->orderBy('l.ledger_id', 'DESC');
            }
        } else {
            $builder->orderBy('l.created_at', 'DESC');
            $builder->orderBy('l.ledger_id', 'DESC');
        }

        $length = isset($postData['length']) ? (int) $postData['length'] : 10;
        $start = isset($postData['start']) ? (int) $postData['start'] : 0;
        if ($length !== -1) {
            $builder->limit($length, $start);
        }

        $result = $builder->get()->getResult();
        $data = [];

        foreach ($result as $row) {
            $entryTypeRaw = (string) $row->entry_type;
            $entryTypeLabel = ucwords(str_replace('_', ' ', $entryTypeRaw));
            $badgeClass = match ($entryTypeRaw) {
                'sale'             => 'badge-primary',
                'purchase'         => 'badge-info',
                'payment_received' => 'badge-success',
                'payment_paid'     => 'badge-warning',
                'opening'          => 'badge-danger',
                default            => 'badge-dark',
            };
            $entryTypeBadge = '<span class="badge ' . $badgeClass . ' badge-pill">' . $entryTypeLabel . '</span>';
            $productName = !empty($row->product_name) ? $row->product_name : '-';
            $weightText = '-';
            if ($row->weight !== null) {
                $weightText = rtrim(rtrim(number_format((float) $row->weight, 3, '.', ''), '0'), '.');
                if (!empty($row->weight_unit)) {
                    $weightText .= ' ' . $this->shortUnitLabel((string) $row->weight_unit);
                }
            }

            $invoiceAction = '<a href="' . site_url('salepurchase/invoice/' . (int) $row->ledger_id) . '" class="btn btn-sm btn-info" target="_blank" title="Print/Download Invoice"><i class="mdi mdi-printer"></i></a>';

            $data[] = [
                's_no' => (int) $row->ledger_id,
                'entry_date' => $this->formatListDateTime($row->entry_date ?? null),
                'entry_type' => $entryTypeBadge,
                'merchant_name' => esc($row->merchant_name),
                'product_name' => esc($productName),
                'weight' => esc($weightText),
                'purity' => esc($row->purity ?? '-'),
                'amount' => number_format((float) $row->amount, 2),
                'receivable_delta' => number_format((float) $row->receivable_delta, 2),
                'current_receivable_balance' => number_format((float) ($row->current_receivable_balance ?? 0), 2),
                'txn_ref' => esc($row->txn_ref ?? '-'),
                'description' => esc($row->description ?? '-'),
                'action' => $invoiceAction,
            ];
        }

        $total = $this->db->table($this->table)
            ->where('shop_id', $shopId)
            ->whereIn('entry_type', ['opening', 'sale', 'purchase', 'payment_received', 'payment_paid'])
            ->countAllResults();

        $builderCount = $this->db->table($this->table . ' l');
        $builderCount->join('merchants m', 'm.merchant_id = l.merchant_id', 'inner');
        $builderCount->join('products p', 'p.product_id = l.product_id', 'left');
        $builderCount->where('l.shop_id', $shopId);
        $this->applyFilters($builderCount, $postData);

        if (!empty($postData['search']['value'])) {
            $search = trim((string) $postData['search']['value']);
            $builderCount->groupStart()
                ->like('m.merchant_name', $search)
                ->orLike('p.product_name', $search)
                ->orLike('l.txn_ref', $search)
                ->orLike('l.description', $search)
                ->orLike('l.purity', $search)
                ->groupEnd();
        }

        $filteredCount = $builderCount->countAllResults();

        return [
            'draw' => isset($postData['draw']) ? (int) $postData['draw'] : 0,
            'recordsTotal' => $total,
            'recordsFiltered' => $filteredCount,
            'data' => $data,
        ];
    }

    public function getSalePurchaseExportRows(array $filters, int $shopId): array
    {
        $builder = $this->db->table($this->table . ' l');
        $builder->select("l.ledger_id, l.entry_date, l.entry_type, l.txn_ref, l.description, l.weight, l.weight_unit, l.purity, l.amount, l.receivable_delta, m.merchant_name, p.product_name,
            (
                SELECT COALESCE(SUM(ml.receivable_delta), 0)
                FROM merchant_ledger ml
                WHERE ml.shop_id = l.shop_id
                  AND ml.merchant_id = l.merchant_id
                  AND (
                      ml.entry_date < l.entry_date
                      OR (ml.entry_date = l.entry_date AND ml.ledger_id <= l.ledger_id)
                  )
            ) AS current_receivable_balance");
        $builder->join('merchants m', 'm.merchant_id = l.merchant_id', 'inner');
        $builder->join('products p', 'p.product_id = l.product_id', 'left');
        $builder->where('l.shop_id', $shopId);

        $this->applyFilters($builder, $filters);

        $builder->orderBy('l.entry_date', 'DESC');
        $builder->orderBy('l.ledger_id', 'DESC');

        return $builder->get()->getResultArray();
    }

    private function applyFilters($builder, array $postData): void
    {
        $allowedEntryTypes = ['opening', 'sale', 'purchase', 'payment_received', 'payment_paid'];
        $builder->whereIn('l.entry_type', $allowedEntryTypes);

        $entryType = trim((string) ($postData['filter_entry_type'] ?? ''));
        if ($entryType !== '' && in_array($entryType, $allowedEntryTypes, true)) {
            $builder->where('l.entry_type', $entryType);
        }

        $merchantId = (int) ($postData['filter_merchant_id'] ?? 0);
        if ($merchantId > 0) {
            $builder->where('l.merchant_id', $merchantId);
        }

        $fromDate = trim((string) ($postData['filter_from_date'] ?? ''));
        if ($fromDate !== '') {
            $builder->where('DATE(l.entry_date) >=', $fromDate);
        }

        $toDate = trim((string) ($postData['filter_to_date'] ?? ''));
        if ($toDate !== '') {
            $builder->where('DATE(l.entry_date) <=', $toDate);
        }
    }

    public function getActiveMerchants(int $shopId): array
    {
        return $this->db->table('merchants')
            ->select('merchant_id, merchant_name')
            ->where('shop_id', $shopId)
            ->where('is_active', 1)
            ->orderBy('merchant_name', 'ASC')
            ->get()
            ->getResult();
    }

    public function getActiveProducts(int $shopId): array
    {
        return $this->db->table('products')
            ->select('product_id, product_name, current_stock, stock_unit, reorder_level')
            ->where('shop_id', $shopId)
            ->where('is_active', 1)
            ->orderBy('product_name', 'ASC')
            ->get()
            ->getResult();
    }

    public function getUserByRefCode(string $referenceCode)
    {
        return $this->db->table('users')
            ->select('shop_id, reference_code')
            ->where('reference_code', $referenceCode)
            ->get()
            ->getRow();
    }

    public function getInvoiceByLedgerId(int $shopId, int $ledgerId): ?array
    {
        $builder = $this->db->table($this->table . ' l');
        $builder->select("l.ledger_id, l.entry_date, l.entry_type, l.txn_ref, l.description, l.weight, l.weight_unit, l.purity, l.amount, l.receivable_delta, l.payable_delta,
            m.merchant_name, m.phone AS merchant_phone, m.email AS merchant_email, m.gstin AS merchant_gstin, m.personal_address, m.shop_address,
            p.product_name,
            s.shop_name, s.owner_name, s.mobile_no AS shop_mobile_no, s.email AS shop_email, s.address AS shop_address_full, s.gstin AS shop_gstin,
            (
                SELECT COALESCE(SUM(ml.receivable_delta), 0)
                FROM merchant_ledger ml
                WHERE ml.shop_id = l.shop_id
                  AND ml.merchant_id = l.merchant_id
                  AND (
                      ml.entry_date < l.entry_date
                      OR (ml.entry_date = l.entry_date AND ml.ledger_id <= l.ledger_id)
                  )
            ) AS current_receivable_balance");
        $builder->join('merchants m', 'm.merchant_id = l.merchant_id', 'inner');
        $builder->join('products p', 'p.product_id = l.product_id', 'left');
        $builder->join('shops s', 's.shop_id = l.shop_id', 'inner');
        $builder->where('l.shop_id', $shopId);
        $builder->where('l.ledger_id', $ledgerId);

        $row = $builder->get()->getRowArray();
        return $row ?: null;
    }

    private function shortUnitLabel(string $unit): string
    {
        return match (strtolower(trim($unit))) {
            'kilogram' => 'kg',
            'gram' => 'gm',
            'milligram' => 'mg',
            'tola' => 'tola',
            'ounce' => 'oz',
            default => $unit,
        };
    }

    private function formatListDateTime(?string $dateTime): string
    {
        if (empty($dateTime)) {
            return '-';
        }

        $ts = strtotime($dateTime);
        if ($ts === false) {
            return '-';
        }

        $day = (int) date('j', $ts);
        return $day . $this->ordinalSuffix($day) . date(' M Y g:i A', $ts);
    }

    private function ordinalSuffix(int $day): string
    {
        if ($day % 100 >= 11 && $day % 100 <= 13) {
            return 'th';
        }

        return match ($day % 10) {
            1 => 'st',
            2 => 'nd',
            3 => 'rd',
            default => 'th',
        };
    }
}
