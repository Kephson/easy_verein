<?php

declare(strict_types=1);

namespace EHAERER\EasyVerein\Service;

use EHAERER\EasyVerein\Utility\ApiUtility;
use GuzzleHttp\Exception\GuzzleException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This file is part of the "easy_verein" extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2023-2026 Ephraim Härer <mail@ephra.im>, EPHRA.IM
 */

/**
 * UserService
 */
class UserService
{

    /**
     * the extension key
     */
    private const ExtKey = 'easy_verein';

    /**
     * Extension settings
     * @var array
     */
    private array $extSettings = [];

    /**
     * API Token
     * @var string
     */
    private string $token = '';

    /**
     * get the user data via API
     *
     * @param string $easyVereinPk
     * @return array
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws GuzzleException
     */
    public function getUserData(string $easyVereinPk): array
    {
        $this->extSettings = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get(self::ExtKey);
        $this->token = ApiUtility::getToken($this->extSettings);
        return $this->getMemberData($easyVereinPk);
    }

    /**
     * @param string $easyVereinPk
     * @return array
     * @throws GuzzleException
     */
    private function getMemberData(string $easyVereinPk): array
    {
        $allowedMemberFields = explode(',', $this->extSettings['easy_verein_member_fields']);
        $allowedContactFields = explode(',', $this->extSettings['easy_verein_contact_fields']);
        $userData = [];
        $uri = $this->extSettings['easy_verein_api_uri'] . '/' . 'member/' . $easyVereinPk;
        $member = ApiUtility::getApiResults($uri, $this->token, $this->extSettings);

        if (isset($member['contact_details'])) {
            foreach ($allowedMemberFields as $f) {
                if (isset($member[$f])) {
                    if (stripos($f, 'Date') === false) {
                        $userData[$f] = $member[$f];
                    } else {
                        $userData[$f] = date("d.m.Y", strtotime($member[$f]));
                    }
                }
            }
            $contactDetails = ApiUtility::getApiResults($member['contact_details'], $this->token, $this->extSettings);
            if ($contactDetails) {
                foreach ($allowedContactFields as $f) {
                    if (isset($contactDetails[$f])) {
                        if (stripos($f, 'Date') === false) {
                            $userData[$f] = $contactDetails[$f];
                        } else {
                            $userData[$f] = date("d.m.Y", strtotime($contactDetails[$f]));
                        }

                    }
                }
            }
        }

        return $userData;
    }

}
