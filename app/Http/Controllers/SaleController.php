<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Student;
use App\Models\PaymentMethod;
use App\Models\CashMovement;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sales = Sale::with(['student', 'paymentMethod', 'user'])
                    ->orderBy('created_at', 'desc')
                    ->paginate(15);

        return view('sales.index', compact('sales'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $students = Student::where('is_active', true)->get();
        $paymentMethods = PaymentMethod::where('is_active', true)->get();
        $products = Product::where('is_active', true)->get();

        return view('sales.create', compact('students', 'paymentMethods', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'student_id' => 'nullable|exists:students,id'
        ]);

        try {
            DB::beginTransaction();

            // Calcular totales
            $subtotal = 0;
            foreach ($request->items as $item) {
                $subtotal += $item['quantity'] * $item['unit_price'];
            }

            $tax = $subtotal * 0.21; // IVA 21%
            $total = $subtotal + $tax;

            // Crear la venta
            $sale = Sale::create([
                'sale_number' => $this->generateSaleNumber(),
                'user_id' => Auth::id(),
                'student_id' => $request->student_id,
                'payment_method_id' => $request->payment_method_id,
                'branch_id' => Auth::user()->branch_id ?? 1,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'discount' => 0,
                'total' => $total,
                'status' => 'completed'
            ]);

            // Crear los items de la venta
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['quantity'] * $item['unit_price']
                ]);

                // Actualizar stock del producto
                if ($product->track_stock) {
                    $product->decrement('stock', $item['quantity']);

                    // Registrar movimiento de stock
                    StockMovement::create([
                        'product_id' => $product->id,
                        'user_id' => Auth::id(),
                        'type' => 'sale',
                        'quantity' => -$item['quantity'],
                        'reason' => "Venta #{$sale->sale_number}",
                        'reference_id' => $sale->id
                    ]);
                }
            }

            // Registrar movimiento de caja
            CashMovement::create([
                'user_id' => Auth::id(),
                'branch_id' => Auth::user()->branch_id ?? 1,
                'type' => 'income',
                'amount' => $total,
                'payment_method_id' => $request->payment_method_id,
                'description' => "Venta #{$sale->sale_number}",
                'reference_type' => 'sale',
                'reference_id' => $sale->id
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Venta procesada exitosamente',
                'sale_id' => $sale->id,
                'sale_number' => $sale->sale_number,
                'total' => $total
            ]);

        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la venta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Sale $sale)
    {
        $sale->load(['student', 'paymentMethod', 'user', 'items.product']);
        return view('sales.show', compact('sale'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sale $sale)
    {
        // Las ventas generalmente no se editan, pero se puede implementar si es necesario
        return redirect()->route('sales.show', $sale);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sale $sale)
    {
        // Implementar si es necesario
        return redirect()->route('sales.show', $sale);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sale $sale)
    {
        // Generalmente las ventas no se eliminan, pero se puede cambiar el status
        $sale->update(['status' => 'cancelled']);

        return redirect()->route('sales.index')
            ->with('success', 'Venta cancelada exitosamente');
    }

    /**
     * Generate a unique sale number
     */
    private function generateSaleNumber()
    {
        $prefix = 'V-' . date('Ymd') . '-';
        $lastSale = Sale::where('sale_number', 'LIKE', $prefix . '%')
                       ->orderBy('sale_number', 'desc')
                       ->first();

        if ($lastSale) {
            $lastNumber = intval(substr($lastSale->sale_number, strlen($prefix)));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
