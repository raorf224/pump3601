<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LoyaltyController extends Controller
{
    public function customerPage(Request $request): View
    {
        $this->authorizeLoyaltyPermission($request, 'read', 'loyalty_customers');

        return view('loyalty-customers');
    }

    public function couponAssignmentPage(Request $request): View
    {
        $this->authorizeLoyaltyPermission($request, 'read', 'loyalty_coupon_assignments');

        return view('loyalty-coupon-assignments');
    }

    public function couponAssignmentOptions(Request $request): JsonResponse
    {
        $this->authorizeLoyaltyPermission($request, 'read', 'loyalty_coupon_assignments');

        $today = now()->toDateString();
        $customers = DB::table('loyalty_customers')
            ->where('status', 'active')
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get();
        $coupons = DB::table('coupons')
            ->where('coupon_for', 'specific')
            ->where('status', 'active')
            ->whereDate('valid_to', '>=', $today)
            ->select(['id', 'name', 'coupon_type', 'value', 'valid_from', 'valid_to'])
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => [
                'customers' => $customers,
                'coupons' => $coupons,
            ],
            'can_assign' => $this->hasLoyaltyPermission(
                $request,
                'update',
                'loyalty_coupon_assignments'
            ),
        ]);
    }

    public function coupons(Request $request): JsonResponse
    {
        $this->authorizeLoyaltyPermission($request, 'read');

        $coupons = DB::table('coupons as c')
            ->leftJoin('users as creator', 'creator.id', '=', 'c.created_by')
            ->select('c.*', 'creator.full_name as creator_name')
            ->orderByDesc('c.id')
            ->get();

        return response()->json([
            'data' => $coupons,
            'can_create' => $this->hasLoyaltyPermission($request, 'create'),
            'can_update' => $this->hasLoyaltyPermission($request, 'update'),
        ]);
    }

    public function storeCoupon(Request $request): JsonResponse
    {
        $this->authorizeLoyaltyPermission($request, 'create');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'coupon_for' => ['required', 'in:welcome,all,specific,referall'],
            'coupon_type' => ['required', 'in:percent,flat'],
            'value' => ['required', 'numeric', 'gt:0'],
            'status' => ['required', 'in:active,inactive'],
            'valid_from' => ['required', 'date'],
            'valid_to' => ['required', 'date', 'after_or_equal:valid_from'],
        ]);

        if ($validated['coupon_type'] === 'percent' && (float) $validated['value'] > 100) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => ['value' => ['Percentage discount cannot exceed 100%.']],
            ], 422);
        }

        $couponId = DB::table('coupons')->insertGetId([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'coupon_for' => $validated['coupon_for'],
            'coupon_type' => $validated['coupon_type'],
            'value' => $validated['value'],
            'status' => $validated['status'],
            'valid_from' => $validated['valid_from'],
            'valid_to' => $validated['valid_to'],
            'created_by' => $request->user()->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Coupon created successfully.',
            'id' => $couponId,
        ], 201);
    }

    public function updateCoupon(Request $request, int $id): JsonResponse
    {
        $this->authorizeLoyaltyPermission($request, 'update');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'coupon_for' => ['required', 'in:welcome,all,specific,referall'],
            'coupon_type' => ['required', 'in:percent,flat'],
            'value' => ['required', 'numeric', 'gt:0'],
            'status' => ['required', 'in:active,inactive'],
            'valid_from' => ['required', 'date'],
            'valid_to' => ['required', 'date', 'after_or_equal:valid_from'],
        ]);

        if ($validated['coupon_type'] === 'percent' && (float) $validated['value'] > 100) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => ['value' => ['Percentage discount cannot exceed 100%.']],
            ], 422);
        }

        $coupon = DB::table('coupons')->where('id', $id);
        abort_unless($coupon->exists(), 404, 'Coupon not found.');

        $coupon->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'coupon_for' => $validated['coupon_for'],
            'coupon_type' => $validated['coupon_type'],
            'value' => $validated['value'],
            'status' => $validated['status'],
            'valid_from' => $validated['valid_from'],
            'valid_to' => $validated['valid_to'],
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Coupon updated successfully.']);
    }

    public function updateCouponStatus(Request $request, int $id): JsonResponse
    {
        $this->authorizeLoyaltyPermission($request, 'update');

        $validated = $request->validate([
            'status' => ['required', 'in:active,inactive'],
        ]);

        $coupon = DB::table('coupons')->where('id', $id);
        abort_unless($coupon->exists(), 404, 'Coupon not found.');

        $coupon->update([
            'status' => $validated['status'],
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Coupon status updated successfully.']);
    }

    public function customers(Request $request): JsonResponse
    {
        $this->authorizeLoyaltyPermission($request, 'read', 'loyalty_customers');

        $customers = DB::table('loyalty_customers')
            ->select([
                'id',
                'player_id',
                'name',
                'phone',
                'email',
                'device_id',
                'type',
                'referral_number',
                'status',
                'created_at',
                'updated_at',
            ])
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'data' => $customers,
            'can_create' => $this->hasLoyaltyPermission($request, 'create', 'loyalty_customers'),
            'can_update' => $this->hasLoyaltyPermission($request, 'update', 'loyalty_customers'),
        ]);
    }

    public function customer(Request $request, int $id): JsonResponse
    {
        $this->authorizeLoyaltyPermission($request, 'read', 'loyalty_customers');

        $customer = $this->customerDetailsQuery()
            ->where('id', $id)
            ->first();

        abort_unless($customer !== null, 404, 'Customer not found.');

        return response()->json(['data' => $customer]);
    }

    public function storeCustomer(Request $request): JsonResponse
    {
        return $this->createCustomerRecord($this->validateCustomer($request));
    }

    public function storeLubeSaleWithCoupon(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'integer', 'exists:loyalty_customers,id'],
            'coupon_id' => ['required', 'integer', 'exists:coupons,id'],
            'station_id' => ['required', 'integer', 'exists:stations,id'],
            'shift_id' => ['required', 'integer'],
            'doc_type' => ['required', 'in:sale'],
            'account_id' => ['required', 'integer'],
            'invoice_no' => ['nullable', 'string'],
            'date' => ['required', 'date'],
            'payment_status' => ['required', 'in:paid,not_paid,partial'],
            'remarks' => ['nullable', 'string'],
            'created_by' => ['required', 'integer'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.product_id' => ['required', 'integer'],
            'lines.*.qty' => ['required', 'numeric', 'min:1'],
            'lines.*.unit_price' => ['required', 'numeric', 'min:0'],
            'lines.*.tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        return DB::transaction(function () use ($request, $validated) {
            $customer = DB::table('loyalty_customers')
                ->where('id', $validated['customer_id'])
                ->lockForUpdate()
                ->first();

            if (!$customer || $customer->status !== 'active') {
                return response()->json([
                    'message' => 'Customer not found or inactive.',
                ], 422);
            }

            $coupon = DB::table('loyalty_customer_coupons as customer_coupon')
                ->join('coupons', 'coupons.id', '=', 'customer_coupon.coupon_id')
                ->where('customer_coupon.customer_id', $validated['customer_id'])
                ->where('customer_coupon.coupon_id', $validated['coupon_id'])
                ->where('customer_coupon.is_used', '0')
                ->where('coupons.status', 'active')
                ->whereDate('coupons.valid_from', '<=', now()->toDateString())
                ->whereDate('coupons.valid_to', '>=', now()->toDateString())
                ->lockForUpdate()
                ->first([
                    'coupons.id',
                    'coupons.name',
                    'coupons.coupon_type',
                    'coupons.value',
                ]);

            if (!$coupon) {
                return response()->json([
                    'message' => 'Coupon is not assigned to this customer, is already used, or is not currently valid.',
                ], 422);
            }

            $subtotal = 0.0;
            foreach ($validated['lines'] as $line) {
                $subtotal += (float) $line['qty'] * (float) $line['unit_price'];
            }

            $couponValue = (float) $coupon->value;
            $discount = $coupon->coupon_type === 'percent'
                ? round($subtotal * $couponValue / 100, 2)
                : min($subtotal, $couponValue);
            $discountFactor = $subtotal > 0
                ? ($subtotal - $discount) / $subtotal
                : 1;
            $discountedLines = $validated['lines'];
            $discountedSubtotal = 0.0;

            foreach ($discountedLines as &$line) {
                $line['unit_price'] = round((float) $line['unit_price'] * $discountFactor, 2);
                $discountedSubtotal += (float) $line['qty'] * $line['unit_price'];
            }
            unset($line);

            $discount = round($subtotal - $discountedSubtotal, 2);
            $couponRemark = sprintf(
                'Coupon #%d (%s) applied; discount: %0.2f',
                $coupon->id,
                $coupon->name,
                $discount
            );
            $remarks = trim((string) ($validated['remarks'] ?? ''));
            $request->merge([
                'lines' => $discountedLines,
                'remarks' => $remarks === '' ? $couponRemark : $remarks . "\n" . $couponRemark,
            ]);

            $saleResponse = app(LubeController::class)->store($request);
            if ($saleResponse->getStatusCode() !== 201) {
                return $saleResponse;
            }

            $updated = DB::table('loyalty_customer_coupons')
                ->where('customer_id', $validated['customer_id'])
                ->where('coupon_id', $validated['coupon_id'])
                ->where('is_used', '0')
                ->update([
                    'is_used' => '1',
                    'updated_at' => now(),
                ]);

            if ($updated !== 1) {
                abort(409, 'Coupon has already been used.');
            }

            DB::table('loyalty_coupon_usage_logs')->insert([
                'coupon_id' => $coupon->id,
                'station_id' => $validated['station_id'],
                'customer_id' => $validated['customer_id'],
                'used_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $saleData = $saleResponse->getData(true);
            $saleTotal = DB::table('lube_lines')
                ->where('document_id', $saleData['id'])
                ->selectRaw('COALESCE(SUM(line_amount + tax_amount), 0) as total_amount')
                ->value('total_amount');

            return response()->json(array_merge($saleData, [
                'coupon_id' => (int) $coupon->id,
                'discount_amount' => $discount,
                'total_amount' => (float) $saleTotal,
            ]), 201);
        });
    }

    public function updateCustomer(Request $request, int $id): JsonResponse
    {
        $this->authorizeLoyaltyPermission($request, 'update', 'loyalty_customers');

        $customer = DB::table('loyalty_customers')->where('id', $id);
        abort_unless($customer->exists(), 404, 'Customer not found.');

        $validated = $request->validate([
            'player_id' => [
                'required',
                'string',
                'max:191',
                Rule::unique('loyalty_customers', 'player_id')->ignore($id),
            ],
            'name' => ['required', 'string', 'max:150'],
            'phone' => [
                'required',
                'string',
                'max:30',
                Rule::unique('loyalty_customers', 'phone')->ignore($id),
            ],
            'email' => [
                'required',
                'email',
                'max:191',
                Rule::unique('loyalty_customers', 'email')->ignore($id),
            ],
            'device_id' => [
                'required',
                'string',
                'max:191',
                Rule::unique('loyalty_customers', 'device_id')->ignore($id),
            ],
            'type' => ['required', 'in:new,referral'],
            'referral_number' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $customer->update([
            'player_id' => $validated['player_id'],
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'device_id' => $validated['device_id'],
            'type' => $validated['type'],
            'referral_number' => $validated['type'] === 'referral'
                ? ($validated['referral_number'] ?? null)
                : null,
            'status' => $validated['status'],
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Loyalty customer updated successfully.']);
    }

    public function updateCustomerStatus(Request $request, int $id): JsonResponse
    {
        $this->authorizeLoyaltyPermission($request, 'update', 'loyalty_customers');

        $validated = $request->validate([
            'status' => ['required', 'in:active,inactive'],
        ]);

        $customer = DB::table('loyalty_customers')->where('id', $id);
        abort_unless($customer->exists(), 404, 'Customer not found.');

        $customer->update([
            'status' => $validated['status'],
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Customer status updated successfully.']);
    }

    public function assignSpecificCoupon(Request $request, int $customerId): JsonResponse
    {
        $request->merge([
            'customer_ids' => [$customerId],
            'coupon_ids' => [$request->input('coupon_id')],
        ]);

        return $this->assignSpecificCoupons($request);
    }

    public function assignSpecificCoupons(Request $request): JsonResponse
    {
        $this->authorizeLoyaltyPermission($request, 'update', 'loyalty_coupon_assignments');

        $validated = $request->validate([
            'customer_ids' => ['required', 'array', 'min:1'],
            'customer_ids.*' => ['required', 'integer', 'distinct'],
            'coupon_ids' => ['required', 'array', 'min:1'],
            'coupon_ids.*' => ['required', 'integer', 'distinct'],
        ]);

        $customerIds = array_map('intval', $validated['customer_ids']);
        $couponIds = array_map('intval', $validated['coupon_ids']);

        return DB::transaction(function () use ($customerIds, $couponIds) {
            $now = now();
            $activeCustomerIds = DB::table('loyalty_customers')
                ->whereIn('id', $customerIds)
                ->where('status', 'active')
                ->orderBy('id')
                ->lockForUpdate()
                ->pluck('id')
                ->map(function ($id) {
                    return (int) $id;
                })
                ->all();

            if (\count($activeCustomerIds) !== \count($customerIds)) {
                return response()->json([
                    'message' => 'All selected customers must exist and be active.',
                ], 422);
            }

            $eligibleCouponIds = DB::table('coupons')
                ->whereIn('id', $couponIds)
                ->where('coupon_for', 'specific')
                ->where('status', 'active')
                ->whereDate('valid_to', '>=', $now->toDateString())
                ->orderBy('id')
                ->lockForUpdate()
                ->pluck('id')
                ->map(function ($id) {
                    return (int) $id;
                })
                ->all();

            if (\count($eligibleCouponIds) !== \count($couponIds)) {
                return response()->json([
                    'message' => 'Select only active, unexpired specific coupons.',
                ], 422);
            }

            $existingAssignments = DB::table('loyalty_customer_coupons')
                ->whereIn('customer_id', $customerIds)
                ->whereIn('coupon_id', $couponIds)
                ->get(['customer_id', 'coupon_id'])
                ->mapWithKeys(function ($assignment) {
                    return ["{$assignment->customer_id}:{$assignment->coupon_id}" => true];
                });

            $newAssignments = [];
            $alreadyAssignedCount = 0;
            foreach ($customerIds as $customerId) {
                foreach ($couponIds as $couponId) {
                    $key = "{$customerId}:{$couponId}";
                    if ($existingAssignments->has($key)) {
                        $alreadyAssignedCount++;
                        continue;
                    }

                    $newAssignments[] = [
                        'customer_id' => $customerId,
                        'coupon_id' => $couponId,
                        'is_used' => '0',
                        'assigned_at' => $now,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            if ($newAssignments !== []) {
                DB::table('loyalty_customer_coupons')->insert($newAssignments);
            }

            $assignedCount = \count($newAssignments);

            return response()->json([
                'message' => "Assigned {$assignedCount} customer-coupon pair(s); {$alreadyAssignedCount} were already assigned.",
                'assigned_count' => $assignedCount,
                'already_assigned_count' => $alreadyAssignedCount,
            ], $assignedCount > 0 ? 201 : 200);
        });
    }

    public function specificCouponAssignments(Request $request): JsonResponse
    {
        $this->authorizeLoyaltyPermission($request, 'read', 'loyalty_coupon_assignments');

        $validated = $request->validate([
            'customer_ids' => ['required', 'array', 'min:1'],
            'customer_ids.*' => ['required', 'integer', 'distinct', 'exists:loyalty_customers,id'],
        ]);

        $today = now()->toDateString();
        $assignments = DB::table('loyalty_customer_coupons as customer_coupon')
            ->join('loyalty_customers as customer', 'customer.id', '=', 'customer_coupon.customer_id')
            ->join('coupons', 'coupons.id', '=', 'customer_coupon.coupon_id')
            ->whereIn('customer_coupon.customer_id', $validated['customer_ids'])
            ->where('coupons.coupon_for', 'specific')
            ->select([
                'customer_coupon.customer_id',
                'customer.name as customer_name',
                'coupons.id as coupon_id',
                'coupons.name',
                'coupons.coupon_type',
                'coupons.value',
                'coupons.status',
                'coupons.valid_from',
                'coupons.valid_to',
                'customer_coupon.is_used',
                'customer_coupon.assigned_at',
            ])
            ->selectSub(function ($query) {
                $query->from('loyalty_coupon_usage_logs')
                    ->selectRaw('MAX(used_at)')
                    ->whereColumn('coupon_id', 'customer_coupon.coupon_id')
                    ->whereColumn('customer_id', 'customer_coupon.customer_id');
            }, 'last_used_at')
            ->orderBy('customer.name')
            ->orderBy('customer_coupon.assigned_at')
            ->get()
            ->map(function ($assignment) use ($today) {
                $assignment->is_used = (string) ($assignment->is_used ?? '0');
                $assignment->usable = $assignment->is_used === '0'
                    && $assignment->status === 'active'
                    && $assignment->valid_from <= $today
                    && $assignment->valid_to >= $today;

                return $assignment;
            });

        return response()->json([
            'data' => $assignments,
        ]);
    }

    public function registerCustomer(Request $request): JsonResponse
    {
        return $this->createCustomerRecord($this->validateCustomer($request));
    }

    public function loginCustomer(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'identifier' => ['required', 'string', 'max:191'],
            'password' => ['required', 'string'],
        ]);

        $customer = DB::table('loyalty_customers')
            ->where(function ($query) use ($validated) {
                $query->where('email', $validated['identifier'])
                    ->orWhere('phone', $validated['identifier']);
            })
            ->first();

        if (!$customer || !Hash::check($validated['password'], $customer->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid phone/email or password.',
            ], 401);
        }

        if ($customer->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Customer account is inactive.',
            ], 403);
        }

        $customerDetails = $this->customerDetailsQuery()
            ->where('id', $customer->id)
            ->first();

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data' => $customerDetails,
        ]);
    }

    public function customerDetailsForApp(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string'],
        ]);

        $customer = DB::table('loyalty_customers')
            ->where('id', $id)
            ->first();

        abort_unless(
            $customer && Hash::check($validated['password'], $customer->password),
            401,
            'Invalid customer ID or password.'
        );

        unset($customer->password);

        return response()->json(['data' => $customer]);
    }

    public function customerCouponsForApp(int $id): JsonResponse
    {
        if (!DB::table('loyalty_customers')->where('id', $id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found.',
            ], 404);
        }

        $today = now()->toDateString();
        $coupons = DB::table('loyalty_customer_coupons as customer_coupon')
            ->join('coupons', 'coupons.id', '=', 'customer_coupon.coupon_id')
            ->where('customer_coupon.customer_id', $id)
            ->select([
                'coupons.id',
                'coupons.name',
                'coupons.description',
                'coupons.coupon_for',
                'coupons.coupon_type',
                'coupons.value',
                'coupons.status',
                'coupons.valid_from',
                'coupons.valid_to',
                'customer_coupon.is_used',
                'customer_coupon.assigned_at',
            ])
            ->selectSub(function ($query) {
                $query->from('loyalty_coupon_usage_logs')
                    ->selectRaw('MAX(used_at)')
                    ->whereColumn('coupon_id', 'customer_coupon.coupon_id')
                    ->whereColumn('customer_id', 'customer_coupon.customer_id');
            }, 'last_used_at')
            ->orderBy('customer_coupon.assigned_at')
            ->get()
            ->map(function ($coupon) use ($today) {
                $coupon->is_used = (string) ($coupon->is_used ?? '0');
                $coupon->usable = $coupon->is_used === '0'
                    && $coupon->status === 'active'
                    && $coupon->valid_from <= $today
                    && $coupon->valid_to >= $today;

                return $coupon;
            });

        return response()->json([
            'success' => true,
            'data' => $coupons,
        ]);
    }

    public function changeCustomerPassword(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'max:255', 'confirmed'],
        ]);

        $customer = DB::table('loyalty_customers')
            ->where('id', $id)
            ->where('status', 'active')
            ->first();

        abort_unless(
            $customer && Hash::check($validated['current_password'], $customer->password),
            401,
            'Invalid customer ID or current password.'
        );

        DB::table('loyalty_customers')
            ->where('id', $id)
            ->update([
                'password' => Hash::make($validated['password']),
                'updated_at' => now(),
            ]);

        return response()->json(['message' => 'Customer password updated successfully.']);
    }

    private function validateCustomer(Request $request): array
    {
        return $request->validate([
            'player_id' => ['required', 'string', 'max:191', 'unique:loyalty_customers,player_id'],
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:30', 'unique:loyalty_customers,phone'],
            'email' => ['required', 'email', 'max:191', 'unique:loyalty_customers,email'],
            'password' => ['required', 'string', 'min:6', 'max:255'],
            'device_id' => ['required', 'string', 'max:191', 'unique:loyalty_customers,device_id'],
            'type' => ['required', 'in:new,referral'],
            'referral_number' => ['nullable', 'string', 'max:100'],
            'status' => ['sometimes', 'in:active,inactive'],
        ]);
    }

    private function createCustomerRecord(array $validated): JsonResponse
    {
        $customerId = DB::transaction(function () use ($validated) {
                $now = now();
                $customerId = DB::table('loyalty_customers')->insertGetId([
                    'player_id' => $validated['player_id'],
                    'name' => $validated['name'],
                    'phone' => $validated['phone'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'device_id' => $validated['device_id'],
                    'type' => $validated['type'],
                    'referral_number' => $validated['type'] === 'referral'
                        ? ($validated['referral_number'] ?? null)
                        : null,
                    'status' => $validated['status'] ?? 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $couponFor = ['all', 'welcome'];
                if ($validated['type'] === 'referral') {
                    $couponFor[] = 'referall';
                }

                $couponIds = DB::table('coupons')
                    ->where('status', 'active')
                    ->whereDate('valid_to', '>=', $now->toDateString())
                    ->whereIn('coupon_for', $couponFor)
                    ->pluck('id');

                if ($couponIds->isNotEmpty()) {
                    DB::table('loyalty_customer_coupons')->insert(
                        $couponIds->map(function ($couponId) use ($customerId, $now) {
                            return [
                                'customer_id' => $customerId,
                                'coupon_id' => $couponId,
                                'is_used' => '0',
                                'assigned_at' => $now,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                        })->all()
                    );
                }

            return $customerId;
        });

        $customer = $this->customerDetailsQuery()
            ->where('id', $customerId)
            ->first();

        return response()->json([
            'message' => 'Loyalty customer created successfully.',
            'data' => $customer,
        ], 201);
    }

    private function customerDetailsQuery()
    {
        return DB::table('loyalty_customers')
            ->select([
                'id',
                'player_id',
                'name',
                'phone',
                'email',
                'device_id',
                'type',
                'referral_number',
                'status',
                'created_at',
                'updated_at',
            ]);
    }

    private function authorizeLoyaltyPermission(
        Request $request,
        string $action,
        string $permission = 'loyalty_coupons'
    ): void {
        $user = $request->user();
        abort_unless($user, 401, 'Unauthenticated.');

        abort_unless(
            $this->hasLoyaltyPermission($request, $action, $permission),
            403,
            'You do not have permission to access Loyalty.'
        );
    }

    private function hasLoyaltyPermission(
        Request $request,
        string $action,
        string $permission = 'loyalty_coupons'
    ): bool {
        $user = $request->user();
        if (!$user || !in_array($action, ['read', 'create', 'update'], true)) {
            return false;
        }

        $roleName = strtolower((string) $user->role);
        if ($roleName === 'employee') {
            $roleName = strtolower((string) DB::table('employees')
                ->where('user_id', $user->id)
                ->value('role'));
        }

        $roleId = DB::table('roles')
            ->whereRaw('LOWER(name) = ?', [$roleName])
            ->value('id');
        if (!$roleId) {
            return false;
        }

        return DB::table('role_has_permissions as rp')
            ->join('permissions as p', 'p.id', '=', 'rp.permission_id')
            ->where('rp.role_id', $roleId)
            ->where('p.name', $permission)
            ->where("rp.{$action}", 1)
            ->exists();
    }
}
