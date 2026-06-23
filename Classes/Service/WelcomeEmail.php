<?php

declare(strict_types=1);

namespace EHAERER\EasyVerein\Service;

use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Mail\FluidEmail;
use TYPO3\CMS\Core\Mail\Mailer;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This file is part of the "Manage the members of the society" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2023-2024 Ephraim Härer <mail@ephra.im>, EPHRA.IM
 *
 * https://stackoverflow.com/questions/60724764/how-to-generate-frontend-uri-in-scheduler-command-typo3-9
 */

/**
 * WelcomeEmail
 */
class WelcomeEmail
{
    /**
     * send email to user
     *
     * @param array $fieldArray
     * @param array $extSettings
     *
     * @return bool
     * @throws SiteNotFoundException
     * @throws TransportExceptionInterface
     */
    public static function sendWelcomeEmail(array $fieldArray, array $extSettings): bool
    {
        if (!empty($fieldArray['email']) && !empty($fieldArray['name'])) {
            $email = GeneralUtility::makeInstance(FluidEmail::class);
            $email
                ->to($fieldArray['email'])
                ->from(new Address($extSettings['welcome_mail_from_email'], $extSettings['welcome_mail_from_name']))
                ->subject($extSettings['welcome_mail_from_subject'])
                ->format('both')
                ->setTemplate('Welcome')
                ->assignMultiple([
                    'user' => $fieldArray,
                    'pwForgetLink' => self::generateLinkToPasswordForgottenPage($extSettings),
                ]);
            GeneralUtility::makeInstance(Mailer::class)->send($email);
            return true;
        }
        return false;
    }

    /**
     * @param array $extSettings
     *
     * @return string
     * @throws SiteNotFoundException
     */
    public static function generateLinkToPasswordForgottenPage(array $extSettings): string
    {
        $pageUid = (int)$extSettings['password_forget_page_uid'];
        $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($pageUid);
        $arguments = [
            'tx_felogin_login' => [
                'controller' => 'PasswordRecovery',
                'action' => 'recovery',
            ],
        ];
        return (string)$site->getRouter()->generateUri((string)$pageUid, $arguments);
    }

}
