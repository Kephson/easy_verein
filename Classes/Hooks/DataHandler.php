<?php

declare(strict_types=1);

namespace EHAERER\EasyVerein\Hooks;

use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\DataHandling\DataHandler as CoreDataHandler;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\Exception;
use EHAERER\EasyVerein\Service\WelcomeEmail;

/**
 * This file is part of the "Manage the members of the society" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2023-2024 Ephraim Härer <mail@ephra.im>, EPHRA.IM
 */

/**
 * Datahandler
 */
class DataHandler implements SingletonInterface
{
    /**
     * the extension key
     */
    const EXTKEY = 'easy_verein';

    /**
     * Extension settings
     * @var array
     */
    private array $extSettings = [];

    /**
     * check if there should be written special fields
     *
     * @param array $fieldArray
     * @param string $table
     * @param int|string $id
     * @param $parentObject CoreDataHandler
     *
     * @return void
     * @throws Exception
     * @throws TransportExceptionInterface
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public function processDatamap_preProcessFieldArray(array &$fieldArray, string $table, $id, CoreDataHandler $parentObject): void
    {
        if ($table === 'fe_users') {
            if (isset($fieldArray['welcome_mail']) && (int)$fieldArray['welcome_mail'] === 1) {

                $this->extSettings = GeneralUtility::makeInstance(ExtensionConfiguration::class)->get(self::EXTKEY);

                if (WelcomeEmail::sendWelcomeEmail($fieldArray, $this->extSettings)) {
                    $message = GeneralUtility::makeInstance(FlashMessage::class,
                        $this->extSettings['welcome_mail_sent_text'],
                        $this->extSettings['welcome_mail_sent_title'],
                        AbstractMessage::OK,
                        true
                    );
                } else {
                    $message = GeneralUtility::makeInstance(FlashMessage::class,
                        $this->extSettings['welcome_mail_not_sent_text'],
                        $this->extSettings['welcome_mail_not_sent_title'],
                        AbstractMessage::ERROR,
                        true
                    );
                }
                $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
                $messageQueue = $flashMessageService->getMessageQueueByIdentifier();
                $messageQueue->addMessage($message);
                $fieldArray['welcome_mail'] = 0;
                $fieldArray['welcome_mail_sent'] = time();
            }

        }
    }

}
