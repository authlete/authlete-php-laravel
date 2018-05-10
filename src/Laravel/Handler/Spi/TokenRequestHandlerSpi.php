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
 * File containing the definition of TokenRequestHandlerSpi interface.
 */


namespace Authlete\Laravel\Handler\Spi;


use Authlete\Dto\Property;


/**
 * Service Provider Interface for TokenRequestHandler.
 *
 * The constructor of `TokenRequestHandler` requires an implementation
 * of this interface.
 *
 * `TokenRequestHandlerSpiAdapter` is an empty implementation of this interface.
 *
 * @link \Authlete\Laravel\Handler\TokenRequestHandler
 */
interface TokenRequestHandlerSpi
{
    /**
     * Authenticate an end-user.
     *
     * This method is called only when
     * [Resource Owner Password Credentials Grant](https://tools.ietf.org/html/rfc6749#section-4.3)
     * was used. Therefore, if you have no plan to support the flow, always
     * return `null`. In most cases, you don't have to support the flow.
     * [RFC 6749](https://tools.ietf.org/html/rfc6749) says _"The authorization
     * server should take special care when enabling this grant type and only
     * allow it when other flows are not viable."_
     *
     * @param string $username
     *     The value of the `username` request parameter of the token request.
     *
     * @param string $password
     *     The value of the `password` request parameter of the token request.
     *
     * @return string
     *     The subject (= unique identifier) of the authenticated end-user.
     *     If the pair of `$username` and `$password` is invalid. `null`
     *     should be returned.
     */
    public function authenticateUser($username, $password);


    /**
     * Get arbitrary key-value pairs to be associated with an access token.
     *
     * Properties returned from this method will appear as top-level entries
     * (unless they are marked as hidden) in a JSON response from the
     * authorization server as shown in
     * [5.1. Successful Response](https://tools.ietf.org/html/rfc6749#section-5.1)
     * of [RFC 6749](https://tools.ietf.org/html/rfc6749).
     *
     * Keys listed below should not be used and they would be ignored on
     * Authlete side even if they were used. It is because they are reserved
     * in [RFC 6749](https://tools.ietf.org/html/rfc6749) and
     * [OpenID Connect Core 1.0](https://openid.net/specs/openid-connect-core-1_0.html).
     *
     * * `access_token`
     * * `token_type`
     * * `expires_in`
     * * `refresh_token`
     * * `scope`
     * * `error`
     * * `error_description`
     * * `error_uri`
     * * `id_token`
     *
     * Note that there is an upper limit on the total size of properties.
     * On Authlete side, the properties will be (1) converted to a
     * multidimensional string array, (2) converted to JSON, (3) encrypted
     * by AES/CBC/PKCS5Padding, (4) encoded by base64url, and then stored
     * into the database. The length of the resultant string must not
     * exceed 65,535 in bytes. This is the upper limit, but we think it is
     * big enough.
     *
     * When the value of the `grant_type` parameter of a token request is
     * `authorization_code` or `refresh_token`, properties are merged.
     * Rules are described below.
     *
     * In the case of `grant_type=authorization_code`:
     *
     * If the authorization code presented by the client application already
     * has properties (this happens if `getProperties()` method of your
     * `AuthorizationDecisionHandlerSpi` returned properties when the
     * authorization code was issued), properties returned from this method
     * will be merged into the existing properties. Note that the existing
     * properties will be overwritten if properties returned from this
     * method have the same keys.
     *
     * For example, if an authorization code has two properties, `a=1` and
     * `b=2`, and if this method returns two properties, `a=A` and `c=3`,
     * the resultant access token will have three properties, `a=A`, `b=2`
     * and `c=3`.
     *
     * In the case of `grant_type=refresh_token`:
     *
     * If the access token associated with the refresh token already has
     * properties, properties returned from this method will be merged into
     * the existing properties. Note that the existing properties will be
     * overwritten if properties returned fro this method have the same keys.
     *
     * @return Property[]
     *     Arbitrary key-value pairs to be associated with an access token.
     */
    public function getProperties();
}
?>
