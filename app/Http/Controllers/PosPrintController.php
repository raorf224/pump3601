<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Illuminate\Support\Facades\DB;

class PosPrintController extends Controller
{
    // === RECEIPT PRINT ===
    public function printReceipt($orderId)
    {
        try {
            $orderItems = \App\Models\PosOrder::where('order_id', $orderId)->get();

            if ($orderItems->isEmpty()) {
                return response()->json(['success' => false, 'error' => 'Order not found']);
            }

            $order = $orderItems->first();

            // ✅ CORRECT WAY - Use the share name directly
            // Since printer is shared as "BC-99AC", we can use just the name
            $printerName = "BC-99AC";


            $connector = new WindowsPrintConnector($printerName);
            $printer = new Printer($connector);

            $store = DB::table('stores')->where('id', $order->store_id)->first();
            $storeName = $store ? $store->store_name : "Unknown Store";

            // Header
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setEmphasis(true);
            $printer->text(strtoupper($storeName) . "\n");
            $printer->setEmphasis(false);
            $printer->text("Receipt #: " . $order->order_id . "\n");
            $printer->text(date('Y-m-d H:i:s') . "\n");
            $printer->text("------------------------------------------\n");

            // Items
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->setEmphasis(true);
            $printer->text(sprintf("%-16s %5s %8s\n", "Item", "Qty", "Total"));
            $printer->setEmphasis(false);
            $printer->text("------------------------------------------\n");

            $totalAmount = 0;

            foreach ($orderItems as $item) {
                $product = DB::table('store_products')->where('id', $item->product_id)->first();
                if ($product) {
                    $qty = $item->quantity ?? 1;
                    $price = $item->price ?? $product->unit_price;
                    $lineTotal = $qty * $price;
                    $totalAmount += $lineTotal;

                    $line = sprintf(
                        "%-16s %5s %8s\n",
                        substr($product->product_name, 0, 16),
                        $qty,
                        number_format($lineTotal, 2)
                    );
                    $printer->text($line);
                }
            }

            $printer->text("------------------------------------------\n");
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->setEmphasis(true);
            $printer->text("TOTAL: " . number_format($totalAmount, 2) . " PKR\n");
            $printer->setEmphasis(false);
            $printer->feed(2);
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("Thank you for shopping!\n");
            $printer->text("Please visit again.\n");
            $printer->feed(3);
            $printer->cut();
            $printer->close();

            return response()->json([
                'success' => true,
                'message' => '🧾 Receipt printed successfully on: ' . $printerName
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'note' => 'Printer is shared as BC-99AC. Check if Apache has permissions.'
            ]);
        }
    }

}
