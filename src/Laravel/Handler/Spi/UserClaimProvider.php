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
 * File containing the definition of UserClaimProvider interface.
 */


namespace Authlete\Laravel\Handler\Spi;


/**
 * Interface to get a claim value by specifying a user's subject,
 * a claim name and optionally a language tag.
 */
interface UserClaimProvider
{
    /**
     * Get the value of a claim of the user.
     *
     * This method may be called multiple times.
     *
     * The value returned from this method must be able to be processed by
     * `json_encode()`. In most cases, a string, a boolean value or an
     * integer should be returned. When `$claimName` is `"address"`, an
     * array which conforms to the format defined in
     * [5.1.1. Address Claim](https://openid.net/specs/openid-connect-core-1_0.html#AddressClaim)
     * of [OpenID Connect Core 1.0](https://openid.net/specs/openid-connect-core-1_0.html)
     * should be returned. For example,
     *
     * ```
     * return array(
     *     'country' => 'Japan',
     *     'region'  => 'Tokyo'
     * );
     * ```
     *
     * \Authlete\Dto\Address class can be used to generate an array that
     * conforms to the specification.
     *
     * ```
     * // Create an instance of Address class and set property values.
     * $address = new Address();
     * $address->setCountry('Japan')->setRegion('Tokyo');
     *
     * // Convert the Address instance into an array.
     * $array = $address->toArray();
     * ```
     *
     * @param string $subject
     *     The subject (= unique identifier) of a user.
     *
     * @param string $claimName
     *     A claim name such as `"name"` and `"family_name"`. Standard claim
     *     names are listed in
     *     [5.1. Standard Claims](https://openid.net/specs/openid-connect-core-1_0.html#StandardClaims) of
     *     [OpenID Connect Core 1.0](https://openid.net/specs/openid-connect-core-1_0.html).
     *     Constanct values that represent the standard claims are listed in
     *     \Authlete\Types\StandardClaims class. Note that the value of this
     *     argument (`$claimName`) does NOT contain a language tag.
     *
     * @param string $languageTag
     *     A language tag such as `"en"` and `"ja"`. Implementations of this
     *     method should take this into consideration if possible. See
     *     [5.2. Claims Languages and Scripts](https://openid.net/specs/openid-connect-core-1_0.html#ClaimsLanguagesAndScripts) of
     *     [OpenID Connect Core 1.0](https://openid.net/specs/openid-connect-core-1_0.html)
     *     for details.
     *
     * @return mixed
     *     The value of the claim. `null` if the value is not available.
     *     The returned value must be able to be processed by `json_encode()`.
     */
    public function getUserClaimValue($subject, $claimName, $languageTag);
}
?>
