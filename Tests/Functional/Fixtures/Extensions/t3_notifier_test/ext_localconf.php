<?php

declare(strict_types=1);

/*
 * This file is part of the "t3_notifier" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Ssch\T3Notifier\Logger\Writer\NotifierWriter;
use Symfony\Component\Notifier\Recipient\Recipient;
use TYPO3\CMS\Core\Log\LogLevel;

$GLOBALS['TYPO3_CONF_VARS']['LOG']['Ssch']['writerConfiguration'] = [
    LogLevel::ERROR => [
        NotifierWriter::class => [
            'recipients' => [new Recipient('max.mustermann@domain.com', '123455678')],
        ],
    ],
];
