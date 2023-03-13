# TYPO3 Symfony notifier adapter
Integrates Symfony Notifier into TYPO3
[https://symfony.com/doc/current/notifier.html](https://symfony.com/doc/current/notifier.html)

## Integration guide
The extension basically provides the same functionality as if you would use the notifier in the Symfony Framework.
In order to configure the notifier you have to put a Notifier.php file under the Configuration folder of an extension.

```php

return [
    'chatter_transports' => [
        'slack' => '%env(SLACK_DSN)%'
    ],
    #
    'texter_transports' => [
        'twilio' => '%env(TWILIO_DSN)%'
    ],
    # https://symfony.com/doc/current/notifier.html#configuring-channel-policies
    'channel_policy' => [
        'urgent' => ['sms', 'chat/slack', 'email'],
        'high' => ['chat/slack'],
        'medium' => ['browser']
    ]
];

```

In order to use the notifier you can inject it via Dependency Injection

```php

use Symfony\Component\Notifier\NotifierInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use Symfony\Component\Notifier\Notification\Notification;
final class SomeController extends ActionController
{
    public function __construct(private readonly NotifierInterface $notifier) {}
    public function someAction() {

        $notification = (new Notification('New Notification'))
            ->content('You got a new notification.')
            ->importance(Notification::IMPORTANCE_HIGH);

        $this->notifier->send($notification, new Recipient('max@mustermann.com'));
    }
}
```

This would send a new notification to the chat channel via the slack transport.

## LogWriter
The extension ships with a custom LogWriter to send LogRecords via the Notifier.
In order to use the LogWriter you can configure it i.e. via ext_localconf.php

```php
use Ssch\T3Notifier\Logger\Writer\NotifierWriter;
use Symfony\Component\Notifier\Recipient\Recipient;
use TYPO3\CMS\Core\Log\LogLevel;

$GLOBALS['TYPO3_CONF_VARS']['LOG']['Examples']['writerConfiguration'] = [
    LogLevel::ERROR => [
        NotifierWriter::class => [
             // Optional configuration
            'channels' => ['chat/slack']
            'recipients' => [new Recipient('max.mustermann@domain.com', '123455678')],
        ],
    ],
];
```

If you don´t pass the channels option, you have to configure your channel_policy.
The PSR-3 LogLevel will be translated to the importance level for the Notification.
I.e. if you would call $this->logger->error it would be translated to the importance level of high.

## BrowserChannel
[tbd]

## EmailNotification
[tbd]
