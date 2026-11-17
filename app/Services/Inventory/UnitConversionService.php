<?php

namespace App\Services\Inventory;

use App\Models\InventoryItem;
use App\Models\InventoryUnitConversion;
use InvalidArgumentException;

/**
 * Converts quantities between units for one inventory item.
 * Rules (in order): same unit; same dimension (config/inventory.php);
 * item-specific rule (inventory_unit_conversions). Anything else is refused —
 * pieces are never silently turned into kilograms.
 */
class UnitConversionService
{
    public function normalize(?string $unit): ?string
    {
        if ($unit === null) {
            return null;
        }
        $u = strtolower(trim(rtrim(trim($unit), '.')));
        if ($u === '') {
            return null;
        }
        $units = config('inventory.units');
        if (isset($units[$u])) {
            return $u;
        }

        return config('inventory.unit_aliases')[$u] ?? $u;
    }

    public function isKnown(?string $unit): bool
    {
        return $unit !== null && isset(config('inventory.units')[$this->normalize($unit)]);
    }

    /** Units the item can be entered in (base unit first). */
    public function unitsFor(InventoryItem $item): array
    {
        $base = $this->normalize($item->unit);
        $units = config('inventory.units');
        $dim = $units[$base]['dimension'] ?? null;
        $out = [$base];
        foreach ($units as $code => $meta) {
            if ($code !== $base && $dim !== null && $meta['dimension'] === $dim) {
                $out[] = $code;
            }
        }
        foreach ($item->unitConversions ?? [] as $conv) {
            $out[] = $conv->unit;
        }

        return array_values(array_unique($out));
    }

    public function canConvert(InventoryItem $item, ?string $fromUnit): bool
    {
        try {
            $this->toBase($item, 1, $fromUnit);

            return true;
        } catch (InvalidArgumentException) {
            return false;
        }
    }

    /** Quantity expressed in the item's stock unit, rounded to 3 dp. */
    public function toBase(InventoryItem $item, float $quantity, ?string $fromUnit): float
    {
        $base = $this->normalize($item->unit);
        $from = $this->normalize($fromUnit) ?? $base;

        if ($from === $base) {
            return round($quantity, 3);
        }

        $units = config('inventory.units');
        if (isset($units[$from], $units[$base]) && $units[$from]['dimension'] === $units[$base]['dimension']) {
            return round($quantity * $units[$from]['factor'] / $units[$base]['factor'], 3);
        }

        $rule = InventoryUnitConversion::where('inventory_item_id', $item->id)->where('unit', $from)->first();
        if ($rule) {
            return round($quantity * (float) $rule->factor, 3);
        }

        throw new InvalidArgumentException("Cannot convert {$from} to {$base} for {$item->name}. Add a unit rule for this item.");
    }

    /** Price per base unit given a price per entered unit. */
    public function rateToBase(InventoryItem $item, float $rate, ?string $fromUnit): float
    {
        $perUnit = $this->toBase($item, 1, $fromUnit);

        return $perUnit > 0 ? $rate / $perUnit : $rate;
    }
}
