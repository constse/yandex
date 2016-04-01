<?php

namespace ConstSe\YandexBundle\Utils;

/**
 * @author Constantine Seleznyoff <const.seoff@gmail.com>
 */
class RemoteRequest
{
    const CONVERT_JSON = 1;
    const CONVERT_NONE = 0;
    const CONVERT_XML = 2;

    /**
     * @var \Exception
     */
    protected $lastError = null;

    /**
     * @return \Exception
     */
    public function getLastError()
    {
        return $this->$lastError;
    }

    /**
     * @param string $url
     * @param array $options
     * @param bool $returnHeaders
     * @param int $conversion
     * @return mixed
     */
    public function send($url, array $options = [], $returnHeaders = false, $conversion = self::CONVERT_NONE)
    {
        if (!$this->validateOptions($options)) {
            return false;
        }

        $response = $this->sendByCurl($url, $options, $returnHeaders, $conversion);

        if (false === $response) {
            $response = $this->sendByFileGetContents($url, $options, $returnHeaders, $conversion);
        }

        return $response;
    }

    /**
     * @param string $data
     * @param int $conversion
     * @return mixed
     */
    protected function convertResponse($data, $conversion = self::CONVERT_NONE)
    {
        if (false === $data) {
            return false;
        }

        try {
            switch ($conversion) {
                case self::CONVERT_JSON:
                    $data = json_decode($data, true);

                    if (JSON_ERROR_NONE !== json_last_error()) {
                        throw new \Exception(json_last_error_msg(), json_last_error());
                    }

                    return $data;
                case self::CONVERT_XML:
                    return new \SimpleXMLElement($data);
                default:
                    return $data;
            }
        } catch (\Exception $e) {
            $this->lastError = $e;

            return false;
        }
    }

    /**
     * @param array $options
     * @return array
     */
    protected function getContextOptions(array $options)
    {
        if (!count($options)) {
            return null;
        }

        $contextOptions = [];

        if (array_key_exists('method', $options)) {
            $contextOptions['method'] = $options['method'];
        }

        if (array_key_exists('headers', $options)) {
            $contextOptions['header'] = $options['headers'];
        }

        if (array_key_exists('data', $options)) {
            $contextOptions['content'] = $options['data'];
        }

        return array('http' => $contextOptions);
    }

    /**
     * @param array $options
     * @param bool $returnHeaders
     * @return array
     */
    protected function getCurlOptions(array $options = [], $returnHeaders = false)
    {
        $post = array_key_exists('method', $options) && $options['method'] == 'POST';
        $curlOptions = array(
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HEADER => $returnHeaders,
            CURLOPT_POST => $post,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false
        );

        if (array_key_exists('headers', $options)) {
            $curlOptions[CURLOPT_HTTPHEADER] = $options['headers'];
        }

        if (array_key_exists('data', $options)) {
            $curlOptions[CURLOPT_POST] = true;
            $curlOptions[CURLOPT_POSTFIELDS] = $options['data'];
        }

        return $curlOptions;
    }

    /**
     * @param array $rawHeaders
     * @return array
     */
    protected function parseRawHeaders(array $rawHeaders)
    {
        $headers = [];

        foreach ($rawHeaders as $header) {
            if (preg_match('/^(.+?):(.+)$/', $header, $matches)) {
                $headers[$matches[1]] = trim($matches[2]);
            } else {
                $headers[] = $header;
            }
        }

        return $headers;
    }

    /**
     * @param string $url
     * @param array $options
     * @param bool $returnHeaders
     * @param int $conversion
     * @return mixed
     */
    protected function sendByCurl($url, array $options = [], $returnHeaders = false, $conversion = self::CONVERT_NONE)
    {
        try {
            if (!function_exists('curl_init')) {
                throw new \Exception('CURL is not available');
            }

            $curl = curl_init($url);
            curl_setopt_array($curl, $this->getCurlOptions($options, $returnHeaders));
            $response = curl_exec($curl);
            $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
            curl_close($curl);

            return $returnHeaders ? [
                'headers' => $this->parseRawHeaders(explode("\r\n", substr($response, 0, $headerSize))),
                'body' => $this->convertResponse(
                    preg_replace('/^\r\n\r\n/', '', substr($response, $headerSize)),
                    $conversion
                )
            ] : $this->convertResponse($response, $conversion);
        } catch (\Exception $e) {
            $this->lastError = $e;

            return false;
        }
    }

    /**
     * @param string $url
     * @param array $options
     * @param bool $returnHeaders
     * @param int $conversion
     * @return mixed
     */
    protected function sendByFileGetContents($url, array $options = [], $returnHeaders = false, $conversion = self::CONVERT_NONE)
    {
        try {
            $response = file_get_contents($url, false, stream_context_create($this->getContextOptions($options)));

            return $returnHeaders ? [
                'headers' => $this->parseRawHeaders($http_response_header),
                'body' => $this->convertResponse($response, $conversion)
            ] : $this->convertResponse($response, $conversion);
        } catch (\Exception $e) {
            $this->lastError = $e;

            return false;
        }
    }

    /**
     * @param array $options
     * @return bool
     */
    protected function validateOptions(array $options)
    {
        try {
            $keys = ['method', 'headers', 'data'];

            foreach (array_keys($options) as $key) {
                if (!in_array($key, $keys)) {
                    throw new \Exception(sprintf('Unknown option "%s"', $key));
                }
            }

            return true;
        } catch (\Exception $e) {
            $this->$lastError = $e;

            return false;
        }
    }
}
