<?php

declare(strict_types=1);

/*
 * This file is part of the "t3_notifier" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Ssch\T3Notifier\Mailer\Notification;

use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Notifier\Exception\InvalidArgumentException;
use Symfony\Component\Notifier\Message\EmailMessage;
use Symfony\Component\Notifier\Notification\EmailNotificationInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Recipient\EmailRecipientInterface;
use TYPO3\CMS\Core\Mail\FluidEmail;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MailUtility;

final class EmailNotification extends Notification implements EmailNotificationInterface
{
    public function asEmailMessage(EmailRecipientInterface $recipient, string $transport = null): ?EmailMessage
    {
        if ($recipient->getEmail() === '') {
            throw new InvalidArgumentException(sprintf('"%s" needs an email, it cannot be empty.', __CLASS__));
        }

        if (! class_exists(FluidEmail::class)) {
            $email = GeneralUtility::makeInstance(MailMessage::class);
            $email->to($recipient->getEmail())
                ->subject($this->getSubject())
                ->text($this->getContent() ?: $this->getSubject())
            ;
        } else {
            $email = GeneralUtility::makeInstance(FluidEmail::class);
            $email
                ->assignMultiple([
                    'headline' => $this->getSubject(),
                    'introduction' => $this->getContent() ?: $this->getSubject(),
                ])
                ->to($recipient->getEmail())
                ->subject($this->getSubject())
                ->text($this->getContent() ?: $this->getSubject());
        }

        if ($email instanceof Email) {
            // Ensure to always have a From: header set
            if ($email->getFrom() === []) {
                $address = MailUtility::getSystemFromAddress();
                if ($address) {
                    $name = MailUtility::getSystemFromName();
                    if ($name) {
                        $from = new Address($address, $name);
                    } else {
                        $from = new Address($address);
                    }
                    $email->from($from);
                }
            }
            if ($email->getReplyTo() === []) {
                $replyTo = MailUtility::getSystemReplyTo();
                if ($replyTo !== []) {
                    $address = key($replyTo);
                    if ($address === 0) {
                        $replyTo = new Address($replyTo[$address]);
                    } else {
                        $replyTo = new Address((string) $address, reset($replyTo));
                    }
                    $email->replyTo($replyTo);
                }
            }
        }

        return new EmailMessage($email);
    }
}
