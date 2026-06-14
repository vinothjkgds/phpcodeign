<?php

namespace App\Models;

use CodeIgniter\Model;

class ShopOnboardingModel extends Model
{
    protected $table = 'shop_onboarding';
    protected $primaryKey = 'onboarding_id';
    protected $protectFields = false;
    protected $useTimestamps = true;

    public function getAllWithCreator(): array
    {
        return $this->db->table($this->table . ' o')
            ->select('o.*, su.name as created_by_name, su2.name as approved_by_name, s.shop_name as created_shop_name, s.is_active as shop_is_active')
            ->join('saas_users su', 'su.saas_user_id = o.created_by_saas_user', 'left')
            ->join('saas_users su2', 'su2.saas_user_id = o.approved_by_saas_user', 'left')
            ->join('shops s', 's.shop_id = o.created_shop_id', 'left')
            ->orderBy('o.onboarding_id', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function getByReferenceCode(string $referenceCode): ?array
    {
        $row = $this->db->table($this->table)
            ->where('reference_code', $referenceCode)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }
}
