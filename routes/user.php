<?php

use App\Http\Controllers\User\AuthController;
use App\Http\Controllers\User\OrderController;
use App\Http\Controllers\User\PasswordResetController;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('user.splash', ['next' => url('/login')]);
});

Route::get('/go/{page}', function ($page) {
    abort_unless(in_array($page, ['login', 'register']), 404);

    return view('user.splash', ['next' => url('/' . $page), 'quick' => true]);
})->middleware('guest');

Route::middleware('guest')->group(function () {
    Route::get('/login', fn () => view('user.login'))->name('login');
    Route::get('/register', fn () => view('user.register'))->name('register');

    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/forgot-password', [PasswordResetController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/verify-reset-code', [PasswordResetController::class, 'showCodeForm'])->name('password.code');
    Route::post('/verify-reset-code', [PasswordResetController::class, 'verifyCode'])->middleware('throttle:10,1')->name('password.code.verify');
    Route::get('/reset-password', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.store');
});

Route::get('/welcome', function () {
    if (! session()->pull('show_splash')) {
        return redirect('/home');
    }

    return view('user.splash', ['next' => url('/home')]);
})->middleware(['auth:web', 'customer']);

Route::get('/home', function () {
    return view('user.home', [
        'products' => Product::query()->where('is_active', true)->latest()->get(),
        'categories' => Product::query()->where('is_active', true)->whereNotNull('category')->where('category', '!=', '')->distinct()->orderBy('category')->pluck('category'),
        'brands' => Product::query()->where('is_active', true)->whereNotNull('brand')->where('brand', '!=', '')->distinct()->orderBy('brand')->pluck('brand'),
        'maximumPrice' => max(5000, (float) Product::query()->where('is_active', true)->max('price')),
    ]);
})->middleware(['auth:web', 'customer']);

Route::get('/products/{id}', function (int $id) {
    $product = Product::query()->where('is_active', true)->findOrFail($id);
    return view('user.product-details', compact('product'));
})->whereNumber('id')->middleware(['auth:web', 'customer'])->name('products.show');

Route::view('/cart', 'user.cart')->middleware(['auth:web', 'customer'])->name('cart');
Route::view('/checkout', 'user.checkout')->middleware(['auth:web', 'customer'])->name('checkout');
Route::middleware(['auth:web', 'customer'])->group(function () {
    Route::get('/orders', [OrderController::class, 'index'])->name('orders');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
});

Route::middleware(['auth:web', 'customer'])->group(function () {
    Route::get('/profile/edit', fn () => view('user.profile-edit'))->name('profile.edit');
    Route::put('/profile', function (Request $request) {
        $user = $request->user('web');
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
            'current_password' => ['required_with:password', 'current_password'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->phone = $data['phone'] ?? null;
        $user->address = $data['address'] ?? null;
        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        return redirect()->route('profile.edit')->with('status', 'Profile updated successfully.');
    })->name('profile.update');

    Route::put('/profile/delivery-details', function (Request $request) {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:500'],
        ]);

        $request->user('web')->update($data);

        return response()->json(['message' => 'Delivery details saved to your profile.']);
    })->name('profile.delivery.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware(['auth:web', 'customer'])->name('logout');
