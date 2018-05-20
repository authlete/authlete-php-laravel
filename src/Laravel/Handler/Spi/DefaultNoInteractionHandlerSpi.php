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
 * File containing the definition of DefaultNoInteractionHandlerSpi class.
 */


namespace Authlete\Laravel\Handler\Spi;


/**
 * An implementation of the NoInteractionHandlerSpi interface
 * that uses Laravel's standard authentication mechanism.
 */
class DefaultNoInteractionHandlerSpi extends DefaultAuthorizationRequestHandlerSpi
implements NoInteractionHandlerSpi
{
    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     *
     * @return boolean
     *     {@inheritdoc}
     */
    public function isUserAuthenticated()
    {
        return !is_null($this->getUserSubject());
    }
}
?>
