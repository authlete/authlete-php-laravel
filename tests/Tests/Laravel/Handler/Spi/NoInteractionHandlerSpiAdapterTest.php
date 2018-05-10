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


namespace Authlete\Tests\Laravel\Handler\Spi;


require_once('vendor/autoload.php');


use PHPUnit\Framework\TestCase;
use Authlete\Laravel\Handler\Spi\NoInteractionHandlerSpiAdapter;


class NoInteractionHandlerSpiAdapterTest extends TestCase
{
    public function test()
    {
        $obj = new NoInteractionHandlerSpiAdapter();

        $this->assertInstanceOf('\Authlete\Laravel\Handler\Spi\UserClaimProvider', $obj);
        $this->assertInstanceOf('\Authlete\Laravel\Handler\Spi\AuthorizationRequestHandlerSpi', $obj);
        $this->assertInstanceOf('\Authlete\Laravel\Handler\Spi\NoInteractionHandlerSpi', $obj);

        $this->assertFalse($obj->isUserAuthenticated());
    }
}
?>
