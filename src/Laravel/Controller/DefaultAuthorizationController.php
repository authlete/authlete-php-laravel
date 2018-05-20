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
 * File containing the definition of DefaultAuthorizationController class.
 */


namespace Authlete\Laravel\Controller;


use App\User;
use App\Http\Controllers\Controller;
use Authlete\Api\AuthleteApi;
use Authlete\Dto\AuthorizationAction;
use Authlete\Dto\AuthorizationRequest;
use Authlete\Dto\AuthorizationResponse;
use Authlete\Laravel\Handler\AuthorizationRequestErrorHandler;
use Authlete\Laravel\Handler\NoInteractionHandler;
use Authlete\Laravel\Handler\Spi\DefaultNoInteractionHandlerSpi;
use Authlete\Laravel\Handler\Spi\NoInteractionHandlerSpi;
use Authlete\Types\Prompt;
use Authlete\Util\MaxAgeValidator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;


/**
 * An implementation of authorization endpoint.
 */
class DefaultAuthorizationController extends Controller
{
    /**
     * The entry point of this controller.
     *
     * @param AuthleteApi $api
     *     An implementation of the `AuthleteApi` interface.
     *
     * @param Request $request
     *     An authorization request.
     *
     * @return Response
     *     A response that should be returned to the client.
     */
    public function __invoke(AuthleteApi $api, Request $request)
    {
        // Call Authlete's /api/auth/authorization API.
        $response = $this->callAuthorizationApi($api, $request);

        // 'action' in the response denotes the next action which this
        // authorization endpoint implementation should take.
        $action = $response->getAction();

        // Dispatch according to the action.
        switch ($action)
        {
            case AuthorizationAction::$INTERACTION:
                // Process the authorization request with user interaction.
                // An authorization page should be returned.
                return $this->handleInteraction($api, $request, $response);

            case AuthorizationAction::$NO_INTERACTION:
                // Process the authorizatino request without user interaction.
                // The flow reaches here only when the authorization request
                // contains 'prompt=none'.
                return $this->handleNoInteraction($api, $request, $response);

            default:
                // Handle error cases here.
                return $this->handleError($api, $request, $response);
        }
    }


    /**
     * Call Authlete's /api/auth/authorization API.
     */
    private function callAuthorizationApi(AuthleteApi $api, Request $request)
    {
        // A request parameter named 'parameters' for /api/auth/authorization API.
        $parameters = http_build_query(
            $request->method() === 'GET' ? $request->query() : $request->input());

        // A request to /api/auth/authorization API.
        $req = (new AuthorizationRequest())->setParameters($parameters);

        // Call Authlete's /api/auth/authorization API.
        return $api->authorization($req);
    }


    /**
     * Handle the authorization request as an error case.
     *
     * @param AuthleteApi $api
     *     An implementation of the `AuthleteApi` interface.
     *
     * @param Request $request
     *     An authorization request from the client.
     *
     * @param AuthorizationResponse $response
     *     A response from Authlete's `/api/auth/authorization` API.
     *
     * @return Response
     *     A response that should be returned to the client.
     */
    protected function handleError(
        AuthleteApi $api, Request $request, AuthorizationResponse $response)
    {
        // Handle the error.
        return (new AuthorizationRequestErrorHandler($api))->handle($response);
    }


    /**
     * Handle the authorization request without user interaction.
     *
     * @param AuthleteApi $api
     *     An implementation of the `AuthleteApi` interface.
     *
     * @param Request $request
     *     An authorization request from the client.
     *
     * @param AuthorizationResponse $response
     *     A response from Authlete's `/api/auth/authorization` API.
     *
     * @return Response
     *     A response that should be returned to the client.
     */
    protected function handleNoInteraction(
        AuthleteApi $api, Request $request, AuthorizationResponse $response)
    {
        // Create a handler for an authorization request which includes 'prompt=none'.
        $spi     = $this->getNoInteractionHandlerSpi($request);
        $handler = new NoInteractionHandler($api, $spi);

        // Handle the authorization request without user interaction.
        return $handler->handle($response);
    }


