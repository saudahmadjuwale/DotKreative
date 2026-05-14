<?php

use Illuminate\Support\Facades\Route;

use App\Http\Middleware\EnsureOwner;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CollaborationController;
use App\Http\Controllers\Admin\GalleryController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\PublicSite\CollabController as PublicCollab;
use App\Http\Controllers\PublicSite\GalleryPageController;

/*
|--------------------------------------------------------------------------
| PUBLIC WEBSITE ROUTES
|--------------------------------------------------------------------------
*/
Route::get('/', [PublicCollab::class, 'home'])->name('home');
Route::get('/collabs', [PublicCollab::class, 'index'])->name('collabs.index');
Route::get('/collabs/{slug}', [PublicCollab::class, 'show'])->name('collabs.show');
Route::get('/galleries', [GalleryPageController::class, 'index'])->name('galleries');





/*
|--------------------------------------------------------------------------
| ADMIN AUTH (NO MIDDLEWARE)
|--------------------------------------------------------------------------
*/
Route::get('/admin/login',  [AuthController::class, 'showLogin'])->name('admin.login.form');
Route::post('/admin/login', [AuthController::class, 'login'])->name('admin.login');
Route::post('/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');

/*
|--------------------------------------------------------------------------
| ADMIN PANEL (OWNER ONLY)
|--------------------------------------------------------------------------
*/
Route::middleware([EnsureOwner::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */
        Route::get('/dashboard', fn () => view('admin.dashboard'))->name('dashboard');


        /*
        |--------------------------------------------------------------------------
        | COLLABORATIONS CRUD
        |--------------------------------------------------------------------------
        */
        Route::get('/collaborations',                [CollaborationController::class, 'index'])->name('collaborations.index');
        Route::get('/collaborations/create',         [CollaborationController::class, 'create'])->name('collaborations.create');
        Route::post('/collaborations',               [CollaborationController::class, 'store'])->name('collaborations.store');
        Route::get('/collaborations/{collab}/edit',  [CollaborationController::class, 'edit'])->name('collaborations.edit');
        Route::put('/collaborations/{collab}',       [CollaborationController::class, 'update'])->name('collaborations.update');
        Route::delete('/collaborations/{collab}', [CollaborationController::class, 'destroy'])
    ->name('collaborations.destroy');
        Route::patch('/collaborations/{collab}/logo',
    [CollaborationController::class,'updateLogo'])
    ->name('collaborations.updateLogo');
        /*
        |--------------------------------------------------------------------------
        | COLLABORATION MEDIA (single file upload/edit/delete)
        |--------------------------------------------------------------------------
        */
        Route::get('/collaborations/{collab}/media',  [MediaController::class, 'index'])->name('collaborations.media');
        Route::post('/collaborations/{collab}/media', [MediaController::class, 'store'])->name('collaborations.media.store');

        Route::get('/media/{media}/edit', [MediaController::class, 'edit'])->name('media.edit');
        Route::put('/media/{media}',      [MediaController::class, 'update'])->name('media.update');
        Route::delete('/media/{media}',   [MediaController::class, 'destroy'])->name('media.destroy');


        /*
        |--------------------------------------------------------------------------
        | COLLABORATION MEDIA — Batch Edit/Update
        |--------------------------------------------------------------------------
        */
        Route::get('/collaborations/{collab}/batches/{batchId}/edit', [MediaController::class, 'editBatch'])->name('media.batch.edit');
        Route::put('/collaborations/{collab}/batches/{batchId}',      [MediaController::class, 'updateBatch'])->name('media.batch.update');
        // DELETE an entire batch (all media uploaded together)
        // Update ORDER of items inside a batch
        Route::patch('/collaborations/{collab}/batches/{batchId}/order',[MediaController::class, 'updateBatchOrder'])->name('media.batch.updateOrder');

        Route::delete('/collaborations/{collab}/batches/{batchId}', [MediaController::class, 'deleteBatch'])->name('media.batch.delete');



        /*
        |--------------------------------------------------------------------------
        | GALLERIES — List, Create, Store
        |--------------------------------------------------------------------------
        */
        Route::get('/galleries',        [GalleryController::class, 'index'])->name('galleries.index');
        Route::get('/galleries/create', [GalleryController::class, 'create'])->name('galleries.create');
        Route::post('/galleries',       [GalleryController::class, 'store'])->name('galleries.store');


        /*
        |--------------------------------------------------------------------------
        | GALLERY MEDIA MANAGEMENT (upload, reorder, delete)
        |--------------------------------------------------------------------------
        */
        Route::get('/galleries/{gallery}/media',  [GalleryController::class, 'manageMedia'])->name('galleries.media');
        Route::post('/galleries/{gallery}/media', [GalleryController::class, 'uploadMedia'])->name('galleries.media.upload');

        // Drag & drop ordering
        Route::patch('/galleries/{gallery}/order', [GalleryController::class, 'updateOrder'])->name('galleries.media.order');

        // Delete a SINGLE media item inside a gallery
        Route::delete('/galleries/{gallery}/media/{media}', [GalleryController::class, 'destroyMedia'])
            ->name('galleries.media.delete');


        /*
        |--------------------------------------------------------------------------
        | GALLERY SETTINGS (Publish / Delete Entire Gallery)
        |--------------------------------------------------------------------------
        */
        Route::patch('/galleries/{gallery}/publish', [GalleryController::class, 'togglePublish'])->name('galleries.publish');
        Route::patch('/galleries/{gallery}/update-info', [GalleryController::class, 'updateInfo'])->name('galleries.update.info');
        // Delete entire gallery
        Route::delete('/galleries/{gallery}', [GalleryController::class, 'destroy'])->name('galleries.destroy');
    });


/*
|--------------------------------------------------------------------------
| Fallback — 404 Page
|--------------------------------------------------------------------------
*/
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
