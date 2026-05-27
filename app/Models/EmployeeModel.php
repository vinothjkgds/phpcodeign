<?php

namespace App\Models;

use CodeIgniter\Model;

class EmployeeModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'user_id';
    protected $protectFields = false;
    protected $useTimestamps = true;

    public function addEmployee(array $data)
    {
        return $this->insert($data);
    }

    public function updateEmployeeByCode(string $referenceCode, int $shopId, array $data)
    {
        return $this->where('reference_code', $referenceCode)
            ->where('shop_id', $shopId)
            ->set($data)
            ->update();
    }

    public function getEmployeeByRefCode(string $referenceCode, ?int $shopId = null)
    {
        $builder = $this->db->table($this->table)
            ->where('reference_code', $referenceCode);

        if ($shopId !== null) {
            $builder->where('shop_id', $shopId);
        }

        return $builder->get()->getRow();
    }

    public function emailExists(string $email, ?string $ignoreReferenceCode = null): bool
    {
        $builder = $this->db->table($this->table)->where('email', $email);
        if (!empty($ignoreReferenceCode)) {
            $builder->where('reference_code !=', $ignoreReferenceCode);
        }

        return $builder->countAllResults() > 0;
    }

    public function mobileExists(string $mobileNo, ?string $ignoreReferenceCode = null): bool
    {
        $builder = $this->db->table($this->table)->where('mobileno', $mobileNo);
        if (!empty($ignoreReferenceCode)) {
            $builder->where('reference_code !=', $ignoreReferenceCode);
        }

        return $builder->countAllResults() > 0;
    }

    public function getEmployeeListDT(array $postData, int $shopId): array
    {
        $builder = $this->db->table($this->table . ' u');
        $builder->select('u.user_id, u.reference_code, u.profile_image, u.name, u.email, u.mobileno, u.user_type, u.is_active, u.last_login_at, u.created_at');
        $builder->where('u.shop_id', $shopId);

        if (!empty($postData['search']['value'])) {
            $search = trim((string) $postData['search']['value']);
            $builder->groupStart()
                ->like('u.name', $search)
                ->orLike('u.email', $search)
                ->orLike('u.mobileno', $search)
                ->orLike('u.user_type', $search)
                ->groupEnd();
        }

        $columns = ['u.profile_image', 'u.name', 'u.email', 'u.mobileno', 'u.user_type', 'u.is_active', 'u.last_login_at', 'u.created_at', 'u.user_id'];
        if (isset($postData['order'][0]['column'], $postData['order'][0]['dir'])) {
            $colIndex = (int) $postData['order'][0]['column'];
            $direction = strtolower((string) $postData['order'][0]['dir']) === 'asc' ? 'ASC' : 'DESC';
            $builder->orderBy($columns[$colIndex] ?? 'u.user_id', $direction);
        } else {
            $builder->orderBy('u.user_id', 'DESC');
        }

        $length = isset($postData['length']) ? (int) $postData['length'] : 10;
        $start = isset($postData['start']) ? (int) $postData['start'] : 0;
        if ($length !== -1) {
            $builder->limit($length, $start);
        }

        $result = $builder->get()->getResult();
        $data = [];
        $currentReference = (string) session()->get('auth_reference');

        foreach ($result as $row) {
            $actionBtns = '<a href="' . site_url('employee/edit/' . $row->reference_code) . '" class="btn btn-sm btn-primary" title="Edit"><i class="mdi mdi-pencil"></i></a>';

            if ($currentReference !== $row->reference_code) {
                $actionBtns .= '&nbsp; <button type="button" class="btn btn-sm btn-danger deleteEmployee" data-id="' . $row->reference_code . '" title="Delete"><i class="mdi mdi-delete"></i></button>';
            }

            $statusBadge = $row->is_active
                ? '<span class="badge badge-success">Active</span>'
                : '<span class="badge badge-danger">Inactive</span>';

            $profileImage = '-';
            if (!empty($row->profile_image)) {
                $profileUrl = base_url(ltrim((string) $row->profile_image, '/'));
                $profileImage = '<img src="' . esc($profileUrl, 'attr') . '" alt="Employee" style="width:40px;height:40px;object-fit:cover;border-radius:50%;" />';
            }

            $data[] = [
                'profile_image' => $profileImage,
                'name' => esc($row->name),
                'email' => esc($row->email),
                'mobileno' => esc($row->mobileno ?? '-'),
                'user_type' => ucfirst((string) $row->user_type),
                'is_active' => $statusBadge,
                'last_login_at' => !empty($row->last_login_at) ? date('Y-m-d H:i', strtotime($row->last_login_at)) : '-',
                'created_at' => !empty($row->created_at) ? date('Y-m-d', strtotime($row->created_at)) : '-',
                'action' => $actionBtns,
            ];
        }

        $total = $this->db->table($this->table)->where('shop_id', $shopId)->countAllResults();

        $builderCount = $this->db->table($this->table . ' u');
        $builderCount->where('u.shop_id', $shopId);
        if (!empty($postData['search']['value'])) {
            $search = trim((string) $postData['search']['value']);
            $builderCount->groupStart()
                ->like('u.name', $search)
                ->orLike('u.email', $search)
                ->orLike('u.mobileno', $search)
                ->orLike('u.user_type', $search)
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
}