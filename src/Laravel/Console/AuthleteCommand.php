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
 * The base class for Authlete*Command classes.
 */
class AuthleteCommand extends Command
{
    use DetectsApplicationNamespace;


    /**
     * The directory that holds resources.
     */
    private static $rsc = __DIR__ . '/../../../rsc/';


    protected function createDirectory($path)
    {
        if (is_dir($path) === false)
        {
            mkdir($path, 0755, true);
        }
    }


    protected function getControllerDirectory()
    {
        return app_path('Http/Controllers/Authlete/');
    }


    protected function getViewDirectory()
    {
        return resource_path('views/authlete/');
    }


    protected function getCssDirectory()
    {
        return public_path('css/authlete/');
    }


    protected function getControllerNamespace()
    {
        return $this->getAppNamespace() . 'Http\Controllers\Authlete';
    }


    protected function copyResourceFile($resourceFile, $targetDirectory)
    {
        $source = self::$rsc       . $resourceFile;
        $target = $targetDirectory . $resourceFile;

        copy($source, $target);
    }


    protected function relocateController($controller)
    {
        $this->relocate(
            self::$rsc . $controller,
            $this->getControllerDirectory() . $controller,
            $this->getControllerNamespace());
    }


    protected function relocate($sourceFile, $targetFile, $namespace)
    {
        // The content written into $targetFile.
        $content = str_replace('_NAMESPACE_', $namespace, file_get_contents($sourceFile));

        // Write $content to $targetFile.
        file_put_contents($targetFile, $content);
    }


    protected function addWebRoute($method, $path, $controller)
    {
        $this->addRoute($method, $path, $controller, base_path('routes/web.php'));
    }


    protected function addApiRoute($method, $path, $controller)
    {
        $this->addRoute($method, $path, $controller, base_path('routes/api.php'));
    }


    protected function addRoute($method, $path, $controller, $targetFile)
    {
        $namespace = $this->getControllerNamespace();
        $content   = "Route::${method}('${path}', '\\${namespace}\\${controller}');\n";

        $this->appendContent($content, $targetFile);
    }


    protected function appendContent($content, $targetFile)
    {
        file_put_contents($targetFile, $content, FILE_APPEND);
    }
}
?>
