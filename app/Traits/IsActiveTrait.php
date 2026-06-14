<?php

namespace App\Traits;

/**
 * IsActiveTrait
 *
 * Provides reusable methods for handling is_active filtering across models.
 * Ensures consistent soft-delete behavior across the entire application.
 *
 * @package App\Traits
 * @author  Vinothkumar J
 * @version 1.0
 */
trait IsActiveTrait
{
    /**
     * Filter query to only include active records
     *
     * @param string $tableAlias Optional table alias/prefix for the is_active column
     * @return $this
     */
    public function onlyActive(string $tableAlias = ''): self
    {
        $column = $tableAlias ? "{$tableAlias}.is_active" : 'is_active';
        return $this->where($column, true);
    }

    /**
     * Filter query to only include inactive records
     *
     * @param string $tableAlias Optional table alias/prefix for the is_active column
     * @return $this
     */
    public function onlyInactive(string $tableAlias = ''): self
    {
        $column = $tableAlias ? "{$tableAlias}.is_active" : 'is_active';
        return $this->where($column, false);
    }

    /**
     * Include both active and inactive records (removes any is_active filter)
     *
     * @return $this
     */
    public function withInactive(): self
    {
        // This is typically handled by not adding the onlyActive filter
        return $this;
    }

    /**
     * Log is_active status change to audit trail
     *
     * @param string $entityType Entity type (user, product, category, merchant, etc.)
     * @param int $entityId Entity ID
     * @param string $entityName Optional entity name for readability
     * @param bool $oldStatus Old is_active status
     * @param bool $newStatus New is_active status
     * @param int $shopId Shop ID for multi-tenancy
     * @param int|null $changedBy User ID who made the change
     * @param string|null $changeReason Optional reason for the change
     * @return bool
     */
    public function logIsActiveChange(
        string $entityType,
        int $entityId,
        string $entityName,
        bool $oldStatus,
        bool $newStatus,
        int $shopId,
        ?int $changedBy = null,
        ?string $changeReason = null
    ): bool {
        $db = \Config\Database::connect();
        
        try {
            return (bool) $db->table('is_active_history')->insert([
                'shop_id' => $shopId,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'entity_name' => $entityName,
                'old_status' => $oldStatus ? 1 : 0,
                'new_status' => $newStatus ? 1 : 0,
                'changed_by' => $changedBy,
                'change_reason' => $changeReason,
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Failed to log is_active change: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get is_active change history for an entity
     *
     * @param string $entityType Entity type
     * @param int $entityId Entity ID
     * @param int|null $limit Number of records to fetch
     * @return array
     */
    public function getIsActiveHistory(string $entityType, int $entityId, ?int $limit = 10): array
    {
        $db = \Config\Database::connect();
        
        return $db->table('is_active_history')
            ->select('history_id, entity_type, old_status, new_status, changed_by, change_reason, created_at')
            ->where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }
}
