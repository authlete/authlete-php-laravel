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
 * File containing the definition of UserClaimProviderAdapter class.
 */


namespace Authlete\Laravel\Handler\Spi;


use Authlete\Laravel\Handler\Spi\UserClaimProvider;


/**
 * An empty implementation of the UserClaimProvider interface.
 *
 * @link \Authlete\Laravel\Handler\Spi\UserClaimProvider
 */
class UserClaimProviderAdapter implements UserClaimProvider
{
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
        return null;
    }
}
?>
