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
 * File containing the definition of AuthleteResourceServerCommand class.
 */


namespace Authlete\Laravel\Console;


/**
 * The implementation of the command for
 * "php artisan authlete:resource-server".
 */
class AuthleteResourceServerCommand extends AuthleteCommand
{
    /**
     * The name and signature of the console comand.
     *
     * @var string
     */
    protected $signature = 'authlete:resource-server';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set up for a resource server.';


    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Create directories as necessary.
        $this->createDirectory($this->getControllerDirectory());

        // Copy files.
        $this->copyResourceFile('authlete.php', config_path(), false);

        // Copy controllers with the namespace replaced.
        $this->relocateController('UserInfoController.php');

        // Add routes to 'routes/api.php'.
        $this->appendContent("\n// Routes added by AuthleteResourceServerCommand.\n", base_path('routes/api.php'));
        $this->addApiRoute('get',  '/userinfo', 'UserInfoController');
        $this->addApiRoute('post', '/userinfo', 'UserInfoController');
    }
}
?>
