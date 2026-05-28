<?php

namespace App\Models;

use CodeIgniter\Model;

class ShopModel extends Model
{
    protected $table = 'shops';
    protected $primaryKey = 'shop_id';
    protected $protectFields = false;
    protected $useTimestamps = true;

    public function getShopById(int $shopId)
    {
        return $this->db->table($this->table)
            ->where('shop_id', $shopId)
            ->get()
            ->getRow();
    }

    public function updateShopById(int $shopId, array $data)
    {
        return $this->where('shop_id', $shopId)
            ->set($data)
            ->update();
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
