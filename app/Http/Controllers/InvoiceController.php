<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function preview($id)
    {
        $order = Order::find($id);
        return view('invoice.index', compact('order'));
    }

    public function download($id)
    {
        $order = Order::find($id);
        $pdf = Pdf::loadView('invoice.index', [
            'order' => $order
        ]);
        $name = 'INV-' . $order->name . '.pdf';
        return $pdf->download($name);
    }
}