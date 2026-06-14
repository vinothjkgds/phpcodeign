<?php

namespace App\Models;

use App\Traits\IsActiveTrait;
use CodeIgniter\Model;

class CategoryModel extends Model
{
    use IsActiveTrait;

    protected $table = 'categories';
    protected $primaryKey = 'category_id';
    protected $protectFields = false;
    protected $useTimestamps = true;

    public function addCategory(array $data)
    {
        return $this->insert($data);
    }

    public function updateCategoryById(int $categoryId, int $shopId, array $data)
    {
        return $this->where('category_id', $categoryId)
            ->where('shop_id', $shopId)
            ->set($data)
            ->update();
    }

    public function updateCategoryByRefCode(string $referenceCode, int $shopId, array $data)
    {
        return $this->where('reference_code', $referenceCode)
            ->where('shop_id', $shopId)
            ->set($data)
            ->update();
    }

    public function getCategoryById(int $categoryId, ?int $shopId = null)
    {
        $builder = $this->db->table($this->table)
            ->where('category_id', $categoryId);

        if ($shopId !== null) {
            $builder->where('shop_id', $shopId);
        }

        return $builder->get()->getRow();
    }

    public function getCategoryByRefCode(string $referenceCode, ?int $shopId = null)
    {
        $builder = $this->db->table($this->table)
            ->where('reference_code', $referenceCode);

        if ($shopId !== null) {
            $builder->where('shop_id', $shopId);
        }

        return $builder->get()->getRow();
    }

    public function getCategoryListDT(array $postData, int $shopId): array
    {
        $builder = $this->db->table($this->table . ' c');
        $builder->select('c.category_id, c.reference_code, c.category_name, c.is_active, c.created_at');
        $builder->where('c.shop_id', $shopId);
        $builder->where('c.is_active', true);

        if (!empty($postData['search']['value'])) {
            $search = trim((string) $postData['search']['value']);
            $builder->groupStart()
                ->like('c.category_name', $search)
                ->groupEnd();
        }

        $columns = ['c.category_name', 'c.is_active', 'c.created_at', 'c.category_id'];
        if (isset($postData['order'][0]['column'], $postData['order'][0]['dir'])) {
            $colIndex = (int) $postData['order'][0]['column'];
            $direction = strtolower((string) $postData['order'][0]['dir']) === 'asc' ? 'ASC' : 'DESC';
            $builder->orderBy($columns[$colIndex] ?? 'c.category_id', $direction);
        } else {
            $builder->orderBy('c.category_id', 'DESC');
        }

        $length = isset($postData['length']) ? (int) $postData['length'] : 10;
        $start = isset($postData['start']) ? (int) $postData['start'] : 0;
        if ($length !== -1) {
            $builder->limit($length, $start);
        }

        $result = $builder->get()->getResult();
        $data = [];

        foreach ($result as $row) {
            $actionBtns = '<a href="' . site_url('category/edit/' . $row->reference_code) . '" class="btn btn-sm btn-primary" title="Edit"><i class="mdi mdi-pencil"></i></a>';
            $actionBtns .= '&nbsp; <button type="button" class="btn btn-sm btn-danger deleteCategory" data-id="' . $row->reference_code . '" title="Delete"><i class="mdi mdi-delete"></i></button>';

            $statusBadge = $row->is_active
                ? '<span class="badge badge-success">Active</span>'
                : '<span class="badge badge-danger">Inactive</span>';

            $data[] = [
                'category_name' => esc((string) $row->category_name),
                'is_active' => $statusBadge,
                'created_at' => $this->formatListDateTime($row->created_at ?? null),
                'action' => $actionBtns,
            ];
        }

        $total = $this->db->table($this->table)->where('shop_id', $shopId)->where('is_active', true)->countAllResults();

        $builderCount = $this->db->table($this->table . ' c');
        $builderCount->where('c.shop_id', $shopId);
        $builderCount->where('c.is_active', true);
        if (!empty($postData['search']['value'])) {
            $search = trim((string) $postData['search']['value']);
            $builderCount->groupStart()
                ->like('c.category_name', $search)
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

    public function getCategoryOptions(int $shopId, bool $onlyActive = true): array
    {
        $builder = $this->db->table($this->table)
            ->select('category_name')
            ->where('shop_id', $shopId);

        if ($onlyActive) {
            $builder->where('is_active', 1);
        }

        $rows = $builder->orderBy('category_name', 'ASC')->get()->getResultArray();
        $options = [];

        foreach ($rows as $row) {
            $name = trim((string) ($row['category_name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $options[$name] = $name;
        }

        return $options;
    }

    public function categoryExistsForShop(string $categoryName, int $shopId, ?int $excludeCategoryId = null): bool
    {
        $builder = $this->db->table($this->table)
            ->where('shop_id', $shopId)
            ->where('category_name', trim($categoryName));

        if ($excludeCategoryId !== null) {
            $builder->where('category_id !=', $excludeCategoryId);
        }

        return $builder->countAllResults() > 0;
    }

    public function countProductsByCategory(int $shopId, string $categoryName): int
    {
        return $this->db->table('products')
            ->where('shop_id', $shopId)
            ->where('category', $categoryName)
            ->countAllResults();
    }

    public function updateProductsCategoryName(int $shopId, string $oldName, string $newName): bool
    {
        return (bool) $this->db->table('products')
            ->where('shop_id', $shopId)
            ->where('category', $oldName)
            ->update([
                'category' => $newName,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
    }

    public function getUserByRefCode(string $referenceCode)
    {
        return $this->db->table('users')
            ->select('shop_id, reference_code')
            ->where('reference_code', $referenceCode)
            ->get()
            ->getRow();
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
