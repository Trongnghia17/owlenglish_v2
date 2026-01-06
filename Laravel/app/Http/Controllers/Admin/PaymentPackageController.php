<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentPackage;
use Illuminate\Http\Request;

class PaymentPackageController extends Controller
{
    /**
     * Danh sách
     */
    public function index(Request $request)
    {
        $query = PaymentPackage::query();
        if ($request->filled('q')) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }

        $packages = $query
            ->orderBy('display_order')
            ->paginate(10)
            ->appends($request->all()); // giữ query khi paginate

        return view('admin.payment-packages.index', compact('packages'));
    }

    /**
     * Form thêm
     */
    public function create()
    {
        return view('admin.payment-packages.create');
    }

    /**
     * Lưu gói mới
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'duration'         => 'required|integer|min:1',
            'price'            => 'required|integer|min:0',
            'discount_percent' => 'nullable|integer|min:0|max:100',
            'display_order'    => 'nullable|integer|min:0',
            'is_featured'      => 'nullable|boolean',
            'status'           => 'nullable|boolean',
        ]);

        $discount = $data['discount_percent'] ?? 0;
        $data['final_price'] = $data['price'] - ($data['price'] * $discount / 100);

        PaymentPackage::create($data);

        return redirect()
            ->route('admin.payment-packages.index')
            ->with('success', 'Thêm gói nạp thành công');
    }

    /**
     * Form sửa
     */
    public function edit(PaymentPackage $paymentPackage)
    {
        return view('admin.payment-packages.edit', compact('paymentPackage'));
    }

    /**
     * Cập nhật
     */
    public function update(Request $request, PaymentPackage $paymentPackage)
    {
        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'duration'         => 'required|integer|min:1',
            'price'            => 'required|integer|min:0',
            'discount_percent' => 'nullable|integer|min:0|max:100',
            'display_order'    => 'nullable|integer|min:0',
            'is_featured'      => 'nullable|boolean',
            'status'           => 'nullable|boolean',
        ]);

        $discount = $data['discount_percent'] ?? 0;
        $data['final_price'] = $data['price'] - ($data['price'] * $discount / 100);

        $paymentPackage->update($data);

        return redirect()
            ->route('admin.payment-packages.index')
            ->with('success', 'Cập nhật gói nạp thành công');
    }

    /**
     * Xoá
     */
    public function destroy(PaymentPackage $paymentPackage)
    {
        $paymentPackage->delete();

        return redirect()
            ->route('admin.payment-packages.index')
            ->with('success', 'Xóa gói nạp thành công');
    }

    /**
     * Bật / tắt active
     */
    public function toggleStatus(PaymentPackage $paymentPackage)
    {
        $paymentPackage->status = !$paymentPackage->status;
        $paymentPackage->save();

        return redirect()
            ->back()
            ->with('success', 'Cập nhật trạng thái thành công');
    }

    public function paymentHistory(Request $request)
    {
        $query = Payment::with(['user', 'package'])
            ->orderByDesc('created_at');

        // ===== FILTER =====
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->q . '%')
                    ->orWhere('email', 'like', '%' . $request->q . '%');
            });
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $payments = $query->paginate(15)->withQueryString();

        // ===== STATISTICS =====
        $stats = [
            'total_orders' => Payment::count(),
            'success_orders' => Payment::where('status', 'success')->count(),
            'pending_orders' => Payment::where('status', 'pending')->count(),
            'failed_orders' => Payment::whereIn('status', ['failed', 'canceled', 'expired'])->count(),

            'total_revenue' => Payment::where('status', 'success')->sum('amount'),

            'today_revenue' => Payment::where('status', 'success')
                ->whereDate('paid_at', now())
                ->sum('amount'),

            'month_revenue' => Payment::where('status', 'success')
                ->whereMonth('paid_at', now()->month)
                ->whereYear('paid_at', now()->year)
                ->sum('amount'),
        ];

        return view('admin.payment-packages.payment-history', compact(
            'payments',
            'stats'
        ));
    }
}
