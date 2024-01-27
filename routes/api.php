<?php

use App\Models\Operation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::middleware(['cors'])->group(function () {

    /**
     * Route pour se connecter à un evenement
    */
    Route::post('/event.login', [\App\Http\Controllers\AppController::class, 'loggedToEvent']);

    /**
     * Route pour créer un evenement dans le système
    */
    Route::post("/event.create", [\App\Http\Controllers\AppController::class, 'createEvent']);

    /**
     * Route pour créer des tables pour un evenement existant
    */
    Route::post('/table.create',[\App\Http\Controllers\AppController::class, 'createTable']);

    /**
     * Route pour créer des invites pour une table de l'evenement
    */
    Route::post('/invite.create', [\App\Http\Controllers\AppController::class, 'createInvite']);

    /**
     * Route pour deplacer un invite d'une table à une autre Table
    */
    Route::post('invite.transfert', [\App\Http\Controllers\AppController::class, 'transfertInvite']);

    /**
     * Route pour afficher tous les evenements ou (un evenement où sa clé d'identification est spécifié)
    */
    Route::get('/events.all/{key?}', [\App\Http\Controllers\AppController::class, 'viewAllEvents']);

    /**
     * Route pour telecharger un PDF des qrcodes
    */
    Route::get("/download.pdf/{eventID}", [\App\Http\Controllers\AppController::class, 'generatePdfWithQRCodes']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
