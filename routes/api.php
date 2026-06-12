<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TanksController;
use App\Http\Controllers\tanksdipController;
use App\Http\Controllers\ProductPricesController;
use App\Http\Controllers\StationProductsController;
use App\Http\Controllers\LubeController;
use App\Http\Controllers\DispensersController;
use App\Http\Controllers\NozzlesController;
use App\Http\Controllers\EmployeesController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\NozzleTotalizerResetController;
use App\Http\Controllers\DriverCreditController;
use App\Http\Controllers\SiteTotalAmountController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\TransactionsController;
use App\Http\Controllers\storecontroller;
use App\Http\Controllers\storeproductController;
use App\Http\Controllers\ShiftReportController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\PosPrintController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\SalaryComponentController;
use App\Http\Controllers\EmployeeSalaryManagementController;
use App\Http\Controllers\PayrollManagementController;
use App\Http\Controllers\PayslipController;
use App\Http\Controllers\AccountsController;
use App\Http\Controllers\OilPurchaseController;
use App\Http\Controllers\ShiftNozzleReadingsController;
use App\Http\Controllers\ChartOfAccountsController;
use App\Http\Controllers\JournalEntriesController;
use App\Http\Controllers\JournalEntryLinesController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SidebarController;
use App\Http\Controllers\DashController;
use App\Http\Controllers\FuelConsumptionReportController;
use App\Http\Controllers\EditCloseShiftController;
use App\Http\Controllers\ShiftCashFlow;

use App\Http\Controllers\ReceivedAmountController;

// ✅ Received Amount Routes - Separate for each role (following accounts pattern)
// Route::middleware('auth:web')->group(function () {
    Route::get('/received-amount', [ReceivedAmountController::class, 'index'])->name('received-amount');
    Route::post('/received-amount/receive', [ReceivedAmountController::class, 'receivePayment']);
    Route::get('/shifts/station/{stationId}/open', [ReceivedAmountController::class, 'getOpenShifts']);
    
    // Role-specific data routes - NO GROUPING, INDIVIDUAL ROWS
    Route::get('/received-amount/data/admin', [ReceivedAmountController::class, 'getDataAdmin']);
    Route::get('/received-amount/data/owner/{user_id}', [ReceivedAmountController::class, 'getDataOwner']);
    Route::get('/received-amount/data/employee/{user_id}', [ReceivedAmountController::class, 'getDataEmployee']);
// });

///////////////////////////////// USER //////////////////////////
Route::post('createuser', [UserController::class, 'store'])->name('user.store');
Route::get('fetch', [StationController::class, 'get'])->name('stations.fetch');
Route::get('user', [UserController::class, 'index'])->name('user.index');
Route::get('user/{id}', [UserController::class, 'show'])->name('user.show');
Route::post('updateuser/{id}', [UserController::class, 'update'])->name('user.update');
Route::delete('user/{id}', [UserController::class, 'destroy'])->name('user.destroy');

/////////////////////////////// Stations  ///////////////////////
Route::post('stations', [StationController::class, 'store'])->name('stations.store');
Route::get('stations/', [StationController::class, 'index'])->name('stations.index');
Route::get('stations/{user_id}', [StationController::class, 'index1']);
Route::get('stations_emp/{user_id}', [StationController::class, 'show_emp']);

Route::get('stationss/{id}', [StationController::class, 'show'])->name('stations.show');

Route::get('stations_product/{id}', [StationController::class, 'stations_product'])->name('stations.station_products');
Route::get('st_products/{id}', [ProductController::class, 'station_products'])->name('product.station_products');
Route::get('stations/{id}/products', [StationController::class, 'stations_product'])->name('stations.products');

Route::put('stations/{id}', [StationController::class, 'update'])->name('stations.update');
Route::delete('stations/{id}', [StationController::class, 'destroy'])->name('stations.destroy');
Route::patch('stations/{id}/status', [StationController::class, 'updateStatus']);
Route::get('stations-employee/{user_id}', [StationController::class, 'stationsForEmployee']);



