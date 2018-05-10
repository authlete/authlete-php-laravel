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
 * File containing the definition of UserInfoRequestHandlerSpi interface.
 */


namespace Authlete\Laravel\Handler\Spi;


use Authlete\Laravel\Handler\Spi\UserClaimProvider;


/**
 * Service Provider Interface for UserInfoRequestHandler.
 *
 * The constructor of `UserInfoRequestHandler` requires an implementation
 * of this interface.
 *
 * `UserInfoRequestHandlerSpiAdapter` is an empty implementation of this
 * interface.
 *
 * @link \Authlete\Laravel\Handler\UserInfoRequestHandler
 */
interface UserInfoRequestHandlerSpi extends UserClaimProvider
{
    /**
     * Get the value of the "sub" claim that will be embedded in the response
     * from the userinfo endpoint.
     *
     * If this method returns `null`, the subject associated with the access
     * token (which was presented by the client application at the userinfo
     * endpoint) will be used as the value of the `"sub"` claim.
     *
     * @return string
     *     The value of the `"sub"` claim.
     */
    public function getSub();
}
?>
