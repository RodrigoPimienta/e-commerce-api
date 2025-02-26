<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTypeCompany
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, int $companyType = 0): Response
    {

        $userCompany = $request->user()->companies()->first();

        if (! $userCompany) {
            return response()->json(['error' => true, 'message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        // Comparar con el tipo de empresa esperado
        if ($userCompany->company_type != $companyType) {
            return response()->json(['error' => true, 'message' => 'Not allowed'], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }

}