/////////////////////////////// Products  ///////////////////////
Route::post('createproducts', [ProductController::class, 'store'])->name('products.store');
Route::get('products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{category}', [ProductController::class, 'getByCategory']);

Route::get('products/{id}', [ProductController::class, 'show'])->name('products.show');
Route::post('updateproducts/{id}', [ProductController::class, 'update'])->name('products.update');
Route::delete('products/{id}', [ProductController::class, 'destroy'])->name('products.destroy');
Route::get('station-product/{id}', [ProductController::class, 'getStationProduct'])->name('products.getStationProduct');

Route::put('station-product/{id}', [ProductController::class, 'updateStationProduct'])->name('products.updateStationProduct');

Route::delete('station-product/{id}', [ProductController::class, 'destroy'])->name('products.destroy');


///////////////////////////////// TANKS //////////////////////////
Route::get('user-tanks/{user_id}', [TanksController::class, 'index1'])->name('tanks.user');
Route::apiResource('tanks', TanksController::class)->names('tanks');

Route::get('stationwise/{id}', [TanksController::class, 'stationwise'])->name('tanks.stationwise');

Route::apiResource('product-prices', ProductPricesController::class)->names('product-prices');

// Tank Dip Routes
//Route::post('/tank-dip-readings', [tanksdipController::class, 'storeTankDipReadings']);
Route::get('/tank-dip-readings/station/{stationId}', [tanksdipController::class, 'getTankDipReadingsByStation']);
Route::delete('/tank-dip-readings/{id}', [tanksdipController::class, 'deleteTankDipReading']);
// Tank Gain/Loss Report Routes
Route::get('/tank-gain-loss-report/{dipReadingId}', [tanksdipController::class, 'getTankGainLossReport']);
Route::get('/download-gain-loss-report-pdf/{dipReadingId}', [tanksdipController::class, 'downloadGainLossReportPDF']);


// Station Products
Route::post('stations/assign-product', [StationProductsController::class, 'assignProductToStation'])->name('stations.assign-product');
Route::get('stations/{stationId}/products-with-prices', [StationProductsController::class, 'getStationProductsWithPrices'])->name('stations.products-with-prices');
Route::post('stations/assign-product-with-price', [StationProductsController::class, 'assignProductWithPrice'])->name('stations.assign-product-with-price');

// Dispensers
Route::get('user-dispensers/{user_id}', [DispensersController::class, 'index1'])->name('dispensers.user');
Route::apiResource('dispensers', DispensersController::class)->names('dispensers');

// Correct way with controller method
Route::get('station_dispensers/{id}', [DispensersController::class, 'station_dispensers'])->name('station.dispensers');

// // Dealer-wise dispensers
Route::get('dealers/{dealerId}/dispensers', [DispensersController::class, 'getDealerDispensers'])->name('dealers.dispensers');

// Accounts
Route::apiResource('accounts', AccountsController::class)->names('accounts');
Route::get('user-accounts/{user_id}', [AccountsController::class, 'index1'])->name('stations.accounts');
Route::post('/sync', [AccountsController::class, 'sync']);

Route::get('stations/{stationId}/accounts', [AccountsController::class, 'getAccountsByStation'])->name('stations.accounts');
Route::get('stations/{stationId}/bank-accounts', [AccountsController::class, 'getAccountsByStationbank'])->name('stations.bank-accounts');

Route::get('accounts/category/{type}', [AccountsController::class, 'getAccountsByCategory'])->name('accounts.category');
Route::get('accounts/category/{type}/{user_id}', [AccountsController::class, 'getAccountsByCategory1'])->name('accounts.category');

// Nozzles
Route::get('station_nozzle/{id}', [NozzlesController::class, 'station_nozzle'])->name('station.nozzles');
Route::apiResource('nozzles', NozzlesController::class)->names('nozzles');
Route::get('user-nozzles/{user_id}', [NozzlesController::class, 'index1'])->name('nozzles.user');



Route::apiResource('employees', EmployeesController::class);
Route::get('/employeebystation/{id}', [EmployeesController::class, 'showbystation'])->name(name: 'employee.station');

