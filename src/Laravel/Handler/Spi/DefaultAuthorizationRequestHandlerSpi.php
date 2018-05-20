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
 * File containing the definition of DefaultAuthorizationRequestHandlerSpi class.
 */


namespace Authlete\Laravel\Handler\Spi;


use App\User;
use Authlete\Util\LanguageUtility;
use Authlete\Util\ValidationUtility;


/**
 * An implementation of the AuthorizationRequestHandlerSpi interface
 * that uses Laravel's standard authentication mechanism.
 */
class DefaultAuthorizationRequestHandlerSpi extends DefaultUserClaimProvider
implements AuthorizationRequestHandlerSpi
{
    private $authenticatedAt = 0;     // integer


    /**
     * Constructor.
     *
     * @param User $user
     *     The user. May be null.
     *
     * @param integer $authenticatedAt
     *     The time which the user was authenticated at. The number of seconds
     *     since the Unix epoch (1970-Jan-1).
     */
    public function __construct(User $user = null, $authenticatedAt = 0)
    {
        parent::__construct($user);

        ValidationUtility::ensureInteger('$authenticatedAt', $authenticatedAt);

        $this->authenticatedAt = $authenticatedAt;
    }


    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function getUserAuthenticatedAt()
    {
        return $this->authenticatedAt;
    }


    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function getUserSubject()
    {
        if (is_null($this->user))
        {
            return null;
        }

        // Get the unique identifier of the user.
        $subject = $this->user->getAuthIdentifier();

        // Convert the identifier to a string.
        return LanguageUtility::toString($subject);
    }


    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function getSub()
    {
        return null;
    }


    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function getAcr()
    {
        return null;
    }


    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function getProperties()
    {
        return null;
    }


    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function getScopes()
    {
        return null;
    }
}
?>
