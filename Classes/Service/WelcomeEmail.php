<?php

declare(strict_types=1);

namespace EHAERER\EasyVerein\Service;

use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core\Mail\FluidEmail;
use TYPO3\CMS\Core\Mail\Mailer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * This file is part of the "Manage the members of the society" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2023-2024 Ephraim Härer <mail@ephra.im>, EPHRA.IM
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
     * @throws TransportExceptionInterface|Exception
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
     * @throws Exception
     */
    public static function generateLinkToPasswordForgottenPage(array $extSettings): string
    {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $uriBuilder = $objectManager->get(UriBuilder::class);
        return $uriBuilder
            ->reset()
            ->setTargetPageUid((int)$extSettings['password_forget_page_uid'])
            ->setArguments([
                'tx_felogin_login' => [
                    'controller' => 'PasswordRecovery',
                    'action' => 'recovery',
                ],
            ])
            ->setCreateAbsoluteUri(true)
            ->buildFrontendUri();
    }

}