Route::get('/oil-purchases', [OilPurchaseController::class, 'index']);
Route::get('/oil-purchases/{user_id}', [OilPurchaseController::class, 'index1']);


Route::get('/oil-purchases/station/{stationId}', [OilPurchaseController::class, 'getByStation']);
Route::post('/oil-purchases', [OilPurchaseController::class, 'store']);
Route::patch('/oil-purchases/{id}/payment-status', [OilPurchaseController::class, 'updatePaymentStatus']);
Route::put('/oil-purchases/{id}', [OilPurchaseController::class, 'update']);
Route::get('/oil-purchasess/{id}', [OilPurchaseController::class, 'getbyId']);
Route::get('/oil-purchases/{id}/payment-history', [OilPurchaseController::class, 'getPaymentHistory']);
Route::post('/oil-purchases/{id}/partial-payment', [OilPurchaseController::class, 'processPartialPayment']);
    Route::get('/oil-purchases/{id}/receive-history', [OilPurchaseController::class, 'getReceiveHistory']);
    Route::get('/oil-purchases/{id}/can-receive', [OilPurchaseController::class, 'canReceiveMore']);

Route::get('/oil-purchases/{id}/payment-history', [OilPurchaseController::class, 'getPaymentHistory']);
Route::post('/oil-purchases/{id}/partial-payment', [OilPurchaseController::class, 'processPartialPayment']);
Route::get('/oil-purchases/{id}/shortage-details', [OilPurchaseController::class, 'getShortageDetails']);
Route::post('/oil-purchases/{id}/shortage-payment', [OilPurchaseController::class, 'processShortagePayment']);
Route::get('/oil-purchases/{id}/shortage-payment-status', [OilPurchaseController::class, 'checkShortagePaymentStatus']);
Route::get('/oil-purchases/{id}/shortage-payment-history', [OilPurchaseController::class, 'getShortagePaymentHistory']);
Route::get('/shortages/shift/{shiftId}', [OilPurchaseController::class, 'getShortagePaymentsByShift']);


// ✅ Driver Credits Routes
Route::post('/driver-credits', [DriverCreditController::class, 'store']);
Route::get('/driver-credits/shift/{shiftId}', [DriverCreditController::class, 'getByShift']);
Route::get('/driver-credits/shifts/{shiftId}', [DriverCreditController::class, 'getByShift1']);

////////////////////////////////////// Lube Purchase //////////////////////////

Route::prefix('lubes')->group(function () {
    // Specific routes FIRST
    Route::get('/inventory', [LubeController::class, 'getInventory']);
    Route::post('/check-stock', [LubeController::class, 'checkStock']);
    Route::get('/owner/{id}', [LubeController::class, 'getByOwner']);
    Route::get('/employee/{id}', [LubeController::class, 'getByEmployee']);
    Route::get('/station/{station_id}', [LubeController::class, 'byStation']);
    Route::get('/account/{account_id}', [LubeController::class, 'byAccount']);
    Route::get('/product/{product_id}', [LubeController::class, 'byProduct']);
    Route::put('/mark-paid/{id}', [LubeController::class, 'markAsPaid']);
    Route::put('/{id}/payment-status', [LubeController::class, 'updatePaymentStatus']);
    Route::post('/{id}/partial-payment', [LubeController::class, 'processPartialPayment']);
    Route::get('/{id}/payment-history', [LubeController::class, 'getPaymentHistory']);
    
    // CRUD routes
    Route::post('/', [LubeController::class, 'store']);
    Route::get('/', [LubeController::class, 'index']);
    Route::get('/{id}', [LubeController::class, 'show']);
    Route::delete('/{id}', [LubeController::class, 'destroy']);
	
	        // ✅ NEW ROUTES
    Route::post('/inventory/setup', [LubeController::class, 'setupInventory']);
    Route::get('/inventory/logs/{product_id?}', [LubeController::class, 'getInventoryLogs']);

});

