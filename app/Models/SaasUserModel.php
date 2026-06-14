<?php

namespace App\Models;

use App\Traits\IsActiveTrait;
use CodeIgniter\Model;

class SaasUserModel extends Model
{
    use IsActiveTrait;

    protected $table = 'saas_users';
    protected $primaryKey = 'saas_user_id';
    protected $protectFields = false;
    protected $useTimestamps = true;

    public function getAuthByEmail(string $email): ?array
    {
        $row = $this->db->table($this->table)
            ->select('saas_user_id, reference_code, name, email, password_hash, role, is_active')
            ->where('email', $email)
            ->where('is_active', 1)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    public function getActiveByReferenceCode(string $referenceCode): ?array
    {
        $row = $this->db->table($this->table)
            ->select('saas_user_id, reference_code, name, email, role, is_active')
            ->where('reference_code', $referenceCode)
            ->where('is_active', 1)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }
}
