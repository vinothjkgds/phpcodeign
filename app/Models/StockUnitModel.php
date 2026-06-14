<?php

namespace App\Models;

use App\Traits\IsActiveTrait;
use CodeIgniter\Model;

class StockUnitModel extends Model
{
    use IsActiveTrait;

    protected $table = 'stock_units';
    protected $primaryKey = 'unit_id';
    protected $protectFields = false;
    protected $useTimestamps = true;

    public function getUnitByRefCode(string $referenceCode, int $shopId)
    {
        return $this->db->table($this->table)
            ->where('reference_code', $referenceCode)
            ->where('shop_id', $shopId)
            ->get()
            ->getRow();
    }

    public function unitCodeExistsForShop(string $unitCode, int $shopId, ?int $excludeUnitId = null): bool
    {
        $builder = $this->db->table($this->table)
            ->where('shop_id', $shopId)
            ->where('unit_code', strtolower(trim($unitCode)));

        if ($excludeUnitId !== null) {
            $builder->where('unit_id !=', $excludeUnitId);
        }

        return $builder->countAllResults() > 0;
    }

    public function hasBaseUnit(int $shopId): bool
    {
        return $this->db->table($this->table)
            ->where('shop_id', $shopId)
            ->where('is_base', 1)
            ->countAllResults() > 0;
    }

    public function clearBaseUnit(int $shopId): bool
    {
        return (bool) $this->db->table($this->table)
            ->where('shop_id', $shopId)
            ->update(['is_base' => 0, 'updated_at' => date('Y-m-d H:i:s')]);
    }

    public function setBaseUnitByRefCode(int $shopId, string $referenceCode): bool
    {
        $this->db->transBegin();

        $this->db->table($this->table)
            ->where('shop_id', $shopId)
            ->update(['is_base' => 0, 'updated_at' => date('Y-m-d H:i:s')]);

        $this->db->table($this->table)
            ->where('shop_id', $shopId)
            ->where('reference_code', $referenceCode)
            ->update(['is_base' => 1, 'is_active' => 1, 'updated_at' => date('Y-m-d H:i:s')]);

        if ($this->db->transStatus() === false) {
            $this->db->transRollback();
            return false;
        }

        $this->db->transCommit();
        return true;
    }

