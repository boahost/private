<?php

namespace App\Http\Middleware;

use Closure;
use App\PlanTenant;
use App\UserAccess;
use App\Utils\ModuleUtil;

class CheckPayment
{
    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $administrator_list = config('constants.administrator_usernames');
        $business_id = request()->session()->get('user.business_id');

        if (!empty($request->user()) && in_array($request->user()->username, explode(',', $administrator_list))) {
            return $next($request);
        } else {
            if (!$this->moduleUtil->isSubscribed($business_id)) {
                $output = [
                    'success' => 0,
                    'msg' => "Realize o pagamento do plano para continuar."
                ];
                return redirect('/payment')->with('status', $output);

            }
            //verifica pagamento
            return $next($request);

        }

    }
}
