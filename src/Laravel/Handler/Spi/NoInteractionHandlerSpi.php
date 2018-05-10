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
 * File containing the definition of NoInteractionHandlerSpi interface.
 */


namespace Authlete\Laravel\Handler\Spi;



/**
 * Service Provider Interface for NoInteractionHandler.
 *
 * The constructor of `NoInteractionHandler` requires an implementation of
 * this interface.
 *
 * `NoInteractionHandlerSpiAdapter` is an empty implementation of this interface.
 *
 * @link \Authlete\Laravel\Handler\NoInteractionHandler
 */
interface NoInteractionHandlerSpi extends AuthorizationRequestHandlerSpi
{
    /**
     * Check whether an end-user has already logged in or not.
     *
     * @return boolean
     *     `true` if an end-user has already logged in.
     */
    public function isUserAuthenticated();
}
?>
