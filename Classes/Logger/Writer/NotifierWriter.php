<?php

declare(strict_types=1);

/*
 * This file is part of the "t3_notifier" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Ssch\T3Notifier\Logger\Writer;

use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use TYPO3\CMS\Core\Log\LogRecord;
use TYPO3\CMS\Core\Log\Writer\AbstractWriter;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class NotifierWriter extends AbstractWriter
{
    private NotifierInterface $notifier;

    /**
     * @param array<mixed> $options
     */
    public function __construct(array $options = [], NotifierInterface $notifier = null)
    {
        $this->notifier = $notifier ?? GeneralUtility::getContainer()->get('notifier');
        parent::__construct($options);
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

        $recipients = [];
        if (method_exists($this->notifier, 'getAdminRecipients')) {
            $recipients = $this->notifier->getAdminRecipients();
        }

        $this->notifier->send($notification, ...$recipients);

        return $this;
    }
}
