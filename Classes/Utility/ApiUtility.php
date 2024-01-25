<?php

declare(strict_types=1);

namespace EHAERER\EasyVerein\Utility;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * This file is part of the "Manage the members of the society" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2024 Ephraim Härer <mail@ephra.im>, EPHRA.IM
 */

/**
 * ApiUtility
 */
class ApiUtility
{

    /**
     * @param string $uri
     * @param string $token
     * @return array
     * @throws GuzzleException
     */
    public static function getApiResults(string $uri, string $token): array
    {
        $client = new Client();
        $response = $client->request('GET', $uri, [
            'headers' => [
                'Authorization' => 'Token ' . $token,
            ]
        ]);

        $results = [];
        $statusCode = $response->getStatusCode();
        $contentType = $response->getHeaderLine('content-type');
        $rawBody = $response->getBody();
        if ($contentType === 'application/json' && $statusCode === 200) {
            $results = json_decode((string)$rawBody, true);
        }

        return $results;
    }

}
