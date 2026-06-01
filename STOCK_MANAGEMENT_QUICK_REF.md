# Stock Management - Quick Reference

## What's New

### Database Tables
```
products (UPDATED)
├── Added: current_stock (DECIMAL 12,3)
├── Added: stock_unit (ENUM: gram, kilogram, tola, ounce, piece, liter)
└── Added: reorder_level (DECIMAL 12,3)

product_stock_history (NEW)
├── Tracks all stock movements
├── Links to merchant_ledger transactions
└── Provides complete audit trail
```

## Usage in Code

### Record a Sale
```php
$this->productStockModel->recordStockMovement(
    shop_id: 1,
    product_id: 3,           // Gold product
    movement_type: 'sale',
    quantity: 40.000,        // 40 grams
    stock_unit: 'gram',
    reference_type: 'merchant_ledger',
    reference_id: $ledger_id,
    txn_ref: 'SAL-0001',
    notes: 'Sold to Ramesh Kumar',
    created_by: $user_id
);
```

### Record a Purchase
```php
$this->productStockModel->recordStockMovement(
    shop_id: 1,
    product_id: 3,
    movement_type: 'purchase',
    quantity: 20.000,
    stock_unit: 'gram',
    reference_type: 'merchant_ledger',
    reference_id: $ledger_id,
    txn_ref: 'PUR-0001',
    notes: 'Purchased from Lakshmi Retail',
    created_by: $user_id
);
```

### Check Current Stock
```php
$product = $this->productStockModel->find($product_id);
echo "Stock: {$product['current_stock']} {$product['stock_unit']}";
// Output: Stock: 230.000 gram
```

### Get Stock History
```php
$history = $this->productStockModel->getStockHistory($product_id);
// Returns: Array of all movements for this product
```

### Check Low Stock
```php
$low_stock = $this->productStockModel->getLowStockProducts($shop_id);
// Returns products where current_stock <= reorder_level
```

## Sample Data After Migration

| Product | Category | Current Stock | Unit | Reorder Level |
|---------|----------|---------------|------|---------------|
| Gold Bar | gold | 223.000 | gram | 100.000 |
| Gold Coin | gold | 500.000 | gram | 150.000 |
| Gold (bulk) | gold | 1000.000 | gram | 200.000 |
| Silver Bar | silver | 2000.000 | gram | 500.000 |
| Silver Coin | silver | 1500.000 | gram | 400.000 |
| Silver (bulk) | silver | 5000.000 | gram | 1000.000 |

*Note: Gold Bar shows 223g after sample transactions in migration*

## Integration Checklist

- [ ] Run migration: `php spark migrate` (or execute final.sql)
- [ ] Copy ProductStockModel to `app/Models/`
- [ ] Update Salepurchase controller to use ProductStockModel
- [ ] Add stock validation before recording sales
- [ ] Update sale/purchase form to show current stock
- [ ] Add low stock warnings to dashboard
- [ ] Test sale → Stock decreases ✓
- [ ] Test purchase → Stock increases ✓
- [ ] Test audit trail in product_stock_history ✓
- [ ] Test insufficient stock error ✓

## Key Methods in ProductStockModel

| Method | Purpose |
|--------|---------|
| `recordStockMovement()` | Main method to record any stock change |
| `getStockHistory()` | Get audit trail for a product |
| `getLowStockProducts()` | Get items needing reorder |
| `getCurrentStock()` | Get stock by product name |
| `getInventoryReport()` | Full inventory status |
| `getStockMovementSummary()` | Report for date range |

## Critical Rules

⚠️ **Never mix units** for the same product
- Don't track Gold in both `gram` and `kilogram`
- Define unit ONCE at product level
- All transactions use defined unit

⚠️ **Stock goes negative = Error**
- Can't sell more than available
- System prevents: current_stock < quantity for sales
- Returns: `{'success': false, 'message': 'Insufficient stock'}`

⚠️ **Always link to merchant_ledger**
- When recording stock movement from a sale/purchase
- Use: `reference_type: 'merchant_ledger'`, `reference_id: $ledger_id`
- Enables full audit trail back to transaction

## Files Changed

```
database/
├── final.sql (UPDATED)
│   ├── Added stock columns to products
│   ├── Created product_stock_history table
│   └── Added sample stock data

app/Models/
├── ProductStockModel.php (NEW)
│   └── Complete stock management API

app/Controllers/
├── Salepurchase.php (NEEDS UPDATE)
│   ├── Import ProductStockModel
│   ├── Call recordStockMovement on sale/purchase
│   └── Validate stock before transaction

docs/
└── STOCK_INTEGRATION_GUIDE.md (NEW)
    └── Detailed implementation examples
```

## Migration Command

If using CodeIgniter migrations (not direct SQL):
```bash
php spark migrate
```

If using direct SQL (manual):
```bash
mysql -u root -p database_name < database/final.sql
```

## Verification Query

After migration, run to verify setup:
```sql
SELECT 
    p.product_id,
    p.product_name,
    p.current_stock,
    p.stock_unit,
    p.reorder_level,
    CASE WHEN p.current_stock <= p.reorder_level THEN '⚠️ LOW' ELSE '✓ OK' END as status,
    COUNT(h.history_id) as movements
FROM products p
LEFT JOIN product_stock_history h ON p.product_id = h.product_id
WHERE p.shop_id = 1
GROUP BY p.product_id
ORDER BY p.product_name;
```

Expected output:
```
product_id | product_name    | current_stock | stock_unit | reorder_level | status | movements
-----------|-----------------|---------------|------------|---------------|--------|----------
1          | Gold Bar        | 223.000       | gram       | 100.000       | ✓ OK   | 4
2          | Gold Coin       | 500.000       | gram       | 150.000       | ✓ OK   | 1
...
```
