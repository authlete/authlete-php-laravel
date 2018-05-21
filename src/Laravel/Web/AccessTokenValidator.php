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
 * File containing the definition of AccessTokenValidator class.
 */


namespace Authlete\Laravel\Web;


use Authlete\Api\AuthleteApi;
use Authlete\Dto\IntrospectionAction;
use Authlete\Dto\IntrospectionRequest;
use Authlete\Dto\IntrospectionResponse;
use Authlete\Laravel\Web\ResponseUtility;
use Authlete\Laravel\Web\WebUtility;
use Authlete\Util\LanguageUtility;
use Authlete\Util\ValidationUtility;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;


/**
 * Access token validator.
 *
 * ```
 * // An implementation of the AuthleteApi interface.
 * $api = ...;
 *
 * // Create an access token validator.
 * $validator = new AccessTokenValidator($api);
 *
 * // Extract an access token from the request.
 * $accessToken = WebUtility::extractAccessToken($request);
 *
 * // Set the access token that should be validated.
 * $validator->setAccessToken($accessToken);
 *
 * // [Optional]
 * // Set scopes required to access the protected resource endpoint.
 * $requiredScopes = array(...);
 * $validator->setRequiredScopes($requiredScopes);
 *
 * // [Optional]
 * // Set a subject (unique user identifier) that must be associated
 * // with the access token.
 * $requiredSubject = ...;
 * $validator->setRequiredSubject($requiredSubject);
 *
 * // [Optional]
 * // Set the client certificate that the client application presented.
 * $clientCertificate = ...;
 * $validator->setClientCertificate($clientCertificate);
 *
 * // Perform validation. This triggers an API call to Authlete's
 * // /api/auth/introspection API.
 * $valid = $validator->validate();
 *
 * // If the access token is not valid.
 * if ($valid === false)
 * {
 *     // If the call to /api/auth/introspection API made by the
 *     // implementation of validate() method threw an exception,
 *     // `getIntrospectionException()` method returns the exception.
 *     $introspectionException = $validator->getIntrospectionException();
 *
 *     // If the call to /api/auth/introspection API made by the
 *     // implementation of validate() method succeeded,
 *     // `getIntrospectionResponse()` method returns the response
 *     // from the API (an instance of IntrospectionResponse).
 *     $introspectionResponse = $validator->getIntrospectionResponse();
 *
 *     // When validate() method returns false, `getErrorResponse()`
 *     // returns an error response (an instance of Response) that
 *     // complies with RFC 6750.
 *     $response = $validator->getErrorResponse();
 *
 *     // Return the error response to the client application.
 *     return $response;
 * }
 *
 * // The access token is valid. The instance returned from
 * // `getIntrospectionResponse()` holds information about the access token.
 * $info = $validator->getIntrospectionResponse();
 *
 * // For example, the ID of the client application to which the access has
 * // been issued.
 * $clientId = $info->getClientId();
 * ```
 *
 * `create()` method is a shortcut method to create a validator.
 *
 * ```
 * // Create a validator.
 * $validator = AccessTokenValidator::create(
 *     $api, $request, $requiredScopes, $requiredSubject);
 *
 * // Validate the access token.
 * if ($validator->validate() === false)
 * {
 *     // Return an error response that complies with RFC 6750.
 *     return $validator->getErrorResponse();
 * }
 * ```
 */
class AccessTokenValidator
{
    private $api                    = null;  // \Authlete\Api\AuthleteApi
    private $accessToken            = null;  // string
    private $requiredScopes         = null;  // array of string
    private $requiredSubject        = null;  // string
    private $clientCertificate      = null;  // string
    private $valid                  = false; // boolean
    private $introspectionResponse  = null;  // \Authlete\Dto\IntrospectionResponse
    private $introspectionException = null;  // \Exception (or a subclass)
    private $errorResponse          = null;  // \Illuminate\Http\Response


    /**
     * Constructor.
     *
     * @param AutheteApi $api
     *     An implementation of the `AuthleteApi` interface.
     */
    public function __construct(AuthleteApi $api)
    {
        $this->api            = $api;
        $this->requiredScopes = array();
    }


    /**
     * Get the access token which was presented by the client application.
     *
     * @return string
     *     The access token.
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }


    /**
     * Set the access token which was presented by the client application.
     *
     * @param string $accessToken
     *     The access token.
     *
     * @return AccessTokenValidator
     *     `$this` object.
     */
    public function setAccessToken($accessToken)
    {
        ValidationUtility::ensureNullOrString('$accessToken', $accessToken);

        $this->accessToken = $accessToken;

        return $this;
    }


    /**
     * Get the scopes which are required to access the protected resource
     * endpoint.
     *
     * @return string[]
     *     The required scopes. This method always returns an array
     *     and never returns `null`.
     */
    public function getRequiredScopes()
    {
        return $this->requiredScopes;
    }