    public function getBaseUnit(int $shopId): ?array
    {
        $row = $this->db->table($this->table)
            ->select('unit_id, reference_code, unit_name, unit_code, unit_symbol, unit_type, factor_to_base')
            ->where('shop_id', $shopId)
            ->where('is_base', 1)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    public function getActiveUnits(int $shopId): array
    {
        return $this->db->table($this->table)
            ->select('unit_id, reference_code, unit_name, unit_code, unit_symbol, unit_type, factor_to_base, is_base, is_active')
            ->where('shop_id', $shopId)
            ->where('is_active', 1)
            ->orderBy('is_base', 'DESC')
            ->orderBy('unit_name', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function getUnitsForDropdown(int $shopId, bool $onlyActive = true, ?string $includeUnitCode = null): array
    {
        $builder = $this->db->table($this->table)
            ->select('unit_name, unit_code, unit_symbol, unit_type, factor_to_base, is_base, is_active')
            ->where('shop_id', $shopId);

        if ($onlyActive) {
            $builder->where('is_active', 1);
        }

        if (!empty($includeUnitCode)) {
            $builder->orGroupStart()
                ->where('shop_id', $shopId)
                ->where('unit_code', strtolower(trim($includeUnitCode)))
                ->groupEnd();
        }

        return $builder
            ->orderBy('is_base', 'DESC')
            ->orderBy('unit_name', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function getUnitLabelByCode(int $shopId, string $unitCode): string
    {
        $row = $this->db->table($this->table)
            ->select('unit_name, unit_symbol, unit_code')
            ->where('shop_id', $shopId)
            ->where('unit_code', strtolower(trim($unitCode)))
            ->get()
            ->getRowArray();

        if (!$row) {
            return $unitCode;
        }

        $symbol = trim((string) ($row['unit_symbol'] ?? ''));
        if ($symbol !== '') {
            return $symbol;
        }

        return (string) ($row['unit_code'] ?? $unitCode);
    }

    public function isUnitCodeActiveForShop(int $shopId, string $unitCode): bool
    {
        return $this->db->table($this->table)
            ->where('shop_id', $shopId)
            ->where('unit_code', strtolower(trim($unitCode)))
            ->where('is_active', 1)
            ->countAllResults() > 0;
    }

    public function getUnitListDT(array $postData, int $shopId): array
    {
        $builder = $this->db->table($this->table . ' su');
        $builder->select('su.unit_id, su.reference_code, su.unit_name, su.unit_code, su.unit_symbol, su.unit_type, su.factor_to_base, su.is_base, su.is_active, su.created_at');
        $builder->where('su.shop_id', $shopId);

        if (!empty($postData['search']['value'])) {
            $search = trim((string) $postData['search']['value']);
            $builder->groupStart()
                ->like('su.unit_name', $search)
                ->orLike('su.unit_code', $search)
                ->orLike('su.unit_symbol', $search)
                ->orLike('su.unit_type', $search)
                ->groupEnd();
        }

        $columns = ['su.unit_name', 'su.unit_code', 'su.unit_symbol', 'su.unit_type', 'su.factor_to_base', 'su.is_base', 'su.is_active', 'su.created_at'];
        if (isset($postData['order'][0]['column'], $postData['order'][0]['dir'])) {
            $colIndex = (int) $postData['order'][0]['column'];
            $direction = strtolower((string) $postData['order'][0]['dir']) === 'asc' ? 'ASC' : 'DESC';
            $builder->orderBy($columns[$colIndex] ?? 'su.unit_id', $direction);
        } else {
            $builder->orderBy('su.is_base', 'DESC')->orderBy('su.unit_name', 'ASC');
        }

        $length = isset($postData['length']) ? (int) $postData['length'] : 10;
        $start = isset($postData['start']) ? (int) $postData['start'] : 0;
        if ($length !== -1) {
            $builder->limit($length, $start);
        }

        $rows = $builder->get()->getResult();
        $data = [];

        foreach ($rows as $row) {
            $statusBadge = (int) $row->is_active === 1
                ? '<span class="badge badge-success">Active</span>'
                : '<span class="badge badge-danger">Inactive</span>';

            $baseBadge = (int) $row->is_base === 1
                ? '<span class="badge badge-primary">Base Unit</span>'
                : '<span class="badge badge-light">Derived</span>';

            $actionBtns = '<a href="' . site_url('stockunit/edit/' . $row->reference_code) . '" class="btn btn-sm btn-primary" title="Edit"><i class="mdi mdi-pencil"></i></a>';
            $actionBtns .= '&nbsp; <button type="button" class="btn btn-sm btn-danger deleteStockUnit" data-id="' . $row->reference_code . '" title="Delete"><i class="mdi mdi-delete"></i></button>';

            $data[] = [
                'unit_name' => esc((string) $row->unit_name),
                'unit_code' => esc((string) $row->unit_code),
                'unit_symbol' => esc((string) ($row->unit_symbol ?: '-')),
                'unit_type' => ucfirst((string) $row->unit_type),
                'factor_to_base' => number_format((float) $row->factor_to_base, 8, '.', ''),
                'is_base' => $baseBadge,
                'is_active' => $statusBadge,
                'created_at' => $this->formatListDateTime($row->created_at ?? null),
                'action' => $actionBtns,
            ];
        }

        $total = $this->db->table($this->table)->where('shop_id', $shopId)->countAllResults();

        $countBuilder = $this->db->table($this->table . ' su');
        $countBuilder->where('su.shop_id', $shopId);
        if (!empty($postData['search']['value'])) {
            $search = trim((string) $postData['search']['value']);
            $countBuilder->groupStart()
                ->like('su.unit_name', $search)
                ->orLike('su.unit_code', $search)
                ->orLike('su.unit_symbol', $search)
                ->orLike('su.unit_type', $search)
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

    public function getUnitUsageCount(int $shopId, string $unitCode): int
    {
        $products = $this->db->table('products')
            ->where('shop_id', $shopId)
            ->where('stock_unit', $unitCode)
            ->countAllResults();

        $ledger = $this->db->table('merchant_ledger')
            ->where('shop_id', $shopId)
            ->where('weight_unit', $unitCode)
            ->countAllResults();

        $history = $this->db->table('product_stock_history')
            ->where('shop_id', $shopId)
            ->where('stock_unit', $unitCode)
            ->countAllResults();

        return $products + $ledger + $history;
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
