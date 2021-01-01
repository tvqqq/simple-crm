<?php

namespace App\Http\Controllers;

use App\Export;
use App\Import;
use App\Product;
use App\ReturnCmd;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    const TYPE_NAME = [
        'exports' => 'Phiếu xuất',
        'imports' => 'Phiếu nhập',
        'returns' => 'Phiếu trả',
    ];
    const MAX_PRODUCT_DISPLAY = 30;

    public function index()
    {
        return view('products.index');
    }

    public function data()
    {
        $listIds = [];
        // Nếu có phiếu xuất -> sắp xếp theo phiếu xuất giảm dần
        $exportToday = Export::whereDate('created_at', today())
            ->get()
            ->sortByDesc('quantity')
            ->pluck('product_id')
            ->unique();
        if (count($exportToday) > 0) {
            $listIds[] = $exportToday->toArray();
        }
        $countExportItem = $exportToday->count();
        if ($countExportItem < 30) {
            // Trong ngày, chưa có phiếu xuất -> sắp xếp theo tồn tăng dần
            $quantityStockProductId = Product::whereNotIn('id', $listIds)
                ->get()
                ->sortByDesc('stock_quantity')
                ->take(self::MAX_PRODUCT_DISPLAY - $countExportItem)
                ->pluck('id')
                ->toArray();
            $listIds[] = $quantityStockProductId;
        }
        $listIds = Arr::flatten($listIds);
        $tempStr = implode(',', $listIds);
        $displayList = Product::whereIn('id', $listIds)
            ->when(!empty($tempStr), function ($q) use ($tempStr) {
                return $q->orderByRaw(DB::raw("FIELD(id, $tempStr)"));
            })
            ->get();

        $data = Product::all();
        return response()->json([
            'product_list' => $data,
            'display_list' => $displayList
        ]);
    }

    public function info($id)
    {
        $product = Product::find($id);
        $imports = Import::where('product_id', $id)->get();
        $exports = Export::where('product_id', $id)->get();
        $returns = ReturnCmd::where('product_id', $id)->get();
        $history = collect()->mergeRecursive([$imports, $exports, $returns])
            ->flatten()
            ->sortByDesc('created_at')
            ->each(function ($item) {
                $item->type = self::TYPE_NAME[$item->getTable()];
            });
        return view('products.info', [
            'product' => $product,
            'history' => $history
        ]);
    }

    public function detail($id)
    {
        $data = Product::find($id);
        return response()->json($data);
    }

    public function command($id, Request $request)
    {
        $result = [
            'success' => false,
        ];
        $product = Product::find($id);
        $productStockQuantity = (int)$product->stock_quantity;

        // Check validation stock quantity
        $exportQuantity = $request->input('export_quantity') ?? 0;
        $importQuantity = $request->input('import_quantity') ?? 0;
        $returnQuantity = $request->input('return_quantity') ?? 0;
        $commandQuantity = (int)$exportQuantity - (int)$importQuantity - (int)$returnQuantity;

        if ($commandQuantity > $productStockQuantity) {
            $result['message'] = 'Số lượng tồn kho không đủ!';
            return response()->json($result);
        }

        if ($importQuantity > 0) {
            $this->import($product, $id, $importQuantity);
        }
        if ($returnQuantity > 0) {
            $this->returnCmd($product, $id, $returnQuantity);
        }
        if ($exportQuantity > 0) {
            $this->export($product, $id, $exportQuantity);
        }
        $result['success'] = true;
        return response()->json($result);
    }

    public function export($product, $productId, $quantity)
    {
        $stockQuantity = $product->stock_quantity;
        $stockQuantity -= $quantity;
        Export::create([
            'product_id' => $productId,
            'quantity' => $quantity,
            'stock_quantity' => $stockQuantity,
            'user_id' => auth()->id()
        ]);
        $product->stock_quantity = $stockQuantity;
        $product->save();
        return [
            'success' => true
        ];
    }

    public function import($product, $productId, $quantity)
    {
        $stockQuantity = $product->stock_quantity;
        $stockQuantity += $quantity;
        Import::create([
            'product_id' => $productId,
            'quantity' => $quantity,
            'stock_quantity' => $stockQuantity,
            'user_id' => auth()->id()
        ]);
        $product->stock_quantity = $stockQuantity;
        $product->save();
        return [
            'success' => true
        ];
    }

    public function returnCmd($product, $productId, $quantity, $note = null)
    {
        $stockQuantity = $product->stock_quantity;
        $stockQuantity += $quantity;
        ReturnCmd::create([
            'product_id' => $productId,
            'quantity' => $quantity,
            'stock_quantity' => $stockQuantity,
            'note' => $note,
            'user_id' => auth()->id()
        ]);
        $product->stock_quantity = $stockQuantity;
        $product->save();
        return [
            'success' => true
        ];
    }

    public function create(Request $request)
    {
        Product::create([
            'name' => $request->input('name'),
            'code' => strtoupper($request->input('code')),
            'stock_quantity' => 0,
            'image' => $request->input('image')
        ]);
        return [
            'success' => true
        ];
    }

    public function update($id, Request $request)
    {
        Product::find($id)->update([
            'name' => $request->input('name'),
            'code' => strtoupper($request->input('code')),
            'image' => $request->input('image')
        ]);
        return [
            'success' => true
        ];
    }

    public function delete($id)
    {
        Product::find($id)->delete();
        Export::where('product_id', $id)->delete();
        Import::where('product_id', $id)->delete();
        ReturnCmd::where('product_id', $id)->delete();
        return [
            'success' => true
        ];
    }
}