// Shift Nozzle Readings
Route::get('/shift-nozzle-readings', [ShiftNozzleReadingsController::class, 'index']); // New
Route::get('/shift-nozzle-readings/last-reading/{nozzleId}', [ShiftNozzleReadingsController::class, 'getLastReading']);
Route::get('/shift-nozzle-readings/station/{stationId}', [ShiftNozzleReadingsController::class, 'getByStation'])->name('shift-nozzle-readings.station');
Route::post('/shift-nozzle-readings', [ShiftNozzleReadingsController::class, 'store'])->name('shift-nozzle-readings_store.store');
Route::put('/shift-nozzle-readings/{id}', [ShiftNozzleReadingsController::class, 'update']);
Route::delete('/shift-nozzle-readings/{id}', [ShiftNozzleReadingsController::class, 'destroy']);
Route::get('/employeebystation/{id}', [EmployeesController::class, 'showbystation'])->name(name: 'employee.station');
// Employees
Route::apiResource('employees', EmployeesController::class)->names('employees');
Route::get('user-employees/{user_id}', [EmployeesController::class, 'index1']);
Route::get('employees/station/{station_id}', [EmployeesController::class, 'show_station_id']);



///////////////////////////// Totelizer Request///////////////////////////
Route::get('/nozzle-totalizer-resets', [NozzleTotalizerResetController::class, 'index']);
Route::get('/nozzle-totalizer-resets/nozzle/{nozzleId}', [NozzleTotalizerResetController::class, 'getByNozzle']);
Route::post('/nozzle-totalizer-resets', [NozzleTotalizerResetController::class, 'store']);
Route::get('/nozzle-totalizer-resets/station/{id}', [NozzleTotalizerResetController::class, 'show']);

///////////////////////////////// Shifts //////////////////////////
Route::get('/shifts', [ShiftController::class, 'index']);
Route::get('user-shifts/{user_id}', [ShiftController::class, 'index1']);
Route::get('/open-shifts/{stationId}', [ShiftController::class, 'getOpenShifts']);

Route::get('/shifts/{id}', [ShiftController::class, 'show'])->name('shift.show');
Route::get('/shift_show/{id}', [ShiftController::class, 'shift_show'])->name('shift_show.show');
Route::post('/shifts', [ShiftController::class, 'store']);
Route::put('/shifts/{id}', [ShiftController::class, 'update']);


Route::get('/shifts/{id}/for-edit', [ShiftController::class, 'getShiftForEdit']);
Route::put('/shifts/{id}/update-closed', [ShiftController::class, 'updateClosedShift']);

// New rputes

// Tank routes
Route::get('tanks/station/{stationId}', [TanksController::class, 'getByStation']);
Route::get('tanks/station/{stationId}/shift/{shiftId}', [TanksController::class, 'getByStationWithShift']);
Route::get('tank-dips/last/{tankId}', [TanksDipController::class, 'getLastDip']);
Route::post('tank-dips', [TanksDipController::class, 'storeTankDipReadings']);

// Nozzle routes  
Route::get('nozzles/station/{stationId}', [NozzlesController::class, 'getByStation']);
Route::get('nozzles/station/{stationId}/shift/{shiftId}', [NozzlesController::class, 'getByStationWithShift']);
Route::get('shift-nozzle-readings/last-reading/{nozzleId}', [ShiftNozzleReadingsController::class, 'getLastReading']);
Route::get('/last-shift-end-time/{stationId}', [ShiftController::class, 'getLastShiftEndTime']);
Route::get('/product-price/{stationId}/{productId}/{date}', [NozzleTotalizerResetController::class, 'getPriceByDate']);
Route::get('/shifts/close', [ShiftController::class, 'closeShiftPage'])->name('shifts.close');
Route::get('/stations/{id}/open-shifts', [ShiftController::class, 'getOpenShiftsByStation']);
Route::post('shift-cash-flow', [ShiftController::class, 'saveCashFlow']);
Route::get('last-shift-cash-return/{stationId}', [ShiftController::class, 'getLastShiftCashReturn']);
Route::get('/transactions/shift/{shiftId}', [TransactionsController::class, 'getCashByShift']);
Route::get('/transactionss/shift/{shiftId}', [LubeController::class, 'getByShift']);

