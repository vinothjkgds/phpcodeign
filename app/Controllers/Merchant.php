<?php
/**
 * Merchant Controller
 *
 * Manages merchants: list, add, edit, delete, save.
 *
 * Author: Vinothkumar
 * Version: 1.0
 * Date: 2026-05-27
 */

namespace App\Controllers;

use App\Models\MerchantModel;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\HTTP\ResponseInterface;

class Merchant extends BaseController
{
    protected $merchantModel;

    public function __construct()
    {
        $this->merchantModel = new MerchantModel();
        helper(['form', 'url']);
    }

    /**
     * Display merchant list page
     *
     * @return string
     */
    public function index(): string
    {
        return view('index', ['body_content' => 'merchant/list']);
    }

    /**
     * Get merchant list for DataTables
     *
     * @return \CodeIgniter\HTTP\Response
     */
    public function getMerchantListJson()
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

        $postData = $this->request->getPost();
        $response = $this->merchantModel->getMerchantListDT($postData, $shopId);
        return $this->response->setJSON($response);
    }

    /**
     * Display add merchant page
     *
     * @return string
     */
    public function add()
    {
        return view('index', ['body_content' => 'merchant/add']);
    }

    /**
     * Edit merchant page
     *
     * @param string $merchantCode
     * @return \CodeIgniter\HTTP\Response|string
     */
    public function edit($merchantCode)
    {
        try {
            $shopId = $this->getCurrentShopId();
            if ($shopId === null) {
                return $this->response->setStatusCode(403)->setJSON(['status' => false, 'message' => 'Unable to identify current shop.']);
            }

            $merchantInfo = $this->merchantModel->getMerchantByRefCode($merchantCode, $shopId);
            if (!$merchantInfo) {
                return $this->response->setStatusCode(404)->setJSON(['status' => false, 'message' => 'Merchant not found']);
            }
            $data = [
                'body_content' => 'merchant/edit',
                'merchantInfo' => $merchantInfo
            ];
            return view('index', $data);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Delete merchant
     *
     * @param string|null $code
     * @return \CodeIgniter\HTTP\Response
     */
    public function delete($code = null)
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return $this->response->setStatusCode(ResponseInterface::HTTP_METHOD_NOT_ALLOWED)->setBody('Method Not Allowed');
        }
        if (!$code) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)->setBody('Merchant code required');
        }

        $shopId = $this->getCurrentShopId();
        if ($shopId === null) {
            return $this->response->setStatusCode(403)->setJSON(['status' => false, 'message' => 'Unable to identify current shop.']);
        }

        $merchant = $this->merchantModel->getMerchantByRefCode($code, $shopId);
        if (!$merchant) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)->setBody('Merchant not found');
        }

        $success = $this->merchantModel->where('reference_code', $code)->where('shop_id', $shopId)->delete();

        if ($success) {
            $this->removeLogoIfExists($merchant->profile_logo ?? null);
            $this->removeLogoIfExists($merchant->shop_logo ?? null);
        }

        return $this->response->setJSON([
            'status' => $success ? true : false,
            'message' => $success ? 'Merchant deleted successfully' : 'Failed to delete merchant',
            'id' => $code
        ]);
    }

    /**
     * Save merchant (add or update)
     *
     * @param string $merchantCode
     * @return \CodeIgniter\HTTP\Response
     */
    public function save($merchantCode = '')
    {
        try {
            $shopId = $this->getCurrentShopId();
            if ($shopId === null) {
                return $this->respondMerchantSave(false, 'Unable to identify current shop.', null, [], 403);
            }

            $validation = \Config\Services::validation();

            $emailValidation = 'permit_empty|valid_email';
            $phoneValidation = 'required';
            $gstinValidation = 'permit_empty|regex_match[/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/i]';
            $merchantType = (string) $this->request->getPost('merchant_type');
            $existingMerchant = null;

            if ($merchantCode) {
                $existingMerchant = $this->merchantModel->getMerchantByRefCode($merchantCode, $shopId);
                if (!$existingMerchant) {
                    return $this->response->setStatusCode(404)->setJSON(['status' => false, 'message' => 'Merchant not found']);
                }
            }

            $shopNameValidation = $merchantType === 'shop' ? 'required' : 'permit_empty';
            $shopAddressValidation = $merchantType === 'shop' ? 'required' : 'permit_empty';

            $validation->setRules([
                'merchant_name' => [
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'Merchant name is required.'
                    ]
                ],
                'merchant_type' => [
                    'rules' => 'required|in_list[individual,shop]',
                    'errors' => [
                        'required' => 'Merchant type is required.',
                        'in_list' => 'Merchant type must be either individual or shop.'
                    ]
                ],
                'email' => [
                    'rules' => $emailValidation,
                    'errors' => [
                        'valid_email' => 'Please provide a valid email.',
                        'is_unique' => 'This email is already registered.'
                    ]
                ],
                'phone' => [
                    'rules' => $phoneValidation,
                    'errors' => [
                        'required' => 'Phone number is required.',
                        'is_unique' => 'This phone number is already in use.'
                    ]
                ],
                'gstin' => [
                    'rules' => $gstinValidation,
                    'errors' => [
                        'regex_match' => 'Please provide a valid Indian GSTIN.'
                    ]
                ],
                'shop_name' => [
                    'rules' => $shopNameValidation,
                    'errors' => [
                        'required' => 'Shop name is required for shop merchants.'
                    ]
                ],
                'shop_address' => [
                    'rules' => $shopAddressValidation,
                    'errors' => [
                        'required' => 'Shop address is required for shop merchants.'
                    ]
                ]
            ]);

            if (!$validation->run($this->request->getPost())) {
                $errors = $validation->getErrors();
                return $this->respondMerchantSave(false, implode(' | ', $errors), null, $errors, 422);
            }

            $merchantId = $existingMerchant ? (int) $existingMerchant->merchant_id : null;
            $phone = trim((string) $this->request->getPost('phone'));
            $email = trim((string) $this->request->getPost('email'));

            if ($this->merchantModel->isPhoneExistsForShop($phone, $shopId, $merchantId)) {
                return $this->respondMerchantSave(false, 'This phone number is already in use.', null, ['phone' => 'This phone number is already in use.'], 422);
            }

            if ($email !== '' && $this->merchantModel->isEmailExistsForShop($email, $shopId, $merchantId)) {
                return $this->respondMerchantSave(false, 'This email is already registered.', null, ['email' => 'This email is already registered.'], 422);
            }

            $data = [
                'shop_id' => $shopId,
                'merchant_type' => $merchantType,
                'merchant_name' => $this->request->getPost('merchant_name'),
                'phone' => $phone,
                'email' => $email !== '' ? $email : null,
                'personal_address' => $this->request->getPost('personal_address') ?: null,
                'shop_name' => $this->request->getPost('shop_name') ?: null,
                'shop_address' => $this->request->getPost('shop_address') ?: null,
                'gstin' => $this->request->getPost('gstin') ?: null,
                'commission_percent' => 0,
                'is_active' => $this->request->getPost('is_active') ? true : false
            ];

            if ($merchantType === 'shop') {
                $data['profile_logo'] = null;
                $data['shop_logo'] = ($existingMerchant && $existingMerchant->merchant_type === 'shop')
                    ? ($existingMerchant->shop_logo ?? null)
                    : null;

                if ($existingMerchant && !empty($existingMerchant->profile_logo)) {
                    $this->removeLogoIfExists($existingMerchant->profile_logo);
                }

                $shopLogoFile = $this->request->getFile('shop_logo');
                if ($shopLogoFile && $shopLogoFile->getError() !== UPLOAD_ERR_NO_FILE) {
                    if (!$shopLogoFile->isValid()) {
                        return $this->respondMerchantSave(false, 'Invalid shop logo upload.', null, [], 422);
                    }

                    $newShopLogoPath = $this->storeMerchantLogo($shopLogoFile, 'shop');
                    if ($existingMerchant && !empty($existingMerchant->shop_logo)) {
                        $this->removeLogoIfExists($existingMerchant->shop_logo);
                    }
                    $data['shop_logo'] = $newShopLogoPath;
                }
            } else {
                $data['shop_logo'] = null;
                $data['profile_logo'] = ($existingMerchant && $existingMerchant->merchant_type === 'individual')
                    ? ($existingMerchant->profile_logo ?? null)
                    : null;

                if ($existingMerchant && !empty($existingMerchant->shop_logo)) {
                    $this->removeLogoIfExists($existingMerchant->shop_logo);
                }

                $profileLogoFile = $this->request->getFile('profile_logo');
                if ($profileLogoFile && $profileLogoFile->getError() !== UPLOAD_ERR_NO_FILE) {
                    if (!$profileLogoFile->isValid()) {
                        return $this->respondMerchantSave(false, 'Invalid profile logo upload.', null, [], 422);
                    }

                    $newProfileLogoPath = $this->storeMerchantLogo($profileLogoFile, 'profile');
                    if ($existingMerchant && !empty($existingMerchant->profile_logo)) {
                        $this->removeLogoIfExists($existingMerchant->profile_logo);
                    }
                    $data['profile_logo'] = $newProfileLogoPath;
                }
            }

            if ($merchantCode) {
                // Update existing merchant
                $this->merchantModel->updateMerchantByCode($merchantCode, $shopId, $data);
                return $this->respondMerchantSave(true, 'Merchant updated successfully', site_url('merchant'));
            } else {
                // Add new merchant
                $this->merchantModel->addMerchant($data);
                return $this->respondMerchantSave(true, 'Merchant added successfully', site_url('merchant'));
            }
        } catch (\Exception $e) {
            return $this->respondMerchantSave(false, $e->getMessage(), null, [], 500);
        }
    }

    private function respondMerchantSave(bool $status, string $message, ?string $redirect = null, array $errors = [], int $statusCode = 200)
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
            return redirect()->to($redirect ?: site_url('merchant'))->with('success', $message);
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

        $employee = $this->merchantModel->getUserByRefCode($referenceCode);
        if (!$employee || empty($employee->shop_id)) {
            return null;
        }

        session()->set('auth_shop_id', (int) $employee->shop_id);
        return (int) $employee->shop_id;
    }

    private function storeMerchantLogo(UploadedFile $file, string $logoType): string
    {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $extension = strtolower($file->getExtension() ?: $file->getClientExtension());
        $mimeType = strtolower((string) $file->getMimeType());

        if (!in_array($extension, $allowedExtensions, true)) {
            throw new \RuntimeException('Logo must be JPG, JPEG, PNG, or WEBP format.');
        }

        if (!in_array($mimeType, $allowedMimeTypes, true)) {
            throw new \RuntimeException('Only image files are allowed for logo upload.');
        }

        $relativeDir = 'uploads/merchant/' . $logoType;
        $targetDir = FCPATH . $relativeDir;

        if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
            throw new \RuntimeException('Unable to create logo upload directory.');
        }

        $newName = time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
        $file->move($targetDir, $newName, true);

        return $relativeDir . '/' . $newName;
    }

    private function removeLogoIfExists(?string $relativePath): void
    {
        if (empty($relativePath)) {
            return;
        }

        $cleanPath = ltrim($relativePath, '/');
        $publicPath = FCPATH . $cleanPath;
        $legacyPath = ROOTPATH . $cleanPath;

        if (is_file($publicPath)) {
            @unlink($publicPath);
            return;
        }

        if (is_file($legacyPath)) {
            @unlink($legacyPath);
        }
    }
}
