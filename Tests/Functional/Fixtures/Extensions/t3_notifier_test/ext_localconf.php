<?php

$GLOBALS['TYPO3_CONF_VARS']['LOG']['Ssch']['writerConfiguration'] = [
    \TYPO3\CMS\Core\Log\LogLevel::ERROR => [
        \Ssch\T3Notifier\Logger\Writer\NotifierWriter::class => [
            'recipients' => [
                new \Symfony\Component\Notifier\Recipient\Recipient('max.mustermann@domain.com', '123455678'),
            ],
        ],
    ],
];
