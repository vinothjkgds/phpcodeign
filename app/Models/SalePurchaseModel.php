<?php

namespace App\Models;

use CodeIgniter\Model;

class SalePurchaseModel extends Model
{
    protected $table = 'merchant_ledger';
    protected $primaryKey = 'ledger_id';
    protected $protectFields = false;
    protected $useTimestamps = true;

    public function addEntry(array $data)
    {
        return $this->insert($data);
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
            $entryTypeLabel = ucwords(str_replace('_', ' ', (string) $row->entry_type));
            $productName = !empty($row->product_name) ? $row->product_name : '-';
            $weightText = '-';
            if ($row->weight !== null) {
                $weightText = rtrim(rtrim(number_format((float) $row->weight, 3, '.', ''), '0'), '.');
                if (!empty($row->weight_unit)) {
                    $weightText .= ' ' . ucfirst((string) $row->weight_unit);
                }
            }

            $data[] = [
                's_no' => (int) $row->ledger_id,
                'entry_date' => !empty($row->entry_date) ? date('Y-m-d H:i', strtotime($row->entry_date)) : '-',
                'entry_type' => $entryTypeLabel,
                'merchant_name' => esc($row->merchant_name),
                'product_name' => esc($productName),
                'weight' => esc($weightText),
                'purity' => esc($row->purity ?? '-'),
                'amount' => number_format((float) $row->amount, 2),
                'receivable_delta' => number_format((float) $row->receivable_delta, 2),
                'current_receivable_balance' => number_format((float) ($row->current_receivable_balance ?? 0), 2),
                'txn_ref' => esc($row->txn_ref ?? '-'),
                'description' => esc($row->description ?? '-'),
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
            ->select('product_id, product_name, purity')
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
}