    /**
     * Get an implementation of the NoInteractionHandlerSpi interface.
     *
     * The default implementation of this method returns an instance of
     * `DefaultNoInteractionHandlerSpi`.
     *
     * The instance returned from this method is used only when the
     * authorization request includes `prompt=none`.
     *
     * @param Request $request
     *     An authorization request from the client.
     *
     * @return NoInteractionHandlerSpi
     *     An implementation of the `NoInteractionHandlerSpi` interface.
     */
    protected function getNoInteractionHandlerSpi(Request $request)
    {
        // The current user.
        $user = Auth::user();

        // The time at which the user was authenticated.
        $authenticatedAt =
            is_null($user) ? 0 : $this->getUserAuthenticatedAt($user, $request);

        return new DefaultNoInteractionHandlerSpi($user, $authenticatedAt);
    }


    /**
     * Get the time at which the user was authenticated.
     *
     * This method is called only when the authorization request is valid and
     * the value of the `prompt` parameter is `none` and a user has already
     * logged in.
     *
     * The default implementation of this method returns 0. However, this
     * method must be implemented properly to support the `max_age` request
     * parameter and the `auth_time` claim. See
     * [OpenID Connect Core 1.0](https://openid.net/specs/openid-connect-core-1_0.html)
     * for details.
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
        return 0;
    }


    /**
     * Handle the authorization request with user interaction.
     *
     * This method is called when the `action` parameter in the response from
     * Authlete's `/api/auth/authorization` API is `INTERACTION`.
     *
     * @param AuthleteApi $api
     *     An implementation of the `AuthleteApi` interface.
     *
     * @param Request $request
     *     An authorization request from the client.
     *
     * @param AuthorizationResponse $response
     *     A response from Authlete's `/api/auth/authorization` API.
     *
     * @return Response
     *     A response that should be returned to the client.
     */
    protected function handleInteraction(
        AuthleteApi $api, Request $request, AuthorizationResponse $response)
    {
        // Remember some variables so that they can be referred to later in
        // order to call either Authlete's /api/auth/authorization/issue API
        // or /api/auth/authorization/fail API.
        $this->rememberVariables($request, $response);

        // Prepare data used in the authorization page.
        $data = $this->prepareData($request, $response);

        // Return the authorization page.
        return $this->getAuthorizationPage($data);
    }


    /**
     * Remember some variables so that they can be referred to later in order
     * to call either Authlete's `/api/auth/authorization/issue` API or
     * `/api/auth/authorization/fail` API.
     */
    private function rememberVariables(Request $request, AuthorizationResponse $response)
    {
        $session = $request->session();

        $session->put('ticket',       $response->getTicket());
        $session->put('claimNames',   $response->getClaims());
        $session->put('claimLocales', $response->getClaimsLocales());
    }


    /**
     * Prepare data used in the authorization page.
     */
    private function prepareData(Request $request, AuthorizationResponse $response)
    {
        // The user who has already logged in. This may be null.
        $user = Auth::user();

        // The client application which has made the authorization request.
        $client = $response->getClient();

        return array(
            'userName'        => is_null($user) ? null : $user->name,
            'authRequired'    => $this->isAuthenticationRequired($request, $response, $user),
            'serviceName'     => $response->getService()->getServiceName(),
            'clientName'      => $client->getClientName(),
            'description'     => $client->getDescription(),
            'logoUri'         => $client->getLogoUri(),
            'clientUri'       => $client->getClientUri(),
            'policyUri'       => $client->getPolicyUri(),
            'tosUri'          => $client->getTosUri(),
            'scopes'          => $this->convertScopesToArray($response->getScopes()),
            'loginId'         => $this->computeLoginId($response),
            'loginIdReadOnly' => $this->computeLoginIdReadOnly($response)
        );
    }


    /**
     * Return true if user authentication is required in the authorization page.
     */
    private function isAuthenticationRequired(
        Request $request, AuthorizationResponse $response, User $user = null)
    {
        // If the user has not logged in yet.
        if (is_null($user))
        {
            // Authentication is required.
            return true;
        }

        // If 'login' is required. In other words, if the 'prompt' parameter
        // of the authorization request contains 'login'.
        if ($this->isLoginRequired($response))
        {
            // Re-authentication is required.
            return true;
        }

        // If the elapsed time since the last login exceeds the maximum
        // authentication age.
        if ($this->isMaxAgeExceeded($request, $response, $user))
        {
            // Re-authentication is required.
            return true;
        }

        // Re-authentication is not required.
        return false;
    }


