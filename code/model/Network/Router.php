<?php

namespace Kelvinho\Notes\Network;

use Kelvinho\Notes\Singleton\Logs;

/**
 * Class Router. Contains multiple routes and will route to the correct location.
 *
 * @package Kelvinho\Notes\Network
 * @author Quang Ho <157239q@gmail.com>
 * @copyright Copyright (c) 2020 Quang Ho <https://github.com/157239n>
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 */
class Router {
    private RequestData $requestData;
    private Session $session;
    private array $routes;

    /**
     * Router constructor.
     * @param RequestData $requestData
     * @param Session $session
     */
    public function __construct(RequestData $requestData, Session $session) {
        $this->requestData = $requestData;
        $this->session = $session;
    }

    /**
     * Create a new GET route.
     *
     * @param string $identifier
     * @param callable $callback
     */
    public function get(string $identifier, callable $callback) {
        $this->routes[] = new Route($identifier, "GET", $callback);
    }

    /**
     * Create multiple new GET routes with the same callback.
     *
     * @param array $identifiers
     * @param callable $callback
     */
    public function getMulti(array $identifiers, callable $callback) {
        foreach ($identifiers as $identifier) {
            $this->routes[] = new Route($identifier, "GET", $callback);
        }
    }

    /**
     * Create a new POST route.
     *
     * @param string $identifier
     * @param callable $callback
     */
    public function post(string $identifier, callable $callback) {
        $this->routes[] = new Route($identifier, "POST", $callback);
    }

    /**
     * Create multiple new POST routes with the same callback.
     *
     * @param array $identifiers
     * @param callable $callback
     */
    public function postMulti(array $identifiers, callable $callback) {
        foreach ($identifiers as $identifier) {
            $this->routes[] = new Route($identifier, "POST", $callback);
        }
    }

    /**
     * Create a new GET and POST route.
     *
     * @param string $identifier
     * @param callable $callback
     */
    public function getPost(string $identifier, callable $callback) {
        $this->get($identifier, $callback);
        $this->post($identifier, $callback);
    }

    /**
     * Create multiple new GET and POST routes with the same callback.
     *
     * @param array $identifiers
     * @param callable $callback
     */
    public function getPostMulti(array $identifiers, callable $callback) {
        $this->getMulti($identifiers, $callback);
        $this->postMulti($identifiers, $callback);
    }

    /**
     * Look for the good route and execute it.
     * @noinspection PhpUnusedParameterInspection
     */
    public function run(): void {
        foreach ($this->routes as $route) {
            /** @var Route $route */
            if (!$route->matches($this->requestData->getExplodedPath(), $this->requestData->getRequestMethod())) continue;
            // if a route returns true, then exits out and executes the default action
            if ($route->run()) break;
            return;
        }

        /*
        $stub = explode("/", trim($this->session->get("remoteFull"), "/"));
        array_pop($stub);
        $uriStub = explode("/" . CHARACTERISTIC_HASH, $this->requestData->serverCheck("REQUEST_URI"))[0];
        if ($this->session->has("remote"))
            Header::redirectBare(implode("/", $stub) . $uriStub);
        /**/
        //readfile($this->session->get("remote") . $this->requestData->serverCheck("REQUEST_URI"));
        //$this->requestData->rightHost() ? Header::redirectToHome() : Header::notFound();
        //\header("Location: http://google.com", true, 308);

        //Header::redirectBare($this->redirectUrl());
        $redirectUrl = $this->redirectUrl();
        //Logs::log($redirectUrl);
        /*
        \header("Content-type: " . mime_content_type($redirectUrl));
        Logs::log($redirectUrl);
        readfile($redirectUrl);
/**/

        header_remove();
        $curl = curl_init($redirectUrl);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_HEADERFUNCTION, function ($curl, $header) {
            if (strpos($header, "Content-Type") === 0) header($header, true);
            return strlen($header);
        });
        curl_exec($curl);
        if (curl_error($curl)) Logs::log("Curl error");
        curl_close($curl);

    }

    // functions below are for dealing with extracting stub only

    /**
     * Gets the redirected URL using a complicated mechanism.
     *
     * @return string
     */
    private function redirectUrl(): string {
        /* remote full looks like https://ruder.io/optimizing-gradient-descent/index.html
        now if the document is requesting ../assets, then we should redirect to https://ruder.io/assets, ...
        however, because we don't really have access to the "..", there is no way directly to redirect to the appropriate
        resource but, the stable hidden url is RANDOM_HASH/9/8/7/6/5/4/3/2/1/0/9/8/7/6/5/4/3/2/1/site. This way, by reading off the remaining
        numbers in the url, we can know how many ".." are there, and then we can do the same with the remote full, to pinpoint
        the resource we want
        */

        $remote = $this->session->getCheck("remote"); // looks like https://ruder.io/optimizing-gradient-descent/index.html
        $requestUri = $this->requestData->serverCheck("REQUEST_URI"); // looks like /4b6fb4.../9/8/7/6/5/4/3/assets/css/style.css
        $backNavs = $this->backNavs($requestUri);
        $host = $this->applyBackNavs($remote, $backNavs);
        $request = $this->stripNavs($requestUri, $backNavs);/*
        $method = $this->requestData->method();
        Logs::log("Remote: $remote\nMethod: $method\nHost: $host\nRequest: $request\nBack navs: $backNavs\nRequest URI: $requestUri\nCombined: $host/$request");/**/
        return "$host/$request";
    }

    /**
     * Get the number of back navigations involved with "/4b6fb4.../9/8/.../1/0/9/8/7/6/5/4/3/assets/css/style.css". This case is 2.
     *
     * @param string $requestUri
     * @return int Number of back navigations. 0 if on same directory, -1 if it doesn't use the scheme at all
     */
    private function backNavs(string $requestUri): int {
        $fragments = explode("/", trim($requestUri, "/"));
        if ($fragments[0] !== CHARACTERISTIC_HASH) return -1;
        for ($i = PADDING_NUMBER - 1; $i >= 1; $i--)
            if (((int)$fragments[PADDING_NUMBER - $i]) != $i % 10) return $i;
        return 0;
    }

    /**
     * Strips away navigation bits, from "/4b6fb4.../9/8/.../1/0/9/8/7/6/5/4/3/assets/css/style.css" to "assets/css/style.css".
     * Expected to be used with backNavs().
     *
     * @param string $requestUri
     * @param int $backNavs
     * @return string
     */
    private function stripNavs(string $requestUri, int $backNavs): string {
        if ($backNavs === -1) return ltrim($requestUri, "/");
        $remaining = [];
        $fragments = explode("/", trim($requestUri, "/"));
        for ($i = PADDING_NUMBER - $backNavs; $i < count($fragments); $i++)
            $remaining[] = $fragments[$i];
        return implode("/", $remaining);
    }

    /**
     * Apply back navigation to a URL, so that https://ruder.io/optimizing-gradient-descent/index.html and backNavs = 1
     * will spit out https://ruder.io.
     *
     * @param string $url
     * @param int $backNavs
     * @return string
     */
    private function applyBackNavs(string $url, int $backNavs): string {
        if ($backNavs === -1) return "http://" . parse_url($url)["host"];
        $fragments = explode("/", $url); // intentionally don't trim
        for ($i = 0; $i < $backNavs + 1; $i++) array_pop($fragments);
        return implode("/", $fragments);
    }
}
