<?php

declare(strict_types=1);

namespace NassFloPetr\Grabber\Grabbers;

abstract class WebGrabber implements Grabber
{
    abstract public function getModels(?Filter $filter = null, ?string $response = null): iterable;

    abstract public function getCurlHandle(): \CurlHandle;

    protected function getResponse(?\CurlHandle $ch = null): string
    {
        if(\is_null($ch)) {
            $ch = $this->getCurlHandle();
        }

        $response = \curl_exec($ch);

        if (!$response || \curl_errno($ch) !== \CURLE_OK) {
            throw new \Exception(
                \sprintf(
                    'Open %s stream failed. %s.',
                    \curl_getinfo($ch, \CURLINFO_EFFECTIVE_URL),
                    \curl_error($ch)
                )
            );
        }

        if (\curl_getinfo($ch, \CURLINFO_RESPONSE_CODE) !== 200) {
            throw new \Exception(
                \sprintf(
                    'Open %s stream failed. Response code %d.',
                    \curl_getinfo($ch, \CURLINFO_EFFECTIVE_URL),
                    \curl_getinfo($ch, \CURLINFO_HTTP_CODE)
                )
            );
        }

        return $response;
    }
}