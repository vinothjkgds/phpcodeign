<?php
/**
 * Merchant Model
 *
 * Handles database operations for merchants.
 *
 * Author: Vinothkumar
 * Version: 1.0
 * Date: 2026-05-27
 */

namespace App\Models;

use CodeIgniter\Model;

class MerchantModel extends Model
{
    protected $table = 'merchants';
    protected $primaryKey = 'merchant_id';
    protected $protectFields = false;
    protected $useTimestamps = true;

    /**
     * Add a new merchant record
     *
     * @param array $data
     * @return int|string Insert ID
     */
    public function addMerchant(array $data)
    {
        return $this->insert($data);
    }

    /**
     * Update merchant record by reference code
     *
     * @param string $referenceCode
     * @param array $data
     * @return bool
     */
    public function updateMerchantByCode(string $referenceCode, int $shopId, array $data)
    {
        return $this->where('reference_code', $referenceCode)
            ->where('shop_id', $shopId)
            ->set($data)
            ->update();
    }

    /**
     * Get merchant info by reference code
     *
     * @param string $merchantCode
     * @return object|null
     */
    public function getMerchantByRefCode(string $merchantCode, ?int $shopId = null)
    {
        $builder = $this->db->table($this->table)
            ->where('reference_code', $merchantCode);

        if ($shopId !== null) {
            $builder->where('shop_id', $shopId);
        }

        return $builder->get()->getRow();
    }