Route::get('/lubes/shift/{shiftId}', [LubeController::class, 'getByShift']);
Route::get('/station-products/{stationId}', [ProductController::class, 'getStationProducts']);
Route::get('/station-product-tanks/{stationId}/{productId}', [TanksController::class, 'getStationProductTanks']);
Route::post('/oil-purchases/{id}/receive', [OilPurchaseController::class, 'receiveOrder']);
Route::get('/oil-purchases/shift/{shiftId}', [OilPurchaseController::class, 'getByShift']);
Route::get('/product-price/{stationId}/{productId}/{date}', [NozzleTotalizerResetController::class, 'getPriceByDate']);
Route::get('/accounts/station/{stationId}/type/{type}', [AccountsController::class, 'getAccountsByStationAndType1']);


// ✅ Site Total Amount Routes
Route::get('/site-total-amount/current/{stationId}/{accountId}', [ShiftController::class, 'getCurrentAmount']);
Route::post('/site-total-amounts', [ShiftController::class, 'storeSiteTotalAmount']);






// routes/web.php
Route::get('/shift-reports', [ShiftReportController::class, 'index']);
Route::get('/shift-reports/{shift_id}', [ShiftReportController::class, 'show']);
Route::post('/shift-reports/generate', [ShiftReportController::class, 'generateReport']);
// Route::get('/shift-reports/download-pdf/{shiftId}', [ShiftReportController::class, 'downloadPDF'])->name('shift.download.pdf');

// Site Total Amount Routes
Route::get('/site-total-amount', [SiteTotalAmountController::class, 'index']);
Route::get('/site-total-amount/user/{userId}', [SiteTotalAmountController::class, 'getByUser']);
Route::get('/site-total-amount/employee/{userId}', [SiteTotalAmountController::class, 'getByEmployee']);
Route::get('/site-total-amount/latest', [SiteTotalAmountController::class, 'getLatestAmount']);
Route::post('/site-total-amount', [SiteTotalAmountController::class, 'store']);
Route::get('/site-total-amount/summary/{stationId}', [SiteTotalAmountController::class, 'getSummaryByStation']);
Route::get('/site-total-amount/summary', [SiteTotalAmountController::class, 'getTotalSummary']);
Route::get('site-total-amount/{stationId}/accounts', [SiteTotalAmountController::class, 'getBankAccountsByStation'])->name('stations.accounts');
Route::get('accounts/station/{stationId}/type/{type}', [AccountsController::class, 'getAccountsByStationAndType'])->name('accounts.station.type');

///////////////////////////////// Attendance //////////////////////////

// Route::apiResource('attendance', AttendanceController::class)->names('attendance');
// Route::get('/attendance/by-employee/{id}', [AttendanceController::class, 'getByEmployee']);

Route::get('/attendance', [AttendanceController::class, 'index']);
Route::get('user-attendance/{user_id}', [AttendanceController::class, 'index1']);

Route::get('/attendance/today-summary', [AttendanceController::class, 'getTodaySummary']);
Route::get('/attendance/{id}', [AttendanceController::class, 'show']);
Route::get('/attendance/by-employee/{id}', [AttendanceController::class, 'getByEmployee']);
Route::post('/attendance', [AttendanceController::class, 'store']);
Route::put('/attendance/{id}', [AttendanceController::class, 'update']);
Route::delete('/attendance/{id}', [AttendanceController::class, 'destroy']);

////////////////////////////Transaction///////////////////////////////////////
Route::get('/transactions', [TransactionsController::class, 'index']);
Route::get('user-transactions/{user_id}', [TransactionsController::class, 'index1']);
Route::get('/transactions/{id}', [TransactionsController::class, 'show']);
Route::post('/transactions', [TransactionsController::class, 'store']);
Route::delete('/transactions/{id}', [TransactionsController::class, 'destroy']);
// Route::get('/transactions/expense-sheet', [TransactionsController::class, 'expenseSheet']);
Route::get('/transactions/employee/{user_id}', [TransactionsController::class, 'getByEmployee']); // Employee ke liye naya route


