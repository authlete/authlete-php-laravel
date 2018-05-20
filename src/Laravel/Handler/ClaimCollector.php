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
 * File containing the definition of ClaimCollector class.
 */


namespace Authlete\Laravel\Handler;


use Authlete\Laravel\Handler\Spi\UserClaimProvider;
use Authlete\Util\ValidationUtility;


/**
 * Collector of claim values.
 */
class ClaimCollector
{
    private $subject       = null;  // string
    private $claimNames    = null;  // string array
    private $claimLocales  = null;  // string array
    private $claimProvider = null;  // \Authlete\Laravel\Handler\Spi\UserClaimProvider


    /**
     * Constructor.
     *
     * @param string $subject
     *     The subject of the end-user.
     *
     * @param string[] $claimNames
     *     Names of requested claims.
     *
     * @param string[] $claimLocales
     *     Requested claim locales.
     *
     * @param UserClaimProvider $claimProvider
     *     An implementation of the `UserClaimProvider` interface that returns
     *     claim values.
     */
    public function __construct(
        $subject, array $claimNames = null, array $claimLocales = null,
        UserClaimProvider $claimProvider)
    {
        ValidationUtility::ensureString('$subject', $subject);
        ValidationUtility::ensureNullOrArrayOfString('$claimNames', $claimNames);
        ValidationUtility::ensureNullOrArrayOfString('$claimLocales', $claimLocales);

        $this->subject       = $subject;
        $this->claimNames    = $claimNames;
        $this->claimLocales  = self::normalizeClaimLocales($claimLocales);
        $this->claimProvider = $claimProvider;
    }


    private static function normalizeClaimLocales(array $claimLocales = null)
    {
        if (is_null($claimLocales) || count($claimLocales) === 0)
        {
            return null;
        }

        // Array to check duplicates.
        $set = array();

        // Normalized list.
        $list = array();

        // Loop to drop empty and duplicate claim locales.
        foreach ($claimLocales as $claimLocale)
        {
            // If the claim locale is invalid.
            if (is_null($claimLocale) || empty($claimLocale))
            {
                // Ignore the entry.
                continue;
            }

            // From 5.2. Claims Languages and Scripts in OpenID Connect Core 1.0
            //
            //   However, since BCP47 language tag values are case insensitive,
            //   implementations SHOULD interpret the language tag values supplied
            //   in a case insensitive manner.
            //

            // Generate a key for duplicate check by lowering the claim locale.
            $key = strtolower($claimLocale);

            // If the claim locale appeared again.
            if (array_key_exists($key, $set))
            {
                // Ignore the duplicate entry.
                continue;
            }

            $set[]  = $key;
            $list[] = $claimLocale;
        }

        if (count($list) === 0)
        {
            return null;
        }

        // Normalized list.
        return $list;
    }


    /**
     * Collect claim values.
     *
     * @return array
     *     An array of pairs of claim name and claim value.
     */
    public function collect()
    {
        // If no claim is required.
        if (is_null($this->claimNames) || count($this->claimNames) === 0)
        {
            return null;
        }

        // Claim values.
        $collectedClaims = array();

        // For each required claim.
        foreach ($this->claimNames as $claimName)
        {
            // If the claim name is empty.
            if (is_null($claimName) || empty($claimName))
            {
                continue;
            }

            // Split the claim name into the name part and the language tag part.
            $elements = explode('#', $claimName, 2);
            $name = $elements[0];
            $tag  = (count($elements) === 2) ? $elements[1]  : null;

            // If the name part is empty.
            if (is_null($name) || empty($name))
            {
                continue;
            }

            // Get the claim value of the claim.
            $value = $this->getClaimValue($name, $tag);

            // If the claim value was not obtained.
            if (is_null($value))
            {
                continue;
            }

            // Just for an edge case where $claimName ends with '#'.
            $key = is_null($tag) ? $name : $claimName;

            // Add the pair of the claim name (which may be followed by
            // a language tag) and the claim value.
            $collectedClaims[$key] = $value;
        }

        // If no claim value has been obtained.
        if (count($collectedClaims) === 0)
        {
            return null;
        }

        // Collected claims.
        return $collectedClaims;
    }


    private function getClaimValue($claimName, $languageTag)
    {
        // If a language tag is explicitly appended.
        if (is_null($languageTag) === false && empty($languageTag) === false)
        {
            // Get the claim value of the claim with the specific language tag.
            return $this->getUserClaimValue($claimName, $languageTag);
        }

        // If claim locales are not specified by the 'claims_locales' request parameter.
        if (is_null($this->claimLocales))
        {
            // Get the claim value of the claim without any language tag.
            return $this->getUserClaimValue($claimName, null);
        }

        // For each claim locale. They are ordered by preference.
        foreach ($this->claimLocales as $claimLocale)
        {
            // Try to get the claim with the claim locale.
            $value = $this->getUserClaimValue($claimName, $claimLocale);

            // If the claim value was obtained.
            if (is_null($value) === false)
            {
                return $value;
            }
        }

        // The last resport. Try to get the claim value without any language tag.
        return $this->getUserClaimValue($claimName, null);
    }


    private function getUserClaimValue($claimName, $claimLocale)
    {
        return $this->claimProvider->getUserClaimValue(
            $this->subject, $claimName, $claimLocale);
    }
}
?>
