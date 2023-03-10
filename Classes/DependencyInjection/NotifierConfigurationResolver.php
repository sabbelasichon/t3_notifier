<?php

declare(strict_types=1);

/*
 * This file is part of the "t3_notifier" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Ssch\T3Notifier\DependencyInjection;

use Symfony\Component\OptionsResolver\OptionsResolver;

final class NotifierConfigurationResolver
{
    public function resolve(array $configuration): array
    {
        $resolver = new OptionsResolver();
        $this->configureDefaultOptions($resolver);

        return $resolver->resolve($configuration);
    }

    private function configureDefaultOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('notification_on_failed_messages', false);
        $resolver->setAllowedTypes('notification_on_failed_messages', 'bool');

        $resolver->setDefault('chatter_transports', function (OptionsResolver $resolver) {
            $resolver
                ->setPrototype(true);
        });
        $resolver->setDefault('texter_transports', function (OptionsResolver $resolver) {
            $resolver
                ->setPrototype(true);
        });
        $resolver->setDefault('channel_policy', function (OptionsResolver $resolver) {
            $resolver
                ->setPrototype(true);
        });
        // admin_recipients
    }
}
