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
 * File containing the definition of AuthorizationRequestDecisionHandlerSpi interface.
 */


namespace Authlete\Laravel\Handler\Spi;



/**
 * Service Provider Interface for AuthorizationRequestDecisionHandler.
 *
 * The constructor of `AuthorizationRequestDecisionHandler` requires an
 * implementation of this interface.
 *
 * `AuthorizationRequestDecisionHandlerSpiAdapter` is an empty implementation
 * of this interface.
 *
 * @link \Authlete\Laravel\Handler\AuthorizationRequestDecisionHandler
 */
interface AuthorizationRequestDecisionHandlerSpi extends AuthorizationRequestHandlerSpi
{
    /**
     * Get the end-user's decision on the authorization request.
     *
     * @return boolean
     *     `true` if the end-user has decided to grant authorization to the
     *     client application. Otherwise, if the end-user has denied the
     *     authorization request, `false` should be returned.
     */
    public function isClientAuthorized();
}
?>
