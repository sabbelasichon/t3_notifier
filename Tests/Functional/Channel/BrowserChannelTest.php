<?php

declare(strict_types=1);

/*
 * This file is part of the "t3_notifier" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Ssch\T3Notifier\Tests\Functional\Channel;

use Ssch\T3Notifier\Tests\Functional\Fixtures\Extensions\t3_notifier_test\Classes\BrowserChannelService;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

final class BrowserChannelTest extends FunctionalTestCase
{
    private BrowserChannelService $browserChannelService;

    private FlashMessageService $flashMessageService;

    protected function setUp(): void
    {
        $this->initializeDatabase = false;
        $this->testExtensionsToLoad = [
            'typo3conf/ext/t3_notifier',
            'typo3conf/ext/t3_notifier/Tests/Functional/Fixtures/Extensions/t3_notifier_test',
        ];

        parent::setUp();
        $this->browserChannelService = $this->get(BrowserChannelService::class);
        $this->flashMessageService = $this->get(FlashMessageService::class);
    }

    public function testThatFlashMessageIsEnqueuedSuccessfully(): void
    {
        // Act
        $this->browserChannelService->addMessageToBrowser('Message');

        // Assert
        self::assertCount(
            1,
            $this->flashMessageService->getMessageQueueByIdentifier('notifier.template.flashMessages')
        );
    }
}
