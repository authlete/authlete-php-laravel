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
 * File containing the definition of WebUtility class.
 */


namespace Authlete\Laravel\Web;


use Illuminate\Http\Request;


/**
 * Web utility.
 */
class WebUtility
{
    /**
     * Extract the value of a header from a request.
     *
     * @param Request $request
     *     An HTTP request.
     *
     * @param string $headerName
     *     The name of an HTTP header.
     *
     * @return string
     *     The value of the header. If the number of headers having the header
     *     name included in the request is 2 or more, the value of the first
     *     header is returned.
     */
    public static function extractRequestHeaderValue(Request $request, $headerName)
    {
        // If the request does not have the header.
        if ($request->hasHeader($headerName) === false)
        {
            return null;
        }

        $value = $request->header($headerName);

        // If the type of the returned value is null or string.
        if (is_null($value) || is_string($value))
        {
            return $value;
        }

        // If the type of the returned value is not array.
        if (is_array($value) === false)
        {
            // Weird.
            return null;
        }

        // $request->header() returned an array.

        // The first element of the array.
        return $value[0];
    }
}
?>
