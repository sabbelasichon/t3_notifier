<?php

declare(strict_types=1);

/*
 * This file is part of the "t3_notifier" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * License.md file that was distributed with this source code.
 */

use Ssch\T3Notifier\Tests\Functional\Fixtures\Extensions\t3_notifier_test\Classes\BrowserChannelService;
use Ssch\T3Notifier\Tests\Functional\Fixtures\Extensions\t3_notifier_test\Classes\LoggerService;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(LoggerService::class)->public();
    $services->set(BrowserChannelService::class)->public();
};
