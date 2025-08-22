<?php

use App\Http\Controllers\PostcardTestController;
use Illuminate\Support\Facades\Route;

// Main test interface
Route::get('/', [PostcardTestController::class, 'index'])->name('postcard.test');

// API testing endpoints
Route::prefix('api/test')->group(function () {
    // Debug endpoints
    Route::post('/debug/oauth', [PostcardTestController::class, 'debugOAuth'])
        ->name('api.test.debug.oauth');
    Route::post('/debug/raw-oauth', [PostcardTestController::class, 'testRawOAuth'])
        ->name('api.test.debug.raw-oauth');
    Route::post('/debug/create-postcard', [PostcardTestController::class, 'debugCreatePostcard'])
        ->name('api.test.debug.create-postcard');
    Route::post('/debug/campaign-stats', [PostcardTestController::class, 'debugCampaignStats'])
        ->name('api.test.debug.campaign-stats');
    Route::post('/debug/validate-address', [PostcardTestController::class, 'debugValidateAddress'])
        ->name('api.test.debug.validate-address');

    // Campaign management
    Route::post('/campaign/stats', [PostcardTestController::class, 'testCampaignStats'])
        ->name('api.test.campaign.stats');

    // Validation endpoints
    Route::post('/validate/address', [PostcardTestController::class, 'validateAddress'])
        ->name('api.test.validate.address');
    Route::post('/validate/text', [PostcardTestController::class, 'validateText'])
        ->name('api.test.validate.text');

    // Postcard creation and management
    Route::post('/postcard/create', [PostcardTestController::class, 'createPostcard'])
        ->name('api.test.postcard.create');
    Route::post('/postcard/create-complete', [PostcardTestController::class, 'createCompletePostcard'])
        ->name('api.test.postcard.create-complete');
    Route::post('/postcard/upload-image', [PostcardTestController::class, 'uploadImage'])
        ->name('api.test.postcard.upload-image');
    Route::post('/postcard/add-text', [PostcardTestController::class, 'addSenderText'])
        ->name('api.test.postcard.add-text');
    Route::post('/postcard/add-branding', [PostcardTestController::class, 'addBranding'])
        ->name('api.test.postcard.add-branding');
    Route::post('/postcard/state', [PostcardTestController::class, 'getPostcardState'])
        ->name('api.test.postcard.state');
    Route::post('/postcard/preview', [PostcardTestController::class, 'getPreview'])
        ->name('api.test.postcard.preview');
    Route::post('/postcard/approve', [PostcardTestController::class, 'approvePostcard'])
        ->name('api.test.postcard.approve');
});
