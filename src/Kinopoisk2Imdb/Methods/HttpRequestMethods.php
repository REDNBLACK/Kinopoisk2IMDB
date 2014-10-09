<?php
namespace Kinopoisk2Imdb\Methods;

/**
 * Class HttpRequestMethods
 * @package Kinopoisk2Imdb\Methods
 */
class HttpRequestMethods
{
    /**
     * cURL GET method constant
     */
    const CURL_METHOD_GET = 'GET';

    /**
     * cURL POST method constant
     */
    const CURL_METHOD_POST = 'POST';

    /**
     * @var resource
     */
    private $handle;

    /**
     * @var mixed
     */
    private $response;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->handle = curl_init();
    }

    /**
     * Set response
     * @param $response
     */
    public function setResponse($response)
    {
        $this->response = $response;
    }

    /**
     * Get response
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Set url and possibly query
     * @param string $url
     * @param mixed $query
     * @return $this
     */
    public function setUrl($url, $query = null)
    {
        if (is_array($query)) {
            $query = http_build_query($query);
        }

        curl_setopt($this->handle, CURLOPT_URL, $url . $query);
        $this->setType('GET');

        return $this;
    }

    /**
     * Set type of request and add POST data, if type POST
     * @param string $type
     * @param mixed $post_data
     * @return $this
     */
    public function setType($type, $post_data = null)
    {
        curl_setopt($this->handle, CURLOPT_CUSTOMREQUEST, $type);

        if ($type === self::CURL_METHOD_POST) {
            curl_setopt($this->handle, CURLOPT_POST, true);
            curl_setopt(
                $this->handle, CURLOPT_POSTFIELDS,
                is_array($post_data) ? http_build_query($post_data) : $post_data
            );
        }

        return $this;
    }

    /**
     * Set cookies
     * @param mixed $cookies
     * @return $this
     */
    public function setCookies($cookies)
    {
        curl_setopt($this->handle, CURLOPT_COOKIE, is_array($cookies) ? $this->httpBuildCookie($cookies) : $cookies);

        return $this;
    }

    /**
     * Set user agent
     * @param $user_agent
     * @return $this
     */
    public function setUserAgent($user_agent)
    {
        curl_setopt($this->handle, CURLOPT_USERAGENT, $user_agent);

        return $this;
    }

    /**
     * Set headers
     * @param array $headers
     * @return $this
     */
    public function setHeaders(array $headers)
    {
        curl_setopt($this->handle, CURLOPT_HTTPHEADER, $headers);

        return $this;
    }

    /**
     * Set option
     * @param $name
     * @param $value
     * @return $this
     */
    public function setOption($name, $value)
    {
        curl_setopt($this->handle, $name, $value);

        return $this;
    }

    /**
     * Set options from specified associative array
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options)
    {
        curl_setopt_array($this->handle, $options);

        return $this;
    }

    /**
     * Execute the query and set the response
     * @return $this
     */
    public function execute()
    {
        $this->setResponse(curl_exec($this->handle));

        return $this;
    }

    /**
     * Get info
     * @param $name
     * @return mixed
     */
    public function getInfo($name)
    {
        return curl_getinfo($this->handle, $name);
    }

    /**
     * Close handle of curl
     * @return $this
     */
    public function close()
    {
        curl_close($this->handle);

        return $this;
    }

    /**
     * Build cookie string from the specified associative array
     * @param array $cookies
     * @return string
     */
    public function httpBuildCookie(array $cookies)
    {
        $string = '';
        foreach ($cookies as $cookie_name => $cookie_value) {
            $string .= "{$cookie_name}={$cookie_value};";
        }

        return $string;
    }
} 
