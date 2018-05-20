<?php
namespace _NAMESPACE_;


use App\User;
use Authlete\Laravel\Controller\DefaultAuthorizationController;
use Illuminate\Http\Request;


class AuthorizationController extends DefaultAuthorizationController
{
    /**
     * Get the time at which the user was authenticated.
     *
     * This method is called only when the authorization request is valid and
     * the value of the `prompt` parameter is `none` and a user has already
     * logged in.
     *
     * @param User $user
     *     The user.
     *
     * @param Request
     *     The authorization request from the client.
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
        // to support the `max_age` request parameter and the `auth_time`
        // claim. See OpenID Connect Core 1.0 for details.
        return parent::getUserAuthenticatedAt($user, $request);
    }


    /**
     * Convert a subject (= user's unique identifier) to its corresponding
     * login ID.
     *
     * This method is called only when the authorization request has the
     * `claims` parameter and the parameter contains the `sub` claim. See
     * [5.5. Requesting Claims using the claims Request Parameter](http://openid.net/specs/openid-connect-core-1_0.html#ClaimsParameter)
     * of [OpenID Connect Core 1.0](http://openid.net/specs/openid-connect-core-1_0.html#ClaimsParameter).
     *
     * @param string $subject
     *     The required subject (= user's unique identifier).
     *
     * @return string
     *     The login ID.
     */
    protected function convertSubjectToLoginId($subject)
    {
        // TODO
        // The default implementation of this method in the parent class
        // returns the given value without any conversion. However, it is
        // not rare that subjects and login IDs are different.
        return $subject;
    }
}
?>
