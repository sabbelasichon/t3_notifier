<?php

declare(strict_types=1);

/*
 * This file is part of the "t3_notifier" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Ssch\T3Notifier\Tests\Functional\Fixtures\Extensions\t3_notifier_test\Classes;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

final class LoggerService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function logError(string $message, array $context = []): void
    {
        $this->logger->error($message, $context);
    }
}
