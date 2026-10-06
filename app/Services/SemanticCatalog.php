<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class SemanticCatalog
{
    private array $pending = [];
    private bool $rebuild = false;

    public function defer(int $productId): void
    {
        if (config('semantic-search.enabled')) $this->pending[$productId] = true;
    }

    public function deferAll(): void
    {
        if (config('semantic-search.enabled')) $this->rebuild = true;
    }

    public function flush(): void
    {
        try {
            if ($this->rebuild) {
                $this->export();
            } elseif ($this->pending) {
                foreach (array_keys($this->pending) as $id) {
                    $product = Product::withTrashed()->with(['brand', 'category', 'attributeValues.attribute'])->find($id);
                    $record = $product && ! $product->trashed() && $product->status === 'published'
                        ? $this->record($product) : ['id' => $id, 'deleted' => true];
                    $this->write('changes/'.$id.'.json', $record);
                }
            }
        } catch (\Throwable $exception) {
            Log::warning('Cannot update semantic search catalogue.', ['reason' => $exception->getMessage()]);
        } finally {
            $this->pending = [];
            $this->rebuild = false;
        }
    }

    public function export(): int
    {
        $records = [];
        Product::published()->with(['brand', 'category', 'attributeValues.attribute'])
            ->chunkById(200, function ($products) use (&$records) {
                foreach ($products as $product) $records[] = $this->record($product);
            });
        $this->write('catalog.json', ['products' => $records]);

        return count($records);
    }

    private function record(Product $product): array
    {
        $attributes = $product->attributeValues->mapWithKeys(fn ($value) => [$value->attribute_id => $value->value])->all();
        $specifications = $product->attributeValues->map(fn ($value) => ($value->attribute?->name ?? '').': '.$value->value)->implode('. ');

        return [
            'id' => $product->id, 'name' => $product->name, 'sku' => $product->sku,
            'category' => $product->category?->name,
            'category_id' => $product->category_id, 'brand_id' => $product->brand_id,
            'price' => $product->sale_price ?? $product->price, 'attributes' => $attributes,
            'named_attributes' => $product->attributeValues->mapWithKeys(fn ($value) => [($value->attribute?->name ?? '') => $value->value])->all(),
            'text' => implode('. ', array_filter([
                $product->name, $product->brand?->name, $product->category?->name,
                $specifications, mb_substr(trim(strip_tags($product->description ?? '')), 0, 1200),
            ])),
        ];
    }

    private function write(string $relativePath, array $data): void
    {
        $path = config('semantic-search.directory').'/'.$relativePath;
        File::ensureDirectoryExists(dirname($path));
        File::replace($path, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    }
}
