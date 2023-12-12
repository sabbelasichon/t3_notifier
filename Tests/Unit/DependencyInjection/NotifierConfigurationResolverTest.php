<?php

declare(strict_types=1);

/*
 * This file is part of the "t3_notifier" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Ssch\T3Notifier\Tests\Unit\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Ssch\T3Notifier\DependencyInjection\NotifierConfigurationResolver;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

final class NotifierConfigurationResolverTest extends TestCase
{
    private NotifierConfigurationResolver $subject;

    protected function setUp(): void
    {
        $this->subject = new NotifierConfigurationResolver();
    }

    public function testThatAnInvalidEmailThrowsAnException(): void
    {
        $this->expectException(InvalidOptionsException::class);

        $validConfiguration = [
            'admin_recipients' => [
                [
                    'email' => '',
                    'phone' => '',
                ],
            ],
        ];
        $this->subject->resolve($validConfiguration);
    }
}
