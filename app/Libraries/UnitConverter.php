<?php

namespace App\Libraries;

use Config\Database;

class UnitConverter
{
    /**
     * Converts a numeric quantity from one stock unit to another.
     * Returns null when conversion is unsupported.
     */
    public function convert(int $shopId, float $value, string $fromUnit, string $toUnit, int $precision = 6): ?float
    {
        $from = strtolower(trim($fromUnit));
        $to = strtolower(trim($toUnit));

        if ($from === '' || $to === '') {
            return null;
        }

        if ($from === $to) {
            return round($value, $precision);
        }

        if ($shopId <= 0) {
            return null;
        }

        $db = Database::connect();
        $rows = $db->table('stock_units')
            ->select('unit_code, unit_type, factor_to_base')
            ->where('shop_id', $shopId)
            ->where('is_active', 1)
            ->whereIn('unit_code', [$from, $to])
            ->get()
            ->getResultArray();

        if (count($rows) !== 2) {
            return null;
        }

        $unitMap = [];
        foreach ($rows as $row) {
            $code = strtolower(trim((string) ($row['unit_code'] ?? '')));
            if ($code === '') {
                continue;
            }
            $unitMap[$code] = [
                'unit_type' => strtolower(trim((string) ($row['unit_type'] ?? ''))),
                'factor_to_base' => (float) ($row['factor_to_base'] ?? 0),
            ];
        }

        if (!isset($unitMap[$from], $unitMap[$to])) {
            return null;
        }

        $fromType = (string) ($unitMap[$from]['unit_type'] ?? '');
        $toType = (string) ($unitMap[$to]['unit_type'] ?? '');
        if ($fromType === '' || $toType === '' || $fromType !== $toType) {
            return null;
        }

        $fromFactor = (float) ($unitMap[$from]['factor_to_base'] ?? 0);
        $toFactor = (float) ($unitMap[$to]['factor_to_base'] ?? 0);
        if ($fromFactor <= 0 || $toFactor <= 0) {
            return null;
        }

        $valueInBase = $value * $fromFactor;
        return round($valueInBase / $toFactor, $precision);
    }
}