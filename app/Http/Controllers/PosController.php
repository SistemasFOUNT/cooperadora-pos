<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Student;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\PaymentMethod;
use App\Models\CashMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PosController extends Controller
{
    /**
     * Mostrar la interfaz principal del POS
     */
    public function index()
    {
        $products = Product::active()->get();
        $paymentMethods = PaymentMethod::where('is_active', true)->get();
        $user = Auth::user();

        return view('pos.index', compact('products', 'paymentMethods', 'user'));
    }

    /**
     * Buscar productos por código o nombre
     */
    public function searchProducts(Request $request)
    {
        $search = $request->get('search');

        $products = Product::active()
            ->where(function($query) use ($search) {
                $query->where('code', 'LIKE', "%{$search}%")
                      ->orWhere('name', 'LIKE', "%{$search}%")
                      ->orWhere('barcode', $search);
            })
            ->limit(10)
            ->get();

        return response()->json($products);
    }

    /**
     * Buscar estudiante por número o documento
     */
    public function searchStudent(Request $request)
    {
        $search = $request->get('search');

        $student = Student::active()
            ->where(function($query) use ($search) {
                $query->where('student_number', $search)
                      ->orWhere('document_number', $search);
            })
            ->first();

        return response()->json($student);
    }

    /**
     * Procesar venta
     */
    public function processSale(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'student_id' => 'nullable|exists:students,id',
            'subtotal' => 'required|numeric|min:0',
            'tax_amount' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            // Crear la venta
            $sale = Sale::create([
                'sale_number' => $this->generateSaleNumber(),
                'branch_id' => Auth::user()->branch_id,
                'user_id' => Auth::id(),
                'student_id' => $request->student_id,
                'payment_method_id' => $request->payment_method_id,
                'sale_datetime' => now(),
                'type' => $request->student_id ? 'student_fee' : 'product_sale',
                'subtotal' => $request->subtotal,
                'tax_amount' => $request->tax_amount,
                'discount_amount' => $request->discount_amount ?? 0,
                'total_amount' => $request->total_amount,
                'status' => 'completed',
                'notes' => $request->notes,
            ]);

            // Crear items de la venta
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'product_code' => $product->code,
                    'product_name' => $product->name,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_percentage' => $item['discount_percentage'] ?? 0,
                    'discount_amount' => $item['discount_amount'] ?? 0,
                    'subtotal' => $item['subtotal'],
                    'tax_percentage' => 21,
                    'tax_amount' => $item['tax_amount'],
                    'total' => $item['total'],
                ]);

                // Actualizar stock si es necesario
                if ($product->track_stock) {
                    $product->decrement('stock', $item['quantity']);

                    // Registrar movimiento de stock
                    $product->stockMovements()->create([
                        'branch_id' => Auth::user()->branch_id,
                        'user_id' => Auth::id(),
                        'sale_id' => $sale->id,
                        'movement_datetime' => now(),
                        'type' => 'sale',
                        'reference' => $sale->sale_number,
                        'quantity' => -$item['quantity'],
                        'stock_before' => $product->stock + $item['quantity'],
                        'stock_after' => $product->stock,
                        'unit_cost' => $product->cost,
                        'notes' => "Venta #{$sale->sale_number}",
                    ]);
                }
            }

            // Registrar movimiento de caja
            $this->registerCashMovement($sale);

            DB::commit();

            return response()->json([
                'success' => true,
                'sale_id' => $sale->id,
                'sale_number' => $sale->sale_number,
                'message' => 'Venta procesada exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la venta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generar número de venta único
     */
    private function generateSaleNumber()
    {
        $branchCode = Auth::user()->branch->code;
        $date = now()->format('Ymd');
        $sequence = Sale::whereDate('created_at', now())
            ->where('branch_id', Auth::user()->branch_id)
            ->count() + 1;

        return $branchCode . '-' . $date . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Registrar movimiento de caja
     */
    private function registerCashMovement(Sale $sale)
    {
        $lastMovement = CashMovement::where('branch_id', Auth::user()->branch_id)
            ->orderBy('id', 'desc')
            ->first();

        $balanceBefore = $lastMovement ? $lastMovement->balance_after : 0;

        CashMovement::create([
            'branch_id' => Auth::user()->branch_id,
            'user_id' => Auth::id(),
            'sale_id' => $sale->id,
            'movement_number' => $this->generateMovementNumber(),
            'movement_datetime' => now(),
            'type' => 'income',
            'concept' => "Venta #{$sale->sale_number}",
            'amount' => $sale->total_amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceBefore + $sale->total_amount,
            'status' => 'completed',
            'notes' => "Pago con {$sale->paymentMethod->name}",
        ]);
    }

    /**
     * Generar número de movimiento de caja
     */
    private function generateMovementNumber()
    {
        $branchCode = Auth::user()->branch->code;
        $date = now()->format('Ymd');
        $sequence = CashMovement::whereDate('created_at', now())
            ->where('branch_id', Auth::user()->branch_id)
            ->count() + 1;

        return 'MOV-' . $branchCode . '-' . $date . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
