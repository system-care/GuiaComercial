<?php

use App\Http\Controllers\AsaasWebhookController;
use Illuminate\Support\Facades\Route;

/*
 * Webhook do ASAAS — sem CSRF (autenticado via header asaas-access-token)
 */
Route::post('/webhooks/asaas', [AsaasWebhookController::class, 'handle'])
    ->name('webhooks.asaas');