Route::put('/transactions/receive/{id}', [TransactionsController::class, 'receiveTransaction']);

Route::get('/sales/expense-sheet', [TransactionsController::class, 'expenseSheet']);
Route::get('/account/view', [TransactionsController::class, 'accountsView']);
Route::get('/account/view/{user_id}', [TransactionsController::class, 'accountsView1']);
Route::get('/account/emp/{user_id}', [TransactionsController::class, 'show_emp']);



////////////////////////////Store Setup///////////////////////////////////////
Route::get('/store', [storecontroller::class, 'index']);
Route::get('/user-store/{user_id}', [storecontroller::class, 'index1']);

Route::get('/store/{id}', [storecontroller::class, 'show']);
Route::post('/store', [storecontroller::class, 'store']);
Route::put('/store/{id}', [storecontroller::class, 'update']);
Route::delete('/store/{id}', [storecontroller::class, 'destroy']);

////////////////////////////////Store Product Setup///////////////////////////////////////
// CATEGORY ROUTES
Route::post('/category', [storeproductController::class, 'createCategory']);
Route::get('/category', [storeproductController::class, 'getCategories']);

// STORE PRODUCT ROUTES
Route::post('/store-product', [storeproductController::class, 'storeProduct']);
Route::get('/store-product', [storeproductController::class, 'getProducts']);      // ✅ all products
Route::get('/store-product/{user_id}', [storeproductController::class, 'getProducts1']);      // ✅ all products station wise
Route::get('/get-product/{id}', [storeproductController::class, 'getProduct']);  // ✅ by ID
Route::put('/store-product/{id}', [storeproductController::class, 'updateProduct']);
Route::delete('/store-product/{id}', [storeproductController::class, 'deleteProduct']);

/////////////////////////////// POS //////////////////////////////////////////////////////////

Route::get('/pos/orders', [POSController::class, 'getOrders']);
Route::get('/pos/orders/{user_id}', [POSController::class, 'getOrders1']);
Route::post('/pos/orders', [POSController::class, 'createOrder']);


// Remove all old printer routes and add ONLY these:
Route::get('/print-receipt/{orderId}', [App\Http\Controllers\PosPrintController::class, 'printReceipt']);
Route::get('/test-printer', [App\Http\Controllers\PosPrintController::class, 'testPrinter']);




///////////////////////////////////payroll employee ////////////////////////////
Route::get('/sales/expense-sheet', [TransactionsController::class, 'expenseSheet']);
Route::get('/sales/expense-sheet/{user_id}', [TransactionsController::class, 'expenseSheet1']);

Route::get('/account/view', [TransactionsController::class, 'accountsView']);
// Payroll
Route::get('/payroll', [PayrollController::class, 'index']);
Route::get('user-payroll/{user_id}', [PayrollController::class, 'index1']);

Route::get('/payroll/employee/{employeeId}', [PayrollController::class, 'getByEmployee']);
Route::post('/payroll', [PayrollController::class, 'store']);
Route::delete('/payroll/{id}', [PayrollController::class, 'destroy']);
Route::get('/payroll/view/{id}', [PayrollController::class, 'getByPayrollId']);
Route::get('/payroll/view/{id}', [PayrollController::class, 'view']);
Route::post('/calculate-deduction', [PayrollController::class, 'calculateDeduction']);
Route::post('/apply-deduction', [PayrollController::class, 'applyDeduction']);

/////////////////////////////// Salary Components ////////////////////////////
Route::get('/salary_component', [SalaryComponentController::class, 'index']);
Route::get('/salary_component/{id}', [SalaryComponentController::class, 'show']);
Route::post('/salary_component/store', [SalaryComponentController::class, 'store']);
Route::put('/salary_component/update/{id}', [SalaryComponentController::class, 'update']);
Route::delete('/salary_component/delete/{id}', [SalaryComponentController::class, 'destroy']);
Route::patch('/salary_component/toggle-status/{id}', [SalaryComponentController::class, 'toggleStatus']);

