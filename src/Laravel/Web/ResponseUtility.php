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
 * File containing the definition of ResponseUtility class.
 */


namespace Authlete\Laravel\Web;


use Illuminate\Http\Response;


/**
 * Utility to generate Response instances.
 *
 * All responses created by methods in this class has
 * `Cache-Control: no-store` and `Pragma: no-cache`.
 */
class ResponseUtility
{
    /**
     * Create a response having the given content formatted in
     * "application/json;charset=UTF-8" with the HTTP status code
     * "200 OK".
     *
     * @param string $content
     *     The content formatted in `application/json;charset=UTF-8`.
     *
     * @return Response
     *     An HTTP response with "200 OK" and JSON.
     */
    public static function okJson($content)
    {
        // 200 OK, application/json;charset=UTF-8
        return self::buildResponseJson(Response::HTTP_OK, $content);
    }


    /**
     * Create a response having the given content formatted in
     * "application/javascript;charset=UTF-8" with the HTTP status code
     * "200 OK".
     *
     * @param string $content
     *     The content formatted in `application/javascript;charset=UTF-8`.
     *
     * @return Response
     *     An HTTP response with "200 OK" and JavaScript.
     */
    public static function okJavaScript($content)
    {
        // 200 OK, application/javascript;charset=UTF-8
        return self::buildResponseJavaScript(Response::HTTP_OK, $content);
    }


    /**
     * Create a response having the given content formatted in
     * "application/jwt" with the HTTP status code
     * "200 OK".
     *
     * @param string $content
     *     The content formatted in `application/jwt`.
     *
     * @return Response
     *     An HTTP response with "200 OK" and JWT.
     */
    public static function okJwt($content)
    {
        // 200 OK, application/jwt
        return self::buildResponseJwt(Response::HTTP_OK, $content);
    }


    /**
     * Create a response having the given content formatted in
     * "text/html;charset=UTF-8" with the HTTP status code
     * "200 OK".
     *
     * @param string $content
     *     The content formatted in `text/html;charset=UTF-8`.
     *
     * @return Response
     *     An HTTP response with "200 OK" and HTML.
     */
    public static function okHtml($content)
    {
        // 200 OK, text/html;charset=UTF-8
        return self::buildResponseHtml(Response::HTTP_OK, $content);
    }


    /**
     * Create a response with the HTTP status code
     * "204 No Content".
     *
     * @return Response
     *     An HTTP response with "204 No Content".
     */
    public static function noContent()
    {
        // 204 No Content
        return self::buildResponseBase(Response::HTTP_NO_CONTENT);
    }


    /**
     * Create a response having the given "Location" header value
     * with the HTTP status code
     * "30 Found".
     *
     * @param string $location
     *     The value of the `Location` header.
     *
     * @return Response
     *     An HTTP response with "302 Found" and a `Location` header.
     */
    public static function location($location)
    {
        // 302 Found
        $response = self::buildResponseBase(Response::HTTP_FOUND);

        // Location: $location
        $response->headers->set('Location', $location);

        return $response;
    }


    /**
     * Create a response having the given content formatted in
     * "application/json;charset=UTF-8" with the HTTP status code
     * "400 Bad Request".
     *
     * @param string $content
     *     The content formatted in `application/json;charset=UTF-8`.
     *
     * @return Response
     *     An HTTP response with "400 Bad Request" and JSON.
     */
    public static function badRequest($content)
    {
        // 400 Bad Request, application/json;charset=UTF-8
        return self::buildResponseJson(Response::HTTP_BAD_REQUEST, $content);
    }


    /**
     * Create a response with the HTTP status code "401 Unauthorized"
     * and optionally with JSON.
     *
     * @param string $challenge
     *     The value of the `WWW-Authenticate` header.
     *
     * @param $content
     *     The content formatted in `application/json;charset=UTF-8'.
     *     This parameter is optional.
     *
     * @return Response
     *     An HTTP response with "401 Unauthorized" and optionally with JSON.
     */
    public static function unauthorized($challenge, $content = null)
    {
        // 401 Unauthorized with a WWW-Authenticate header.
        return self::wwwAuthenticate(Response::HTTP_UNAUTHORIZED, $challenge, $content);
    }


