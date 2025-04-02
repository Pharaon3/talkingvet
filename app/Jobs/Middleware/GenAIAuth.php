<?php

namespace App\Jobs\Middleware;
use App\Http\Controllers\NvoqNetworkController;
use Closure;
use Illuminate\Http\Request;

class GenAIAuth
{
    /**
     * Process the queued job.
     *
     * @param  \Closure(object): void  $next
     */
    public function handle(object $job, Closure $next): void
    {
        $request = $job->getGenAIRequest();

        if(
            !isset($request->country) ||
            !isset($request->userAuthString) ||
            !$request->valid
        )
        {
            $job->fail("nvoq initial authentication failed | country not set");
        }

        /* try login user */
        $result = NvoqNetworkController::GenAI_LoginWithBasicAuthentication(new Request([
            'country' => $request->country,
            'userAuthString' => $request->userAuthString
        ]));

        if(!$result)
        {
                $job->fail("nvoq initial authentication failed | unauthorized");
        }

        $next($job);
    }
}