    /**
     * Set the scopes which are required to access the protected resource
     * endpoint.
     *
     * @param string[] $requiredScopes
     *     The required scopes.
     *
     * @return AccessTokenValidator
     *     `$this` object.
     */
    public function setRequiredScopes(array $requiredScopes = null)
    {
        ValidationUtility::ensureNullOrArrayOfString('$requiredScopes', $requiredScopes);

        if (is_null($requiredScopes))
        {
            $requiredScopes = array();
        }

        $this->requiredScopes = $requiredScopes;
    }


    /**
     * Get the subject (= the unique identifier of a user) which is required
     * to be associated with the access token.
     *
     * @return string
     *     The required subject.
     */
    public function getRequiredSubject()
    {
        return $this->requiredSubject;
    }


    /**
     * Set the subject (= the unique identifier of a user) which is required
     * to be associated with the access token.
     *
     * @param string $requiredSubject
     *     The required subject.
     *
     * @return AccessTokenValidator
     *     `$this` object.
     */
    public function setRequiredSubject($requiredSubject)
    {
        ValidationUtility::ensureNullOrString('$requiredSubject', $requiredSubject);

        $this->requiredSubject = $requiredSubject;

        return $this;
    }


    /**
     * Get the client certificate which the client application presented
     * at the protected resource endpoint.
     *
     * @return string
     *     The client certificate in PEM format.
     */
    public function getClientCertificate()
    {
        return $this->clientCertificate;
    }


    /**
     * Set the client certificate which the client application presented
     * at the protected resource endpoint.
     *
     * @param string $clientCertificate
     *     The client certificate in PEM format.
     *
     * @return AccessTokenValidator
     *     `$this` object.
     */
    public function setClientCertificate($clientCertificate)
    {
        ValidationUtility::ensureNullOrString('$clientCertificate', $clientCertificate);

        $this->clientCertificate = $clientCertificate;

        return $this;
    }


    /**
     * Create a validator.
     *
     * @param AuthleteApi $api
     *     An implementation of the `AuthleteApi` interface.
     *
     * @param Request $request
     *     A request from a client application.
     *
     * @param string[] $requiredScopes
     *     Scopes which are required to access the protected resource endpoint.
     *     This argument is optional and its default value is `null`.
     *
     * @param string $requiredSubject
     *     Subject (= unique user identifier) which is required to be
     *     associated with the access token. This argument is optional and
     *     its default value is `null`.
     *
     * @return AccessTokenValidator
     *     A new `AccessTokenValidator` instance.
     */
    public static function create(
        AuthleteApi $api, Request $request, array $requiredScopes = null,
        $requiredSubject = null)
    {
        // The access token contained in the request.
        $accessToken = WebUtility::extractAccessToken($request);

        // TODO
        // The client certificate contained in the request.
        $clientCertificate = null;

        return (new AccessTokenValidator($api))
            ->setAccessToken($accessToken)
            ->setRequiredScopes($requiredScopes)
            ->setRequiredSubject($requiredSubject)
            ->setClientCertificate($clientCertificate)
            ;
    }


    /**
     * Get the result of the access token validation.
     *
     * After the call to `validate()` method, this method returns the same
     * value which was returned by `validate()`.
     *
     * On entry of `validate()` method, this property is reset to `false`.
     *
     * @return boolean
     *     `true` if the access token is valid (= has the right to access
     *     the protected resource endpoint).
     */
    public function isValid()
    {
        return $this->valid;
    }


    /**
     * Get the response from Authlete's /api/auth/introspection API.
     *
     * `validate()` method internally calls `/api/auth/introspection` API.
     * This method returns the response from the API call.
     *
     * Note that this property remains `null` if the API call threw an
     * exception. In the error case, `getIntrospectionException()` returns
     * a non-null value.
     *
     * On entry of `validate()` method, this property is reset to `null`.
     *
     * @return IntrospectionResponse
     *     The response from Authlete's `/api/auth/introspection` API.
     */
    public function getIntrospectionResponse()
    {
        return $this->introspectionResponse;
    }


    /**
     * Get the exception which was raised during the call to Authlete's
     * /api/auth/introspection API.
     *
     * `validate()` method internally calls Authlete's /api/auth/introspection
     * API. If the API call threw an exception, the exception is set to this
     * property.
     *
     * Note that this property remains `null` if the API call succeeded.
     * In the successful case, `getIntrospectionResponse()` returns a non-null
     * value.
     *
     * On entry of `validate()` method, this property is reset to `null`.
     *
     * @return Exception
     *     The exception raised during the call to Authlete's
     *     `/api/auth/authorization` API.
     */
    public function getIntrospectionException()
    {
        return $this->introspectionException;
    }


    /**
     * Get the error response that the protected resource endpoint should
     * return to the client application.
     *
     * If the result of `validate()` is `false`, an error response is generated
     * and set to this property.
     *
     * Note that this property remains `null` if the result of `validate()` is
     * `true`.
     *
     * On entry of `validate()` method, this property is reset to `null`.
     *
     * @return Response
     *     An error response that complies with
     *     [RFC 6750](https://tools.ietf.org/html/rfc6750) (The OAuth 2.0
     *     Authorization Framework: Bearer Token Usage).
     */
    public function getErrorResponse()
    {
        return $this->errorResponse;
    }


