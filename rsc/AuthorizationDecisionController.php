<?php
namespace _NAMESPACE_;


use App\User;
use Authlete\Laravel\Controller\DefaultAuthorizationDecisionController;
use Illuminate\Http\Request;


class AuthorizationDecisionController extends DefaultAuthorizationDecisionController
{
    /**
     * Get the time at which the user was authenticated.
     *
     * @param User $user
     *     The user.
     *
     * @param Request
     *     The request from the authorization page.
     *
     * @return integer
     *     The time at which the user was authenticated.
     *     The number of seconds since the Unix epoch (1970-Jan-1).
     */
    protected function getUserAuthenticatedAt(User $user, Request $request)
    {
        // TODO
        // The default implementation of this method in the parent class
        // returns 0. However, this method must be implemented properly
        // to support the `auth_time` claim. See OpenID Connect Core 1.0
        // for details.
        return parent::getUserAuthenticatedAt($user, $request);
    }
}
?>
