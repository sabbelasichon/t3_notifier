<?php

declare(strict_types=1);

/*
 * This file is part of the "t3_notifier" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * License.md file that was distributed with this source code.
 */

return [
    'chatter_transports' => [
        'slack' => 'null://null',
    ],
    'texter_transports' => [
        'twilio' => 'null://null',
    ],
    'channel_policy' => [
        'high' => ['chat/slack'],
    ],
];
