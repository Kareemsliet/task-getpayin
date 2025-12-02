<?php

namespace App\Http\Controllers\Api;

use App\Enums\PaymentStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\HoldRequest;
use App\Http\Requests\OrderRequest;
use App\Http\Requests\WebHookPaymentRequest;
use App\Http\Resources\HoldResource;
use App\Http\Resources\OrderResource;
use App\Http\Resources\ProductResource;
use App\Models\Hold;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function getProduct(string $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return errorJsonResponse("Product not found");
        }

        return successJsonResponse(
            "Product retrieved successfully",
            ProductResource::make($product),
        );
    }

    public function createHold(HoldRequest $request)
    {
        // validate the data request
        $request->validated();

        $holdQuantity = $request->input("quantity");
        $expiresDate = now()->addMinutes(2);

        // create hold within a transaction
        return DB::transaction(function () use ($holdQuantity, $expiresDate) {

            // Lock the product row for update
            $product = Product::query()->lockForUpdate()->find(request()->input("product_id"));

            // check if sufficient stock is available
            if ($product->stock < $holdQuantity) {
                return errorJsonResponse("Insufficient stock available for the requested hold quantity,product stock: {$product->stock}, requested: {$holdQuantity}");
            }

            // Create the hold
            $hold = $product->holds()->create([
                "quantity" => $holdQuantity,
                "expires_at" => $expiresDate,
            ]);

            // Deduct the held quantity from product stock
            $product->decrement('stock', $holdQuantity);

            return successJsonResponse(
                "Hold created successfully",
                HoldResource::make($hold),
            );

        });
    }

    public function createOrder(OrderRequest $request)
    {
        // validate the data request
        $request->validated();

        // Retrieve the hold with its associated order existence status
        $hold = Hold::withExists("order")->find($request->hold_id);

        // check if the hold is exists
        if (!$hold) {
            return errorJsonResponse("Hold Not Found");
        }

        // Check if the hold is expired or already used
        if ($hold->is_expired || $hold->order_exists) {
            return errorJsonResponse("Hold is either expired or already used for an order.");
        }

        // Create the order associated with the hold in pre payment status
        $order = $hold->order()->create(["payment_status" => PaymentStatusEnum::PREPARING]);

        return successJsonResponse(
            "Order created successfully",
            OrderResource::make($order),
        );
    }

    public function handlePaymentWebhook(WebHookPaymentRequest $request)
    {
        // validate the data request
        $request->validated();
    
        // Log the incoming webhook request
        Log::info("Received payment webhook", $request->all());

        $orderId = $request->input("order_id");
        $paymentStatus = PaymentStatusEnum::from($request->input("status"));

        // Use transaction with row locking
        return DB::transaction(function () use ($orderId, $paymentStatus) {

            // Lock the order row for update
            $order = Order::query()->where('id', $orderId)->lockForUpdate()->first();

            if (!$order) {

                // Log and store webhook for non-existing order
                Log::error("Order not found: {$orderId}");

                // Return error response for non-existing order
                return errorJsonResponse("Order not found");

            }

            // Check if order payment status already in final state
            if (!$order->payment_status->isPrepare()) {

                Log::warning("Order already in final state: {$order->id}");

                return errorJsonResponse("Order already processed");

            }

            // Process payment status if success
            if ($paymentStatus->isSuccess()) {

                $order->payment_status = PaymentStatusEnum::SUCCESS;

                $order->save();

                Log::info("Payment success for order: {$order->id}");

            }

            // Process payment status if failure
            if ($paymentStatus->isFailure()) {

                $hold = $order->hold;

                $product = $hold->product;

                $product->increment('stock', $hold->quantity);

                $order->payment_status = PaymentStatusEnum::FAILURE;
                $order->save();

                Log::info("Payment failed for order: {$order->id}, stock released");

            }

            return successJsonResponse("Webhook processed successfully");

        });
    }
}
