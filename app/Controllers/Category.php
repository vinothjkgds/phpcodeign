<?php

namespace App\Controllers;

use App\Models\CategoryModel;
use CodeIgniter\HTTP\ResponseInterface;

class Category extends BaseController
{
    protected $categoryModel;

    public function __construct()
    {
        $this->categoryModel = new CategoryModel();
        helper(['form', 'url']);
    }

    public function index(): string
    {
        return view('index', ['body_content' => 'category/list']);
    }

    public function getCategoryListJson()
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

        $response = $this->categoryModel->getCategoryListDT($this->request->getPost(), $shopId);
        return $this->response->setJSON($response);
    }

    public function add()
    {
        return view('index', ['body_content' => 'category/add']);
    }

    public function edit($referenceCode)
    {
        $shopId = $this->getCurrentShopId();
        if ($shopId === null) {
            return $this->response->setStatusCode(403)->setJSON(['status' => false, 'message' => 'Unable to identify current shop.']);
        }

        $categoryInfo = $this->categoryModel->getCategoryByRefCode((string) $referenceCode, $shopId);
        if (!$categoryInfo) {
            return $this->response->setStatusCode(404)->setJSON(['status' => false, 'message' => 'Category not found']);
        }

        return view('index', [
            'body_content' => 'category/edit',
            'categoryInfo' => $categoryInfo,
        ]);
    }

    public function save($referenceCode = '')
    {
        try {
            $shopId = $this->getCurrentShopId();
            if ($shopId === null) {
                return $this->respondCategorySave(false, 'Unable to identify current shop.', null, [], 403);
            }

            $existingCategory = null;
            if ($referenceCode !== '') {
                $existingCategory = $this->categoryModel->getCategoryByRefCode((string) $referenceCode, $shopId);
                if (!$existingCategory) {
                    return $this->respondCategorySave(false, 'Category not found.', null, [], 404);
                }
            }

            $validation = \Config\Services::validation();
            $validation->setRules([
                'category_name' => [
                    'rules' => 'required|max_length[100]',
                    'errors' => [
                        'required' => 'Category name is required.',
                        'max_length' => 'Category name cannot exceed 100 characters.',
                    ],
                ],
            ]);

            if (!$validation->run($this->request->getPost())) {
                $errors = $validation->getErrors();
                return $this->respondCategorySave(false, implode(' | ', $errors), null, $errors, 422);
            }

            $categoryName = trim((string) $this->request->getPost('category_name'));
            if ($this->categoryModel->categoryExistsForShop($categoryName, $shopId, $referenceCode !== '' ? (int) $existingCategory->category_id : null)) {
                return $this->respondCategorySave(false, 'Category already exists for this shop.', null, ['category_name' => 'Category already exists for this shop.'], 422);
            }

            $data = [
                'shop_id' => $shopId,
                'category_name' => $categoryName,
                'is_active' => $this->request->getPost('is_active') ? true : false,
            ];

            if ($referenceCode !== '') {
                $this->categoryModel->updateCategoryByRefCode((string) $referenceCode, $shopId, $data);

                $oldName = trim((string) ($existingCategory->category_name ?? ''));
                if ($oldName !== '' && $oldName !== $categoryName) {
                    $this->categoryModel->updateProductsCategoryName($shopId, $oldName, $categoryName);
                }

                return $this->respondCategorySave(true, 'Category updated successfully', site_url('category'));
            }

            $this->categoryModel->addCategory($data);
            return $this->respondCategorySave(true, 'Category added successfully', site_url('category'));
        } catch (\Throwable $e) {
            return $this->respondCategorySave(false, $e->getMessage(), null, [], 500);
        }
    }

    public function delete($referenceCode = null)
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return $this->response->setStatusCode(ResponseInterface::HTTP_METHOD_NOT_ALLOWED)->setBody('Method Not Allowed');
        }

        if (!$referenceCode) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)->setBody('Category reference code required');
        }

        $shopId = $this->getCurrentShopId();
        if ($shopId === null) {
            return $this->response->setStatusCode(403)->setJSON(['status' => false, 'message' => 'Unable to identify current shop.']);
        }

        $category = $this->categoryModel->getCategoryByRefCode((string) $referenceCode, $shopId);
        if (!$category) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)->setJSON(['status' => false, 'message' => 'Category not found']);
        }

        $categoryName = trim((string) ($category->category_name ?? ''));
        if ($categoryName !== '' && $this->categoryModel->countProductsByCategory($shopId, $categoryName) > 0) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => false,
                'message' => 'Category is linked to one or more products and cannot be deleted.',
            ]);
        }

        $success = $this->categoryModel
            ->where('reference_code', (string) $referenceCode)
            ->where('shop_id', $shopId)
            ->delete();

        return $this->response->setJSON([
            'status' => $success ? true : false,
            'message' => $success ? 'Category deleted successfully' : 'Failed to delete category',
            'id' => (string) $referenceCode,
        ]);
    }

    private function respondCategorySave(bool $status, string $message, ?string $redirect = null, array $errors = [], int $statusCode = 200)
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
            return redirect()->to($redirect ?: site_url('category'))->with('success', $message);
        }

        return redirect()->back()->withInput()->with('error', $message)->with('errors', $errors);
    }

    private function getCurrentShopId(): ?int
    {
        return $this->resolveAuthenticatedShopId();
    }
}
