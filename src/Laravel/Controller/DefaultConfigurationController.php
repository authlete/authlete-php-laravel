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
 * File containing the definition of DefaultConfigurationController class.
 */


namespace Authlete\Laravel\Controller;


use App\Http\Controllers\Controller;
use Authlete\Api\AuthleteApi;
use Authlete\Laravel\Handler\ConfigurationRequestHandler;
use Illuminate\Http\Response;


/**
 * An implementation of configuration endpoint.
 *
 * An OpenID provider that supports
 * [OpenID Connect Discovery 1.0](https://openid.net/specs/openid-connect-discovery-1_0.html)
 * provides an endpoint that returns its configuration information in JSON
 * format. Details about the format are described in
 * [3. OpenID Provider Metadata](https://openid.net/specs/openid-connect-discovery-1_0.html#ProviderMetadata)
 * of [OpenID Connect Discovery 1.0](https://openid.net/specs/openid-connect-discovery-1_0.html).
 *
 * Note that the URI of an OpenID provider configuration endpoint is defined in
 * [4.1. OpenID Provider Configuration Request](https://openid.net/specs/openid-connect-discovery-1_0.html#ProviderConfigurationRequest).
 * In short, the URI must be:
 *
 * ```
 * {Issuer-Identifier}/.well-known/openid-configuration
 * ```
 *
 * "{Issuer-Identifier}" is a URL to identify an OpenID provider. For example,
 * `https://example.com`. For details about Issuer Identifier, see the
 * description about the `issuer` metadata defined in
 * [3. OpenID Provider Metadata](https://openid.net/specs/openid-connect-discovery-1_0.html#ProviderMetadata)
 * of [OpenID Connect Discovery 1.0](https://openid.net/specs/openid-connect-discovery-1_0.html)
 * and the `iss` claim in
 * [2. ID Token](https://openid.net/specs/openid-connect-core-1_0.html#IDToken)
 * of [OpenID Connect Core 1.0](https://openid.net/specs/openid-connect-core-1_0.html).
 *
 * You can change the Issuer Identifier of your service by using the management
 * console ([Service Owner Console](https://www.authlete.com/documents/so_console)).
 * Note that the default value of Issuer Identifier is not appropriate for
 * production use, so you should change it.
 */
class DefaultConfigurationController extends Controller
{
    /**
     * The entry point of this controller.
     *
     * @param AuthleteApi $api
     *     An implementation of the `AuthleteApi` interface.
     *
     * @return Response
     *     A response that should be returned to the client.
     */
    public function __invoke(AuthleteApi $api)
    {
        return (new ConfigurationRequestHandler($api))->handle();
    }
}
?>