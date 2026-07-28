<?php

namespace App\UI\Http\Middleware;

use App\Domain\Shared\UsuarioContexto;
use App\Domain\Shared\Repositories\SucursalRepositoryInterface;
use App\Infrastructure\Auth\Models\AuthenticatedUser;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class EnsureUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response|RedirectResponse)  $next
     * @return Response|RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        /** @var AuthenticatedUser|null $user */
        $user = Auth::user();

        if ($user === null) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        $context = session()->get('usuario_contexto');

        if (!$context instanceof UsuarioContexto) {
            $tipo = in_array((string)$user->rol, ['CO', '2']) ? 'CO' : 'AM';
            $zona = !empty($user->zona) ? $user->zona : ($tipo === 'CO' ? 'FA' : 'TODAS');

            $context = new UsuarioContexto(
                zona: $zona,
                tipo: $tipo,
                puntosDeVenta: [],
                status: 'A'
            );
            session()->put('usuario_contexto', $context);
        }

        if (in_array($context->tipo, ['AM', 'CO']) && $context->status === 'A') {
            return $next($request);
        }

        abort(403, 'No tienes permisos para acceder a esta sección.');
    }
}
