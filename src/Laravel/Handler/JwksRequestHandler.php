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
 * File containing the definition of JwksRequestHandler class.
 */


namespace Authlete\Laravel\Handler;


use Authlete\Api\AuthleteApiException;
use Authlete\Laravel\Web\ResponseUtility;
use Authlete\Util\ValidationUtility;
use Illuminate\Http\Response;


/**
 * Handler for requests to an endpoint that exposes JSON Web Key Set document.
 *
 * An OpenID Provider (OP) is required to expose its JSON Web Key Set document
 * (JWK Set) so that client applications can (1) verify signatures by the OP
 * and (2) encrypt their requests to the OP. See
 * [RFC 7517](https://tools.ietf.org/html/rfc7517) for details about JWK Set.
 *
 * The URI of a JWK Set document endpoint can be found as the value of the
 * `jwks_uri` metadata which is defined in
 * [OpenID Provider Metadata](https://openid.net/specs/openid-connect-discovery-1_0.html#ProviderMetadata)
 * if the OP supports
 * [OpenID Connect Discovery 1.0](https://openid.net/specs/openid-connect-discovery-1_0.html).
 */
class JwksRequestHandler extends BaseRequestHandler
{
    /**
     * Handle a request to a JWK Set document endpoint.
     *
     * This method calls Authlete's `/api/service/jwks/get` API.
     *
     * @param boolean $pretty
     *     `true` to format the output JSON in a more human-readable way.
     *
     * @return Response
     *     An HTTP response that should be returned from the JWK Set document
     *     endpoint implementation to the client application.
     *
     * @throws AuthleteApiException
     */
    public function handle($pretty = true)
    {
        ValidationUtility::ensureBoolean('$pretty', $pretty);

        $cause = null;

        try
        {
            // Call Authlete's /api/service/jwks/get API. The API returns the
            // JWK Set document of the service. The second argument given to
            // getServiceJwks() is false not to include private keys.
            $jwks = $this->getApi()->getServiceJwks($pretty, false);

            // If no JWK Set for the service is registered.
            if (is_null($jwks) || empty($jwks))
            {
                // 204 No Content.
                return ResponseUtility::noContent();
            }

            // 200 OK, application/json;charset=UTF-8
            return ResponseUtility::okJson($jwks);
        }
        catch (AuthleteApiException $e)
        {
            $cause = $e;
        }

        // If the HTTP status code of the response from the Authlete API is not
        // "302 Found".
        if ($cause->getStatusCode() !== Response::HTTP_FOUND)
        {
            // Something wrong happened.
            throw $cause;
        }

        // The value of the Location header of the response from the Authlete API.
        $location = $cause->getResponseHeaders()->getFirst('Location');

        // 302 Found with a Location header.
        return ResponseUtility::location($location);
    }
}
?>
