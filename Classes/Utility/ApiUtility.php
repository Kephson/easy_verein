<?php

declare(strict_types=1);

namespace EHAERER\EasyVerein\Utility;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use TYPO3\CMS\Core\Registry;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This file is part of the "Manage the members of the society" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2024-2026 Ephraim Härer <mail@ephra.im>, EPHRA.IM
 */

/**
 * ApiUtility
 */
class ApiUtility
{

    /**
     * @param string $uri
     * @param string $token
     * @param array $extSettings
     * @return array
     * @throws GuzzleException
     */
    public static function getApiResults(string $uri, string $token, array $extSettings): array
    {
        $client = new Client();
        $response = $client->request('GET', $uri, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ]
        ]);

        $results = [];

        $statusCode = $response->getStatusCode();
        $contentType = $response->getHeaderLine('content-type');
        $rawBody = $response->getBody();
        if ($contentType === 'application/json' && $statusCode === 200) {
            $results = json_decode((string)$rawBody, true);
        }

        $tokenRefreshNeeded = $response->getHeaderLine('token_refresh_needed');
        if ($tokenRefreshNeeded === 'True') {
            self::refreshToken($token, $extSettings);
        }

        return $results;
    }

    /**
     * @param array $extSettings
     * @return string
     */
    public static function getToken(array $extSettings): string
    {
        $registry = GeneralUtility::makeInstance(Registry::class);
        $apiToken = $registry->get('easy_verein', 'api_token');
        if ($apiToken === null) {
            $apiToken = $extSettings['easy_verein_api_token'];
            $registry->set('easy_verein', 'api_token', $apiToken);
        }
        return $apiToken;
    }

    /**
     * @param string $token
     * @param array $extSettings
     * @return void
     * @throws GuzzleException
     */
    public static function refreshToken(string $token, array $extSettings): void
    {
        $uri = $extSettings['easy_verein_api_uri'] . '/refresh-token';
        $newTokenData = ApiUtility::getApiResults($uri, $token, $extSettings);
        if (isset($newTokenData['Bearer'])) {
            $registry = GeneralUtility::makeInstance(Registry::class);
            $registry->set('easy_verein', 'api_token', $newTokenData['Bearer']);
        }
    }

}
