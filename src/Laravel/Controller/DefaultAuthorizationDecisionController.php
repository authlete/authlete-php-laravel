<?php
//
// Copyright (C) 2018 Authlete, Inc.
//
// Licensed under the Apache License, Version 2.0 (the "License");
// you may not use this file except in compliance with the License.
// You may obtain a copy of the License at
//
//     http://www.apache.org/licenses/LICENSE-2.0
//
// Unless required by applicable law or agreed to in writing,
// software distributed under the License is distributed on an
// "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND,
// either express or implied. See the License for the specific
// language governing permissions and limitations under the
// License.
//


/**
 * File containing the definition of DefaultAuthorizationDecisionController class.
 */


namespace Authlete\Laravel\Controller;


use App\User;
use App\Http\Controllers\Controller;
use Authlete\Api\AuthleteApi;
use Authlete\Laravel\Handler\AuthorizationRequestDecisionHandler;
use Authlete\Laravel\Handler\Spi\AuthorizationRequestDecisionHandlerSpi;
use Authlete\Laravel\Handler\Spi\DefaultAuthorizationRequestDecisionHandlerSpi;
use Authlete\Laravel\Util\UserUtility;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;


/**
 * An implementation of authorization decision endpoint.
 */
class DefaultAuthorizationDecisionController extends Controller
{
    /**
     * The entry point of this controller.
     *
     * @param AuthleteApi $api
     *     An implementation of the `AuthleteApi` interface.
     *
     * @param Request $request
     *     A request which has come from the authorization page.
     *
     * @return Response
     *     A response that should be returned to the client.
     */
    public function __invoke(AuthleteApi $api, Request $request)
    {
        // Create a handler for the request.
        $spi     = $this->getAuthorizationRequestDecisionHandlerSpi($request);
        $handler = new AuthorizationRequestDecisionHandler($api, $spi);

        // Prepare arguments for the 'handle()' method of the handler.
        $session      = $request->session();
        $ticket       = $session->pull('ticket');
        $claimNames   = $session->pull('claimNames');
        $claimLocales = $session->pull('claimLocales');

        // Handle the request. This will return a response that conforms to
        // the specifications of OAuth 2.0 and OpenID Connect. For example,
        // a response with '302 Found' and an authorization code is returned.
        return $handler->handle($ticket, $claimNames, $claimLocales);
    }


    /**
     * Get an implementation of the AuthorizationRequestDecisionHandlerSpi interface.
     *
     * The default implementation of this method returns an instance of
     * `DefaultAuthorizationRequestDecisionHandlerSpi`.
     *
     * @param Request $request
     *     A request which has come from the authorization page.
     *
     * @return AuthorizationRequestDecisionHandlerSpi
     *     An implementation of the `AuthorizationRequestDecisionHandlerSpi` interface.
     */
    protected function getAuthorizationRequestDecisionHandlerSpi(Request $request)
    {
        // Get the user. This may be null if the user has rejected the
        // authorization request or if the credentials input by the user
        // to the login form in the authorization page were wrong.
        $user = $this->getUser($request);

        // Get the time at which the user was authenticated. This may be 0
        // if the authentication mechanism does not track timestamps of
        // user authentication.
        $authenticatedAt =
            is_null($user) ? 0 : $this->getUserAuthenticatedAt($user, $request);

        // Get the decision made by the user.
        $authorized = $this->isClientAuthorized($request);

        // Create an implementation of the service provider interface.
        return new DefaultAuthorizationRequestDecisionHandlerSpi(
            $user, $authenticatedAt, $authorized);
    }


    private function getUser(Request $request)
    {
        // If (re-)authentication was not required.
        if (empty($request->input('authRequired')))
        {
            // The current user who has already logged in.
            return Auth::user();
        }

        // The credentials input by the user to the login form.
        $loginId  = $request->input('loginId');
        $password = $request->input('password');

        // Find the user who has the credentials.
        return UserUtility::findUser($loginId, $password);
    }


    /**
     * Get the time at which the user was authenticated.
     *
     * The default implementation of this method returns 0. However, this
     * method must be implemented properly to support the `auth_time` claim.
     * See [OpenID Connect Core 1.0](https://openid.net/specs/openid-connect-core-1_0.html)
     * for details.
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
        return 0;
    }


    private function isClientAuthorized(Request $request)
    {
        return !empty($request->input('authorized'));
    }
}
?>