    /**
     * Create a response having the given content formatted in
     * "application/json;charset=UTF-8" with the HTTP status code
     * "403 Forbidden".
     *
     * @param string $content
     *     The content formatted in `application/json;charset=UTF-8`.
     *
     * @return Response
     *     An HTTP response with "403 Forbidden" and JSON.
     */
    public static function forbidden($content)
    {
        // 403 Forbidden, application/json;charset=UTF-8
        return self::buildResponseJson(Response::HTTP_FORBIDDEN, $content);
    }


    /**
     * Create a response having the given content formatted in
     * "application/json;charset=UTF-8" with the HTTP status code
     * "404 Not Found".
     *
     * @param string $content
     *     The content formatted in `application/json;charset=UTF-8`.
     *
     * @return Response
     *     An HTTP response with "404 Not Found" and JSON.
     */
    public static function notFound($content)
    {
        // 404 Not Found, application/json;charset=UTF-8
        return self::buildResponseJson(Response::HTTP_NOT_FOUND, $content);
    }


    /**
     * Create a response having the given content formatted in
     * "application/json;charset=UTF-8" with the HTTP status code
     * "500 Internal Server Error".
     *
     * @param string $content
     *     The content formatted in `application/json;charset=UTF-8`.
     *
     * @return Response
     *     An HTTP response with "500 Internal Server Error" and JSON.
     */
    public static function internalServerError($content)
    {
        // 500 Internal Server Error, application/json;charset=UTF-8
        return self::buildResponseJson(Response::HTTP_INTERNAL_SERVER_ERROR, $content);
    }


    /**
     * Create a response with a WWW-Authenticate header.
     *
     * @param integer $statusCode
     *     HTTP status code of the response.
     *
     * @param string $challenge
     *     The value of the `WWW-Authenticate` header.
     *
     * @param string $content
     *     The content formatted in `application/json;charset=UTF-8'.
     *     This parameter is optional.
     *
     * @return Response
     *     An HTTP response with the specified status code and a
     *     `WWW-Authenticate` header, and optionally with JSON.
     */
    public static function wwwAuthenticate($statusCode, $challenge, $content = null)
    {
        $response = null;

        if (is_null($content))
        {
            $response = self::buildResponseBase($statusCode);
        }
        else
        {
            $response = self::buildResponseJson($statusCode, $content);
        }

        // WWW-Authenticate: $challenge
        $response->headers->set('WWW-Authenticate', $challenge);

        return $response;
    }


    /**
     * Build a response which has the specified HTTP status code with
     * "Cache-Control: no-store" header and "Pragma: no-cache" header.
     *
     * @param integer $statusCode
     *     HTTP status code.
     *
     * @param string $content
     *     Response body.
     *
     * @param string $contentType
     *     The value of the `Content-Type` header.
     *
     * @param string $charset
     *     The value of the character set of the response.
     *
     * @return Response
     */
    private static function buildResponseBase(
        $statusCode, $content = null, $contentType = null, $charset = null)
    {
        $response = new Response();

        // HTTP status code.
        $response->setStatusCode($statusCode);

        // Cache-Control: no-store
        $response->headers->set('Cache-Control', 'no-store');

        // Pragma: no-cache
        $response->headers->set('Pragma', 'no-cache');

        // Content
        if (!is_null($content))
        {
            $response->setContent($content);
        }

        // Content-Type: $contentType
        if (!is_null($contentType))
        {
            $response->headers->set('Content-Type', $contentType);
        }

        // Charset
        if (!is_null($charset))
        {
            $response->setCharset($charset);
        }

        return $response;
    }


    /**
     * Build a response whose Content-Type is 'application/json'.
     */
    private static function buildResponseJson($statusCode, $content, $charset = 'UTF-8')
    {
        return self::buildResponseBase(
            $statusCode, $content, 'application/json', $charset);
    }


    /**
     * Build a response whose Content-Type is 'application/javascript'.
     */
    private static function buildResponseJavaScript($statusCode, $content, $charset = 'UTF-8')
    {
        return self::buildResponseBase(
            $statusCode, $content, 'application/javascript', $charset);

    }


    /**
     * Build a response whose Content-Type is 'application/jwt'.
     */
    private static function buildResponseJwt($statusCode, $content, $charset = null)
    {
        return self::buildResponseBase(
            $statusCode, $content, 'application/jwt', $charset);

    }


    /**
     * Build a response whose Content-Type is 'text/html'.
     */
    private static function buildResponseHtml($statusCode, $content, $charset = 'UTF-8')
    {
        return self::buildResponseBase(
            $statusCode, $content, 'text/html', $charset);
    }
}
?>
