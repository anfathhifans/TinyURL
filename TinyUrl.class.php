<?php
namespace MyLib\tinyurl;

class TinyURL
{
    const DEBUG = false;
    const API_URL = 'http://tinyurl.com';
    const API_PORT = 80;
    const VERSION = '1.0.1';
    private $timeOut = 60;
    private $userAgent;

    public function __construct()
    {
    }

    public function short_create(string $long_url) {
        $url = self::API_URL . '/api-create.php?url=' . urlencode($long_url);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    public function create(string $url)
	{
		// build parameters
		$aParameters['url'] = $url;

		// make the call
		return (string) $this->doCall(self::API_URL .'/api-create.php', $aParameters);
	}

    public function reverse($url)
	{
		// redefine
		$url = (string) $url;

		// explode on .com
		$aChunks = explode('tinyurl.com/', $url);

		if(isset($aChunks[1]))
		{
			// rebuild url
			$requestUrl = 'http://preview.tinyurl.com/'.$aChunks[1];

			// make the call
			$response = $this->doCall($requestUrl);

			// init var
			$aMatches = array();

			// match
			preg_match('/redirecturl" href="(.*)">/', $response, $aMatches);

			// return if something was found
			if(isset($aMatches[1])) return (string) $aMatches[1];
		}

		// fallback
		return false;
	}

    private function doCall($url, $aParameters = [])
	{
		// redefine
		$url = (string) $url;
		$aParameters = (array) $aParameters;

		// rebuild url if we don't use post
		if(!empty($aParameters))
		{
			// init var
			$queryString = '';

			// loop parameters and add them to the queryString
			foreach($aParameters as $key => $value) $queryString .= '&'. $key .'='. urlencode(mb_convert_encoding($value, 'UTF-8', mb_list_encodings()));

			// cleanup querystring
			$queryString = trim($queryString, '&');

			// append to url
			$url .= '?'. $queryString;
		}

		// set options
		$options[CURLOPT_URL] = $url;
		$options[CURLOPT_PORT] = self::API_PORT;
		// $options[CURLOPT_USERAGENT] = $this->getUserAgent();
		$options[CURLOPT_FOLLOWLOCATION] = true;
		$options[CURLOPT_RETURNTRANSFER] = true;
		// $options[CURLOPT_TIMEOUT] = (int) $this->getTimeOut();

		// init
		$curl = curl_init();

		// set options
		curl_setopt_array($curl, $options);

		// execute
		$response = curl_exec($curl);
		$headers = curl_getinfo($curl);

		// fetch errors
		$errorNumber = curl_errno($curl);
		$errorMessage = curl_error($curl);

		// close
		curl_close($curl);

		// invalid headers
		if(!in_array($headers['http_code'], array(0, 200)))
		{
			// should we provide debug information
			if(self::DEBUG)
			{
				// make it output proper
				echo '<pre>';

				// dump the header-information
				var_dump($headers);

				// dump the raw response
				var_dump($response);

				// end proper format
				echo '</pre>';

				// stop the script
				exit;
			}

			// throw error
			throw new TinyUrlException(null, (int) $headers['http_code']);
		}

		// error?
		if($errorNumber != '') throw new TinyUrlException($errorMessage, $errorNumber);

		// return
		return $response;
	}

    public function getTimeOut()
	{
		return (int) $this->timeOut;
	}

    public function getUserAgent()
	{
		return (string) 'PHP TinyUrl/'. self::VERSION .' '. $this->userAgent;
	}

    public function setTimeOut($seconds)
	{
		$this->timeOut = (int) $seconds;
	}

    public function setUserAgent($userAgent)
	{
		$this->userAgent = (string) $userAgent;
	}

}

class TinyUrlException extends \Exception
{
	/**
	 * Http header-codes
	 *
	 * @var	array
	 */
	private $aStatusCodes = array(100 => 'Continue',
									101 => 'Switching Protocols',
									200 => 'OK',
									201 => 'Created',
									202 => 'Accepted',
									203 => 'Non-Authoritative Information',
									204 => 'No Content',
									205 => 'Reset Content',
									206 => 'Partial Content',
									300 => 'Multiple Choices',
									301 => 'Moved Permanently',
									301 => 'Status code is received in response to a request other than GET or HEAD, the user agent MUST NOT automatically redirect the request unless it can be confirmed by the user, since this might change the conditions under which the request was issued.',
									302 => 'Found',
									302 => 'Status code is received in response to a request other than GET or HEAD, the user agent MUST NOT automatically redirect the request unless it can be confirmed by the user, since this might change the conditions under which the request was issued.',
									303 => 'See Other',
									304 => 'Not Modified',
									305 => 'Use Proxy',
									306 => '(Unused)',
									307 => 'Temporary Redirect',
									400 => 'Bad Request',
									401 => 'Unauthorized',
									402 => 'Payment Required',
									403 => 'Forbidden',
									404 => 'Not Found',
									405 => 'Method Not Allowed',
									406 => 'Not Acceptable',
									407 => 'Proxy Authentication Required',
									408 => 'Request Timeout',
									409 => 'Conflict',
									411 => 'Length Required',
									412 => 'Precondition Failed',
									413 => 'Request Entity Too Large',
									414 => 'Request-URI Too Long',
									415 => 'Unsupported Media Type',
									416 => 'Requested Range Not Satisfiable',
									417 => 'Expectation Failed',
									500 => 'Internal Server Error',
									501 => 'Not Implemented',
									502 => 'Bad Gateway',
									503 => 'Service Unavailable',
									504 => 'Gateway Timeout',
									505 => 'HTTP Version Not Supported');


	/**
	 * Default constructor
	 *
	 * @return	void
	 * @param	string[optional] $message
	 * @param	int[optional] $code
	 */
	public function __construct($message = null, $code = null)
	{
		// set message
		if($message === null && isset($this->aStatusCodes[(int) $code])) $message = $this->aStatusCodes[(int) $code];

		// call parent
		parent::__construct((string) $message, $code);
	}
}

?>