/////////////////////////////// Employee Salary Management ////////////////////////////
Route::get('/employee-salary-management', [EmployeeSalaryManagementController::class, 'index']);
Route::get('/employee-salary-management/{user_id}', [EmployeeSalaryManagementController::class, 'index1']);

Route::get('/employee-salary-management-emp', [EmployeeSalaryManagementController::class, 'By_status']);
Route::get('/employee-salary-management/{id}', [EmployeeSalaryManagementController::class, 'show']);
Route::post('/employee-salary-management/store', [EmployeeSalaryManagementController::class, 'store']);
Route::put('/employee-salary-management/update-employee/{employeeId}', [EmployeeSalaryManagementController::class, 'updateEmployee']); // NEW
Route::delete('/employee-salary-management/delete-all/{employeeId}', [EmployeeSalaryManagementController::class, 'deleteAll']);
Route::get('/employees-dropdown', [EmployeeSalaryManagementController::class, 'getEmployees']);
Route::get('/employees-dropdown/{user_id}', [EmployeeSalaryManagementController::class, 'getEmployees_byuserid']);
Route::get('/salary-components-dropdown', [EmployeeSalaryManagementController::class, 'getSalaryComponents']);
Route::get('/employee-components/{employeeId}', [EmployeeSalaryManagementController::class, 'getEmployeeComponents']);
Route::get('/employee-salary-management-by-employee/{employee_user_id}', [EmployeeSalaryManagementController::class, 'getByEmployeeUser']); // ✅ NEW
Route::get('/employees-dropdown/by-employee/{employee_user_id}', [EmployeeSalaryManagementController::class, 'getEmployeesByEmployeeUser']); // ✅ NEW


/////////////////////////////// Payroll Management ////////////////////////////
Route::get('/payroll-management', [PayrollManagementController::class, 'index']);
Route::get('/payroll-management/{user_id}', [PayrollManagementController::class, 'index1']);

Route::post('/payroll-management/store', [PayrollManagementController::class, 'store']);
Route::delete('/payroll-management/delete/{mutli_employes_id}', [PayrollManagementController::class, 'destroy']);
Route::get('/payroll-management/summary', [PayrollManagementController::class, 'getSummary']);
 // Attendance Deduction Calculation
Route::post('/calculate-attendance-deduction', [PayrollManagementController::class, 'calculateAttendanceDeductionApi']);

// Payslip Routes
Route::get('/payslips', [PayslipController::class, 'index']);
Route::get('/payslips/{id}', [PayslipController::class, 'show']);

 


// Chart of Accounts
Route::get('/chart-of-accounts', [ChartOfAccountsController::class, 'index']);
Route::get('/chart-of-accounts/{id}', [ChartOfAccountsController::class, 'show']);
Route::post('/chart-of-accounts', [ChartOfAccountsController::class, 'store']);
Route::put('/chart-of-accounts/{id}', [ChartOfAccountsController::class, 'update']);
Route::delete('/chart-of-accounts/{id}', [ChartOfAccountsController::class, 'destroy']);

// Journal Entries
Route::get('/journal-entries', [JournalEntriesController::class, 'index']);
Route::get('/journal-entries/{id}', [JournalEntriesController::class, 'show']);
Route::post('/journal-entries', [JournalEntriesController::class, 'store']);
Route::put('/journal-entries/{id}', [JournalEntriesController::class, 'update']);
Route::delete('/journal-entries/{id}', [JournalEntriesController::class, 'destroy']);

