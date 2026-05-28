<?php

namespace App\Controllers;

use App\Config\ProductConstants;
use App\Models\ProductModel;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\HTTP\ResponseInterface;

class Product extends BaseController
{
    protected $productModel;

    public function __construct()
    {
        $this->productModel = new ProductModel();
        helper(['form', 'url']);
    }

    public function index(): string
    {
        return view('index', ['body_content' => 'product/list']);
    }

    public function getProductListJson()
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

        $response = $this->productModel->getProductListDT($this->request->getPost(), $shopId);
        return $this->response->setJSON($response);
    }

    public function add()
    {
        return view('index', [
            'body_content' => 'product/add',
            'categories' => ProductConstants::CATEGORIES,
        ]);
    }

    public function edit($productId)
    {
        $shopId = $this->getCurrentShopId();
        if ($shopId === null) {
            return $this->response->setStatusCode(403)->setJSON(['status' => false, 'message' => 'Unable to identify current shop.']);
        }

        $productInfo = $this->productModel->getProductById((int) $productId, $shopId);
        if (!$productInfo) {
            return $this->response->setStatusCode(404)->setJSON(['status' => false, 'message' => 'Product not found']);
        }

        return view('index', [
            'body_content' => 'product/edit',
            'productInfo' => $productInfo,
            'categories' => ProductConstants::CATEGORIES,
        ]);
    }

    public function delete($id = null)
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return $this->response->setStatusCode(ResponseInterface::HTTP_METHOD_NOT_ALLOWED)->setBody('Method Not Allowed');
        }

        if (!$id) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)->setBody('Product ID required');
        }

        $shopId = $this->getCurrentShopId();
        if ($shopId === null) {
            return $this->response->setStatusCode(403)->setJSON(['status' => false, 'message' => 'Unable to identify current shop.']);
        }

        $product = $this->productModel->getProductById((int) $id, $shopId);
        if (!$product) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)->setJSON(['status' => false, 'message' => 'Product not found']);
        }

        $success = $this->productModel->where('product_id', (int) $id)->where('shop_id', $shopId)->delete();

        if ($success) {
            $this->removeProductImageIfExists($product->product_image ?? null);
        }

        return $this->response->setJSON([
            'status' => $success ? true : false,
            'message' => $success ? 'Product deleted successfully' : 'Failed to delete product',
            'id' => (int) $id,
        ]);
    }

    public function save($productId = '')
    {
        try {
            $shopId = $this->getCurrentShopId();
            if ($shopId === null) {
                return $this->respondProductSave(false, 'Unable to identify current shop.', null, [], 403);
            }

            $existingProduct = null;
            if ($productId !== '') {
                $existingProduct = $this->productModel->getProductById((int) $productId, $shopId);
                if (!$existingProduct) {
                    return $this->respondProductSave(false, 'Product not found.', null, [], 404);
                }
            }

            $validation = \Config\Services::validation();
            $categoryValues = array_keys(ProductConstants::CATEGORIES);
            $categoryInList = implode(',', $categoryValues);
            $validation->setRules([
                'product_name' => [
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'Product name is required.',
                    ],
                ],
                'category' => [
                    'rules' => 'permit_empty|in_list[' . $categoryInList . ']',
                    'errors' => [
                        'in_list' => 'Please select a valid category.',
                    ],
                ],
            ]);

            if (!$validation->run($this->request->getPost())) {
                $errors = $validation->getErrors();
                return $this->respondProductSave(false, implode(' | ', $errors), null, $errors, 422);
            }

            $productImageFile = $this->request->getFile('product_image');
            if ($productImageFile && $productImageFile->getError() !== UPLOAD_ERR_NO_FILE && !$productImageFile->isValid()) {
                return $this->respondProductSave(false, 'Invalid product image upload.', null, ['product_image' => 'Invalid product image upload.'], 422);
            }

            $data = [
                'shop_id' => $shopId,
                'product_name' => trim((string) $this->request->getPost('product_name')),
                'category' => trim((string) ($this->request->getPost('category') ?? '')) ?: null,
                'purity' => trim((string) ($this->request->getPost('purity') ?? '')) ?: null,
                'is_active' => $this->request->getPost('is_active') ? true : false,
            ];

            if ($productId !== '') {
                $data['product_image'] = $existingProduct->product_image ?? null;
            }

            if ($productImageFile && $productImageFile->getError() !== UPLOAD_ERR_NO_FILE) {
                $newProductImage = $this->storeProductImage($productImageFile);
                if ($productId !== '' && !empty($existingProduct->product_image)) {
                    $this->removeProductImageIfExists($existingProduct->product_image);
                }
                $data['product_image'] = $newProductImage;
            }

            if ($productId !== '') {
                $this->productModel->updateProductById((int) $productId, $shopId, $data);
                return $this->respondProductSave(true, 'Product updated successfully', site_url('product'));
            }

            $this->productModel->addProduct($data);
            return $this->respondProductSave(true, 'Product added successfully', site_url('product'));
        } catch (\Throwable $e) {
            return $this->respondProductSave(false, $e->getMessage(), null, [], 500);
        }
    }

    private function respondProductSave(bool $status, string $message, ?string $redirect = null, array $errors = [], int $statusCode = 200)
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
            return redirect()->to($redirect ?: site_url('product'))->with('success', $message);
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

        $employee = $this->productModel->getUserByRefCode($referenceCode);
        if (!$employee || empty($employee->shop_id)) {
            return null;
        }

        session()->set('auth_shop_id', (int) $employee->shop_id);
        return (int) $employee->shop_id;
    }

    private function storeProductImage(UploadedFile $file): string
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

        $relativeDir = 'uploads/business/product';
        $targetDir = FCPATH . $relativeDir;

        if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
            throw new \RuntimeException('Unable to create product upload directory.');
        }

        $newName = time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
        $file->move($targetDir, $newName, true);

        return $relativeDir . '/' . $newName;
    }

    private function removeProductImageIfExists(?string $relativePath): void
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
