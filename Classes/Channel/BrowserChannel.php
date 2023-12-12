<?php

declare(strict_types=1);

/*
 * This file is part of the "t3_notifier" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Ssch\T3Notifier\Channel;

use Symfony\Component\Notifier\Channel\ChannelInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Recipient\RecipientInterface;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class BrowserChannel implements ChannelInterface
{
    private const MAP_NOTIFICATION_IMPORTANCE = [
        Notification::IMPORTANCE_HIGH => FlashMessage::WARNING,
        Notification::IMPORTANCE_MEDIUM => FlashMessage::OK,
        Notification::IMPORTANCE_URGENT => FlashMessage::ERROR,
        Notification::IMPORTANCE_LOW => FlashMessage::INFO,
    ];

    private FlashMessageService $flashMessageService;

    public function __construct(FlashMessageService $flashMessageService)
    {
        $this->flashMessageService = $flashMessageService;
    }

    public function notify(
        Notification $notification,
        RecipientInterface $recipient,
        string $transportName = null
    ): void {
        $message = $notification->getSubject();
        if ($notification->getEmoji() !== '') {
            $message = $notification->getEmoji() . ' ' . $message;
        }

        $severity = self::MAP_NOTIFICATION_IMPORTANCE[$notification->getImportance()] ?? FlashMessage::INFO;

        $this->flashMessageService->getMessageQueueByIdentifier('notifier.template.flashMessages')
            ->addMessage(
                GeneralUtility::makeInstance(FlashMessage::class, $message, $notification->getContent(), $severity)
            );
    }

    public function supports(Notification $notification, RecipientInterface $recipient): bool
    {
        return true;
    }
}
