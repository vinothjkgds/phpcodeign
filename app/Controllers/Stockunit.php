<?php

namespace App\Controllers;

use App\Models\StockUnitModel;
use CodeIgniter\HTTP\ResponseInterface;

class Stockunit extends BaseController
{
    protected StockUnitModel $stockUnitModel;

    public function __construct()
    {
        $this->stockUnitModel = new StockUnitModel();
        helper(['form', 'url']);
    }

    public function index(): string
    {
        return view('index', ['body_content' => 'stockunit/list']);
    }

    public function add(): string
    {
        $shopId = $this->getCurrentShopId();
        $hasBaseUnit = $shopId !== null ? $this->stockUnitModel->hasBaseUnit($shopId) : true;

        return view('index', [
            'body_content' => 'stockunit/add',
            'hasBaseUnit' => $hasBaseUnit,
        ]);
    }

    public function edit(string $referenceCode)
    {
        $shopId = $this->getCurrentShopId();
        if ($shopId === null) {
            return $this->response->setStatusCode(403)->setJSON(['status' => false, 'message' => 'Unable to identify current shop.']);
        }

        $unitInfo = $this->stockUnitModel->getUnitByRefCode($referenceCode, $shopId);
        if (!$unitInfo) {
            return $this->response->setStatusCode(404)->setJSON(['status' => false, 'message' => 'Stock unit not found']);
        }

        return view('index', [
            'body_content' => 'stockunit/edit',
            'unitInfo' => $unitInfo,
        ]);
    }

    public function getStockUnitListJson()
    {
        $shopId = $this->getCurrentShopId();
        if ($shopId === null) {
            return $this->response->setStatusCode(403)->setJSON([
                'draw' => (int) ($this->request->getPost('draw') ?? 0),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'message' => 'Unable to identify current shop.',
            ]);
        }

        return $this->response->setJSON($this->stockUnitModel->getUnitListDT($this->request->getPost(), $shopId));
    }

    public function save(string $referenceCode = '')
    {
        try {
            $shopId = $this->getCurrentShopId();
            if ($shopId === null) {
                return $this->respondSave(false, 'Unable to identify current shop.', null, [], 403);
            }

            $existing = null;
            if ($referenceCode !== '') {
                $existing = $this->stockUnitModel->getUnitByRefCode($referenceCode, $shopId);
                if (!$existing) {
                    return $this->respondSave(false, 'Stock unit not found.', null, [], 404);
                }
            }

            $mustCreateBaseUnit = $referenceCode === '' && !$this->stockUnitModel->hasBaseUnit($shopId);

            $validation = \Config\Services::validation();
            $validation->setRules([
                'unit_name' => [
                    'rules' => 'required|max_length[100]',
                    'errors' => [
                        'required' => 'Unit name is required.',
                        'max_length' => 'Unit name cannot exceed 100 characters.',
                    ],
                ],
                'unit_code' => [
                    'rules' => 'required|max_length[50]|regex_match[/^[a-z0-9_]+$/]',
                    'errors' => [
                        'required' => 'Unit code is required.',
                        'max_length' => 'Unit code cannot exceed 50 characters.',
                        'regex_match' => 'Unit code can only contain lowercase letters, numbers, and underscore.',
                    ],
                ],
                'unit_symbol' => [
                    'rules' => 'permit_empty|max_length[20]',
                    'errors' => [
                        'max_length' => 'Unit symbol cannot exceed 20 characters.',
                    ],
                ],
                'unit_type' => [
                    'rules' => 'required|in_list[mass,volume,count,other]',
                    'errors' => [
                        'required' => 'Unit type is required.',
                        'in_list' => 'Please select a valid unit type.',
                    ],
                ],
                'factor_to_base' => [
                    'rules' => $mustCreateBaseUnit ? 'permit_empty|decimal|greater_than[0]' : 'required|decimal|greater_than[0]',
                    'errors' => [
                        'required' => 'Conversion factor is required.',
                        'decimal' => 'Conversion factor must be a valid decimal number.',
                        'greater_than' => 'Conversion factor must be greater than 0.',
                    ],
                ],
            ]);

            if (!$validation->run($this->request->getPost())) {
                $errors = $validation->getErrors();
                return $this->respondSave(false, implode(' | ', $errors), null, $errors, 422);
            }

            $unitCode = strtolower(trim((string) $this->request->getPost('unit_code')));
            $excludeUnitId = $existing ? (int) $existing->unit_id : null;
            if ($this->stockUnitModel->unitCodeExistsForShop($unitCode, $shopId, $excludeUnitId)) {
                return $this->respondSave(false, 'Unit code already exists for this shop.', null, ['unit_code' => 'Unit code already exists for this shop.'], 422);
            }

            $isBase = $mustCreateBaseUnit ? true : ($this->request->getPost('is_base') ? true : false);
            $factorInput = trim((string) ($this->request->getPost('factor_to_base') ?? ''));
            $factorToBase = $factorInput !== '' ? (float) $factorInput : 1.0;

            if ($existing && (int) ($existing->is_base ?? 0) === 1 && !$isBase) {
                $otherBaseCount = $this->stockUnitModel->where('shop_id', $shopId)
                    ->where('is_base', 1)
                    ->where('unit_id !=', (int) $existing->unit_id)
                    ->countAllResults();
                if ($otherBaseCount <= 0) {
                    return $this->respondSave(false, 'A base unit is required for the shop. Set another unit as base before unsetting this one.', null, ['is_base' => 'Base unit is required.'], 422);
                }
            }

            $data = [
                'shop_id' => $shopId,
                'unit_name' => trim((string) $this->request->getPost('unit_name')),
                'unit_code' => $unitCode,
                'unit_symbol' => trim((string) ($this->request->getPost('unit_symbol') ?? '')) ?: null,
                'unit_type' => trim((string) $this->request->getPost('unit_type')),
                'factor_to_base' => $factorToBase,
                'is_base' => $isBase ? 1 : 0,
                'is_active' => $this->request->getPost('is_active') ? 1 : 0,
            ];

            if ($mustCreateBaseUnit) {
                $data['is_base'] = 1;
                $data['is_active'] = 1;
                $data['factor_to_base'] = 1.0;
            }

            if ($referenceCode !== '') {
                $this->stockUnitModel->update($existing->unit_id, $data);
                if ((int) $data['is_base'] === 1) {
                    $this->stockUnitModel->setBaseUnitByRefCode($shopId, $referenceCode);
                }
                return $this->respondSave(true, 'Stock unit updated successfully.', site_url('stockunit'));
            }

            $this->stockUnitModel->insert($data);
            if ((int) $data['is_base'] === 1) {
                $insertId = (int) $this->stockUnitModel->getInsertID();
                $created = $this->stockUnitModel->find($insertId);
                if (!empty($created['reference_code'])) {
                    $this->stockUnitModel->setBaseUnitByRefCode($shopId, (string) $created['reference_code']);
                }
            }

            return $this->respondSave(true, 'Stock unit added successfully.', site_url('stockunit'));
        } catch (\Throwable $e) {
            return $this->respondSave(false, $e->getMessage(), null, [], 500);
        }
    }

