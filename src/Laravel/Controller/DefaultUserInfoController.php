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
 * File containing the definition of DefaultUserInfoController class.
 */


namespace Authlete\Laravel\Controller;


use Authlete\Api\AuthleteApi;
use Authlete\Laravel\Handler\UserInfoRequestHandler;
use Authlete\Laravel\Handler\Spi\UserInfoRequestHandlerSpi;
use Authlete\Laravel\Handler\Spi\DefaultUserInfoRequestHandlerSpi;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;


/**
 * An implementation of userinfo endpoint.
 */
class DefaultUserInfoController extends Controller
{
    /**
     * The entry point of this controller.
     *
     * @param AuthleteApi $api
     *     An implementation of the `AuthleteApi` interface.
     *
     * @param Request $request
     *     A userinfo request.
     *
     * @return Response
     *     A response that should be returned to the client.
     */
    public function __invoke(AuthleteApi $api, Request $request)
    {
        // Create a handler for the userinfo request.
        $spi     = $this->getUserInfoRequestHandlerSpi($request);
        $handler = new UserInfoRequestHandler($api, $spi);

        // Handle the userinfo request.
        return $handler->handle($request);
    }


    /**
     * Get an implementation of the UserInfoRequestHandlerSpi interface.
     *
     * The default implementation of this method returns an instance of
     * `DefaultUserInfoRequestHandlerSpi`.
     *
     * @param Request $request
     *     A userinfo request.
     *
     * @return UserInfoRequestHandlerSpi
     *     An implementation of the `UserInfoRequestHandlerSpi` interface.
     */
    protected function getUserInfoRequestHandlerSpi(Request $request)
    {
        return new DefaultUserInfoRequestHandlerSpi();
    }
}
?>
