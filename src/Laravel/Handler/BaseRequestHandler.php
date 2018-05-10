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
 * File containing the definition of BaseRequestHandler class.
 */


namespace Authlete\Laravel\Handler;


use Authlete\Api\AuthleteApi;
use Authlete\Web\ResponseUtility;
use Illuminate\Http\Response;


/**
 * The base class for request handlers.
 */
class BaseRequestHandler
{
    private $api = null;  // Authlete\Api\AuthleteApi


    /**
     * Constructor with an implementation of the AuthleteApi interface.
     *
     * The given value can be obtained later by calling `getApi()` method.
     *
     * @param AuthleteApi $api
     *     An implementation of the `AuthleteApi` interface.
     */
    public function __construct(AuthleteApi $api)
    {
        $this->api = $api;
    }


    /**
     * Get the implementation of the AuthleteApi interface.
     *
     * The value returned from this method is the instance that was given to
     * the constructor.
     *
     * @return AuthleteApi
     *     An implementation of the `AuthleteApi` interface.
     */
    public function getApi()
    {
        return $this->api;
    }


    /**
     * A utility method to generate a Response instance with
     * "500 Internal Server Error" and an error message in JSON.
     *
     * This method is expected to be used when the value of the `action`
     * parameter in a response from an Authlete API holds an unexpected
     * value.
     *
     * @param string $apiPath
     *     The path of an Authlete API.
     *
     * @return Response
     *     A Response instahce which represents a server error.
     */
    protected function unknownAction($apiPath)
    {
        $content = "{{\"error\":\"Authlete's '" . $apiPath . "' API returned an unknown action.\"}}";

        return ResponseUtility::internalServerError($content);
    }
}
?>
