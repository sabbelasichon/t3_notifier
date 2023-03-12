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
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class NotifierConfigurationResolver
{
    /**
     * @param array<mixed> $configuration
     *
     * @return array<mixed>
     */
    public function resolve(array $configuration): array
    {
        $resolver = new OptionsResolver();
        $this->configureDefaultOptions($resolver);

        return $resolver->resolve($configuration);
    }

    private function configureDefaultOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('notification_on_failed_messages', false);
        $resolver->setDefault('chatter_transports', []);
        $resolver->setDefault('texter_transports', []);
        $resolver->setDefault('channel_policy', []);
        $resolver->setAllowedTypes('notification_on_failed_messages', 'bool');

        $resolver->setDefault('admin_recipients', function (OptionsResolver $adminRecipientsResolver) {
            $adminRecipientsResolver->setPrototype(true)
                ->setRequired('email')
                ->setAllowedValues('email', function ($email) {
                    if ($email === null) {
                        return false;
                    }
                    if ($email === '') {
                        return false;
                    }

                    return GeneralUtility::validEmail($email);
                })
                ->setDefault('phone', '');
        });
    }
}
