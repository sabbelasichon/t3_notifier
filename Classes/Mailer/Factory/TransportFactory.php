<?php

declare(strict_types=1);

/*
 * This file is part of the "t3_notifier" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * License.md file that was distributed with this source code.
 */

namespace Ssch\T3Notifier\Mailer\Factory;

use Symfony\Component\Mailer\Transport\TransportInterface;

final class TransportFactory
{
    private \TYPO3\CMS\Core\Mail\TransportFactory $transportFactory;

    public function __construct(\TYPO3\CMS\Core\Mail\TransportFactory $transportFactory)
    {
        $this->transportFactory = $transportFactory;
    }

    public function get(): TransportInterface
    {
        $mailSettings = (array) $GLOBALS['TYPO3_CONF_VARS']['MAIL'];
        return $this->transportFactory->get($mailSettings);
    }
}