    public function delete(string $referenceCode = null)
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return $this->response->setStatusCode(ResponseInterface::HTTP_METHOD_NOT_ALLOWED)->setBody('Method Not Allowed');
        }

        if (!$referenceCode) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)->setBody('Stock unit reference code required');
        }

        $shopId = $this->getCurrentShopId();
        if ($shopId === null) {
            return $this->response->setStatusCode(403)->setJSON(['status' => false, 'message' => 'Unable to identify current shop.']);
        }

        $unit = $this->stockUnitModel->getUnitByRefCode($referenceCode, $shopId);
        if (!$unit) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)->setJSON(['status' => false, 'message' => 'Stock unit not found']);
        }

        if ((int) ($unit->is_base ?? 0) === 1) {
            return $this->response->setStatusCode(422)->setJSON(['status' => false, 'message' => 'Base unit cannot be deleted. Set another base unit first.']);
        }

        $usageCount = $this->stockUnitModel->getUnitUsageCount($shopId, (string) ($unit->unit_code ?? ''));
        if ($usageCount > 0) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => false,
                'message' => 'This unit is used in product/transaction data and cannot be deleted.',
            ]);
        }

        $success = $this->stockUnitModel
            ->where('reference_code', $referenceCode)
            ->where('shop_id', $shopId)
            ->delete();

        return $this->response->setJSON([
            'status' => $success ? true : false,
            'message' => $success ? 'Stock unit deleted successfully' : 'Failed to delete stock unit',
            'id' => $referenceCode,
        ]);
    }

    private function respondSave(bool $status, string $message, ?string $redirect = null, array $errors = [], int $statusCode = 200)
    {
        if ($this->request->isAJAX()) {
            $payload = ['status' => $status, 'message' => $message];
            if (!empty($redirect)) {
                $payload['redirect'] = $redirect;
            }
            if (!empty($errors)) {
                $payload['errors'] = $errors;
            }

            return $this->response->setStatusCode($statusCode)->setJSON($payload);
        }

        if ($status) {
            return redirect()->to($redirect ?: site_url('stockunit'))->with('success', $message);
        }

        return redirect()->back()->withInput()->with('error', $message)->with('errors', $errors);
    }

    private function getCurrentShopId(): ?int
    {
        return $this->resolveAuthenticatedShopId();
    }
}
