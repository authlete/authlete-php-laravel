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
    /**
     * The directory that holds resources.
     */
    private static $rsc = __DIR__ . '/../../../rsc/';


    /**
     * Create a directory if it does not exist.
     *
     * @param string $path
     *     The path of a directory.
     */
    protected function createDirectory($path)
    {
        if (is_dir($path) === false)
        {
            mkdir($path, 0755, true);
        }
    }


    /**
     * Create a directory for controllers ('Http/Controllers/Authlete/')
     * under the application directory.
     */
    protected function getControllerDirectory()
    {
        return app_path('Http/Controllers/Authlete/');
    }


    /**
     * Create a directory for views ('views/authlete/') under the resource
     * directory.
     */
    protected function getViewDirectory()
    {
        return resource_path('views/authlete/');
    }


    /**
     * Create a directory for CSS files ('css/authlete/') under the public
     * directory.
     */
    protected function getCssDirectory()
    {
        return public_path('css/authlete/');
    }


    /**
     * Get the name space for the controllers which are created under
     * 'Http/Controllers/Authlete/' directory.
     */
    protected function getControllerNamespace()
    {
        return $this->laravel->getNamespace() . 'Http\Controllers\Authlete';
    }


    /**
     * Copy a file from the resource directoy to the target directory.
     *
     * @param string $resourceFile
     *     The name of a resource file.
     *
     * @param string $targetDirectory
     *     The path of a target directory.
     *
     * @param boolean $overwrite
     *     `true` to overwrite the target file even if it exists.
     *     The default value is `true`.
     */
    protected function copyResourceFile($resourceFile, $targetDirectory, $overwrite = true)
    {
        if (substr($targetDirectory, -1) !== '/')
        {
            $targetDirectory = $targetDirectory . '/';
        }

        $sourceFile = self::$rsc       . $resourceFile;
        $targetFile = $targetDirectory . $resourceFile;

        if ($overwrite === false && file_exists($targetFile))
        {
            // Not overwrite the target file.
            return;
        }

        copy($sourceFile, $targetFile);
    }


    /**
     * Relocate a controller from the resource directory to the controller
     * directory.
     *
     * @param string $controller
     *     The file name of a controller.
     */
    protected function relocateController($controller)
    {
        $this->relocate(
            self::$rsc . $controller,
            $this->getControllerDirectory() . $controller,
            $this->getControllerNamespace());
    }


    private function relocate($sourceFile, $targetFile, $namespace)
    {
        // The content written into $targetFile.
        $content = str_replace('_NAMESPACE_', $namespace, file_get_contents($sourceFile));

        // Write $content to $targetFile.
        file_put_contents($targetFile, $content);
    }


    /**
     * Add a route to 'routes/web.php'.
     *
     * @param string $method
     *     An HTTP method such as `post`.
     *
     * @param string $path
     *     The path to which the controller is mapped.
     *
     * @param string $controller
     *     The name of a controller.
     */
    protected function addWebRoute($method, $path, $controller)
    {
        $this->addRoute($method, $path, $controller, base_path('routes/web.php'));
    }


    /**
     * Add a route to 'routes/api.php'.
     *
     * @param string $method
     *     An HTTP method such as `post`.
     *
     * @param string $path
     *     The path to which the controller is mapped.
     *
     * @param string $controller
     *     The name of a controller.
     */
    protected function addApiRoute($method, $path, $controller)
    {
        $this->addRoute($method, $path, $controller, base_path('routes/api.php'));
    }


    private function addRoute($method, $path, $controller, $targetFile)
    {
        $namespace = $this->getControllerNamespace();
        $content   = "Route::${method}('${path}', '\\${namespace}\\${controller}');\n";

        $this->appendContent($content, $targetFile);
    }


    /**
     * Append a content to a target file.
     *
     * @param string $content
     *     A content which is appended to the target file.
     *
     * @param string $targetFile
     *     The path of a target file.
     */
    protected function appendContent($content, $targetFile)
    {
        file_put_contents($targetFile, $content, FILE_APPEND);
    }
}
?>
