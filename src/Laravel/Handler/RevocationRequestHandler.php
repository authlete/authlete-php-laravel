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
 * File containing the definition of RevocationRequestHandler class.
 */


namespace Authlete\Laravel\Handler;


use Authlete\Api\AuthleteApiException;
use Authlete\Dto\RevocationAction;
use Authlete\Dto\RevocationRequest;
use Authlete\Laravel\Web\ResponseUtility;
use Authlete\Util\ValidationUtility;
use Authlete\Web\BasicCredentials;
use Illuminate\Http\Response;


/**
 * Handler for requests to a revocation endpoint.
 */
class RevocationRequestHandler extends BaseRequestHandler
{
    private static $CHALLENGE = 'Basic realm="revocation"';


    /**
     * Handle a revocation request.
     *
     * This method calls Authlete's `/api/auth/revocation` API.
     *
     * @param string $parameters
     *     Request parameters of a revocation request which complies
     *     with [RFC 7009](https://tools.ietf.org/html/rfc7009).
     *
     * @param string $authorization
     *     The value of the `Authorization` header in the revocation request.
     *     A client application may embed its pair of client ID and client
     *     secret in a revocation request using Basic Authentication.
     *
     * @return Response
     *     An HTTP response that should be returned from the revocation
     *     endpoint implementation to the client application.
     *
     * @throws AuthleteApiException
     */
    public function handle($parameters, $authorization)
    {
        ValidationUtility::ensureNullOrString('$parameters',    $parameters);
        ValidationUtility::ensureNullOrString('$authorization', $authorization);

        // Convert the value of the Authorization header (credentials of the
        // client application), if any, into BasicCredentials.
        $credentials = BasicCredentials::parse($authorization);

        // Call Authlete's /api/auth/revocation API.
        $response = $this->callRevocationApi($parameters, $credentials);

        // 'action' in the response denotes the next action which the
        // implementation of revocation endpoint should take.
        $action = $response->getAction();

        // The content of the response to the client application.
        $content = $response->getResponseContent();

        // Dispatch according to the action.
        switch ($action)
        {
            case RevocationAction::$INVALID_CLIENT:
                // 401 Unauthorized
                return ResponseUtility::unauthorized(self::$CHALLENGE, $content);

            case RevocationAction::$INTERNAL_SERVER_ERROR:
                // 500 Internal Server Error
                return ResponseUtility::internalServerError($content);

            case RevocationAction::$BAD_REQUEST:
                // 400 Bad Request
                return ResponseUtility::badRequest($content);

            case RevocationAction::$OK:
                // 200 OK
                return ResponseUtility::okJavaScript($content);

            default:
                // 500 Internal Server Error.
                // This should never happen.
                return $this->unknownAction('/api/auth/revocation');
        }
    }


    private function callRevocationApi($parameters, $credentials)
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

        // Create a request for Authlete's /api/auth/revocation API.
        $request = (new RevocationRequest())
            ->setParameters($parameters)
            ->setClientId($clientId)
            ->setClientSecret($clientSecret)
            ;

        // Call Authlete's /api/auth/revocation API.
        return $this->getApi()->revocation($request);
    }
}
?>
