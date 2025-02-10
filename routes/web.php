<?php

use App\Models\Product;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BoxController;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\ChangeLanguage;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LogsController;
use App\Http\Controllers\TypeController;
use App\Http\Controllers\CostsController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\CaliberController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\QuantityController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\DataEntryController;
use App\Http\Controllers\InvantoryController;
use App\Http\Controllers\CaliberTransormation;
use App\Http\Controllers\CostsTypesController;
use App\Http\Controllers\UserQuantityController;
use App\Http\Controllers\SavedInvantoryController;
use App\Http\Controllers\EmployerSellingController;
use App\Http\Controllers\UserMaintenenceController;
use App\Http\Controllers\DataEntryQuantityController;
use App\Http\Controllers\SupplierTransactionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
// Route::get('/clear-cache', function() {
//     Artisan::call('cache:clear');
//     return "Cache is cleared";
// });

// User routes
Route::middleware(['auth', 'isUser', 'checkActiveUser'])->group(function () {
    // Route::view('/user_selling_page', 'user_selling_page')->name('user_home');
    Route::get('/sell/search', [EmployerSellingController::class, 'search'])->name('selling_search');

    Route::resource('/sell', EmployerSellingController::class);
    Route::post('/sell/device_mode', [EmployerSellingController::class, 'sellManyProducts'])->name('sell.many.products');

    Route::get('/sell/u/quantity', [UserQuantityController::class, 'quantity'])->name('sell.quantity');
    Route::post('/sell/u/quantity', [UserQuantityController::class, 'sellFromQuantity'])->name('sell.quantity.store');
    Route::get('/sell/u/quantity/info/{product}', [UserQuantityController::class, 'getProductInfo'])->name('sell.quantity.product_info');
    Route::get('/sell/u/quantity/sales', [EmployerSellingController::class, 'sales'])->name('sell.sales');

    Route::get('/sell/u/quantity/device_mode', [UserQuantityController::class, 'getDeviceModePage'])->name('sell.quantity.device_mode');
    Route::post('/sell/u/cancel/{product}', [EmployerSellingController::class, 'cancelSelledProduct'])->name('sell.cancel');


    //Customer
    Route::get('/customer/{customer}', [CustomerController::class, 'getCustomerinfos'])->name('customer_info');

    Route::get('/customer', [CustomerController::class, 'index'])->name('customer.index');
    Route::get('/customer/installments/{id}', [CustomerController::class, 'customerInstallments'])->name('customer.installments');
    Route::post('/customer/add-payment', [CustomerController::class, 'addPayment'])->name('customer.addPayment');

    //Orderes
    Route::get('/orderes', [OrderController::class, 'index'])->name('orderes.index');
    Route::get('/orderes/create', [OrderController::class, 'create'])->name('orderes.create');
    Route::post('/orderes', [OrderController::class, 'store'])->name('orderes.store');
    Route::post('/orderes/cancel', [OrderController::class, 'cancelOrder'])->name('orderes.cancel');
    Route::post('/orderes/deliver', [OrderController::class, 'deliveredOrder'])->name('orderes.deliver');
    // Route::post('products/{product}', [EmployerSellingController::class, 'show'])->name('products.show');
    Route::get('/sell/products/{product}', [EmployerSellingController::class, 'getProduct'])->name('products.getProduct');

    //maintenance

    Route::get('/maintenance', [UserMaintenenceController::class, 'index'])->name('maintenance.index');
    Route::post('/maintenance', [UserMaintenenceController::class, 'store'])->name('maintenance.store');
    Route::post('/maintenance/cancel', [UserMaintenenceController::class, 'cancel'])->name('maintenance.cancel');
    Route::post('/maintenance/recive', [UserMaintenenceController::class, 'recive'])->name('maintenance.recive');
});
Route::view('/', 'welcome');
Route::get('/s', function () {

    // Define the target and link paths
    $target = storage_path('app/public');
    $link = public_path('storage');

    // Check if the target directory exists
    if (!file_exists($target)) {
        die('The target directory does not exist: ' . $target);
    }

    // Check if the public directory exists
    if (!is_dir(public_path('storage'))) {
        die('The public directory does not exist: ./public');
    }

    // Check if the link already exists
    if (file_exists($link)) {
        die('The symbolic link already exists: ' . $link);
    }

    // Create the symbolic link
    if (symlink($target, $link)) {
        echo "Symbolic link created successfully.";
    } else {
        echo "Failed to create the symbolic link.";
    }
});
// Dashboard routes
Route::middleware(['setLang', 'auth', 'isAdmin', 'checkActiveUser'])->group(function () {

    Route::view('/', 'welcome');
    Route::get('/dashboard', [HomeController::class, 'index'])->name('home');
    Route::get('locale/{locale}', ChangeLanguage::class)->name('set-locale');
    Route::resource('calibers', CaliberController::class);
    Route::resource('types', TypeController::class);
    Route::resource('products', ProductController::class);
    //search for products
    Route::get('product_search/{search}', [ProductController::class, 'productSearch'])->name('product_search');
    // //check if id of product exists in DB
    // Route::get('product_exist/{product}', [ProductController::class, 'CheckProductExist'])->name('product_exist');
    //  reset the product status to available
    Route::post('product/reset', [ProductController::class, 'reset'])->name('product_reset');
    //invantory fetch result (جلب البيانات بعد الضغط على زر معالجة)
    Route::resource('invantory', InvantoryController::class);
    Route::post('result', [InvantoryController::class, 'resultCalculator']);
    // save invanotry (ajax request)
    Route::post('saveInvantory', [SavedInvantoryController::class, 'saveInvantory'])->name('save_invantory');
    // display main page for saved invanotories
    Route::get('displaySavedInvantory', [SavedInvantoryController::class, 'index'])->name('display_saved_invantory');
    Route::get('invanotoryDetails/{invantory}', [SavedInvantoryController::class, 'getInvantoryDetails'])->name('invanotory_details');

    //user routed
    Route::post('sell/{product}', [ProductController::class, 'sell'])->name('products.sell'); // product selled 

    Route::get('products/refund/{product}', [ProductController::class, 'refund'])->name('products.refund'); // product refunded 

    Route::post('products/refund/{product}', [ProductController::class, 'refund'])->name('products.refund_update'); // product refunded 

    Route::resource('users', UsersController::class);

    //transformations routes
    Route::get('transformation', [CaliberTransormation::class, 'index'])->name('caliber_trans');
    Route::get('transformation/result', [CaliberTransormation::class, 'transformatinResult'])->name('caliber_trans_result');

    // Quantities Management
    Route::get('quantities', [QuantityController::class, 'index'])->name('quantities.index');
    Route::get('quantities/create', [QuantityController::class, 'create'])->name('quantities.create');
    Route::post('quantities', [QuantityController::class, 'store'])->name('quantities.store');
    Route::post('quantities/add_to_existing_quantity', [QuantityController::class, 'addToExistQuantity'])->name('quantities.add_to_existing_quantity');
    Route::get('quantities/get_product_info/{product}', [QuantityController::class, 'getProductInfo'])->name('quantities.get_product_info');
    Route::post('quantities/sell_quantity', [QuantityController::class, 'sellQuantity'])->name('quantities.sell_quantity');
    Route::get('quantities/quantity_details/{quantity}', [QuantityController::class, 'quantityDetails'])->name('quantities.quantity_details');
    Route::get('quantities/quantity_details/edit/{quantity}', [QuantityController::class, 'editQuantity'])->name('quantities.edit');
    Route::get('quantities/quantity_details/show/{quantity}', [QuantityController::class, 'weightQunatityDetails'])->name('quantities.detail');
    Route::post('quantities/quantity_details/edit/{quantity}', [QuantityController::class, 'storeEditQuantity'])->name('quantities.edit.store');
    Route::post('quantities/transfarequantity', [QuantityController::class, 'transfareQuantity'])->name('quantities.transfareQuantity');
    Route::delete('quantities', [QuantityController::class, 'destroy'])->name('quantities.destroy');

    // reports
    Route::get('reports', [ReportsController::class, 'index'])->name('reports.index');
    Route::post('reports', [ReportsController::class, 'getResult'])->name('reports.result');

    //box
    Route::get('box', [BoxController::class, 'index'])->name('box.index');
    Route::post('box', [BoxController::class, 'store'])->name('box.store');

    //costs_types
    Route::get('cost_types', [CostsTypesController::class, 'index'])->name('costs_types.index');
    Route::get('cost_types/create', [CostsTypesController::class, 'create'])->name('costs_types.create');
    Route::post('cost_types', [CostsTypesController::class, 'store'])->name('costs_types.store');

    //costs
    Route::get('costs', [CostsController::class, 'index'])->name('costs.index');
    Route::post('costs', [CostsController::class, 'store'])->name('costs.store');
    Route::delete('costs', [CostsController::class, 'destroy'])->name('costs.destroy');

    // orders
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');

    Route::get('/logs', [LogsController::class, 'index'])->name('logs.index');

    // عرض الموردين
    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');

    // إضافة مورد جديد
    Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');

    // إضافة معاملة جديدة لمورد
    Route::post('/supplier-transactions/store', [SupplierTransactionController::class, 'storeTransaction'])->name('supplierTransactions.store');
    //عرض عمليات المورد
    Route::get('/suppliers/{supplier}/transactions', [SupplierController::class, 'showTransactions'])->name('suppliers.transactions');
    Route::post('/suppliers//transactions/fixprice', [SupplierTransactionController::class, 'fixPrice'])->name('supplierTransactions.fixPrice');

});


