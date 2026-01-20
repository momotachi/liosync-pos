<?php

namespace App\Imports;

use App\Models\Item;
use App\Models\Category;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ItemsImport implements ToCollection, WithHeadingRow
{
    protected $branchId;
    protected $companyId;
    protected $imported = 0;
    protected $updated = 0;
    protected $skipped = 0;
    protected $failed = 0;
    protected $errors = [];
    protected $itemMap = []; // Store imported items for BOM reference

    public function __construct($branchId, $companyId)
    {
        $this->branchId = $branchId;
        $this->companyId = $companyId;
    }

    /**
     * Helper function to parse numeric value from Excel
     * Handles both comma and dot as decimal separators
     */
    private function parseFloat($value): float
    {
        if (empty($value)) return 0;

        // Replace comma with dot for decimal separator
        // This handles: "0,5" -> "0.5", "1.500,00" -> "1500.00" (Excel will handle this)
        $cleaned = str_replace(',', '.', $value);
        return floatval($cleaned);
    }

    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        DB::transaction(function() use ($collection) {
            foreach ($collection as $index => $row) {
                try {
                    $rowNumber = $index + 2; // +2 because header is row 1 and array is 0-indexed

                    // Clean and validate data - support multiple column name variations
                    $name = trim($row['nama_item'] ?? $row['nama item'] ?? $row['name'] ?? '');
                    $categoryName = trim($row['kategori'] ?? $row['category'] ?? '');
                    $sku = trim($row['sku'] ?? '');
                    $barcode = trim($row['barcode'] ?? '');
                    $type = trim($row['tipe'] ?? $row['type'] ?? '');
                    $unitPrice = $this->parseFloat($row['harga_beli'] ?? $row['harga beli'] ?? $row['unit_price'] ?? '');
                    $unit = trim($row['satuan'] ?? $row['unit'] ?? 'pcs');
                    $currentStock = $this->parseFloat($row['stok'] ?? $row['stok awal'] ?? $row['current_stock'] ?? '');
                    $minStock = $this->parseFloat($row['min_stok'] ?? $row['min stok'] ?? '');
                    $sellingPrice = $this->parseFloat($row['harga_jual'] ?? $row['harga jual'] ?? $row['selling_price'] ?? '');
                    $hpp = $this->parseFloat($row['hpp'] ?? '');

                    // Parse active status - supports: Aktif/aktif/Yes/yes/1/true (case-insensitive)
                    $activeValue = strtolower(trim($row['status'] ?? $row['aktif'] ?? $row['is_active'] ?? 'yes'));
                    $isActive = in_array($activeValue, ['aktif', 'yes', '1', 'true', 'on']);

                    $description = trim($row['deskripsi'] ?? $row['description'] ?? '');

                    // Validate required fields
                    if (empty($name)) {
                        $this->errors[] = "Row $rowNumber: Nama Item wajib diisi";
                        $this->failed++;
                        continue;
                    }

                    if (empty($type) || !in_array(strtolower($type), ['purchase', 'sales', 'both'])) {
                        $this->errors[] = "Row $rowNumber: Tipe harus salah satu dari: Purchase, Sales, Both";
                        $this->failed++;
                        continue;
                    }

                    // Determine item type flags
                    $typeLower = strtolower($type);
                    $isPurchase = $typeLower === 'purchase' || $typeLower === 'both';
                    $isSales = $typeLower === 'sales' || $typeLower === 'both';

                    // Find or create category (categories are global)
                    $categoryId = null;
                    if (!empty($categoryName)) {
                        $category = Category::firstOrCreate(
                            ['name' => $categoryName],
                            ['type' => $isSales ? 'product' : 'material']
                        );
                        $categoryId = $category->id;
                    }

                    // Check if item exists (by name, barcode, or SKU)
                    $existingItem = null;

                    // Priority: Barcode > SKU > Name
                    if (!empty($barcode)) {
                        $existingItem = Item::where('barcode', $barcode)
                            ->where('branch_id', $this->branchId)
                            ->first();
                    }

                    if (!$existingItem && !empty($sku)) {
                        $existingItem = Item::where('sku', $sku)
                            ->where('branch_id', $this->branchId)
                            ->first();
                    }

                    if (!$existingItem) {
                        $existingItem = Item::where('name', $name)
                            ->where('branch_id', $this->branchId)
                            ->first();
                    }

                    // Prepare item data
                    $itemData = [
                        'name' => $name,
                        'category_id' => $categoryId,
                        'sku' => $sku ?: null,
                        'barcode' => $barcode ?: null,
                        'is_purchase' => $isPurchase,
                        'is_sales' => $isSales,
                        'unit_price' => $isPurchase ? $unitPrice : 0,
                        'unit' => $isPurchase ? $unit : null,
                        'current_stock' => $isPurchase ? $currentStock : 0,
                        'min_stock_level' => $isPurchase ? $minStock : 0,
                        'selling_price' => $isSales ? $sellingPrice : 0,
                        'hpp' => $hpp,
                        'is_active' => $isSales ? $isActive : true,
                        'description' => $description,
                        'company_id' => $this->companyId,
                        'branch_id' => $this->branchId,
                    ];

                    if ($existingItem) {
                        // Check if any values are different
                        $hasChanges = false;
                        $changes = [];

                        foreach ($itemData as $key => $value) {
                            if ($key === 'company_id' || $key === 'branch_id') continue;

                            $currentValue = $existingItem->$key;

                            // Handle numeric comparison
                            if (is_numeric($value) && is_numeric($currentValue)) {
                                if (abs($value - $currentValue) > 0.01) {
                                    $hasChanges = true;
                                    $changes[] = "$key: " . $currentValue . " -> " . $value;
                                }
                            } else {
                                if ($value != $currentValue) {
                                    $hasChanges = true;
                                    $changes[] = "$key: " . $currentValue . " -> " . $value;
                                }
                            }
                        }

                        if ($hasChanges) {
                            $existingItem->update($itemData);
                            $this->updated++;
                        } else {
                            $this->skipped++;
                        }

                        // Store in item map for BOM reference
                        $this->itemMap[$name] = $existingItem;
                        if (!empty($sku)) {
                            $this->itemMap["SKU:$sku"] = $existingItem;
                        }
                    } else {
                        // Create new item
                        $item = Item::create($itemData);
                        $this->imported++;

                        // Store in item map for BOM reference (by name and SKU)
                        $this->itemMap[$name] = $item;
                        if (!empty($sku)) {
                            $this->itemMap["SKU:$sku"] = $item;
                        }
                    }

                } catch (\Exception $e) {
                    $this->errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
                    $this->failed++;
                }
            }
        });
    }

    public function getImported(): int
    {
        return $this->imported;
    }

    public function getUpdated(): int
    {
        return $this->updated;
    }

    public function getSkipped(): int
    {
        return $this->skipped;
    }

    public function getFailed(): int
    {
        return $this->failed;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get reference to item map for BOM import
     * This allows BomImport to access items created in ItemsImport
     */
    public function &getItemMapReference()
    {
        return $this->itemMap;
    }
}
