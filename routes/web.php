<?php
// routes/web.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController; // <-- Importamos el controlador

// Cuando alguien visite la página de inicio ('/'), 
// llama a la función 'index' de HomeController
Route::get('/', [HomeController::class, 'index']);