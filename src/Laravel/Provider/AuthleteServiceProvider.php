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
 * File containing the definition of AuthleteServiceProvider class.
 */


namespace Authlete\Laravel\Provider;


use Authlete\Api\AuthleteApi;
use Authlete\Api\AuthleteApiImpl;
use Authlete\Laravel\Conf\AuthleteLaravelConfiguration;
use Authlete\Laravel\Console\AuthleteCommand;
use Illuminate\Support\ServiceProvider;


/**
 * Service provider for Authlete.
 * 
 * The `register()` method of this service provider registers a singleton
 * instance which implements the AuthleteApi interface.
 *
 * It is expected that this service provider is listed in the `providers`
 * array in `config/app.php` like the following.
 *
 * ```
 * 'providers' => [
 *     // Other Service Providers
 * 
 *     Authlete\Laravel\Provider\AuthleteServiceProvider::class,
 * ],
 * ```
 *
 * If the version of Laravel is 5.5 or higher, this service provider will
 * be automatically detected thanks to the feature of 'auto-discovery'.
 * Therefore, you don't have to modify `config/app.php` to add this service
 * provider manually.
 *
 * The `boot()` method of this service provider registers some commands for
 * "php artisan authlete:*".
 */
class AuthleteServiceProvider extends ServiceProvider
{
    /**
     * Register a singleton instance for the AuthleteApi interface.
     */
    public function register()
    {
        // Register an instance that implements the AuthleteApi interface.
        $this->app->singleton(
            \Authlete\Api\AuthleteApi::class,
            function () { return self::instantiateAuthleteApi(); }
        );
    }


    /**
     * Create an instance which implements the AuthleteApi interface.
     */
    private static function instantiateAuthleteApi()
    {
        // Create an instance of AuthleteApiImp which implements AuthleteApi.
        // AuthleteLaravelConfiguration refers to 'config/authlete.php' to
        // find parameters which are necessary to access Authlete APIs.
        return new AuthleteApiImpl(
            new AuthleteLaravelConfiguration()
        );
    }


    /**
     * Register some "php artisan authlete:*" commands.
     */
    public function boot()
    {
        if ($this->app->runningInConsole())
        {
            $this->commands([
                AuthleteAuthorizationServerCommand::class
            ]);
        }
    }
}
?>
