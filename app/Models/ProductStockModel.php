<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Class ProductStockModel
 * 
 * Handles product stock management and stock history tracking
 * - Tracks current stock levels
 * - Records all stock movements (sales, purchases, adjustments)
 * - Provides stock audit trail
 * 
 * @package App\Models
 * @author Vinothkumar J
 * @version 1.0
 */
class ProductStockModel extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'product_id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = ['shop_id', 'product_name', 'product_image', 'category', 'current_stock', 'stock_unit', 'reorder_level', 'is_active'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \Config\Database::connect();
    }

    /**
     * Record a stock movement (sale, purchase, adjustment)
     * 
     * @param int $shop_id
     * @param int $product_id
     * @param string $movement_type ('sale', 'purchase', 'adjustment', 'opening')
    * @param float $quantity Quantity to add/deduct
     * @param string $stock_unit Unit of measurement
     * @param string $reference_type Reference source ('merchant_ledger', 'manual', 'system')
     * @param int|null $reference_id Reference ID (ledger_id, etc)
     * @param string|null $txn_ref Transaction reference
     * @param string|null $notes Additional notes
     * @param int|null $created_by User ID who created this
     * @return bool Success/failure
     */
    public function recordStockMovement(
        $shop_id,
        $product_id,
        $movement_type,
        $quantity,
        $stock_unit,
        $reference_type = null,
        $reference_id = null,
        $txn_ref = null,
        $notes = null,
        $created_by = null
    ) {
        // Get current stock
        $product = $this->find($product_id);
        if (!$product || $product['shop_id'] != $shop_id) {
            return false;
        }

        $stock_before = floatval($product['current_stock']);

        // Calculate new stock
        if ($movement_type === 'sale') {
            $stock_after = $stock_before - floatval($quantity);
        } elseif ($movement_type === 'purchase' || $movement_type === 'opening') {
            $stock_after = $stock_before + floatval($quantity);
        } elseif ($movement_type === 'adjustment') {
            $stock_after = $stock_before + floatval($quantity);
        } else {
            return false;
        }

        // Prevent negative stock for sales/adjustments
        if ($stock_after < 0) {
            return false;
        }

        // Start transaction
        $this->db->transStart();

        try {
            // Update product current stock
            $this->update($product_id, [
                'current_stock' => $stock_after,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // Record in stock history
            $stock_history_table = 'product_stock_history';
            $this->db->table($stock_history_table)->insert([
                'shop_id' => $shop_id,
                'product_id' => $product_id,
                'movement_type' => $movement_type,
                'quantity' => $quantity,
                'stock_unit' => $stock_unit,
                'stock_before' => $stock_before,
                'stock_after' => $stock_after,
                'reference_type' => $reference_type,
                'reference_id' => $reference_id,
                'txn_ref' => $txn_ref,
                'notes' => $notes,
                'created_by' => $created_by,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                return false;
            }

            return true;
        } catch (\Exception $e) {
            $this->db->transRollback();
            return false;
        }
    }

    /**
     * Get stock history for a product
     * 
     * @param int $product_id
     * @param int|null $shop_id
     * @param int $limit
     * @return array
     */
    public function getStockHistory($product_id, $shop_id = null, $limit = 100)
    {
        $query = $this->db->table('product_stock_history')
            ->where('product_id', $product_id);

        if ($shop_id) {
            $query->where('shop_id', $shop_id);
        }

        return $query->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    /**
     * Get low stock products for a shop
     * 
     * @param int $shop_id
     * @return array
     */
    public function getLowStockProducts($shop_id)
    {
        return $this->where('shop_id', $shop_id)
            ->where('is_active', true)
            ->where('current_stock <=', $this->db->raw('reorder_level'))
            ->findAll();
    }

    /**
     * Get current stock for product by name
     * 
     * @param int $shop_id
     * @param string $product_name
     * @return float|false
     */
    public function getCurrentStock($shop_id, $product_name)
    {
        $product = $this->where('shop_id', $shop_id)
            ->where('product_name', $product_name)
            ->first();

        return $product ? floatval($product['current_stock']) : false;
    }

    /**
     * Get inventory report for shop
     * 
     * @param int $shop_id
     * @return array
     */
    public function getInventoryReport($shop_id)
    {
        return $this->where('shop_id', $shop_id)
            ->where('is_active', true)
            ->orderBy('category', 'ASC')
            ->orderBy('product_name', 'ASC')
            ->findAll();
    }

    /**
     * Get stock movement summary for period
     * 
     * @param int $shop_id
     * @param string $start_date
     * @param string $end_date
     * @return array
     */
    public function getStockMovementSummary($shop_id, $start_date, $end_date)
    {
        return $this->db->table('product_stock_history')
            ->select([
                'p.product_id',
                'p.product_name',
                'p.category',
                'psh.movement_type',
                'COUNT(*) as count',
                'SUM(psh.quantity) as total_quantity',
                'psh.stock_unit'
            ])
            ->from('product_stock_history psh')
            ->join('products p', 'psh.product_id = p.product_id')
            ->where('psh.shop_id', $shop_id)
            ->where('psh.created_at >=', $start_date)
            ->where('psh.created_at <=', $end_date)
            ->groupBy(['p.product_id', 'psh.movement_type'])
            ->get()
            ->getResultArray();
    }
}
