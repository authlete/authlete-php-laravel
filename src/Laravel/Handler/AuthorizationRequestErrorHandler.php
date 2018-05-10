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
 * File containing the definition of AuthorizationRequestErrorHandler class.
 */


namespace Authlete\Laravel\Handler;


use Authlete\Api\AuthleteApiException;
use Authlete\Dto\AuthorizationAction;
use Authlete\Dto\AuthorizationResponse;
use Authlete\Laravel\Web\ResponseUtility;
use Illuminate\Http\Response;


/**
 * Handler for error cases of authorization requests.
 *
 * A response from Authlete's `/api/auth/authorization` API contains an
 * `action` response parameter. When the value of the response parameter is
 * neither `AuthorizationAction::$INTERACTION` nor
 * `AuthorizationAction::$NO_INTERACTION`, the authorization request should
 * be handled as an error case. This class is a handler for such error cases.
 */
class AuthorizationRequestErrorHandler extends AuthorizationRequestBaseHandler
{
    /**
     * Handle an error case of an authorization request.
     *
     * This method returns `null` when `$response->getAction()` returns
     * `AuthorizationAction::$INTERACTION` or `AuthorizationAction::$NO_INTERACTION`.
     * In other cases, an instance of `Response` is returned.
     *
     * @param AuthorizationResponse $response
     *     A response from Authlete's `/api/auth/authorization` API.
     *
     * @return Response
     *     An error response that should be returned to the client application
     *     from the authorization endpoint. `null` is returned when
     *     `$response->getAction()` returns `AuthorizationAction::$INTERACTION`
     *     or `AuthorizationAction::$NO_INTERACTION`.
     *
     * @throws AuthleteApiException
     */
    public function handle(AuthorizationResponse $response)
    {
        // 'action' in the response denotes the next action which the
        // implementation of authorization endpoint should take.
        $action = $response->getAction();

        // The content of the response to the client application. The format
        // of the content varies depending on the action.
        $content = $response->getResponseContent();

        // Dispatch according to the action.
        switch ($action)
        {
            case AuthorizationAction::$INTERNAL_SERVER_ERROR:
                // 500 Internal Server Error
                return ResponseUtility::internalServerError($content);

            case AuthorizationAction::$BAD_REQUEST:
                // 400 Bad Request
                return ResponseUtility::badRequest($content);

            case AuthorizationAction::$LOCATION:
                // 302 Found
                return ResponseUtility::location($content);

            case AuthorizationAction::$FORM:
                // 200 OK
                return ResponseUtility::okHtml($content);

            case AuthorizationAction::$INTERACTION:
                // This is not an error case. The implementation of the
                // authorization endpoint should show an authorization page
                // to the end-user.
                return null;

            case AuthorizationAction::$NO_INTERACTION:
                // This is not an error case. The implementation of the
                // authorization endpoint should handle the authorization
                // request without user interaction.
                return null;

            default:
                // 500 Internal Server Error
                // This should never happen.
                return $this->unknownAction('/api/auth/authorization');
        }
    }
}
?>
