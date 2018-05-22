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
 * File containing the definition of DefaultUserInfoRequestHandlerSpi class.
 */


namespace Authlete\Laravel\Handler\Spi;


use App\User;


/**
 * An implementation of the UserInfoRequestHandlerSpi interface.
 */
class DefaultUserInfoRequestHandlerSpi extends DefaultUserClaimProvider
implements UserInfoRequestHandlerSpi
{
    private $tried = false; // boolean


    /**
     * Constructor.
     */
    public function __construct()
    {
        // $user is null. The user will be looked up later
        // in the first call of getUserClaimValue().
        parent::__construct(null);
    }


    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     *
     * @param string $subject
     *     {@inheritdoc}
     *
     * @param string $claimName
     *     {@inheritdoc}
     *
     * @param string $languageTag
     *     {@inheritdoc}
     */
    public function getUserClaimValue($subject, $claimName, $languageTag)
    {
        if ($this->tried === false)
        {
            $this->setUser(User::find($subject));
            $this->tried = true;
        }

        return parent::getUserClaimValue($subject, $claimName, $languageTag);
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
}
?>
