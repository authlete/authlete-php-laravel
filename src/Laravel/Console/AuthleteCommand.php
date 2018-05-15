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
use Illuminate\Console\DetectsApplicationNamespace;


/**
 * The implementation of the command for "php artisan authlete".
 */
class AuthleteCommand extends Command
{
    use DetectsApplicationNamespace;


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
     * The directory that holds resources.
     */
    private $rsc = __DIR__ . '/../../../rsc';


    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Copy 'authlete.php' to 'config/authlete.php'.
        $this->copyToConfig('authlete.php');

        // Append the content of 'routes-web.php' to 'routes/web.php'.
        $this->appendToBase('routes-web.php', 'routes/web.php');

        // Append the content of 'routes-api.php' to 'routes/api.php'.
        $this->appendToBase('routes-api.php', 'routes/api.php');

        // Copy controllers with the namespace replaced.
        $this->createDirectory(app_path('Http/Controllers/Authlete'));
        $this->relocateController('TokenController.php');

        // Add routes to 'routes/api.php'.
        $this->addApiRoute('post', '/token', 'TokenController');
    }


    private function copyToConfig($source)
    {
        copy("${rsc}/${source}", config_path($source));
    }


    private function appendToBase($source, $target)
    {
        $this->append("${rsc}/${source}", base_path($target));
    }


    private function append($source, $target)
    {
        file_put_contents($target, file_get_contents($source), FILE_APPEND);
    }


    private function createDirectory($path)
    {
        if (is_dir($path) === false)
        {
            mkdir($path, 0755, true);
        }
    }


    private function relocateController($controller)
    {
        $this->relocate(
            "${rsc}/${controller}",
            app_path("Http/Controllers/Authlete/${controller}"),
            $this->getAppNamespace() . 'Http\Controllers\Authlete');
    }


    private function relocate($source, $target, $namespace)
    {
        // The content written into $target.
        $content = str_replace('_NAMESPACE_', $namespace, file_get_contents($source));

        // Write $content to $target.
        file_put_contents($target, $content);
    }


    private function addApiRoute($method, $path, $controller)
    {
        $namespace = $this->getAppNamespace() . 'Http\Controllers\Authlete';

        $line = "Route::${method}('${path}', '${namespace}\\${controller}');\n";

        file_put_contents(base_path('routes/api.php'), $line, FILE_APPEND);
    }
}
?>
