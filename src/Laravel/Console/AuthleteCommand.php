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
    private static $rsc = __DIR__ . '/../../../rsc/';


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
        $this->appendContent("\n// Routes added by AuthleteCommand.\n", base_path('routes/web.php'));
        $this->addWebRoute('get',  '/.well-known/openid-configuration', 'ConfigurationController');
        $this->addWebRoute('get',  '/authorization', 'AuthorizationController');
        $this->addWebRoute('post', '/authorization', 'AuthorizationController');
        $this->addWebRoute('post', '/authorization/decision', 'AuthorizationDecisionController');

        // Add routes to 'routes/api.php'.
        $this->appendContent("\n// Routes added by AuthleteCommand.\n", base_path('routes/api.php'));
        $this->addApiRoute('get',  '/jwks', 'JwksController');
        $this->addApiRoute('post', '/revocation', 'RevocationController');
        $this->addApiRoute('post', '/token', 'TokenController');
    }


    private function createDirectory($path)
    {
        if (is_dir($path) === false)
        {
            mkdir($path, 0755, true);
        }
    }


    private function getControllerDirectory()
    {
        return app_path('Http/Controllers/Authlete/');
    }


    private function getViewDirectory()
    {
        return resource_path('views/authlete/');
    }


    private function getCssDirectory()
    {
        return public_path('css/authlete/');
    }


    private function getControllerNamespace()
    {
        return $this->getAppNamespace() . 'Http\Controllers\Authlete';
    }


    private function copyResourceFile($resourceFile, $targetDirectory)
    {
        $source = self::$rsc       . $resourceFile;
        $target = $targetDirectory . $resourceFile;

        copy($source, $target);
    }


    private function relocateController($controller)
    {
        $this->relocate(
            self::$rsc . $controller,
            $this->getControllerDirectory() . $controller,
            $this->getControllerNamespace());
    }


    private function relocate($sourceFile, $targetFile, $namespace)
    {
        // The content written into $target.
        $content = str_replace('_NAMESPACE_', $namespace, file_get_contents($sourceFile));

        // Write $content to $target.
        file_put_contents($targetFile, $content);
    }


    private function addWebRoute($method, $path, $controller)
    {
        $this->addRoute($method, $path, $controller, base_path('routes/web.php'));
    }


    private function addApiRoute($method, $path, $controller)
    {
        $this->addRoute($method, $path, $controller, base_path('routes/api.php'));
    }


    private function addRoute($method, $path, $controller, $targetFile)
    {
        $namespace = $this->getControllerNamespace();
        $content   = "Route::${method}('${path}', '\\${namespace}\\${controller}');\n";

        $this->appendContent($content, $targetFile);
    }


    private function appendContent($content, $targetFile)
    {
        file_put_contents($targetFile, $content, FILE_APPEND);
    }
}
?>
