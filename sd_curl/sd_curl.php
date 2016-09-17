<?php

class SDCurl
{

    /**
     * PHP CURL resource
     *
     * @var resource
     */
    private $curl;

    /**
     * Array of header strings
     *
     * @var array
     */
    private $headers = [];

    /**
     * Request body string
     *
     * @var string
     */
    private $payload;


    /**
     * Reponse body string
     *
     * @var string
     */
    private $response_body;

    /**
     * SDCurl constructor.
     * @param string $url
     */
    public function __construct($url = null)
    {
        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, true);

        if ($url)
            $this->set_url($url);
    }


    /**
     * Sets whether CURL is required to verify peer.
     *
     * @param bool $value
     * @return null
     */
    public function verify_ssl($value = true)
    {
        if (!is_bool($value))
            return null;

        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, $value);
    }


    /**
     * Sets custom timeout for the transfer ($timeout in seconds).
     *
     * @param int $timeout
     * @return null
     */
    public function set_timeout($timeout)
    {
        if (!is_int($timeout))
            return null;

        curl_setopt($this->curl, CURLOPT_TIMEOUT, $timeout);
    }

    /**
     * Closes CURL resource and the connection.
     */
    public function __destruct()
    {
        curl_close($this->curl);
    }


    /**
     * Sets URL to make request to. Throws Exception if URL is empty or cannot be validated.
     *
     * @param string $url
     * @throws Exception
     */
    public function set_url($url)
    {
        if (filter_var($url, FILTER_VALIDATE_URL) != true)
            throw  new Exception("'$url' is not a valid URL");

        curl_setopt($this->curl, CURLOPT_URL, $url);
    }


    /**
     * Returns current information about CURL transfer
     * (http://php.net/manual/en/function.curl-getinfo.php)
     *
     * @return array
     */
    public function get_info()
    {
        return curl_getinfo($this->curl);
    }


    /**
     * Sets (appends) request headers to the request.
     *
     * @param array $headers
     * @throws Exception
     */
    public function add_headers($headers)
    {
        if (!is_array($headers) || empty($headers))
            throw new Exception("Expected array of headers, " . gettype($headers) . " given");

        $this->headers = array_merge($this->headers, $headers);

        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $this->headers);
    }


    /**
     * Converts $data to a JSON string and uses it as a request body when get() or post() is called.
     *
     * @param $data
     * @throws Exception
     * @return null
     */
    public function set_json_body($data)
    {
        if (!is_array($data) || empty($data))
            return null;

        $data = json_encode($data);

        $this->add_headers([
            'Content-Length: ' . strlen($data),
            'Accept: application/json',
            'Content-Type: application/json'
        ]);

        $this->set_request_body($data);
    }


    /**
     * Loads $payload string into the instance that will be used as a request body when get() or post() is called.
     *
     * @param string $payload
     * @return null
     */
    public function set_request_body($payload)
    {
        if (!is_string($payload) || empty($payload))
            return null;

        $this->payload = $payload;

        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $this->payload);
    }


    /**
     * Makes a GET request and returns raw response.
     *
     * @return mixed
     */
    public function get()
    {
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "GET");
        return curl_exec($this->curl);
    }


    /**
     * Makes a POST request and returns raw response.
     *
     * @return mixed
     */
    public function post()
    {
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "POST");
        $this->response_body = curl_exec($this->curl);
        return $this->response_body;
    }


    /**
     * Returns received response code. Will return 0 if request has not yet been made.
     *
     * @return int
     */
    public function get_response_code()
    {
        return curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
    }


    /**
     * Rerurns response body string if request has been made, null otherwise
     *
     * @return string
     */
    public function get_reponse_body()
    {
        return $this->response_body;
    }


    /**
     * Returns request payload if it has been set
     *
     * @return string
     */
    public function get_payload()
    {
        return $this->payload;
    }
}
