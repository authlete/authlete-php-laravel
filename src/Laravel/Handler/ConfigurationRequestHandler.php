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
 * File containing the definition of ConfigurationRequestHandler class.
 */


namespace Authlete\Laravel\Handler;


use Authlete\Api\AuthleteApiException;
use Authlete\Util\ValidationUtility;
use Authlete\Web\ResponseUtility;
use Illuminate\Http\Response;


/**
 * Handler for requests to a configuration endpoint.
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
class ConfigurationRequestHandler extends BaseRequestHandler
{
    /**
     * Handle a request to a configuration endpoint.
     *
     * This method calls Authlete's `/api/service/configuration` API.
     *
     * @param boolean $pretty
     *     `true` to format the output JSON in a more human-readable way.
     *
     * @return Response
     *     A HTTP response that should be returned from the configuration
     *     endpoint implementation to the client application.
     *
     * @throws AuthleteApiException
     */
    public function handle($pretty = true)
    {
        ValidationUtility::ensureBoolean('$pretty', $pretty);

        // Call Authlete's /api/service/configuration API. The API returns
        // a JSON that complies with OpenID Connect Discovery 1.0.
        $json = $this->getApi()->getServiceConfiguration($pretty);

        // 200 OK, application/json;charset=UTF-8
        return ResponseUtility::okJson($json);
    }
}
?>
