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
 * File containing the definition of DefaultAuthorizationRequestDecisionHandlerSpi class.
 */


namespace Authlete\Laravel\Handler\Spi;


use App\User;
use Authlete\Util\ValidationUtility;


/**
 * An implementation of the AuthorizationRequestDecisionHandlerSpi interface
 * that uses Laravel's standard authentication mechanism.
 */
class DefaultAuthorizationRequestDecisionHandlerSpi extends DefaultAuthorizationRequestHandlerSpi
implements AuthorizationRequestDecisionHandlerSpi
{
    private $authorized = false;  // boolean


    /**
     * Constructor.
     *
     * @param User $user
     *     The current user. May be null.
     *
     * @param integer $authenticatedAt
     *     The time which the user was authenticated at. The number of seconds
     *     since the Unix epoch (1970-Jan-1).
     *
     * @param boolean $authorized
     *     `true` if the user has authorized the authorization request from
     *     the client application.
     */
    public function __construct(
        User $user = null, $authenticatedAt = 0, $authorized = false)
    {
        parent::__construct($user, $authenticatedAt);

        ValidationUtility::ensureBoolean('$authorized', $authorized);

        $this->authorized = $authorized;
    }


    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function isClientAuthorized()
    {
        return $this->authorized;
    }
}
?>