    /**
     * Return true if the "prompt" parameter of the authorization request
     * contains "login".
     */
    private function isLoginRequired(AuthorizationResponse $response)
    {
        // The value of the 'prompt' parameter of the authorization request.
        $prompts = $response->getPrompts();

        // If the authorization request does not contain the 'prompt' parameter.
        if (is_null($prompts))
        {
            // 'login' is not required.
            return false;
        }

        // For each value in the 'prompt' request parameter.
        foreach ($prompts as $prompt)
        {
            if ($prompt === Prompt::$LOGIN)
            {
                // 'login' is required.
                return true;
            }
        }

        // 'login' is not required.
        return false;
    }


    /**
     * Return true if the maximum authentication age has passed since the
     * last user authentication.
     */
    private function isMaxAgeExceeded(
        Request $request, AuthorizationResponse $response, User $user = null)
    {
        // The required maximum authentication age.
        $maxAge = $response->getMaxAge();

        if (empty($maxAge))
        {
            // Don't have to care about the maximum authentication age.
            return false;
        }

        // The time at which the user was authenticated.
        $authenticatedAt = $this->getUserAuthenticatedAt($user, $request);

        $validator = (new MaxAgeValidator())
            ->setMaxAge($maxAge)
            ->setAuthenticationTime($authenticatedAt)
            ;

        return !$validator->validate();
    }


    /**
     * Convert an array of \Authlete\Dto\Scope to a multidimensional array.
     */
    private function convertScopesToArray(array $scopes = null)
    {
        if (is_null($scopes))
        {
            return null;
        }

        $array = array();

        foreach ($scopes as $scope)
        {
            // Convert \Authlete\Dto\Scope to a normal array.
            $array[] = $scope->toArray();
        }

        return $array;
    }


    /**
     * Compute the initial value for the input field for the login ID.
     */
    private function computeLoginId(AuthorizationResponse $response)
    {
        // The value of the 'sub' claim in the 'claims' request parameter.
        $subject = $response->getSubject();

        if (is_null($subject) === false)
        {
            // Convert the subject to its corresponding login ID.
            return $this->convertSubjectToLoginId($subject);
        }

        // The value of the 'login_hint' request parameter.
        $loginHint = $response->getLoginHint();

        if (is_null($loginHint) === false)
        {
            // Convert the login hint to its corresponding login ID.
            return $this->convertLoginHintToLoginId($loginHint);
        }

        return null;
    }


    /**
     * Convert a subject (= user's unique identifier) to its corresponding
     * login ID.
     *
     * This method is called only when the authorization request has the
     * `claims` parameter and the parameter contains the `sub` claim. See
     * [5.5. Requesting Claims using the claims Request Parameter](https://openid.net/specs/openid-connect-core-1_0.html#ClaimsParameter)
     * of [OpenID Connect Core 1.0](https://openid.net/specs/openid-connect-core-1_0.html#ClaimsParameter).
     *
     * The default implementation of this method returns the given value
     * without any conversion. Note that, however, it is not rare that
     * subjects and login IDs are different.
     *
     * @param string $subject
     *     The required subject (= user's unique identifier).
     *
     * @return string
     *     The login ID.
     */
    protected function convertSubjectToLoginId($subject)
    {
        return $subject;
    }


    /**
     * Convert a login hint to its corresponding login ID.
     *
     * This method may be called when the authorization request contains
     * the `login_hint` parameter.
     *
     * The default implementation of this method returns the given value
     * without any conversion.
     *
     * @param string $loginHint
     *     The value of the `login_hint` request parameter.
     *
     * @return string
     *     The login ID.
     */
    protected function convertLoginHintToLoginId($loginHint)
    {
        return $loginHint;
    }


    /**
     * Return 'readonly' if the input field for the login ID should be
     * marked as readonly.
     */
    private function computeLoginIdReadOnly(AuthorizationResponse $response)
    {
        if (is_null($response->getSubject()) === false)
        {
            return 'readonly';
        }
        else
        {
            return '';
        }
    }


    /**
     * Get the view of the authorization page.
     *
     * @param array $data
     *     The data used in the authorization page.
     *
     * @return Response
     *     The view of the authorization page.
     */
    protected function getAuthorizationPage(array $data)
    {
        // The name of the template for the authorization page.
        $template = $this->getAuthorizationPageTemplateName();

        return view($template, $data);
    }


    /**
     * Get the name of the template for the authorization page.
     *
     * The default implementation of this method returns
     * `authlete.authorization`.
     *
     * @return string
     *     The name of the template for the authorization page.
     */
    protected function getAuthorizationPageTemplateName()
    {
        return 'authlete.authorization';
    }
}
?>
