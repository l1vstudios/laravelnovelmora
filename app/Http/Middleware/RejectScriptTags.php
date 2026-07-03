<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RejectScriptTags
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->isMethodSafe() && $this->containsScriptTag($request->except(['_token', '_method']))) {
            $message = 'Input tidak boleh berisi tag script.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return back()
                ->withInput()
                ->withErrors(['input' => $message])
                ->with('error', $message);
        }

        return $next($request);
    }

    private function containsScriptTag(mixed $value): bool
    {
        if (is_array($value)) {
            foreach ($value as $item) {
                if ($this->containsScriptTag($item)) {
                    return true;
                }
            }

            return false;
        }

        return is_string($value) && preg_match('/<\s*\/?\s*script\b/i', $value) === 1;
    }
}
