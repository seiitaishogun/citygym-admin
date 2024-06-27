<?php
/**
 * @author tmtuan
 * created Date: 08-Dec-20
 */
namespace App\Http\Middleware;

use Closure;

class CheckForSalesforceRole {
    public function handle($request, Closure $next)
    {
        $user = auth()->user();
        $roles = $user->getRoleNames();
//        dd($roles);
        if ( $user->type == 'admin' && in_array('Salesforce', $roles->toArray()) ) {
            return $next($request);
        } else {
            return response()->json('You don\'t have permission to access this action!', 401);
        }
    }
}
