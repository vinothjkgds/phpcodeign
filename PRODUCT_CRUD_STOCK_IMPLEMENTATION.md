# Product CRUD Implementation - Stock Fields Guide

## Overview

The Product model and controller have been updated to handle the new stock management fields:
- `current_stock` - Current inventory level
- `stock_unit` - Unit of measurement (gram, kilogram, tola, ounce, piece, liter, etc.)
- `reorder_level` - Minimum threshold before low-stock alert

## Code Changes Made

### ProductModel.php
✅ Added `allowedFields` array including:
```php
'current_stock',
'stock_unit',
'reorder_level'
```

✅ Updated `getProductListDT()` to:
- Select stock fields in query
- Display current stock with unit in DataTable
- Show LOW badge (warning) when stock <= reorder_level
- Show OK badge (success) when stock > reorder_level

### Product.php Controller

✅ Updated `save()` method to:
- Validate `current_stock` (required, decimal, non-negative)
- Validate `stock_unit` (required, from allowed list)
- Validate `reorder_level` (required, decimal, non-negative)
- Include fields in product data array

✅ Added `adjustStock()` method for inventory adjustments:
- Manual stock correction endpoint
- Records adjustment notes
- Links to ProductStockModel for audit trail
- Validates adjustment wouldn't go negative

✅ Added `inventoryReport()` method:
- JSON endpoint listing all products with current stock
- Shows low-stock items count
- Useful for dashboard/reports

## Views That Need Updates

### 1. Product Add/Edit Form
**File:** `app/Views/product/add.php` and `app/Views/product/edit.php`

**Add these form fields:**

```html
<!-- Current Stock Field -->
<div class="form-group">
    <label for="current_stock">Current Stock <span class="text-danger">*</span></label>
    <div class="input-group">
        <input type="number" 
               id="current_stock" 
               name="current_stock" 
               class="form-control" 
               step="0.001" 
               min="0" 
               required
               placeholder="0.000"
               value="<?= isset($productInfo) ? number_format($productInfo->current_stock, 3) : '0.000' ?>">
        <span class="input-group-text" id="stockUnitDisplay">gram</span>
    </div>
    <small class="form-text text-muted">Quantity in units specified below</small>
</div>

<!-- Stock Unit Field -->
<div class="form-group">
    <label for="stock_unit">Stock Unit <span class="text-danger">*</span></label>
    <select id="stock_unit" name="stock_unit" class="form-control" required>
        <option value="">-- Select Unit --</option>
        <option value="gram" <?= isset($productInfo) && $productInfo->stock_unit === 'gram' ? 'selected' : '' ?>>Gram (g)</option>
        <option value="kilogram" <?= isset($productInfo) && $productInfo->stock_unit === 'kilogram' ? 'selected' : '' ?>>Kilogram (kg)</option>
        <option value="milligram" <?= isset($productInfo) && $productInfo->stock_unit === 'milligram' ? 'selected' : '' ?>>Milligram (mg)</option>
        <option value="tola" <?= isset($productInfo) && $productInfo->stock_unit === 'tola' ? 'selected' : '' ?>>Tola</option>
        <option value="ounce" <?= isset($productInfo) && $productInfo->stock_unit === 'ounce' ? 'selected' : '' ?>>Ounce (oz)</option>
        <option value="piece" <?= isset($productInfo) && $productInfo->stock_unit === 'piece' ? 'selected' : '' ?>>Piece</option>
        <option value="liter" <?= isset($productInfo) && $productInfo->stock_unit === 'liter' ? 'selected' : '' ?>>Liter (L)</option>
        <option value="other" <?= isset($productInfo) && $productInfo->stock_unit === 'other' ? 'selected' : '' ?>>Other</option>
    </select>
    <small class="form-text text-muted">Units are consistent for all transactions of this product</small>
</div>

<!-- Reorder Level Field -->
<div class="form-group">
    <label for="reorder_level">Reorder Level <span class="text-danger">*</span></label>
    <div class="input-group">
        <input type="number" 
               id="reorder_level" 
               name="reorder_level" 
               class="form-control" 
               step="0.001" 
               min="0" 
               required
               placeholder="100.000"
               value="<?= isset($productInfo) ? number_format($productInfo->reorder_level, 3) : '100.000' ?>">
        <span class="input-group-text" id="reorderUnitDisplay">gram</span>
    </div>
    <small class="form-text text-muted">Stock level below which "LOW" alert triggers</small>
</div>
```

**Add this JavaScript** (to update unit display when stock_unit changes):

```javascript
<script>
document.getElementById('stock_unit').addEventListener('change', function() {
    document.getElementById('stockUnitDisplay').textContent = this.value;
    document.getElementById('reorderUnitDisplay').textContent = this.value;
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    const unit = document.getElementById('stock_unit').value;
    if (unit) {
        document.getElementById('stockUnitDisplay').textContent = unit;
        document.getElementById('reorderUnitDisplay').textContent = unit;
    }
});
</script>
```

### 2. Product List DataTable
**File:** `app/Views/product/list.php`

**Update DataTable column definitions:**

