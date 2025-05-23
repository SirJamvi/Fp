<?php

namespace App\Http\Controllers\Pelayan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\AddItemsRequest;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Services\ReservasiService;

class PelayanController extends Controller
{
    protected $orderService;
    protected $paymentService;
    protected $reservasiService;

    public function __construct(
        OrderService $orderService,
        PaymentService $paymentService,
        ReservasiService $reservasiService
    ) {
        $this->orderService     = $orderService;
        $this->paymentService   = $paymentService;
        $this->reservasiService = $reservasiService;
    }

    /**
     * Tampilkan halaman dashboard (daftar menu & meja).
     */
    public function index()
    {
        return $this->orderService->showDashboard();
    }

    /**
     * Simpan pesanan baru (reservasi + order items).
     */
    public function storeOrder(StoreOrderRequest $request)
    {
        return $this->orderService->createOrder($request);
    }

    /**
     * Proses pembayaran (tunai atau QRIS) untuk reservasi tertentu.
     */
    public function processPayment(Request $request, $reservasi_id)
    {
        return $this->paymentService->process($request, $reservasi_id);
    }

    /**
     * Tampilkan ringkasan pesanan (order summary).
     */
    public function showOrderSummary($reservasi_id)
    {
        return $this->orderService->summary($reservasi_id);
    }

    /**
     * Daftar reservasi (list view).
     */
    public function reservasi(Request $request)
    {
        return $this->reservasiService->list($request);
    }

    /**
     * View untuk scan QR Code.
     */
    public function scanQr()
    {
        return view('pelayan.scanqr', ['title' => 'Scan QR Code']);
    }

    /**
     * Tampilkan form bayar sisa (partial payment).
     */
    public function bayarSisa($id)
    {
        return $this->paymentService->showPartialPayment($id);
    }

    /**
     * Proses bayar sisa (partial payment) via tunai atau QRIS.
     */
    public function bayarSisaPost(Request $request, $id)
    {
        return $this->paymentService->handlePartialPayment($request, $id);
    }

    /**
     * Tampilkan halaman pembayaran QRIS.
     */
    public function showQrisPayment($id)
    {
        return $this->paymentService->showQrisPayment($id);
    }

    /**
     * Callback handler Midtrans setelah QRIS selesai.
     */
    public function handleQrisCallback(Request $request, $id)
    {
        return $this->paymentService->handleQrisCallback($request, $id);
    }

    /**
     * Proses hasil scan QR (confirm kehadiran).
     */
    public function prosesScanQr($kodeReservasi)
    {
        return $this->reservasiService->handleScan($kodeReservasi);
    }

    /**
     * Tandai reservasi selesai (set meja kembali tersedia).
     */
    public function completeReservation($reservasi_id)
    {
        return $this->reservasiService->complete($reservasi_id);
    }

    /**
     * Batalkan reservasi (set meja kembali tersedia).
     */
    public function cancelReservation($reservasi_id)
    {
        return $this->reservasiService->cancel($reservasi_id);
    }

    /**
     * Tambah item ke order (reservasi sudah aktif).
     */
    public function addItemsToOrder(AddItemsRequest $request, $reservasi_id)
    {
        return $this->orderService->addItems($request, $reservasi_id);
    }
}
