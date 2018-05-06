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
 * File containing the definition of JwksController class.
 */


namespace Authlete\Laravel\Controller;


use Authlete\Api\AuthleteApi;
use Authlete\Laravel\Handler\JwksRequestHandler;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;


/**
 * An implementation of JWK Set document endpoint.
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
class JwksController extends Controller
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
        return (new JwksRequestHandler($api))->handle();
    }
}
?>