```javascript
// In your DataTable initialization
columnDefs: [
    { targets: 0, title: "Image", sortable: false, searchable: false },
    { targets: 1, title: "Product Name", sortable: true, searchable: true },
    { targets: 2, title: "Category", sortable: true, searchable: true },
    { targets: 3, title: "Stock", sortable: true, searchable: false },  // NEW
    { targets: 4, title: "Status", sortable: true, searchable: false },
    { targets: 5, title: "Created", sortable: true, searchable: false },
    { targets: 6, title: "Actions", sortable: false, searchable: false, className: "text-center" }
]
```

The stock column will show badges like:
- `250.000 gram` (green if OK)
- `50.000 gram` (yellow if LOW)

### 3. Product Dashboard/Cards
**Optional enhancement for dashboard:**

```html
<div class="alert alert-warning">
    <h6>⚠️ Low Stock Items</h6>
    <div id="lowStockList"></div>
</div>

<script>
fetch('<?= site_url("product/inventoryReport") ?>')
    .then(res => res.json())
    .then(data => {
        if (data.status && data.low_stock_count > 0) {
            const list = data.inventory
                .filter(item => item.status === 'LOW')
                .map(item => `<div>${item.product_name}: ${item.current_stock} ${item.stock_unit}</div>`)
                .join('');
            document.getElementById('lowStockList').innerHTML = list;
        }
    });
</script>
```

## New API Endpoints

### 1. Stock Adjustment
```
POST /product/adjustStock/{productId}

Body:
{
    "adjustment_quantity": -10.5,  // Can be positive or negative
    "adjustment_notes": "Physical inspection discrepancy"
}

Response:
{
    "status": true,
    "message": "Stock adjusted successfully",
    "product_id": 1,
    "old_stock": 250.0,
    "new_stock": 239.5,
    "stock_unit": "gram"
}
```

### 2. Inventory Report
```
GET /product/inventoryReport

Response:
{
    "status": true,
    "inventory": [
        {
            "product_id": 1,
            "product_name": "Gold Bar",
            "category": "gold",
            "current_stock": 250.0,
            "stock_unit": "gram",
            "reorder_level": 100.0,
            "status": "OK"
        },
        ...
    ],
    "low_stock_count": 2
}
```

## Validation Rules

### current_stock
- ✅ Required
- ✅ Must be decimal (supports 3 decimal places)
- ✅ Cannot be negative
- ✅ Example: 250.000, 1000.50, 0.001

### stock_unit
- ✅ Required
- ✅ Must be one of: gram, kilogram, milligram, tola, ounce, piece, liter, other
- ✅ Cannot be changed after first sale transactions

### reorder_level
- ✅ Required
- ✅ Must be decimal
- ✅ Cannot be negative
- ✅ Recommended: 10-20% of typical stock

## Important Notes

⚠️ **Unit Consistency**
- Once a product is created with a specific unit (e.g., gram), all stock movements must use the same unit
- Do NOT change unit after transactions have occurred
- Changing unit requires careful data migration

⚠️ **Initial Stock**
- `current_stock` should be set when creating product
- For existing products, adjust stock using the `adjustStock()` endpoint
- Stock history IS recorded in `product_stock_history` table

⚠️ **Reorder Level**
- Recommendation: Set to 10-20% of typical stock volume
- Too low: Might miss reordering need
- Too high: Unnecessary inventory holding

## Testing Checklist

- [ ] Create product with stock fields
  - [ ] Validates current_stock required
  - [ ] Validates stock_unit required
  - [ ] Validates reorder_level required
  - [ ] Validates decimal format correct
  - [ ] Saves all fields to database

- [ ] Edit product stock fields
  - [ ] Loads existing stock values
  - [ ] Can update without affecting other fields
  - [ ] Saves to database correctly

- [ ] Product list shows stock
  - [ ] Stock displays with unit
  - [ ] LOW badge shows when stock <= reorder_level
  - [ ] OK badge shows when stock > reorder_level

- [ ] Adjust stock endpoint works
  - [ ] Accepts positive adjustments
  - [ ] Accepts negative adjustments
  - [ ] Prevents going below zero
  - [ ] Records in product_stock_history

- [ ] Inventory report works
  - [ ] Lists all products with stock
  - [ ] Counts low stock items
  - [ ] Shows correct status

## Database Queries for Verification

```sql
-- Check product stock structure
SELECT product_id, product_name, current_stock, stock_unit, reorder_level
FROM products
WHERE shop_id = 1;

-- Find low stock items
SELECT product_id, product_name, current_stock, stock_unit, reorder_level
FROM products
WHERE shop_id = 1 AND current_stock <= reorder_level;

-- Check stock history
SELECT * FROM product_stock_history
WHERE product_id = 1
ORDER BY created_at DESC
LIMIT 20;
```

## Migration Path for Existing Projects

If you already have products without stock fields:

1. **Database already updated** ✅ (new fields have defaults)
2. **Update ProductModel** ✅ (already done)
3. **Update Product controller** ✅ (already done)
4. **Update views** 🔄 (YOU ARE HERE)
5. **Test CRUD operations** - Verify all updates work
6. **Add stock to existing products** - Use adjustStock() or direct database update
7. **Integrate with Salepurchase** - Have sales/purchases update stock via ProductStockModel

## Next Steps

1. Update the product add/edit views with the new fields
2. Test creating/editing products
3. Verify stock appears in product list
4. Test adjustStock() endpoint
5. Test inventoryReport() endpoint
6. Integrate product stock updates into Salepurchase module
