<?php

use Illuminate\Foundation\Application;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
  ->withRouting(
    web: __DIR__.'/../routes/web.php',
    commands: __DIR__.'/../routes/console.php',
    health: '/up',
  )
  ->withMiddleware(function (Middleware $middleware) {
    $middleware->web(append: [
      \App\Http\Middleware\RejectScriptTags::class,
    ]);

    $middleware->alias([
      'role.permission' => \App\Http\Middleware\CheckRolePermission::class,
    ]);
  })
  ->withExceptions(function (Exceptions $exceptions) {
    $exceptions->render(function (QueryException $e, $request) {
      $message = strtolower($e->getMessage());
      $isNumberOverflow = $e->getCode() === '22003'
        || str_contains($message, 'numeric value out of range')
        || str_contains($message, 'integer out of range')
        || str_contains($message, 'out of range');

      if (!$isNumberOverflow) {
        return null;
      }

      $friendlyMessage = 'Maaf, angka yang dimasukkan terlalu besar.';

      if ($request->expectsJson()) {
        return response()->json(['message' => $friendlyMessage], 422);
      }

      return back()
        ->withInput()
        ->withErrors(['number' => $friendlyMessage])
        ->with('error', $friendlyMessage);
    });
  })->create();
