<?php

namespace App\Controllers;

use App\Models\SalePurchaseModel;

class Salepurchase extends BaseController
{
    protected $salePurchaseModel;

    public function __construct()
    {
        $this->salePurchaseModel = new SalePurchaseModel();
        helper(['form', 'url']);
    }

    public function index()
    {
        $shopId = $this->getCurrentShopId();
        if ($shopId === null) {
            return redirect()->to(site_url('dashboard'))->with('error', 'Unable to identify current shop.');
        }

        return view('index', [
            'body_content' => 'salepurchase/list',
            'merchants' => $this->salePurchaseModel->getActiveMerchants($shopId),
        ]);
    }

    public function add()
    {
        $shopId = $this->getCurrentShopId();
        if ($shopId === null) {
            return redirect()->to(site_url('dashboard'))->with('error', 'Unable to identify current shop.');
        }

        return view('index', [
            'body_content' => 'salepurchase/add',
            'merchants' => $this->salePurchaseModel->getActiveMerchants($shopId),
            'products' => $this->salePurchaseModel->getActiveProducts($shopId),
        ]);
    }

    public function save()
    {
        try {
            $shopId = $this->getCurrentShopId();
            if ($shopId === null) {
                return $this->respondSave(false, 'Unable to identify current shop.', null, [], 403);
            }

            $entryType = trim((string) $this->request->getPost('entry_type'));
            $requiresWeight = in_array($entryType, ['sale', 'purchase'], true);
            $weightRule = $requiresWeight ? 'required|numeric|greater_than[0]' : 'permit_empty|numeric|greater_than[0]';
            $weightUnitRule = $requiresWeight ? 'required|in_list[gram,kilogram,milligram,tola,ounce,other]' : 'permit_empty|in_list[gram,kilogram,milligram,tola,ounce,other]';

            $validation = \Config\Services::validation();
            $validation->setRules([
                'entry_date' => [
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'Entry date is required.',
                    ],
                ],
                'merchant_id' => [
                    'rules' => 'required|integer',
                    'errors' => [
                        'required' => 'Please select merchant.',
                        'integer' => 'Invalid merchant selected.',
                    ],
                ],
                'entry_type' => [
                    'rules' => 'required|in_list[opening,sale,purchase,payment_received,payment_paid]',
                    'errors' => [
                        'required' => 'Entry type is required.',
                        'in_list' => 'Entry type must be opening, sale, purchase, payment received, or payment paid.',
                    ],
                ],
                'opening_balance_type' => [
                    'rules' => 'permit_empty|in_list[receivable,payable]',
                    'errors' => [
                        'in_list' => 'Opening balance type must be receivable or payable.',
                    ],
                ],
                'product_id' => [
                    'rules' => 'permit_empty|integer',
                ],
                'weight' => [
                    'rules' => $weightRule,
                    'errors' => [
                        'required' => 'Weight is required.',
                        'numeric' => 'Weight must be numeric.',
                        'greater_than' => 'Weight must be greater than 0.',
                    ],
                ],
                'weight_unit' => [
                    'rules' => $weightUnitRule,
                    'errors' => [
                        'required' => 'Weight unit is required.',
                        'in_list' => 'Please select a valid weight unit.',
                    ],
                ],
                'amount' => [
                    'rules' => 'required|numeric|greater_than[0]',
                    'errors' => [
                        'required' => 'Amount is required.',
                        'numeric' => 'Amount must be numeric.',
                        'greater_than' => 'Amount must be greater than 0.',
                    ],
                ],
            ]);

            if (!$validation->run($this->request->getPost())) {
                $errors = $validation->getErrors();
                return $this->respondSave(false, implode(' | ', $errors), null, $errors, 422);
            }

            $merchantId = (int) $this->request->getPost('merchant_id');
            $amount = (float) $this->request->getPost('amount');
            $entryDateInput = trim((string) $this->request->getPost('entry_date'));
            $entryDate = str_replace('T', ' ', $entryDateInput);
            $openingBalanceType = trim((string) $this->request->getPost('opening_balance_type'));

            $allowedMerchants = $this->salePurchaseModel->getActiveMerchants($shopId);
            $merchantIds = array_map(static fn($item) => (int) $item->merchant_id, $allowedMerchants);
            if (!in_array($merchantId, $merchantIds, true)) {
                return $this->respondSave(false, 'Selected merchant is invalid.', null, ['merchant_id' => 'Selected merchant is invalid.'], 422);
            }

            $receivableDelta = 0;
            $payableDelta = 0;
            if ($entryType === 'opening') {
                if (!in_array($openingBalanceType, ['receivable', 'payable'], true)) {
                    return $this->respondSave(false, 'Please select opening balance type.', null, ['opening_balance_type' => 'Please select opening balance type.'], 422);
                }

                if ($openingBalanceType === 'receivable') {
                    $receivableDelta = $amount;
                }

                if ($openingBalanceType === 'payable') {
                    $receivableDelta = -$amount;
                }
            }
            if ($entryType === 'sale') {
                $receivableDelta = $amount;
            }
            if ($entryType === 'purchase') {
                $receivableDelta = -$amount;
            }
            if ($entryType === 'payment_received') {
                $receivableDelta = -$amount;
            }
            if ($entryType === 'payment_paid') {
                $receivableDelta = $amount;
            }

            $weightInput = trim((string) $this->request->getPost('weight'));
            $weightUnitInput = trim((string) $this->request->getPost('weight_unit'));

            $data = [
                'shop_id' => $shopId,
                'merchant_id' => $merchantId,
                'entry_date' => $entryDate,
                'entry_type' => $entryType,
                'txn_ref' => trim((string) $this->request->getPost('txn_ref')) ?: null,
                'product_id' => (int) ($this->request->getPost('product_id') ?: 0) ?: null,
                'description' => trim((string) $this->request->getPost('description')) ?: null,
                'weight' => $weightInput !== '' ? (float) $weightInput : null,
                'weight_unit' => $weightUnitInput !== '' ? $weightUnitInput : null,
                'purity' => trim((string) $this->request->getPost('purity')) ?: null,
                'amount' => $amount,
                'receivable_delta' => $receivableDelta,
                'payable_delta' => $payableDelta,
            ];

            $this->salePurchaseModel->addEntry($data);
            return $this->respondSave(true, 'Sale/Purchase entry added successfully.', site_url('salepurchase'));
        } catch (\Throwable $e) {
            return $this->respondSave(false, $e->getMessage(), null, [], 500);
        }
    }

    public function getSalePurchaseListJson()
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

        $response = $this->salePurchaseModel->getSalePurchaseListDT($this->request->getPost(), $shopId);
        return $this->response->setJSON($response);
    }

    private function respondSave(bool $status, string $message, ?string $redirect = null, array $errors = [], int $statusCode = 200)
    {
        if ($this->request->isAJAX()) {
            $payload = [
                'status' => $status,
                'message' => $message,
            ];

            if (!empty($redirect)) {
                $payload['redirect'] = $redirect;
            }

            if (!empty($errors)) {
                $payload['errors'] = $errors;
            }

            return $this->response->setStatusCode($statusCode)->setJSON($payload);
        }

        if ($status) {
            return redirect()->to($redirect ?: site_url('salepurchase'))->with('success', $message);
        }

        return redirect()->back()->withInput()->with('error', $message)->with('errors', $errors);
    }

    private function getCurrentShopId(): ?int
    {
        $shopId = session()->get('auth_shop_id');
        if (!empty($shopId)) {
            return (int) $shopId;
        }

        $referenceCode = (string) session()->get('auth_reference');
        if ($referenceCode === '') {
            return null;
        }

        $employee = $this->salePurchaseModel->getUserByRefCode($referenceCode);
        if (!$employee || empty($employee->shop_id)) {
            return null;
        }

        session()->set('auth_shop_id', (int) $employee->shop_id);
        return (int) $employee->shop_id;
    }
}
