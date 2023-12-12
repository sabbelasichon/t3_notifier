<?php

declare(strict_types=1);

/*
 * This file is part of the "t3_notifier" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Ssch\T3Notifier\Tests\Functional\Logger\Writer;

use Ssch\T3Notifier\Tests\Functional\Fixtures\Extensions\t3_notifier_test\Classes\LoggerService;
use Symfony\Component\Notifier\EventListener\NotificationLoggerListener;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

final class NotifierWriterTest extends FunctionalTestCase
{
    private LoggerService $loggerService;

    private NotificationLoggerListener $notificationLoggerListener;

    protected function setUp(): void
    {
        $this->initializeDatabase = false;
        $this->testExtensionsToLoad = [
            'typo3conf/ext/t3_notifier',
            'typo3conf/ext/t3_notifier/Tests/Functional/Fixtures/Extensions/t3_notifier_test',
        ];
        parent::setUp();
        $this->loggerService = $this->get(LoggerService::class);
        $this->notificationLoggerListener = $this->get('notifier.logger_notification_listener');
    }

    public function testThatALogRecordIsSendViaNotifier(): void
    {
        // Arrange
        $logMessage = 'An error occurred. Please send the message via the notifier component';

        // Act
        $this->loggerService->logError($logMessage);

        // Assert
        $events = $this->notificationLoggerListener->getEvents();
        $message = $events->getMessages()[0];

        self::assertSame($logMessage, $message->getSubject());
    }

    public function testThatLogRecordWithExceptionContextIsSendViaNotifier(): void
    {
        // Arrange
        $logMessage = 'An error occurred. Please send the message via the notifier component';
        $exception = new \RuntimeException('An exception was thrown');

        // Act
        $this->loggerService->logError($logMessage, [
            'exception' => $exception,
        ]);

        // Assert
        $events = $this->notificationLoggerListener->getEvents();
        $message = $events->getMessages()[0];

        self::assertSame('RuntimeException: ' . $exception->getMessage(), $message->getSubject());
    }
}
