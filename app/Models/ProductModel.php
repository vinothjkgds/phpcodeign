<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'product_id';
    protected $protectFields = false;
    protected $useTimestamps = true;

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
        $builder->select('p.product_id, p.product_name, p.product_image, p.category, p.purity, p.is_active, p.created_at');
        $builder->where('p.shop_id', $shopId);

        if (!empty($postData['search']['value'])) {
            $search = trim((string) $postData['search']['value']);
            $builder->groupStart()
                ->like('p.product_name', $search)
                ->orLike('p.category', $search)
                ->orLike('p.purity', $search)
                ->groupEnd();
        }

        $columns = ['p.product_image', 'p.product_name', 'p.category', 'p.purity', 'p.is_active', 'p.created_at', 'p.product_id'];
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
            $actionBtns = '<a href="' . site_url('product/edit/' . $row->product_id) . '" class="btn btn-sm btn-primary" title="Edit"><i class="mdi mdi-pencil"></i></a>';
            $actionBtns .= '&nbsp; <button type="button" class="btn btn-sm btn-danger deleteProduct" data-id="' . $row->product_id . '" title="Delete"><i class="mdi mdi-delete"></i></button>';

            $statusBadge = $row->is_active
                ? '<span class="badge badge-success">Active</span>'
                : '<span class="badge badge-danger">Inactive</span>';

            $productImage = '-';
            if (!empty($row->product_image)) {
                $imageUrl = base_url(ltrim((string) $row->product_image, '/'));
                $productImage = '<img src="' . esc($imageUrl, 'attr') . '" alt="Product" style="width:40px;height:40px;object-fit:cover;border-radius:6px;" />';
            }

            $data[] = [
                'product_image' => $productImage,
                'product_name' => esc($row->product_name),
                'category' => esc($row->category ?? '-'),
                'purity' => esc($row->purity ?? '-'),
                'is_active' => $statusBadge,
                'created_at' => !empty($row->created_at) ? date('Y-m-d', strtotime($row->created_at)) : '-',
                'action' => $actionBtns,
            ];
        }

        $total = $this->db->table($this->table)->where('shop_id', $shopId)->countAllResults();

        $builderCount = $this->db->table($this->table . ' p');
        $builderCount->where('p.shop_id', $shopId);
        if (!empty($postData['search']['value'])) {
            $search = trim((string) $postData['search']['value']);
            $builderCount->groupStart()
                ->like('p.product_name', $search)
                ->orLike('p.category', $search)
                ->orLike('p.purity', $search)
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
}
