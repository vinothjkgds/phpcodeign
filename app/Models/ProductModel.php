<?php

namespace App\Models;

use App\Traits\IsActiveTrait;
use CodeIgniter\Model;

class ProductModel extends Model
{
    use IsActiveTrait;

    protected $table = 'products';
    protected $primaryKey = 'product_id';
    protected $protectFields = false;
    protected $useTimestamps = true;
    protected $allowedFields = [
        'shop_id',
        'product_name',
        'product_image',
        'category',
        'current_stock',
        'stock_unit',
        'reorder_level',
        'is_active'
    ];

    public function addProduct(array $data)
    {
        return $this->insert($data);
    }

    public function updateProductById(int $productId, int $shopId, array $data)
    {
        return $this->where('product_id', $productId)
            ->where('shop_id', $shopId)
            ->set($data)
            ->update();
    }

    public function getProductById(int $productId, ?int $shopId = null)
    {
        $builder = $this->db->table($this->table)
            ->where('product_id', $productId);

        if ($shopId !== null) {
            $builder->where('shop_id', $shopId);
        }

        return $builder->get()->getRow();
    }

    public function getProductListDT(array $postData, int $shopId): array
    {
        $builder = $this->db->table($this->table . ' p');
        $builder->select('p.product_id, p.product_name, p.product_image, p.category, p.current_stock, p.stock_unit, COALESCE(su.unit_symbol, p.stock_unit) AS stock_unit_label, p.reorder_level, p.is_active, p.created_at');
        $builder->join('stock_units su', 'su.shop_id = p.shop_id AND su.unit_code = p.stock_unit', 'left');
        $builder->where('p.shop_id', $shopId);
        $builder->where('p.is_active', true);

        if (!empty($postData['search']['value'])) {
            $search = trim((string) $postData['search']['value']);
            $builder->groupStart()
                ->like('p.product_name', $search)
                ->orLike('p.category', $search)
                ->groupEnd();
        }

        $columns = ['p.product_image', 'p.product_name', 'p.category', 'p.current_stock', 'p.is_active', 'p.created_at', 'p.product_id'];
        if (isset($postData['order'][0]['column'], $postData['order'][0]['dir'])) {
            $colIndex = (int) $postData['order'][0]['column'];
            $direction = strtolower((string) $postData['order'][0]['dir']) === 'asc' ? 'ASC' : 'DESC';
            $builder->orderBy($columns[$colIndex] ?? 'p.product_id', $direction);
        } else {
            $builder->orderBy('p.product_id', 'DESC');
        }

        $length = isset($postData['length']) ? (int) $postData['length'] : 10;
        $start = isset($postData['start']) ? (int) $postData['start'] : 0;
        if ($length !== -1) {
            $builder->limit($length, $start);
        }

        $result = $builder->get()->getResult();
        $data = [];

        foreach ($result as $row) {
            $actionBtns = '<a href="' . site_url('product/view/' . $row->product_id) . '" class="btn btn-sm btn-info" title="View"><i class="mdi mdi-eye"></i></a>';
            $actionBtns .= '&nbsp; <a href="' . site_url('product/edit/' . $row->product_id) . '" class="btn btn-sm btn-primary" title="Edit"><i class="mdi mdi-pencil"></i></a>';
            $actionBtns .= '&nbsp; <button type="button" class="btn btn-sm btn-danger deleteProduct" data-id="' . $row->product_id . '" title="Delete"><i class="mdi mdi-delete"></i></button>';

            $statusBadge = $row->is_active
                ? '<span class="badge badge-success">Active</span>'
                : '<span class="badge badge-danger">Inactive</span>';

            // Stock status badge
            $stockStatus = 'OK';
            $stockBadgeClass = 'badge-success';
            if ($row->current_stock <= $row->reorder_level) {
                $stockStatus = 'LOW';
                $stockBadgeClass = 'badge-warning';
            }
            $stockUnitLabel = (string) ($row->stock_unit_label ?? $row->stock_unit ?? '');
            $stockBadge = '<span class="badge ' . $stockBadgeClass . '">' . number_format($row->current_stock, 3) . ' ' . esc($stockUnitLabel) . '</span>';

            $productImage = '-';
            if (!empty($row->product_image)) {
                $imageUrl = base_url(ltrim((string) $row->product_image, '/'));
                $productImage = '<img src="' . esc($imageUrl, 'attr') . '" alt="Product" style="width:40px;height:40px;object-fit:cover;border-radius:6px;" />';
            }

            $data[] = [
                'product_image' => $productImage,
                'product_name' => esc($row->product_name),
                'category' => esc($row->category ?? '-'),
                'stock' => $stockBadge,
                'is_active' => $statusBadge,
                'created_at' => $this->formatListDateTime($row->created_at ?? null),
                'action' => $actionBtns,
            ];
        }

        $total = $this->db->table($this->table)->where('shop_id', $shopId)->where('is_active', true)->countAllResults();

        $builderCount = $this->db->table($this->table . ' p');
        $builderCount->where('p.shop_id', $shopId);
        $builderCount->where('p.is_active', true);
        if (!empty($postData['search']['value'])) {
            $search = trim((string) $postData['search']['value']);
            $builderCount->groupStart()
                ->like('p.product_name', $search)
                ->orLike('p.category', $search)
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

    public function getUserByRefCode(string $referenceCode)
    {
        return $this->db->table('users')
            ->select('shop_id, reference_code')
            ->where('reference_code', $referenceCode)
            ->get()
            ->getRow();
    }

    public function getStockHistoryListDT(array $postData, int $shopId): array
    {
        $builder = $this->db->table('product_stock_history sh');
        $builder->select('sh.history_id, sh.created_at, sh.movement_type, sh.quantity, sh.stock_unit, COALESCE(su.unit_symbol, sh.stock_unit) AS stock_unit_label, sh.stock_before, sh.stock_after, sh.txn_ref, sh.notes, p.product_name');
        $builder->join('products p', 'p.product_id = sh.product_id', 'inner');
        $builder->join('stock_units su', 'su.shop_id = sh.shop_id AND su.unit_code = sh.stock_unit', 'left');
        $builder->where('sh.shop_id', $shopId);

        $productId = (int) ($postData['filter_product_id'] ?? 0);
        if ($productId > 0) {
            $builder->where('sh.product_id', $productId);
        }

        $movementType = trim((string) ($postData['filter_movement_type'] ?? ''));
        if ($movementType !== '' && in_array($movementType, ['opening', 'sale', 'purchase', 'adjustment'], true)) {
            $builder->where('sh.movement_type', $movementType);
        }

        $fromDate = trim((string) ($postData['filter_from_date'] ?? ''));
        if ($fromDate !== '') {
            $builder->where('DATE(sh.created_at) >=', $fromDate);
        }

        $toDate = trim((string) ($postData['filter_to_date'] ?? ''));
        if ($toDate !== '') {
            $builder->where('DATE(sh.created_at) <=', $toDate);
        }

        if (!empty($postData['search']['value'])) {
            $search = trim((string) $postData['search']['value']);
            $builder->groupStart()
                ->like('p.product_name', $search)
                ->orLike('sh.txn_ref', $search)
                ->orLike('sh.notes', $search)
                ->groupEnd();
        }

        $columns = ['sh.history_id', 'sh.created_at', 'p.product_name', 'sh.movement_type', 'sh.quantity', 'sh.stock_before', 'sh.stock_after', 'sh.txn_ref', 'sh.notes'];
        if (isset($postData['order'][0]['column'], $postData['order'][0]['dir'])) {
            $colIndex = (int) $postData['order'][0]['column'];
            $direction = strtolower((string) $postData['order'][0]['dir']) === 'asc' ? 'ASC' : 'DESC';
            $builder->orderBy($columns[$colIndex] ?? 'sh.history_id', $direction);
        } else {
            $builder->orderBy('sh.history_id', 'DESC');
        }

        $length = isset($postData['length']) ? (int) $postData['length'] : 10;
        $start = isset($postData['start']) ? (int) $postData['start'] : 0;
        if ($length !== -1) {
            $builder->limit($length, $start);
        }

        $result = $builder->get()->getResult();
        $data = [];

        foreach ($result as $row) {
            $movementLabel = ucwords(str_replace('_', ' ', (string) $row->movement_type));
            $badgeClass = match ((string) $row->movement_type) {
                'sale' => 'badge-primary',
                'purchase' => 'badge-info',
                'adjustment' => 'badge-warning',
                'opening' => 'badge-success',
                default => 'badge-dark',
            };

            $data[] = [
                'history_id' => (int) $row->history_id,
                'created_at' => $this->formatListDateTime($row->created_at ?? null),
                'product_name' => esc((string) ($row->product_name ?? '-')),
                'movement_type' => '<span class="badge ' . $badgeClass . '">' . esc($movementLabel) . '</span>',
                'quantity' => number_format((float) ($row->quantity ?? 0), 3) . ' ' . esc((string) ($row->stock_unit_label ?? $row->stock_unit ?? '')),
                'stock_before' => number_format((float) ($row->stock_before ?? 0), 3) . ' ' . esc((string) ($row->stock_unit_label ?? $row->stock_unit ?? '')),
                'stock_after' => number_format((float) ($row->stock_after ?? 0), 3) . ' ' . esc((string) ($row->stock_unit_label ?? $row->stock_unit ?? '')),
                'txn_ref' => esc((string) ($row->txn_ref ?? '-')),
                'notes' => esc((string) ($row->notes ?? '-')),
            ];
        }

        $totalBuilder = $this->db->table('product_stock_history')->where('shop_id', $shopId);
        $total = $totalBuilder->countAllResults();

        $countBuilder = $this->db->table('product_stock_history sh');
        $countBuilder->join('products p', 'p.product_id = sh.product_id', 'inner');
        $countBuilder->where('sh.shop_id', $shopId);

        if ($productId > 0) {
            $countBuilder->where('sh.product_id', $productId);
        }
        if ($movementType !== '' && in_array($movementType, ['opening', 'sale', 'purchase', 'adjustment'], true)) {
            $countBuilder->where('sh.movement_type', $movementType);
        }
        if ($fromDate !== '') {
            $countBuilder->where('DATE(sh.created_at) >=', $fromDate);
        }
        if ($toDate !== '') {
            $countBuilder->where('DATE(sh.created_at) <=', $toDate);
        }
        if (!empty($postData['search']['value'])) {
            $search = trim((string) $postData['search']['value']);
            $countBuilder->groupStart()
                ->like('p.product_name', $search)
                ->orLike('sh.txn_ref', $search)
                ->orLike('sh.notes', $search)
                ->groupEnd();
        }

        $filteredCount = $countBuilder->countAllResults();

        return [
            'draw' => isset($postData['draw']) ? (int) $postData['draw'] : 0,
            'recordsTotal' => $total,
            'recordsFiltered' => $filteredCount,
            'data' => $data,
        ];
    }

    public function getStockHistoryByProduct(int $shopId, int $productId, int $limit = 500): array
    {
        return $this->db->table('product_stock_history sh')
            ->select('sh.history_id, sh.created_at, sh.movement_type, sh.quantity, sh.stock_unit, COALESCE(su.unit_symbol, sh.stock_unit) AS stock_unit_label, sh.stock_before, sh.stock_after, sh.txn_ref, sh.notes')
            ->join('stock_units su', 'su.shop_id = sh.shop_id AND su.unit_code = sh.stock_unit', 'left')
            ->where('sh.shop_id', $shopId)
            ->where('sh.product_id', $productId)
            ->orderBy('sh.created_at', 'DESC')
            ->orderBy('sh.history_id', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
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