    /**
     * Validate the access token.
     *
     * On entry, as the first step, the implementation of this method resets
     * the following properties to `false` or `null`.
     *
     * * `valid`
     * * `introspectionResponse`
     * * `introspectionException`
     * * `errorResponse`
     *
     * Then, this method internally calls Authlete's `/api/auth/introspection`
     * API to get information about the access token.
     *
     * If the API call failed, the exception thrown by the API call is set to
     * the `introspectionException` property, an error response (500 Internal
     * Server Error) is set to the `errorResponse` property, and `false` is
     * set to the `valid` property. Then, this method returns `false`.
     *
     * If the API call succeeded, the response from the API is set to the
     * `introspectionResponse` property. Then, the implementation of this
     * method checks the value of the `action` parameter in the response from
     * the API.
     *
     * If the value of the `action` parameter is `OK`, this method sets `true`
     * to the `valid` property and returns `true`.
     *
     * If the value of the `action` parameter is not `OK`, this method builds
     * an error response that should be returned to the client application and
     * sets it to the `errorResponse` property. Then, this method sets `false`
     * to the `valid` property and returns `false`.
     *
     * This method returns `true` if the access token is valid. "Valid" here
     * means that the access token exists, has not expired, covers all the
     * required scopes (if required scopes have been set by `setRequiredScopes()`
     * method), and is associated with the required subject (if a required
     * subject has been set by `setRequiredSubject()` method).
     *
     * In addition, if the access token is bound to a client certificate (see
     * "OAuth 2.0 Mutual TLS Client Authentication and Certificate Bound Access"
     * for details), it is checked whether the client certificate (set by
     * `setClientCertificate()` method) is identical to the bound certificate.
     *
     * @return boolean
     *     `true` if the access token is valid (= has the right to access the
     *     protected resource endpoint).
     */
    public function validate()
    {
        // Clear properties that may have been set by the previous call.
        $this->valid                  = false;
        $this->introspectionResponse  = null;
        $this->introspectionException = null;
        $this->errorResponse          = null;

        try
        {
            // Call Authlete's /api/auth/introspection API.
            $this->introspectionResponse = $this->callIntrospectionApi();
        }
        catch (Exception $cause)
        {
            // The API call failed.
            $this->introspectionException = $cause;
            $this->errorResponse = $this->buildErrorResponseFromException($cause);
            $this->valid = false;
            return false;
        }

        // The 'action' parameter in the response from /api/auth/introspection
        // denotes the next action the protected resource endpoint should take.
        $action = $this->introspectionResponse->getAction();

        switch ($action)
        {
            case IntrospectionAction::$OK:
                // The access token is valid.
                $this->valid = true;
                return true;

            default:
                // The access token is not valid, or an unexpected error occurred.
                $this->errorResponse =
                    $this->buildErrorResponseFromIntrospectionResponse(
                        $this->introspectionResponse);
                $this->valid = false;
                return false;
        }
    }


    private function callIntrospectionApi()
    {
        // Prepare a request to /api/auth/introspection API.
        $request = (new IntrospectionRequest())
            ->setToken($this->accessToken)
            ->setScopes($this->requiredScopes)
            ->setSubject($this->requiredSubject)
            ->setClientCertificate($this->clientCertificate)
            ;

        // Call /api/auth/introspection API.
        return $this->api->introspection($request);
    }


    private function buildErrorResponseFromException(Exception $exception)
    {
        // The error message set to the WWW-Authenticate header.
        $challenge = 'Bearer error="server_error",'
                   . 'error_description="Introspection API call failed."';

        // Build a response that complies with RFC 6750.
        return ResponseUtility::wwwAuthenticate(
            Response::HTTP_INTERNAL_SERVER_ERROR, $challenge);
    }


    private function buildErrorResponseFromIntrospectionResponse(
        IntrospectionResponse $response)
    {
        // The 'action' parameter in the response from Authlete's
        // /api/auth/introspection API denotes the next action that the
        // protected resource endpoint should take.
        $action = $response->getAction();

        $statusCode = 0;

        switch ($action)
        {
            case IntrospectionAction::$INTERNAL_SERVER_ERROR:
                $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
                break;

            case IntrospectionAction::$BAD_REQUEST:
                $statusCode = Response::HTTP_BAD_REQUEST;
                break;

            case IntrospectionAction::$UNAUTHORIZED:
                $statusCode = Response::HTTP_UNAUTHORIZED;
                break;

            case IntrospectionAction::$FORBIDDEN:
                $statusCode = Response::HTTP_FORBIDDEN;
                break;

            default:
                // This should not happen.
                $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
                break;
        }

        // In error cases, the 'responseContent' parameter in the response
        // from Authlete's /api/auth/introspection API contains a value for
        // the WWW-Authenticate header.
        $challenge = $response->getResponseContent();

        // Build a response that complies with RFC 6750.
        return ResponseUtility::wwwAuthenticate($statusCode, $challenge);
    }
}
?>
