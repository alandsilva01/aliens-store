<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['images', 'creator']);

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        return response()->json($query->latest()->paginate(12));
    }

    public function categories()
    {
        $categories = Product::whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->sort()
            ->values();

        return response()->json($categories);
    }

    public function store(StoreProductRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $product = Product::create([
                'title'       => $request->title,
                'description' => $request->description,
                'sale_price'  => $request->sale_price,
                'cost_price'  => $request->cost_price,
                'category'    => $request->category,
                'is_active'   => true,
                'created_by'  => auth()->id(),
                'updated_by'  => auth()->id(),
            ]);

            $this->handleImages($request, $product);
            $this->log($product, 'created', []);

            return response()->json($product->load('images'), 201);
        });
    }

    public function show(Product $product)
    {
        return response()->json($product->load(['images', 'logs.user']));
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        return DB::transaction(function () use ($request, $product) {
            $before = $product->only(['title', 'description', 'sale_price', 'cost_price', 'category', 'is_active']);

            $product->update([
                'title'       => $request->title,
                'description' => $request->description,
                'sale_price'  => $request->sale_price,
                'cost_price'  => $request->cost_price,
                'category'    => $request->category,
                'updated_by'  => auth()->id(),
            ]);

            $this->handleImages($request, $product);

            $after   = $product->fresh()->only(['title', 'description', 'sale_price', 'cost_price', 'category', 'is_active']);
            $changes = array_diff_assoc($after, $before);
            $this->log($product, 'updated', $changes);

            return response()->json($product->load('images'));
        });
    }

    public function toggleActive(Product $product)
    {
        $product->update([
            'is_active'  => !$product->is_active,
            'updated_by' => auth()->id(),
        ]);

        $action = $product->is_active ? 'activated' : 'inactivated';
        $this->log($product, $action, ['is_active' => $product->is_active]);

        return response()->json($product);
    }

    public function destroy(Product $product)
    {
        $this->log($product, 'deleted', []);
        $product->delete();
        return response()->json(['message' => 'Produto removido com sucesso.']);
    }

    public function removeImage(Product $product, ProductImage $image)
    {
        abort_if($image->product_id !== $product->id, 403);
        Storage::disk('public')->delete($image->path);
        $image->delete();
        return response()->json(['message' => 'Imagem removida.']);
    }

    private function handleImages(Request $request, Product $product): void
    {
        if (!$request->hasFile('images')) return;

        foreach ($request->file('images') as $file) {
            $path = $file->store('products', 'public');
            $product->images()->create([
                'path'          => $path,
                'original_name' => $file->getClientOriginalName(),
            ]);
        }
    }

    private function log(Product $product, string $action, array $changes): void
    {
        ProductLog::create([
            'product_id' => $product->id,
            'user_id'    => auth()->id(),
            'action'     => $action,
            'changes'    => $changes,
            'logged_at'  => now(),
        ]);
    }
}
