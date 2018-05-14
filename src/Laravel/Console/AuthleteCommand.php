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
 * File containing the definition of AuthleteCommand class.
 */


namespace Authlete\Laravel\Console;


use Illuminate\Console\Command;


/**
 * The implementation of the command for "php artisan authlete".
 */
class AuthleteCommand extends Command
{
    /**
     * The name and signature of the console comand.
     *
     * @var string
     */
    protected $signature = 'authlete';


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
        // The directory that holds resources.
        $rsc = __DIR__ . '/../../../rsc/';

        // Copy 'authlete.php' to 'config/authlete.php'.
        //
        // This is a configuration file which holds parameters to access
        // Authlete APIs. AuthleteLaravelConfiguration class refers to
        // the configuration file.
        copy($rsc . 'authlete.php', config_path('authlete.php'));

        // Append the content of 'routes-web.php' to 'routes/web.php'.
        file_put_contents(
            base_path('routes/web.php'),
            file_get_contents($rsc . 'routes-web.php'),
            FILE_APPEND
        );

        // Append the content of 'routes-api.php' to 'routes/api.php'.
        file_put_contents(
            base_path('routes/api.php'),
            file_get_contents($rsc . 'routes-api.php'),
            FILE_APPEND
        );
    }
}
?>
