<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MessengerController;
use App\Http\Controllers\UserProfileController;

use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    //Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
   //Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


require __DIR__.'/auth.php';

Route::middleware(['auth','verified'])->group(function () {
    Route::get('/Messenger', [MessengerController::class, 'index'])->name('home');
});




Route::middleware(['auth'])->group(function () {
    //profile Route
    Route::post('/profile', [UserProfileController::class, 'update'])->name('profile.update');

    //Search Users Route
    Route::get('/messenger/search', [MessengerController::class, 'search'])->name('search');

    //Fetch data of user iD
    Route::get('/messenger/id-info', [MessengerController::class, 'fetchData'])->name('fetchData');

    //Send Message
    Route::post('/messenger/send-message', [MessengerController::class, 'sendMessage'])->name('send.message');

    //Fetch Messages Between Users
    Route::get('/messenger/fetch-messages', [MessengerController::class, 'FetchMessages'])->name('fetch.message');

    //Fetch Contacts

    Route::get('/messenger/fetch-contacts', [MessengerController::class, 'FetchContacts'])->name('fetch.contacts');

    //Update Contact Item
    Route::get('/messenger/update-contacts/item', [MessengerController::class, 'UpdateContacts'])->name('update.contacts');


    // make-message-seen
    Route::post('/messenger/make-message-seen', [MessengerController::class, 'MakeMsgSeen'])->name('seen.message');

    /// make-favorite-User
    Route::post('/messenger/make-favorite-user', [MessengerController::class, 'MakeFavoriteUser'])->name('make.favorite.user');

    //Fetch Favorite Contacts
    Route::get('/messenger/fetch-favorite-list', [MessengerController::class, 'FetchFavoriteList'])->name('Favorite.contacts');

    //Delete Messages
    Route::delete('/messenger/delete-messages', [MessengerController::class, 'DestroyMessage'])->name('destroy.message');

    //Fetch Shared Gallery
    Route::get('/messenger/fetch-shared-gallery', [MessengerController::class, 'FetchGallery'])->name('fetch.gallery');




});

