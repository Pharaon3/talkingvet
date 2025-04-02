<?php

namespace App\Policies;

use App\Models\Nvoq\NvoqUser;
use Illuminate\Auth\Access\Response;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/** Help - https://laravel.com/docs/11.x/authorization#creating-policies */
class NarrativeApiPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the given post can be updated by the user.
     */
    public function narrativeLicensed(?NvoqUser $user): Response
    {
        $licensed = false;
        if(session()->has('narrative'))
        {
            try {
                $licensed = session()->get('narrative', false);
            } catch (NotFoundExceptionInterface $e) {
                // Ignored
            } catch (ContainerExceptionInterface $e) {
                // Ignored
            }
        }

        return $licensed
            ? Response::allow()
            : Response::denyWithStatus(
                ResponseAlias::HTTP_UNAUTHORIZED,
                'No narrative license found.',
                ResponseAlias::HTTP_UNAUTHORIZED
            ); // https://laravel.com/docs/11.x/authorization#customising-policy-response-status
//            : Response::deny('No narrative license found.');
    }
}
