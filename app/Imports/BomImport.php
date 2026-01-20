<?php

namespace App\Imports;

use App\Models\Item;
use App\Models\ItemRecipe;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BomImport implements ToCollection, WithHeadingRow
{
    protected $branchId;
    protected $itemMap;
    protected $recipesImported = 0;
    protected $failed = 0;
    protected $errors = [];

    public function __construct($branchId, &$itemMap)
    {
        $this->branchId = $branchId;
        $this->itemMap = &$itemMap; // Reference to item map from ItemsImport
    }

    /**
     * Helper function to parse numeric value from Excel
     */
    private function parseFloat($value): float
    {
        if (empty($value)) return 0;
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
                    $rowNumber = $index + 2;

                    // Clean data - support multiple column name variations
                    $productName = trim($row['nama_produk'] ?? $row['nama produk'] ?? $row['product_name'] ?? '');
                    $productSku = trim($row['sku_produk'] ?? $row['sku produk'] ?? $row['product_sku'] ?? '');
                    $ingredientName = trim($row['nama_bahan'] ?? $row['nama bahan'] ?? $row['ingredient_name'] ?? '');
                    $ingredientSku = trim($row['sku_bahan'] ?? $row['sku bahan'] ?? $row['ingredient_sku'] ?? '');
                    $quantity = $this->parseFloat($row['jumlah'] ?? $row['quantity'] ?? '');

                    // Find parent item (product) - try name first, then SKU
                    $parentItem = null;
                    if (!empty($productName)) {
                        // First check if it's in the item map (newly imported items)
                        if (isset($this->itemMap[$productName])) {
                            $parentItem = $this->itemMap[$productName];
                        } else {
                            // Check in database
                            $parentItem = Item::where('name', $productName)
                                ->where('branch_id', $this->branchId)
                                ->first();
                        }
                    } elseif (!empty($productSku)) {
                        if (isset($this->itemMap["SKU:$productSku"])) {
                            $parentItem = $this->itemMap["SKU:$productSku"];
                        } else {
                            $parentItem = Item::where('sku', $productSku)
                                ->where('branch_id', $this->branchId)
                                ->first();
                        }
                    }

                    // Find ingredient item
                    $ingredientItem = null;
                    if (!empty($ingredientName)) {
                        if (isset($this->itemMap[$ingredientName])) {
                            $ingredientItem = $this->itemMap[$ingredientName];
                        } else {
                            $ingredientItem = Item::where('name', $ingredientName)
                                ->where('branch_id', $this->branchId)
                                ->first();
                        }
                    } elseif (!empty($ingredientSku)) {
                        if (isset($this->itemMap["SKU:$ingredientSku"])) {
                            $ingredientItem = $this->itemMap["SKU:$ingredientSku"];
                        } else {
                            $ingredientItem = Item::where('sku', $ingredientSku)
                                ->where('branch_id', $this->branchId)
                                ->first();
                        }
                    }

                    // Validate
                    if (!$parentItem) {
                        $this->errors[] = "BOM Row $rowNumber: Produk '$productName' (SKU: $productSku) tidak ditemukan";
                        $this->failed++;
                        continue;
                    }

                    if (!$ingredientItem) {
                        $this->errors[] = "BOM Row $rowNumber: Bahan '$ingredientName' (SKU: $ingredientSku) tidak ditemukan";
                        $this->failed++;
                        continue;
                    }

                    if ($quantity <= 0) {
                        $this->errors[] = "BOM Row $rowNumber: Jumlah harus lebih dari 0";
                        $this->failed++;
                        continue;
                    }

                    // Check if ingredient is a purchase item
                    if (!$ingredientItem->is_purchase) {
                        $this->errors[] = "BOM Row $rowNumber: Bahan '$ingredientName' harus bertipe Purchase atau Both";
                        $this->failed++;
                        continue;
                    }

                    // Delete existing recipe if any
                    ItemRecipe::where('parent_item_id', $parentItem->id)
                        ->where('ingredient_item_id', $ingredientItem->id)
                        ->delete();

                    // Create recipe
                    ItemRecipe::create([
                        'parent_item_id' => $parentItem->id,
                        'ingredient_item_id' => $ingredientItem->id,
                        'quantity_required' => $quantity,
                    ]);

                    $this->recipesImported++;

                } catch (\Exception $e) {
                    $this->errors[] = "BOM Row " . ($index + 2) . ": " . $e->getMessage();
                    $this->failed++;
                }
            }
        });
    }

    public function getRecipesImported(): int
    {
        return $this->recipesImported;
    }

    public function getFailed(): int
    {
        return $this->failed;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
