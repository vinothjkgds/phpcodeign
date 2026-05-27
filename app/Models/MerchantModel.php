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
    public function updateMerchantByCode(string $referenceCode, array $data)
    {
        return $this->where('reference_code', $referenceCode)->set($data)->update();
    }

    /**
     * Get merchant info by reference code
     *
     * @param string $merchantCode
     * @return object|null
     */
    public function getMerchantByRefCode(string $merchantCode)
    {
        return $this->db->table($this->table)
            ->where('reference_code', $merchantCode)
            ->get()
            ->getRow();
    }

    /**
     * Get merchant list for DataTables (server-side)
     *
     * @param array $postData
     * @return array
     */
    public function getMerchantListDT(array $postData)
    {
        $builder = $this->db->table($this->table . ' m');
        $builder->select('m.merchant_id, m.merchant_name, m.merchant_type, m.profile_logo, m.shop_logo, m.phone, m.email, m.commission_percent, m.is_active, m.created_at, m.reference_code');

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
        $columns = ['m.merchant_name', 'm.merchant_type', 'm.merchant_id', 'm.phone', 'm.email', 'm.commission_percent', 'm.is_active', 'm.created_at', 'm.merchant_id'];
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
            $actionBtns = '<a href="' . site_url('merchant/edit/' . $row->reference_code) . '" class="btn btn-sm btn-primary" title="Edit"><i class="mdi mdi-pencil"></i></a>
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
                'commission_percent' => $row->commission_percent . '%',
                'is_active' => $statusBadge,
                'created_at' => date('Y-m-d', strtotime($row->created_at)),
                'action' => $actionBtns
            ];
        }

        // --- Total and Filtered Count
        $total = $this->countAll();
        $builderCount = $this->db->table($this->table . ' m');
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
            'draw' => intval($postData['draw']),
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
    public function getActiveMerchants()
    {
        return $this->where('is_active', 1)->findAll();
    }
}
