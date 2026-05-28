<?php

namespace App\Controllers;

use App\Models\ShopModel;
use CodeIgniter\HTTP\Files\UploadedFile;

class Shop extends BaseController
{
    protected $shopModel;

    public function __construct()
    {
        $this->shopModel = new ShopModel();
        helper(['form', 'url']);
    }

    public function index()
    {
        $shopId = $this->getCurrentShopId();
        if ($shopId === null) {
            return redirect()->to(site_url('dashboard'))->with('error', 'Unable to identify current shop.');
        }

        $shopInfo = $this->shopModel->getShopById($shopId);
        if (!$shopInfo) {
            return redirect()->to(site_url('dashboard'))->with('error', 'Shop not found.');
        }

        return view('index', [
            'body_content' => 'shop/profile',
            'shopInfo' => $shopInfo,
        ]);
    }

    public function save()
    {
        try {
            $shopId = $this->getCurrentShopId();
            if ($shopId === null) {
                return $this->respondShopSave(false, 'Unable to identify current shop.', null, [], 403);
            }

            $shopInfo = $this->shopModel->getShopById($shopId);
            if (!$shopInfo) {
                return $this->respondShopSave(false, 'Shop not found.', null, [], 404);
            }

            $validation = \Config\Services::validation();
            $validation->setRules([
                'shop_name' => [
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'Shop name is required.',
                    ],
                ],
                'email' => [
                    'rules' => 'permit_empty|valid_email',
                    'errors' => [
                        'valid_email' => 'Please provide a valid email.',
                    ],
                ],
                'gstin' => [
                    'rules' => 'permit_empty|regex_match[/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/i]',
                    'errors' => [
                        'regex_match' => 'Please provide a valid Indian GSTIN.',
                    ],
                ],
            ]);

            if (!$validation->run($this->request->getPost())) {
                $errors = $validation->getErrors();
                return $this->respondShopSave(false, implode(' | ', $errors), null, $errors, 422);
            }

            $logoFile = $this->request->getFile('logo');
            if ($logoFile && $logoFile->getError() !== UPLOAD_ERR_NO_FILE && !$logoFile->isValid()) {
                return $this->respondShopSave(false, 'Invalid logo upload.', null, ['logo' => 'Invalid logo upload.'], 422);
            }

            $bannerFile = $this->request->getFile('banner');
            if ($bannerFile && $bannerFile->getError() !== UPLOAD_ERR_NO_FILE && !$bannerFile->isValid()) {
                return $this->respondShopSave(false, 'Invalid banner upload.', null, ['banner' => 'Invalid banner upload.'], 422);
            }

            $data = [
                'shop_name' => trim((string) $this->request->getPost('shop_name')),
                'owner_name' => trim((string) $this->request->getPost('owner_name')) ?: null,
                'mobile_no' => trim((string) $this->request->getPost('mobile_no')) ?: null,
                'email' => trim((string) $this->request->getPost('email')) ?: null,
                'street' => trim((string) $this->request->getPost('street')) ?: null,
                'city' => trim((string) $this->request->getPost('city')) ?: null,
                'state_code' => trim((string) $this->request->getPost('state_code')) ?: null,
                'state_name' => trim((string) $this->request->getPost('state_name')) ?: null,
                'country' => trim((string) $this->request->getPost('country')) ?: null,
                'pincode' => trim((string) $this->request->getPost('pincode')) ?: null,
                'address' => trim((string) $this->request->getPost('address')) ?: null,
                'gstin' => trim((string) $this->request->getPost('gstin')) ?: null,
                'is_active' => $this->request->getPost('is_active') ? true : false,
            ];

            $data['logo'] = $shopInfo->logo ?? null;
            $data['banner'] = $shopInfo->banner ?? null;

            if ($logoFile && $logoFile->getError() !== UPLOAD_ERR_NO_FILE) {
                $newLogoPath = $this->storeShopImage($logoFile, 'logo');
                if (!empty($shopInfo->logo)) {
                    $this->removeShopImageIfExists($shopInfo->logo);
                }
                $data['logo'] = $newLogoPath;
            }

            if ($bannerFile && $bannerFile->getError() !== UPLOAD_ERR_NO_FILE) {
                $newBannerPath = $this->storeShopImage($bannerFile, 'banner');
                if (!empty($shopInfo->banner)) {
                    $this->removeShopImageIfExists($shopInfo->banner);
                }
                $data['banner'] = $newBannerPath;
            }

            $this->shopModel->updateShopById($shopId, $data);
            return $this->respondShopSave(true, 'Shop information updated successfully.', site_url('shop'));
        } catch (\Throwable $e) {
            return $this->respondShopSave(false, $e->getMessage(), null, [], 500);
        }
    }

    private function respondShopSave(bool $status, string $message, ?string $redirect = null, array $errors = [], int $statusCode = 200)
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
            return redirect()->to($redirect ?: site_url('shop'))->with('success', $message);
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

        $employee = $this->shopModel->getUserByRefCode($referenceCode);
        if (!$employee || empty($employee->shop_id)) {
            return null;
        }

        session()->set('auth_shop_id', (int) $employee->shop_id);
        return (int) $employee->shop_id;
    }

    private function storeShopImage(UploadedFile $file, string $type): string
    {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $extension = strtolower($file->getExtension() ?: $file->getClientExtension());
        $mimeType = strtolower((string) $file->getMimeType());

        if (!in_array($extension, $allowedExtensions, true)) {
            throw new \RuntimeException('Image must be JPG, JPEG, PNG, or WEBP format.');
        }

        if (!in_array($mimeType, $allowedMimeTypes, true)) {
            throw new \RuntimeException('Only image files are allowed for upload.');
        }

        $relativeDir = 'uploads/business/shop/' . $type;
        $targetDir = FCPATH . $relativeDir;

        if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
            throw new \RuntimeException('Unable to create shop upload directory.');
        }

        $newName = time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
        $file->move($targetDir, $newName, true);

        return $relativeDir . '/' . $newName;
    }

    private function removeShopImageIfExists(?string $relativePath): void
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