    /**
     * Get merchant list for DataTables (server-side)
     *
     * @param array $postData
     * @return array
     */
    public function getMerchantListDT(array $postData, int $shopId)
    {
        $builder = $this->db->table($this->table . ' m');
        $builder->join(
            '(SELECT shop_id, merchant_id, COALESCE(SUM(receivable_delta), 0) AS net_balance FROM merchant_ledger GROUP BY shop_id, merchant_id) ml',
            'ml.merchant_id = m.merchant_id AND ml.shop_id = m.shop_id',
            'left'
        );
        $builder->select("m.merchant_id, m.merchant_name, m.merchant_type, m.profile_logo, m.shop_logo, m.phone, m.email, m.is_active, m.created_at, m.reference_code,
            CASE WHEN COALESCE(ml.net_balance, 0) > 0 THEN COALESCE(ml.net_balance, 0) ELSE 0 END AS receivable_amount,
            CASE WHEN COALESCE(ml.net_balance, 0) < 0 THEN ABS(COALESCE(ml.net_balance, 0)) ELSE 0 END AS payable_amount");
        $builder->where('m.shop_id', $shopId);

        // --- Search Filter
        if (!empty($postData['search']['value'])) {
            $search = $postData['search']['value'];
            $builder->groupStart()
                ->like('m.merchant_name', $search)
                ->orLike('m.phone', $search)
                ->orLike('m.email', $search)
                ->orLike('m.shop_name', $search)
                ->groupEnd();
        }

        // --- Ordering
        $columns = ['m.merchant_name', 'm.merchant_type', 'm.merchant_id', 'm.phone', 'm.email', 'receivable_amount', 'payable_amount', 'm.is_active', 'm.created_at', 'm.merchant_id'];
        if (isset($postData['order'])) {
            $colIndex = $postData['order'][0]['column'];
            $builder->orderBy($columns[$colIndex] ?? 'm.merchant_id', $postData['order'][0]['dir']);
        } else {
            $builder->orderBy('m.merchant_id', 'DESC');
        }

        // --- Pagination
        if ($postData['length'] != -1) {
            $builder->limit($postData['length'], $postData['start']);
        }

        // --- Fetch Data
        $query = $builder->get();
        $result = $query->getResult();
        $data = [];

        foreach ($result as $row) {
            $actionBtns = '<a href="' . site_url('merchant/view/' . $row->reference_code) . '" class="btn btn-sm btn-info" title="View"><i class="mdi mdi-eye"></i></a>
            <a href="' . site_url('merchant/edit/' . $row->reference_code) . '" class="btn btn-sm btn-primary" title="Edit"><i class="mdi mdi-pencil"></i></a>
            <button type="button" class="btn btn-sm btn-danger deleteMerchant" data-id="' . $row->reference_code . '" title="Delete"><i class="mdi mdi-delete"></i></button>';

            $typeLabel = ucfirst($row->merchant_type);
            $statusBadge = $row->is_active
                ? '<span class="badge badge-success">Active</span>'
                : '<span class="badge badge-danger">Inactive</span>';
            $logoPath = $row->merchant_type === 'shop' ? ($row->shop_logo ?? null) : ($row->profile_logo ?? null);
            $logoHtml = !empty($logoPath)
                ? '<img src="' . base_url(ltrim($logoPath, '/')) . '" alt="' . $typeLabel . ' Logo" style="max-height:36px; max-width:36px; border-radius:50%; object-fit:cover;">'
                : '-';

            $data[] = [
                'merchant_name' => $row->merchant_name,
                'merchant_type' => $typeLabel,
                'logo' => $logoHtml,
                'phone' => $row->phone ?? '-',
                'email' => $row->email ?? '-',
                'receivable_amount' => number_format((float) ($row->receivable_amount ?? 0), 2),
                'payable_amount' => number_format((float) ($row->payable_amount ?? 0), 2),
                'is_active' => $statusBadge,
                'created_at' => date('Y-m-d', strtotime($row->created_at)),
                'action' => $actionBtns
            ];
        }

        // --- Total and Filtered Count
        $total = $this->db->table($this->table)->where('shop_id', $shopId)->countAllResults();
        $builderCount = $this->db->table($this->table . ' m');
        $builderCount->where('m.shop_id', $shopId);
        if (!empty($postData['search']['value'])) {
            $search = $postData['search']['value'];
            $builderCount->groupStart()
                ->like('m.merchant_name', $search)
                ->orLike('m.phone', $search)
                ->orLike('m.email', $search)
                ->orLike('m.shop_name', $search)
                ->groupEnd();
        }
        $filteredCount = $builderCount->countAllResults();

        return [
            'draw' => isset($postData['draw']) ? (int) $postData['draw'] : 0,
            'recordsTotal' => $total,
            'recordsFiltered' => $filteredCount,
            'data' => $data
        ];
    }

    /**
     * Get all active merchants
     *
     * @return array
     */
    public function getActiveMerchants(?int $shopId = null)
    {
        $builder = $this->where('is_active', 1);

        if ($shopId !== null) {
            $builder->where('shop_id', $shopId);
        }

        return $builder->findAll();
    }

    public function getUserByRefCode(string $referenceCode)
    {
        return $this->db->table('users')
            ->select('shop_id, reference_code')
            ->where('reference_code', $referenceCode)
            ->get()
            ->getRow();
    }

    public function isPhoneExistsForShop(string $phone, int $shopId, ?int $excludeMerchantId = null): bool
    {
        $builder = $this->db->table($this->table)
            ->where('shop_id', $shopId)
            ->where('phone', $phone);

        if ($excludeMerchantId !== null) {
            $builder->where('merchant_id !=', $excludeMerchantId);
        }

        return $builder->countAllResults() > 0;
    }

    public function isEmailExistsForShop(string $email, int $shopId, ?int $excludeMerchantId = null): bool
    {
        $builder = $this->db->table($this->table)
            ->where('shop_id', $shopId)
            ->where('email', $email);

        if ($excludeMerchantId !== null) {
            $builder->where('merchant_id !=', $excludeMerchantId);
        }

        return $builder->countAllResults() > 0;
    }

    public function getMerchantTransactionRows(int $shopId, int $merchantId): array
    {
        $builder = $this->db->table('merchant_ledger l');
        $builder->select("l.ledger_id, l.entry_date, l.entry_type, l.txn_ref, l.description, l.weight, l.weight_unit, l.purity, l.amount, l.receivable_delta, p.product_name,
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
        $builder->join('products p', 'p.product_id = l.product_id', 'left');
        $builder->where('l.shop_id', $shopId);
        $builder->where('l.merchant_id', $merchantId);
        $builder->orderBy('l.entry_date', 'DESC');
        $builder->orderBy('l.ledger_id', 'DESC');

        return $builder->get()->getResultArray();
    }

    public function getMerchantNetBalance(int $shopId, int $merchantId): float
    {
        $row = $this->db->table('merchant_ledger')
            ->select('COALESCE(SUM(receivable_delta), 0) AS net_balance', false)
            ->where('shop_id', $shopId)
            ->where('merchant_id', $merchantId)
            ->get()
            ->getRowArray();

        return (float) ($row['net_balance'] ?? 0);
    }
}
