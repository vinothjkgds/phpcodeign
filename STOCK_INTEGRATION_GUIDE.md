# Stock Management Integration Guide

## Overview
This guide explains how to integrate stock management into your Salepurchase module using the new `ProductStockModel`.

## Database Changes
The updated schema includes:

### 1. **products table changes**
- Added: `current_stock` (DECIMAL 12,3) - Current quantity in storage
- Added: `stock_unit` (ENUM) - Unit of measurement (gram, kilogram, tola, ounce, piece, etc.)
- Added: `reorder_level` (DECIMAL 12,3) - Minimum stock alert threshold

### 2. **New table: product_stock_history**
Audit trail for all stock movements:
- `movement_type`: purchase, sale, adjustment, opening
- `quantity`: Amount added/deducted
- `stock_before/stock_after`: Snapshot of inventory levels
- `reference_type`: Links to source (merchant_ledger, manual, system)
- `reference_id`: ID of the transaction
- `created_by`: User who made the movement

## Implementation Steps

### Step 1: Use ProductStockModel in Salepurchase Controller

```php
<?php

namespace App\Controllers;

use App\Models\SalePurchaseModel;
use App\Models\ProductStockModel;
use App\Models\MerchantModel;

class Salepurchase extends BaseController
{
    protected $salePurchaseModel;
    protected $productStockModel;
    protected $merchantModel;

    public function __construct()
    {
        $this->salePurchaseModel = new SalePurchaseModel();
        $this->productStockModel = new ProductStockModel();
        $this->merchantModel = new MerchantModel();
    }

    /**
     * Record a sale transaction with stock deduction
     */
    public function recordSale($merchant_id, $product_id, $quantity, $weight_unit, $amount, $shop_id, $user_id)
    {
        try {
            // 1. Record in merchant_ledger
            $ledger_data = [
                'shop_id' => $shop_id,
                'merchant_id' => $merchant_id,
                'entry_type' => 'sale',
                'product_id' => $product_id,
                'weight' => $quantity,
                'weight_unit' => $weight_unit,
                'amount' => $amount,
                'receivable_delta' => $amount,
                'payable_delta' => 0,
                'entry_date' => date('Y-m-d H:i:s'),
                'txn_ref' => 'SAL-' . str_pad(time() % 10000, 4, '0', STR_PAD_LEFT)
            ];
            
            $ledger_id = $this->salePurchaseModel->insertLedgerEntry($ledger_data);
            
            // 2. Update product stock (IMPORTANT: MUST USE SAME UNIT)
            $success = $this->productStockModel->recordStockMovement(
                shop_id: $shop_id,
                product_id: $product_id,
                movement_type: 'sale',
                quantity: $quantity,
                stock_unit: $weight_unit,
                reference_type: 'merchant_ledger',
                reference_id: $ledger_id,
                txn_ref: $ledger_data['txn_ref'],
                notes: "Sold {$quantity}{$weight_unit} to merchant #{$merchant_id}",
                created_by: $user_id
            );

            if (!$success) {
                return ['success' => false, 'message' => 'Stock update failed. Transaction rolled back.'];
            }

            return ['success' => true, 'ledger_id' => $ledger_id, 'message' => 'Sale recorded successfully'];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Record a purchase transaction with stock increase
     */
    public function recordPurchase($merchant_id, $product_id, $quantity, $weight_unit, $amount, $shop_id, $user_id)
    {
        try {
            // 1. Record in merchant_ledger
            $ledger_data = [
                'shop_id' => $shop_id,
                'merchant_id' => $merchant_id,
                'entry_type' => 'purchase',
                'product_id' => $product_id,
                'weight' => $quantity,
                'weight_unit' => $weight_unit,
                'amount' => $amount,
                'receivable_delta' => 0,
                'payable_delta' => $amount,
                'entry_date' => date('Y-m-d H:i:s'),
                'txn_ref' => 'PUR-' . str_pad(time() % 10000, 4, '0', STR_PAD_LEFT)
            ];
            
            $ledger_id = $this->salePurchaseModel->insertLedgerEntry($ledger_data);
            
            // 2. Update product stock
            $success = $this->productStockModel->recordStockMovement(
                shop_id: $shop_id,
                product_id: $product_id,
                movement_type: 'purchase',
                quantity: $quantity,
                stock_unit: $weight_unit,
                reference_type: 'merchant_ledger',
                reference_id: $ledger_id,
                txn_ref: $ledger_data['txn_ref'],
                notes: "Purchased {$quantity}{$weight_unit} from merchant #{$merchant_id}",
                created_by: $user_id
            );

            if (!$success) {
                return ['success' => false, 'message' => 'Stock update failed. Transaction rolled back.'];
            }

            return ['success' => true, 'ledger_id' => $ledger_id, 'message' => 'Purchase recorded successfully'];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
```

### Step 2: Add Stock Validation Before Sale

```php
public function validateStock($shop_id, $product_id, $quantity, $stock_unit)
{
    $product = $this->productStockModel->find($product_id);
    
    if (!$product || $product['shop_id'] != $shop_id) {
        return ['valid' => false, 'message' => 'Product not found'];
    }

    if ($product['stock_unit'] != $stock_unit) {
        return ['valid' => false, 'message' => "Unit mismatch. Product uses {$product['stock_unit']}, not {$stock_unit}"];
    }

    if ($product['current_stock'] < $quantity) {
        return [
            'valid' => false,
            'message' => "Insufficient stock. Available: {$product['current_stock']} {$stock_unit}, Required: {$quantity} {$stock_unit}"
        ];
    }

    return ['valid' => true];
}
```

