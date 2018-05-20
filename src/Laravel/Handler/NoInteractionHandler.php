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
 * File containing the definition of NoInteractionHandler class.
 */


namespace Authlete\Laravel\Handler;


use Authlete\Api\AuthleteApi;
use Authlete\Api\AuthleteApiException;
use Authlete\Dto\AuthorizationAction;
use Authlete\Dto\AuthorizationFailReason;
use Authlete\Dto\AuthorizationResponse;
use Authlete\Laravel\Handler\Spi\NoInteractionHandlerSpi;
use Authlete\Util\MaxAgeValidator;
use Illuminate\Http\Response;


/**
 * Handler for the case where an authorization request should be processed
 * without user interaction.
 *
 * A response from Authlete's `/api/auth/authorization` API contains an
 * `action` response parameter. When the value of the response parameter is
 * `AuthorizationAction::$NO_INTERACTION`, the authorization request needs to
 * be processed without user interaction. This class is a handler for the case.
 */
class NoInteractionHandler extends AuthorizationRequestBaseHandler
{
    private $spi = null;  // \Authlete\Laravel\Handler\Spi\NoInteractionHandlerSpi


    /**
     * Constructor.
     *
     * @param AuthleteApi $api
     *     An implementation of the `AuthleteApi` interface.
     *
     * @param NoInteractionHandlerSpi $spi
     *     An implementation of the `NoInteractionHandler` interface.
     */
    public function __construct(AuthleteApi $api, NoInteractionHandlerSpi $spi)
    {
        parent::__construct($api);

        $this->spi = $spi;
    }


    /**
     * Handle an authorization request without user interaction.
     *
     * This method calls Authlete's `/api/auth/authorization/issue` API or
     * `/api/auth/authorization/fail` API.
     *
     * @param AuthorizationResponse $response
     *     A response from Authlete's `/api/auth/authorization` API.
     *
     * @return Response
     *     An HTTP response that should be returned to the user agent. If
     *     `$response->getAction()` is not `AuthorizationAction::$NO_INTERACTION`,
     *     this method returns `null`.
     *
     * @throws AuthleteApiException
     */
    public function handle(AuthorizationResponse $response)
    {
        // If the value of the "action" parameter in the response from
        // Authlete's /api/auth/authorization API is not "NO_INTERACTION".
        if ($response->getAction() !== AuthorizationAction::$NO_INTERACTION)
        {
            // This handler does not handle other cases than NO_INTERACTION.
            return null;
        }

        // Check 1: End-User Authentication
        if ($this->checkUserAuthentication() === false)
        {
            // A user must have logged in.
            return $this->authorizationFail(
                $response->getTicket(), AuthorizationFailReason::$NOT_LOGGED_IN);
        }

        // Get the last time when the user was authenticated.
        $authTime = $this->spi->getUserAuthenticatedAt();

        // Check 2: Max Age
        if ($this->checkMaxAge($response, $authTime) === false)
        {
            // The maximum authentication age has elapsed since the last time
            // when the end-user was authenticated.
            return $this->authorizationFail(
                $response->getTicket(), AuthorizationFailReason::$EXCEEDS_MAX_AGE);
        }

        // The subject (unique identifier) of the current user.
        $subject = $this->spi->getUserSubject();

        // Check 3: Subject
        if ($this->checkSubject($response, $subject) === false)
        {
            // The requested subject and that of the current user do not match.
            return $this->authorizationFail(
                $response->getTicket(), AuthorizationFailReason::$DIFFERENT_SUBJECT);
        }

        // Get the value of the "sub" claim. This is optional. When $sub is null,
        // the value of $subject will be used as the value of the "sub" claim.
        $sub = $this->spi->getSub();

        // Get the ACR that was satisfied when the current user was authenticated.
        $acr = $this->spi->getAcr();

        // Check 4: ACR
        if ($this->checkAcr($response, $acr) === false)
        {
            // None of the requested ACRs is satisified.
            return $this->authorizationFail(
                $response->getTicket(), AuthorizationFailReason::$ACR_NOT_SATISFIED);
        }

        // Collect claim values.
        $collector = createClaimCollector($response, $subject);
        $claims    = $collector->collect();

        // Properties that will be associated with an access token and/or an
        // authorization code.
        $properties = $this->spi->getProperties();

        // Scopes that will be associated with an access token and/or an
        // authorization code. If a non-null value is returned from
        // $this->spi->getScopes(), the scope set replaces the scopes that
        // were specified in the original authorization request.
        $scopes = $this->spi->getScopes();

        // Issue tokens without user interaction.
        return $this->authorizationIssue(
            $response->getTicket(), $subject, $authTime, $acr, $claims,
            $properties, $scopes, $sub);
    }


    private function createClaimCollector(AuthorizationResponse $response, $subject)
    {
        return new ClaimCollector(
            $subject,
            $response->getClaims(),
            $response->getClaimLocales(),
            $this->spi
        );
    }


    private function checkUserAuthentication()
    {
        return $this->spi->isUserAuthenticated();
    }


    private function checkMaxAge(AuthorizationResponse $response, $authTime)
    {
        return (new MaxAgeValidator())
            ->setMaxAge($response->getMaxAge())
            ->setAuthenticationTime($authTime)
            ->validate();
    }


    private function checkSubject(AuthorizationResponse $response, $subject)
    {
        // Get the requested subject.
        $requestedSubject = $response->getSubject();

        // If no subject is requested.
        if (is_null($requestedSubject))
        {
            // No need to care about the subject.
            return true;
        }

        // If the requested subject matches that of the current user.
        if ($requestedSubject === $subject)
        {
            // The subjects match.
            return true;
        }

        // The subjects do not match.
        return false;
    }


    private function checkAcr(AuthorizationResponse $response, $acr)
    {
        // Get the list of requested ACRs.
        $requestedAcrs = $response->getAcrs();

        // If no ACR is required.
        if (is_null($requestedAcrs) || count($requestedAcrs) === 0)
        {
            // No need to care about ACR.
            return true;
        }

        // For each requested ACR.
        foreach ($requestedAcrs as $requestedAcr)
        {
            if ($requestedAcr === $acr)
            {
                // OK. The ACR satisfied when the current user was
                // authenticated matches one of the requested ACRs.
                return true;
            }
        }

        // If one of the requested ACRs must be satisfied.
        if ($response->isAcrEssential())
        {
            // None of the requested ACRs is satisified.
            return false;
        }

        // The ACR satisfied when the current user was authenticated does not
        // match any one of the requested ACRs, but the authorization request
        // from the client application did not request ACR as essential.
        // Therefore, it is not necessary to raise an error here.
        return true;
    }
}
?>
