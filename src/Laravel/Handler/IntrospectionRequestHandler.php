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
 * File containing the definition of IntrospectionRequestHandler class.
 */


namespace Authlete\Laravel\Handler;


use Authlete\Api\AuthleteApiException;
use Authlete\Dto\StandardIntrospectionAction;
use Authlete\Dto\StandardIntrospectionRequest;
use Authlete\Laravel\Web\ResponseUtility;
use Authlete\Util\ValidationUtility;
use Illuminate\Http\Response;


/**
 * Handler for requests to an introspection endpoint.
 */
class IntrospectionRequestHandler extends BaseRequestHandler
{
    /**
     * Handle an introspection request.
     *
     * This method calls Authlete's `/api/auth/introspection/standard` API.
     *
     * @param string $parameters
     *     Request parameters of an introspection request which complies
     *     with [RFC 7662](https://tools.ietf.org/html/rfc7662).
     *
     * @return Response
     *     An HTTP response that should be returned from the introspection
     *     endpoint implementation to the client application.
     *
     * @throws AuthleteApiException
     */
    public function handle($parameters)
    {
        ValidationUtility::ensureNullOrString('$parameters', $parameters);

        // Call Authlete's /api/auth/introspection/standard API.
        $response = $this->callStandardIntrospectionApi($parameters);

        // 'action' in the response denotes the next action which the
        // implementation of introspection endpoint should take.
        $action = $response->getAction();

        // The content of the response to the client application.
        $content = $response->getResponseContent();

        // Dispatch according to the action.
        switch ($action)
        {
            case StandardIntrospectionAction::$INTERNAL_SERVER_ERROR:
                // 500 Internal Server Error
                return ResponseUtility::internalServerError($content);

            case StandardIntrospectionAction::$BAD_REQUEST:
                // 400 Bad Request
                return ResponseUtility::badRequest($content);

            case StandardIntrospectionAction::$OK:
                // 200 OK
                return ResponseUtility::okJson($content);

            default:
                // 500 Internal Server Error.
                // This should never happen.
                return $this->unknownAction('/api/auth/introspection/standard');
        }
    }


    private function callStandardIntrospectionApi($parameters)
    {
        if (is_null($parameters))
        {
            // Authlete returns different error codes for null and an empty
            // string. 'null' is regarded as a caller's error. An empty
            // string is regarded as a client application's error.
            $parameters = "";
        }

        // Create a request for Authlete's /api/auth/introspection/standard API.
        $request = (new StandardIntrospectionRequest())->setParameters($parameters);

        // Call Authlete's /api/auth/introspection/standard API.
        return $this->getApi()->standardIntrospection($request);
    }
}
?>