### Step 3: Add Stock Check View Helper

```php
public function getStockStatus($shop_id, $product_id)
{
    $product = $this->productStockModel->find($product_id);
    
    if (!$product) {
        return null;
    }

    return [
        'product_name' => $product['product_name'],
        'current_stock' => $product['current_stock'],
        'stock_unit' => $product['stock_unit'],
        'reorder_level' => $product['reorder_level'],
        'is_low' => $product['current_stock'] <= $product['reorder_level'],
        'status' => $product['current_stock'] <= $product['reorder_level'] ? 'LOW' : 'OK'
    ];
}
```

### Step 4: Add Inventory Dashboard Query

```php
public function getInventoryDashboard($shop_id)
{
    $report = $this->productStockModel->getInventoryReport($shop_id);
    return $report;
}

public function getLowStockAlert($shop_id)
{
    $low_stock = $this->productStockModel->getLowStockProducts($shop_id);
    return $low_stock;
}
```

## Important Notes

### Unit Consistency
⚠️ **CRITICAL**: All transactions for a product MUST use the same unit as defined in the products table.

**Example:**
- Gold Bar is defined in `gram`
- All sales/purchases must be in `gram`
- Do NOT mix gram and kilogram for same product

### Stock Validation Flow
1. User selects product → Check `stock_unit`
2. User enters quantity → Validate against `current_stock`
3. User confirms → Record both ledger AND stock movement
4. System prevents negative stock

### API Response Format
```json
{
  "success": true,
  "ledger_id": 123,
  "message": "Sale recorded successfully",
  "stock_status": {
    "product_name": "Gold Bar",
    "stock_before": 250.000,
    "stock_after": 210.000,
    "stock_unit": "gram"
  }
}
```

## Example: Complete Sale Flow in View

```html
<!-- Salepurchase Form -->
<form id="salePurchaseForm">
    <select id="productSelect" name="product_id" onchange="updateStockInfo()">
        <option value="">Select Product</option>
        <?php foreach ($products as $product): ?>
            <option value="<?= $product['product_id'] ?>" data-stock="<?= $product['current_stock'] ?>" data-unit="<?= $product['stock_unit'] ?>">
                <?= $product['product_name'] ?> (Stock: <?= $product['current_stock'] ?> <?= $product['stock_unit'] ?>)
            </option>
        <?php endforeach; ?>
    </select>

    <input type="number" id="quantityInput" name="quantity" placeholder="Quantity" step="0.001">
    <span id="stockWarning" style="color: red; display: none;">Insufficient stock!</span>

    <input type="submit" value="Record Sale">
</form>

<script>
function updateStockInfo() {
    const select = document.getElementById('productSelect');
    const option = select.options[select.selectedIndex];
    const stock = parseFloat(option.dataset.stock);
    const unit = option.dataset.unit;
    document.getElementById('quantityInput').max = stock;
    document.getElementById('quantityInput').placeholder = `Max: ${stock} ${unit}`;
}

document.getElementById('salePurchaseForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const quantity = parseFloat(document.getElementById('quantityInput').value);
    const stock = parseFloat(document.getElementById('productSelect').options[document.getElementById('productSelect').selectedIndex].dataset.stock);
    
    if (quantity > stock) {
        document.getElementById('stockWarning').style.display = 'block';
        return;
    }
    
    // Submit form
    this.submit();
});
</script>
```

## Database Queries for Reports

### Get Low Stock Items
```sql
SELECT product_id, product_name, current_stock, stock_unit, reorder_level
FROM products
WHERE shop_id = 1 AND is_active = TRUE AND current_stock <= reorder_level
ORDER BY current_stock ASC;
```

### Get Stock Movement for Period
```sql
SELECT 
    p.product_name,
    psh.movement_type,
    SUM(psh.quantity) as total_quantity,
    COUNT(*) as transactions,
    psh.stock_unit
FROM product_stock_history psh
JOIN products p ON psh.product_id = p.product_id
WHERE psh.shop_id = 1 
    AND psh.created_at BETWEEN '2026-05-01' AND '2026-05-31'
GROUP BY p.product_id, psh.movement_type
ORDER BY p.product_name;
```

### Get Product Stock Timeline
```sql
SELECT 
    created_at,
    movement_type,
    quantity,
    stock_before,
    stock_after,
    txn_ref,
    notes
FROM product_stock_history
WHERE product_id = 1 AND shop_id = 1
ORDER BY created_at DESC
LIMIT 50;
```

## Testing

1. **Test Sale with Insufficient Stock**
   - Try to sell 300g when only 250g available
   - Should return error, no changes to database

2. **Test Stock History**
   - Record a sale, check product_stock_history table
   - Verify stock_before, stock_after calculations

3. **Test Low Stock Alert**
   - Set reorder_level to 100g for Gold Bar
   - Current stock is 210g after sale
   - getLowStockProducts() should not include it

4. **Test Adjustment**
   - Use recordStockMovement with type 'adjustment' to manually correct stock

---

## Next Steps
1. Integrate ProductStockModel into Salepurchase controller
2. Add stock validation to form submission
3. Add inventory dashboard view
4. Add low stock alerts on dashboard
