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
 * File containing the definition of TokenRequestHandler class.
 */


namespace Authlete\Laravel\Handler;


use Authlete\Api\AuthleteApiException;
use Authlete\Dto\TokenAction;
use Authlete\Dto\TokenFailAction;
use Authlete\Dto\TokenFailReason;
use Authlete\Dto\TokenFailRequest;
use Authlete\Dto\TokenIssueAction;
use Authlete\Dto\TokenIssueRequest;
use Authlete\Dto\TokenRequest;
use Authlete\Dto\TokenResponse;
use Authlete\Laravel\Handler\Spi\TokenRequestHandlerSpi;
use Authlete\Laravel\Web\ResponseUtility;
use Authlete\Web\BasicCredentials;
use Illuminate\Http\Request;
use Illuminate\Http\Response;


/**
 * Handler for requests to a token endpoint.
 */
class TokenRequestHandler extends BaseRequestHandler
{
    /**
     * The value of the WWW-Authenticate header of the response from
     * the token endpoint when the client is invalid.
     */
    private static $CHALLENGE = 'Basic realm="token"';


    private $spi = null;  // \Authlete\Laravel\Handler\Spi\TokenRequestHandlerSpi


    /**
     * Constructor.
     *
     * @param AuthleteApi $api
     *     An implementation of the `AuthleteApi` interface.
     *
     * @param TokenRequestHandlerSpi $spi
     *     An implementation of the `TokenRequestHandlerSpi` interface.
     */
    public function __construct(AuthleteApi $api, TokenRequestHandlerSpi $spi)
    {
        parent::__construct($api);

        $this->spi = $spi;
    }


    /**
     * Handle a token request.
     *
     * This method calls Authlete's `/api/auth/token` API and optionally
     * `/api/auth/token/issue` API or `/api/auth/token/fail` API.
     *
     * @param Request $request
     *     A token request.
     *
     * @return Response
     *     An HTTP response that should be returned from the token endpoint
     *     implementation to the client application.
     *
     * @throws AuthleteApiException
     */
    public function handle(Request $request)
    {
        // The value of the Authorization header.
        $authorization = WebUtility::extractRequestHeaderValue($request, 'Authorization');

        // The form parameters.
        $parameters = http_build_query($request->input());

        // Convert the value of the Authorization header (credentials of the
        // client application), if any, into BasicCredentials.
        $credentials = BasicCredentials::parse($authorization);

        // Call Authlete's /api/auth/token API.
        $response = $this->callTokenApi($parameters, $credentials);

        // 'action' in the response denotes the next action which the
        // implementation of userinfo endpoint should take.
        $action = $response->getAction();

        // The content of the response to the client application.
        $content = $response->getResponseContent();

        // Dispatch according to the action.
        switch ($action)
        {
            case TokenAction::$INVALID_CLIENT:
                // 401 Unauthorized
                return ResponseUtility::unauthorized(self::$CHALLENGE, $content);

            case TokenAction::$INTERNAL_SERVER_ERROR:
                // 500 Internal Server Error
                return ResponseUtility::internalServerError($content);

            case TokenAction::$BAD_REQUEST:
                // 400 Bad Request
                return ResponseUtility::badRequest($content);

            case TokenAction::$PASSWORD:
                // Process the token request whose flow is
                // "Resource Owner Password Credentials".
                return $this->handlePassword($response);

            case TokenAction::$OK:
                // 200 OK
                return ResponseUtility::okJson($content);

            default:
                // 500 Internal Server Error.
                // This should never happen.
                return $this->unknownAction('/api/auth/token');
        }
    }


    private function callTokenApi($parameters, BasicCredentials $credentials)
    {
        if (is_null($parameters))
        {
            // Authlete returns different error codes for null and an empty
            // string. 'null' is regarded as a caller's error. An empty
            // string is regarded as a client application's error.
            $parameters = "";
        }

        $clientId     = is_null($credentials) ? null : $credentials->getUserId();
        $clientSecret = is_null($credentials) ? null : $credentials->getPassword();

        // Prepare a request for Authlete's /api/auth/token API.
        $request = (new TokenRequest())
            ->setParameters($parameters)
            ->setClientId($clientId)
            ->setClientSecret($clientSecret)
            ->setProperties($this->spi->getProperties())
            ;

        // Call Authlete's /api/auth/token API.
        return $this->getApi()->token($request);
    }


    private function handlePassword(TokenResponse $response)
    {
        // The ticket to call Authlete's /api/auth/token/* API.
        $ticket = $response->getTicket();

        // The credentials of the resource owner.
        $username = $response->getUsername();
        $password = $response->getPassword();

        // Validate the credentials.
        $subject = $this->spi->authenticateUser($username, $password);

        // If the credentials of the resource owner are invalid.
        if (is_null($subject))
        {
            // The credentials are invalid. Nothing is issued.
            return $this->tokenFail(
                $ticket, TokenFailReason::$INVALID_RESOURCE_OWNER_CREDENTIALS);
        }

        // Issue an access token and optionally an ID token.
        return $this->tokenIssue($ticket, $subject);
    }


    private function tokenIssue($ticket, $subject)
    {
        // Call Authlete's /api/auth/token/issue API.
        $response = $this->callTokenIssueApi($ticket, $subject);

        // 'action' in the response denotes the next action which the
        // implementation of token endpoint should take.
        $action = $response->getAction();

        // The content of the response to the client application.
        // The format of the content varies depending on the action.
        $content = $response->getResponseContent();

        // Dispatch according to the action.
        switch ($action)
        {
            case TokenIssueAction::$INTERNAL_SERVER_ERROR:
                // 500 Internal Server Error
                return ResponseUtility::internalServerError($content);

            case TokenIssueAction::$OK:
                // 200 OK
                return ResponseUtility::okJson($content);

            default:
                // 500 Internal Server Error.
                // This should never happen.
                return $this->unknownAction('/api/auth/token/issue');
        }
    }


    private function callTokenIssueApi($ticket, $subject)
    {
        // Prepare a request for Authlete's /api/auth/token/issue API.
        $request = (new TokenIssueRequest())
            ->setTicket($ticket)
            ->setSubject($subject)
            ->setProperties($this->spi->getProperties())
            ;

        // Call Authlete's /api/auth/token/issue API.
        return $this->getApi()->tokenIssue($request);
    }


    private function tokenFail($ticket, TokenFailReason $reason)
    {
        // Call Authlete's /api/auth/token/fail API.
        $response = $this->callTokenFailApi($ticket, $reason);

        // 'action' in the response denotes the next action which the
        // implementation of token endpoint should take.
        $action = $response->getAction();

        // The content of the response to the client application.
        // The format of the content varies depending on the action.
        $content = $response->getResponseContent();

        // Dispatch according to the action.
        switch ($action)
        {
            case TokenFailAction::$INTERNAL_SERVER_ERROR:
                // 500 Internal Server Error
                return ResponseUtility::internalServerError($content);

            case TokenFailAction::$BAD_REQUEST:
                // 400 Bad Request
                return ResponseUtility::badRequest($content);

            default:
                // 500 Internal Server Error.
                // This should never happen.
                return $this->unknownAction('/api/auth/token/fail');
        }
    }


    private function callTokenFailApi($ticket, TokenFailReason $reason)
    {
        // Prepare a request for Authlete's /api/auth/token/fail API.
        $request = (new TokenFailRequest())
            ->setTicket($ticket)
            ->setReason($reason)
            ;

        // Call Authlete's /api/auth/token/fail API.
        return $this->getApi()->tokenFail($request);
    }
}
?>