Route::middleware(['dataEntry', 'checkActiveUser'])->group(function () {

    Route::get('dataentry', [DataEntryController::class, 'index'])->name('dataentry.index');
    Route::get('dataentry/products', [DataEntryController::class, 'getProducts'])->name('dataentry.getproducts');
    Route::get('dataentry/products/view/{product}', [DataEntryController::class, 'show'])->name('dataentry.show');
    Route::get('dataentry/products/create', [DataEntryController::class, 'create'])->name('dataentry.create');
    Route::post('dataentry/products', [DataEntryController::class, 'store'])->name('dataentry.store');
    Route::get('dataentry/products/fromquantity/create', [DataEntryController::class, 'createProductFromQuantity'])->name('dataentry.create_from_quantity');

    // Quantities Management
    Route::get('quantities/entry', [DataEntryQuantityController::class, 'index'])->name('quantities.entry.index');
    Route::get('quantities/entry/create', [DataEntryQuantityController::class, 'create'])->name('quantities.entry.create');
    Route::post('quantities/entry', [DataEntryQuantityController::class, 'store'])->name('quantities.entry.store');
    Route::post('quantities/entry/add_to_existing_quantity', [DataEntryQuantityController::class, 'addToExistQuantity'])->name('quantities.entry.add_to_existing_quantity');
    Route::get('quantities/entry/get_product_info/{product}', [DataEntryQuantityController::class, 'getProductInfo'])->name('quantities.entry.get_product_info');
    Route::post('quantities/entry/sell_quantity', [DataEntryQuantityController::class, 'sellQuantity'])->name('quantities.entry.sell_quantity');
    Route::get('quantities/entry/quantity_details/{quantity}', [DataEntryQuantityController::class, 'quantityDetails'])->name('quantities.entry.quantity_details');
    Route::post('quantities/entry/transfarequantity', [QuantityController::class, 'transfareQuantity'])->name('quantities.entry.transfareQuantity');
    Route::get('quantities/entry/quantity_details/show/{quantity}', [QuantityController::class, 'weightQunatityDetails'])->name('quantities.entry.detail');

    //search for products
    Route::get('product_search/entry/{search}', [ProductController::class, 'productSearch'])->name('entry_product_search');
    //box
    Route::get('box/entry', [BoxController::class, 'index'])->name('box.entry.index');
    Route::post('box/entry', [BoxController::class, 'store'])->name('box.entry.store');

    //costs_types
    Route::get('cost_types/create/entry', [CostsTypesController::class, 'create'])->name('costs_types.entry.create');
    Route::get('cost_types/entry', [CostsTypesController::class, 'index'])->name('costs_types.entry.index');
    Route::post('cost_types/entry', [CostsTypesController::class, 'store'])->name('costs_types.entry.store');

    //costs
    Route::get('costs/entry', [CostsController::class, 'index'])->name('costs.entry.index');
    Route::post('costs/entry', [CostsController::class, 'store'])->name('costs.entry.store');
    Route::delete('costs/entry', [CostsController::class, 'destroy'])->name('costs.entry.destroy');
});

require __DIR__ . '/auth.php';
