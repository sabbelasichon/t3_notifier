<?php

declare(strict_types=1);

/*
 * This file is part of the "t3_notifier" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

namespace Ssch\T3Notifier\Logger\Writer;

use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\RecipientInterface;
use TYPO3\CMS\Core\Log\LogRecord;
use TYPO3\CMS\Core\Log\Writer\WriterInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class NotifierWriter implements WriterInterface
{
    /**
     * @var string[]
     */
    private array $channels;

    /**
     * @var RecipientInterface[]
     */
    private array $recipients;

    private NotifierInterface $notifier;

    /**
     * @param array{"channels"?: string[], "recipients"?: RecipientInterface[]} $options
     */
    public function __construct(array $options = [], NotifierInterface $notifier = null)
    {
        $this->notifier = $notifier ?? GeneralUtility::getContainer()->get('notifier');
        $this->channels = $options['channels'] ?? [];
        $this->recipients = $options['recipients'] ?? [];
    }

    public function writeLog(LogRecord $record)
    {
        $context = $record->getData();
        $message = $record->getMessage();
        if ($context !== []) {
            if (isset($context['exception']) && $context['exception'] instanceof \Throwable) {
                $notification = Notification::fromThrowable($context['exception']);
            } else {
                $notification = new Notification($message);
            }
        } else {
            $notification = new Notification($message);
        }

        $notification->importanceFromLogLevelName($record->getLevel());

        $notification->channels($this->channels);

        $recipients = $this->recipients;
        if ($recipients === [] && method_exists($this->notifier, 'getAdminRecipients')) {
            $recipients = $this->notifier->getAdminRecipients();
        }

        $this->notifier->send($notification, ...$recipients);

        return $this;
    }
}
