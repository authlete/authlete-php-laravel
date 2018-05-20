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
 * File containing the definition of DefaultTokenController class.
 */


namespace Authlete\Laravel\Controller;


use Authlete\Api\AuthleteApi;
use Authlete\Laravel\Handler\TokenRequestHandler;
use Authlete\Laravel\Handler\Spi\TokenRequestHandlerSpi;
use Authlete\Laravel\Handler\Spi\DefaultTokenRequestHandlerSpi;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;


/**
 * An implementation of token endpoint.
 */
class DefaultTokenController extends Controller
{
    /**
     * The entry point of this controller.
     *
     * @param AuthleteApi $api
     *     An implementation of the `AuthleteApi` interface.
     *
     * @param Request $request
     *     A token request.
     *
     * @return Response
     *     A response that should be returned to the client.
     */
    public function __invoke(AuthleteApi $api, Request $request)
    {
        // Create a handler for the token request.
        $spi     = $this->getTokenRequestHandlerSpi($request);
        $handler = new TokenRequestHandler($api, $spi);

        // Handle the token request.
        return $handler->handle($request);
    }


    /**
     * Get an implementation of the TokenRequestHandlerSpi interface.
     *
     * The default implementation of this method returns an instance of
     * `DefaultTokenRequestHandlerSpi`.
     *
     * @param Request $request
     *     A token request.
     *
     * @return TokenRequestHandlerSpi
     *     An implementation of the `TokenRequestHandlerSpi` interface.
     */
    protected function getTokenRequestHandlerSpi(Request $request)
    {
        return new DefaultTokenRequestHandlerSpi();
    }
}
?>
