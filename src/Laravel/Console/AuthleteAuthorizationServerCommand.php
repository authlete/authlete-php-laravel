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
 * File containing the definition of AuthleteAuthorizationServerCommand class.
 */


namespace Authlete\Laravel\Console;


/**
 * The implementation of the command for
 * "php artisan authlete:authorization-server".
 */
class AuthleteAuthorizationServerCommand extends AuthleteCommand
{
    /**
     * The name and signature of the console comand.
     *
     * @var string
     */
    protected $signature = 'authlete:authorization-server';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set up for an authorization server and an OpenID provider.';


    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Create directories as necessary.
        $this->createDirectory($this->getControllerDirectory());
        $this->createDirectory($this->getViewDirectory());
        $this->createDirectory($this->getCssDirectory());

        // Copy files.
        $this->copyResourceFile('authlete.php', config_path());
        $this->copyResourceFile('authorization.blade.php', $this->getViewDirectory());
        $this->copyResourceFile('authorization.css', $this->getCssDirectory());

        // Copy controllers with the namespace replaced.
        $this->relocateController('AuthorizationController.php');
        $this->relocateController('AuthorizationDecisionController.php');
        $this->relocateController('ConfigurationController.php');
        $this->relocateController('JwksController.php');
        $this->relocateController('RevocationController.php');
        $this->relocateController('TokenController.php');

        // Add routes to 'routes/web.php'.
        $this->appendContent("\n// Routes added by AuthleteAuthorizationServerCommand.\n", base_path('routes/web.php'));
        $this->addWebRoute('get',  '/.well-known/openid-configuration', 'ConfigurationController');
        $this->addWebRoute('get',  '/authorization', 'AuthorizationController');
        $this->addWebRoute('post', '/authorization', 'AuthorizationController');
        $this->addWebRoute('post', '/authorization/decision', 'AuthorizationDecisionController');

        // Add routes to 'routes/api.php'.
        $this->appendContent("\n// Routes added by AuthleteAuthorizationServerCommand.\n", base_path('routes/api.php'));
        $this->addApiRoute('get',  '/jwks', 'JwksController');
        $this->addApiRoute('post', '/revocation', 'RevocationController');
        $this->addApiRoute('post', '/token', 'TokenController');
    }
}
?>
