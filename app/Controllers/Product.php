<?php

namespace App\Controllers;

use App\Models\CategoryModel;
use App\Models\ProductModel;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\HTTP\ResponseInterface;

class Product extends BaseController
{
    protected $productModel;
    protected $categoryModel;

    public function __construct()
    {
        $this->productModel = new ProductModel();
        $this->categoryModel = new CategoryModel();
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
        $shopId = $this->getCurrentShopId();
        if ($shopId === null) {
            return $this->response->setStatusCode(403)->setJSON(['status' => false, 'message' => 'Unable to identify current shop.']);
        }

        return view('index', [
            'body_content' => 'product/add',
            'categories' => $this->categoryModel->getCategoryOptions($shopId),
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
            'categories' => $this->getProductCategoryOptions($shopId, (string) ($productInfo->category ?? '')),
        ]);
    }

    public function view($productId)
    {
        $shopId = $this->getCurrentShopId();
        if ($shopId === null) {
            return $this->response->setStatusCode(403)->setJSON(['status' => false, 'message' => 'Unable to identify current shop.']);
        }

        $productInfo = $this->productModel->getProductById((int) $productId, $shopId);
        if (!$productInfo) {
            return $this->response->setStatusCode(404)->setJSON(['status' => false, 'message' => 'Product not found']);
        }

        $rawHistoryRows = $this->productModel->getStockHistoryByProduct($shopId, (int) $productId, 1000);

        $historyRows = [];
        foreach ($rawHistoryRows as $row) {
            $unitLabel = $this->shortUnitLabel((string) ($row['stock_unit'] ?? ''));
            $historyRows[] = [
                'history_id' => (int) ($row['history_id'] ?? 0),
                'created_at' => $this->formatListDateTime($row['created_at'] ?? null),
                'movement_type' => ucwords(str_replace('_', ' ', (string) ($row['movement_type'] ?? ''))),
                'quantity' => number_format((float) ($row['quantity'] ?? 0), 3) . ' ' . $unitLabel,
                'stock_before' => number_format((float) ($row['stock_before'] ?? 0), 3) . ' ' . $unitLabel,
                'stock_after' => number_format((float) ($row['stock_after'] ?? 0), 3) . ' ' . $unitLabel,
                'txn_ref' => (string) ($row['txn_ref'] ?? '-'),
                'notes' => (string) ($row['notes'] ?? '-'),
            ];
        }

        $chartRowsAsc = array_reverse($rawHistoryRows);
        $stockChartLabels = [];
        $stockChartValues = [];
        foreach ($chartRowsAsc as $row) {
            $ts = strtotime((string) ($row['created_at'] ?? ''));
            $stockChartLabels[] = $ts ? date('d M Y g:i A', $ts) : '-';
            $stockChartValues[] = (float) ($row['stock_after'] ?? 0);
        }

        return view('index', [
            'body_content' => 'product/view',
            'productInfo' => $productInfo,
            'stockHistoryRows' => $historyRows,
            'stockChartLabels' => $stockChartLabels,
            'stockChartValues' => $stockChartValues,
            'stockChartUnit' => $this->shortUnitLabel((string) ($productInfo->stock_unit ?? '')),
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
            $validation->setRules([
                'product_name' => [
                    'rules' => 'required',
                    'errors' => [
                        'required' => 'Product name is required.',
                    ],
                ],
                'category' => [
                    'rules' => 'permit_empty|max_length[100]',
                ],
                'current_stock' => [
                    'rules' => 'required|decimal|greater_than_equal_to[0]',
                    'errors' => [
                        'required' => 'Current stock is required.',
                        'decimal' => 'Current stock must be a valid decimal number.',
                        'greater_than_equal_to' => 'Current stock cannot be negative.',
                    ],
                ],
                'stock_unit' => [
                    'rules' => 'required|in_list[gram,kilogram,milligram,tola,ounce,piece,liter,other]',
                    'errors' => [
                        'required' => 'Stock unit is required.',
                        'in_list' => 'Please select a valid stock unit.',
                    ],
                ],
                'reorder_level' => [
                    'rules' => 'required|decimal|greater_than_equal_to[0]',
                    'errors' => [
                        'required' => 'Reorder level is required.',
                        'decimal' => 'Reorder level must be a valid decimal number.',
                        'greater_than_equal_to' => 'Reorder level cannot be negative.',
                    ],
                ],
            ]);

            if (!$validation->run($this->request->getPost())) {
                $errors = $validation->getErrors();
                return $this->respondProductSave(false, implode(' | ', $errors), null, $errors, 422);
            }

            $categoryName = trim((string) ($this->request->getPost('category') ?? ''));
            if ($categoryName !== '' && !$this->categoryModel->categoryExistsForShop($categoryName, $shopId)) {
                return $this->respondProductSave(false, 'Please select a valid category.', null, ['category' => 'Please select a valid category.'], 422);
            }

            $productImageFile = $this->request->getFile('product_image');
            if ($productImageFile && $productImageFile->getError() !== UPLOAD_ERR_NO_FILE && !$productImageFile->isValid()) {
                return $this->respondProductSave(false, 'Invalid product image upload.', null, ['product_image' => 'Invalid product image upload.'], 422);
            }

            $data = [
                'shop_id' => $shopId,
                'product_name' => trim((string) $this->request->getPost('product_name')),
                'category' => $categoryName !== '' ? $categoryName : null,
                'current_stock' => (float) ($this->request->getPost('current_stock') ?? 0),
                'stock_unit' => trim((string) ($this->request->getPost('stock_unit') ?? 'gram')) ?: 'gram',
                'reorder_level' => (float) ($this->request->getPost('reorder_level') ?? 100),
                'is_active' => $this->request->getPost('is_active') ? true : false,
            ];

            if ($productId !== '') {
                // Current stock is managed via adjustStock flow to keep stock history accurate.
                $data['current_stock'] = (float) ($existingProduct->current_stock ?? 0);
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

    /**
     * Adjust product stock manually
     * Used for stock corrections, inventory reconciliation, etc.
     * Records adjustment in product_stock_history via ProductStockModel
     */
    public function adjustStock($productId = '')
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return $this->response->setStatusCode(405)->setJSON(['status' => false, 'message' => 'Method not allowed']);
        }

        try {
            $shopId = $this->getCurrentShopId();
            if ($shopId === null) {
                return $this->response->setStatusCode(403)->setJSON(['status' => false, 'message' => 'Unable to identify current shop']);
            }

            if (!$productId) {
                return $this->response->setStatusCode(400)->setJSON(['status' => false, 'message' => 'Product ID required']);
            }

            $product = $this->productModel->getProductById((int) $productId, $shopId);
            if (!$product) {
                return $this->response->setStatusCode(404)->setJSON(['status' => false, 'message' => 'Product not found']);
            }

            $validation = \Config\Services::validation();
            $validation->setRules([
                'adjustment_quantity' => [
                    'rules' => 'required|decimal',
                    'errors' => [
                        'required' => 'Adjustment quantity is required.',
                        'decimal' => 'Adjustment quantity must be a valid decimal number.',
                    ],
                ],
                'adjustment_unit' => [
                    'rules' => 'required|in_list[gram,kilogram,milligram,tola,ounce,piece,liter,other]',
                    'errors' => [
                        'required' => 'Adjustment unit is required.',
                        'in_list' => 'Please select a valid adjustment unit.',
                    ],
                ],
                'adjustment_notes' => [
                    'rules' => 'permit_empty|string|max_length[500]',
                    'errors' => [
                        'max_length' => 'Notes cannot exceed 500 characters.',
                    ],
                ],
            ]);

            if (!$validation->run($this->request->getPost())) {
                $errors = $validation->getErrors();
                return $this->response->setStatusCode(422)->setJSON(['status' => false, 'message' => implode(' | ', $errors), 'errors' => $errors]);
            }

            $adjustmentQty = (float) $this->request->getPost('adjustment_quantity');
            $adjustmentUnit = strtolower(trim((string) ($this->request->getPost('adjustment_unit') ?? '')));
            $productUnit = strtolower(trim((string) ($product->stock_unit ?? '')));
            $notes = trim((string) ($this->request->getPost('adjustment_notes') ?? ''));

            if ($adjustmentQty == 0.0) {
                return $this->response->setStatusCode(422)->setJSON(['status' => false, 'message' => 'Adjustment quantity cannot be zero']);
            }

            $convertedAdjustmentQty = $this->convertStockQuantity($adjustmentQty, $adjustmentUnit, $productUnit);
            if ($convertedAdjustmentQty === null) {
                return $this->response->setStatusCode(422)->setJSON([
                    'status' => false,
                    'message' => 'Cannot convert adjustment unit from ' . $adjustmentUnit . ' to ' . $productUnit,
                ]);
            }

            $currentStock = floatval($product->current_stock);
            $newStock = $currentStock + $convertedAdjustmentQty;

            if ($newStock < 0) {
                return $this->response->setStatusCode(422)->setJSON(['status' => false, 'message' => 'Adjustment would result in negative stock']);
            }

            $stockModel = new \App\Models\ProductStockModel();
            $saved = $stockModel->recordStockMovement(
                shop_id: $shopId,
                product_id: (int) $productId,
                movement_type: 'adjustment',
                quantity: $convertedAdjustmentQty,
                stock_unit: $productUnit,
                reference_type: 'manual',
                notes: $notes,
                created_by: session()->get('auth_id')
            );

            if (!$saved) {
                return $this->response->setStatusCode(500)->setJSON([
                    'status' => false,
                    'message' => 'Failed to save stock adjustment.',
                ]);
            }

            $actionText = $convertedAdjustmentQty > 0 ? 'added' : 'reduced';

            return $this->response->setJSON([
                'status' => true,
                'message' => 'Stock ' . $actionText . ' successfully',
                'product_id' => (int) $productId,
                'old_stock' => $currentStock,
                'new_stock' => $newStock,
                'stock_unit' => $productUnit,
                'entered_quantity' => $adjustmentQty,
                'entered_unit' => $adjustmentUnit,
                'converted_quantity' => $convertedAdjustmentQty,
                'converted_unit' => $productUnit,
            ]);

        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get product inventory report
     */
    public function inventoryReport()
    {
        $shopId = $this->getCurrentShopId();
        if ($shopId === null) {
            return $this->response->setStatusCode(403)->setJSON(['status' => false, 'message' => 'Unable to identify current shop']);
        }

        $products = $this->productModel->where('shop_id', $shopId)->where('is_active', true)->findAll();
        
        $inventory = [];
        foreach ($products as $product) {
            $isLow = $product['current_stock'] <= $product['reorder_level'];
            $inventory[] = [
                'product_id' => $product['product_id'],
                'product_name' => $product['product_name'],
                'category' => $product['category'],
                'current_stock' => floatval($product['current_stock']),
                'stock_unit' => $product['stock_unit'],
                'reorder_level' => floatval($product['reorder_level']),
                'status' => $isLow ? 'LOW' : 'OK'
            ];
        }

        return $this->response->setJSON([
            'status' => true,
            'inventory' => $inventory,
            'low_stock_count' => count(array_filter($inventory, fn($item) => $item['status'] === 'LOW'))
        ]);
    }

    public function stockHistory()
    {
        $shopId = $this->getCurrentShopId();
        if ($shopId === null) {
            return redirect()->to(site_url('dashboard'))->with('error', 'Unable to identify current shop.');
        }

        $products = $this->productModel
            ->where('shop_id', $shopId)
            ->where('is_active', true)
            ->orderBy('product_name', 'ASC')
            ->findAll();

        return view('index', [
            'body_content' => 'product/stock_history',
            'products' => $products,
        ]);
    }

    public function getStockHistoryListJson()
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

        $response = $this->productModel->getStockHistoryListDT($this->request->getPost(), $shopId);
        return $this->response->setJSON($response);
    }

    /**
     * Return basic info (current_stock, stock_unit) for a product — used by Add Stock on stock-history page.
     */
    public function getProductInfo($productId = '')
    {
        $shopId = $this->getCurrentShopId();
        if ($shopId === null) {
            return $this->response->setStatusCode(403)->setJSON(['status' => false, 'message' => 'Unable to identify current shop.']);
        }

        if (!$productId) {
            return $this->response->setStatusCode(400)->setJSON(['status' => false, 'message' => 'Product ID required.']);
        }

        $product = $this->productModel->getProductById((int) $productId, $shopId);
        if (!$product) {
            return $this->response->setStatusCode(404)->setJSON(['status' => false, 'message' => 'Product not found.']);
        }

        $unitLabel = $this->shortUnitLabel((string) ($product->stock_unit ?? ''));

        return $this->response->setJSON([
            'status' => true,
            'product_id' => (int) $product->product_id,
            'product_name' => (string) $product->product_name,
            'current_stock' => (float) $product->current_stock,
            'stock_unit' => (string) ($product->stock_unit ?? 'gram'),
            'stock_unit_label' => $unitLabel,
            'current_stock_formatted' => number_format((float) $product->current_stock, 3) . ' ' . $unitLabel,
        ]);
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
        return $this->resolveAuthenticatedShopId();
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

    private function shortUnitLabel(string $unit): string
    {
        return match (strtolower(trim($unit))) {
            'kilogram' => 'kg',
            'gram' => 'gm',
            'milligram' => 'mg',
            'tola' => 'tola',
            'ounce' => 'oz',
            'piece' => 'pc',
            'liter' => 'ltr',
            default => $unit,
        };
    }

    private function convertStockQuantity(float $value, string $fromUnit, string $toUnit): ?float
    {
        $from = strtolower(trim($fromUnit));
        $to = strtolower(trim($toUnit));

        if ($from === '' || $to === '') {
            return null;
        }

        if ($from === $to) {
            return round($value, 6);
        }

        $massUnitToGram = [
            'milligram' => 0.001,
            'gram' => 1,
            'kilogram' => 1000,
            'tola' => 11.6638038,
            'ounce' => 28.349523125,
        ];

        if (!isset($massUnitToGram[$from], $massUnitToGram[$to])) {
            return null;
        }

        $valueInGram = $value * $massUnitToGram[$from];
        $converted = $valueInGram / $massUnitToGram[$to];

        return round($converted, 6);
    }

    private function getProductCategoryOptions(int $shopId, string $selectedCategory = ''): array
    {
        $categories = $this->categoryModel->getCategoryOptions($shopId);
        $selectedCategory = trim($selectedCategory);

        if ($selectedCategory !== '' && !array_key_exists($selectedCategory, $categories)) {
            $categories = [$selectedCategory => $selectedCategory] + $categories;
        }

        return $categories;
    }
}
