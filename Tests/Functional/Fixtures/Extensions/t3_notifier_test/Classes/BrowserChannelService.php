<?php

declare(strict_types=1);

/*
 * This file is part of the "t3_notifier" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * License.md file that was distributed with this source code.
 */

namespace Ssch\T3Notifier\Tests\Functional\Fixtures\Extensions\t3_notifier_test\Classes;

use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;

final class BrowserChannelService
{
    private NotifierInterface $notifier;

    public function __construct(NotifierInterface $notifier)
    {
        $this->notifier = $notifier;
    }

    public function addMessageToBrowser(string $message): void
    {
        $this->notifier->send(new Notification($message, ['browser']));
    }
}