// Journal Entry Lines
Route::get('/journal-entry-lines', [JournalEntryLinesController::class, 'index']);
Route::get('/journal-entry-lines/{id}', [JournalEntryLinesController::class, 'show']);
Route::post('/journal-entry-lines', [JournalEntryLinesController::class, 'store']);
Route::put('/journal-entry-lines/{id}', [JournalEntryLinesController::class, 'update']);
Route::delete('/journal-entry-lines/{id}', [JournalEntryLinesController::class, 'destroy']);

    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::get('/getroles', [RoleController::class, 'get'])->name('roles.index');
    Route::post('/roles', [RoleController::class, 'store']);
    Route::get('/roles/{id}/edit', [RoleController::class, 'edit']);
    Route::put('/roles/{id}', [RoleController::class, 'update']);
    Route::delete('/roles/{id}', [RoleController::class, 'destroy']);

    Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
    Route::post('/assignPermissions/{roleId}', [PermissionController::class, 'assignPermissions']);

    Route::get('/permissions/{id}/edit', [PermissionController::class, 'edit']);
    Route::get('/getpermissions', [PermissionController::class, 'get']);
    Route::put('/permissions/{id}', [PermissionController::class, 'update']);
    Route::delete('/permissions/{id}', [PermissionController::class, 'destroy']);
    Route::get('/sidebar/{userId}', [SidebarController::class, 'getSidebar']);

    Route::get('/get_rolespermission/{id}', [PermissionController::class, 'getrolespermission']);
    Route::get('/getpermissionbyuserid/{id}/{role}', [PermissionController::class, 'getpermissionbyuserid']);
Route::get('/fuel-consumption-report', [FuelConsumptionReportController::class, 'index'])->name('fuel.consumption.report');
Route::get('/fuel-consumption-data', [FuelConsumptionReportController::class, 'getData'])->name('fuel.consumption.data');
Route::get('/dashboard', [DashController::class, 'index'])->name('api.dashboard');

// Edit Shift APIs
Route::get('/edit-shift/{shift_id}/data', [EditCloseShiftController::class, 'getShiftData']);
Route::get('/edit-shift/{shift_id}/cash-flow', [EditCloseShiftController::class, 'getCashFlow']);
Route::get('/edit-shift/{shift_id}/driver-credits', [EditCloseShiftController::class, 'getDriverCredits']);
Route::get('/edit-shift/{shift_id}/tank-dips', [EditCloseShiftController::class, 'getTankDips']);
Route::get('/edit-shift/{shift_id}/nozzle-readings', [EditCloseShiftController::class, 'getNozzleReadings']);
Route::put('/edit-shift/{shift_id}/update', [EditCloseShiftController::class, 'updateClosedShift']);
// For edit shift page
Route::get('/shift-cash-flow/shift/{shiftId}', [ShiftController::class, 'getByShiftId']);
Route::get('/tanks-dip/shift/{shiftId}/tank/{tankId}', [TanksDipController::class, 'getByShiftAndTank']);
Route::get('/shift-nozzle-readings/shift/{shiftId}/nozzle/{nozzleId}', [ShiftNozzleReadingsController::class, 'getByShiftAndNozzle']);
Route::put('/edit-shift/{shiftId}/update', [EditCloseShiftController::class, 'updateClosedShift']);
// Add these routes
// Add/Update these routes
Route::get('/edit-shift/{shift_id}/bank-transfer', [EditCloseShiftController::class, 'getBankTransferDetails']);
Route::get('/edit-shift/{shift_id}/fuel-card', [EditCloseShiftController::class, 'getFuelCardDetails']);
    Route::get('/edit-shift/{shift_id}/credit-card', [EditCloseShiftController::class, 'getCreditCardDetails']);
Route::get('/credit-driver/shift/{shiftId}', [DriverCreditController::class, 'getByShiftId']);
Route::get('/currentstatus/{id}', [StationController::class, 'currentstatus'])->name('currentstatus');
Route::get('/station/{stationId}/audit-pdf', [StationController::class, 'downloadAuditReport']);
Route::get('/audit-pdf/{stationId}', [StationController::class, 'generateAuditPdf']);
 Route::get('/driver-credit/admin', [DriverCreditController::class, 'getAdminData']);
Route::get('/driver-credit/owner/{userId}', [DriverCreditController::class, 'getOwnerData']);
Route::get('/driver-credit/employee/{userId}', [DriverCreditController::class, 'getEmployeeData']);
Route::post('/driver-credit/receive', [DriverCreditController::class, 'receivePayment']);
Route::get('/station/{stationId}/bank-accounts', [DriverCreditController::class, 'getBankAccounts']);
Route::put('/test', function () {
    dd('PUT working');
});