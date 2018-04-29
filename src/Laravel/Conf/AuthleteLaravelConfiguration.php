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
 * File containing the definition of AuthleteLaravelConfiguration class.
 */


namespace Authlete\Laravel\Conf;


use Authlete\Conf\AuthleteConfiguration;
use Authlete\Conf\AuthleteConfigurationTrait;


/**
 * An implementation of the \Authlete\Conf\AuthleteConfiguration
 * interface that utilizes Laravel's configuration mechanism.
 *
 * This class requires that `config/authlete.php` exist and have
 * configuration like the following.
 *
 * ```
 * <?php
 * return [
 *     'base_url'                 => 'https://api.authlete.com',
 *     'service_owner.api_key'    => '',
 *     'service_owner.api_secret' => '',
 *     'service.api_key'          => '',
 *     'service.api_secret'       => ''
 * ];
 * ?>
 * ```
 *
 * Of course, values of the configuration parameters need to
 * replaced with your own values.
 */
class AuthleteLaravelConfiguration implements AuthleteConfiguration
{
    use AuthleteConfigurationTrait;


    private static $CONFIGURATION_BASE_NAME      = 'authlete';
    private static $KEY_BASE_URL                 = 'base_url';
    private static $KEY_SERVICE_OWNER_API_KEY    = 'service_owner.api_key';
    private static $KEY_SERVICE_OWNER_API_SECRET = 'service_owner.api_secret';
    private static $KEY_SERVICE_API_KEY          = 'service.api_key';
    private static $KEY_SERVICE_API_SECRET       = 'service.api_secret';
    private static $DEFAULT_BASE_URL             = 'https://api.authlete.com';


    /**
     * Constructor which refers to 'config/authlete.php' and sets up
     * the corresponding properties.
     */
    public function __construct()
    {
        $this->baseUrl               = self::get($KEY_BASE_URL);
        $this->serviceOwnerApiKey    = self::get($KEY_SERVICE_OWNER_API_KEY);
        $this->serviceOwnerApiSecret = self::get($KEY_SERVICE_OWNER_API_SECRET);
        $this->serviceApiKey         = self::get($KEY_SERVICE_API_KEY);
        $this->serviceApiSecret      = self::get($KEY_SERVICE_API_SECRET);

        // If the value of 'base_url' is not available.
        if (is_null($this->baseUrl) || empty($this->baseUrl))
        {
            // Use the default value for 'base_url'.
            $this->baseUrl = $DEFAULT_BASE_URL;
        }
    }


    /**
     * Get the value of the configuration parameter which is identified by the key.
     */
    private static function get($key)
    {
        return config("${CONFIGURATION_BASE_NAME}.${key}");
    }
}
?>
