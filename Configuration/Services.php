<?php

declare(strict_types=1);

/*
 * This file is part of the "t3_notifier" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * License.md file that was distributed with this source code.
 */

use Ssch\T3Notifier\Channel\BrowserChannel;
use Ssch\T3Notifier\DependencyInjection\Compiler\NotifierCompilerPass;
use Ssch\T3Notifier\DependencyInjection\NotifierConfigurationResolver;
use Ssch\T3Notifier\Mailer\Factory\TransportFactory;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Notifier\Channel\ChannelPolicy;
use Symfony\Component\Notifier\Channel\ChatChannel;
use Symfony\Component\Notifier\Channel\EmailChannel;
use Symfony\Component\Notifier\Channel\PushChannel;
use Symfony\Component\Notifier\Channel\SmsChannel;
use Symfony\Component\Notifier\Chatter;
use Symfony\Component\Notifier\ChatterInterface;
use Symfony\Component\Notifier\EventListener\NotificationLoggerListener;
use Symfony\Component\Notifier\EventListener\SendFailedMessageToNotifierListener;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Notifier\Message\PushMessage;
use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Component\Notifier\Messenger\MessageHandler;
use Symfony\Component\Notifier\Notifier;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Texter;
use Symfony\Component\Notifier\TexterInterface;
use Symfony\Component\Notifier\Transport;
use Symfony\Component\Notifier\Transport\Transports;
use TYPO3\CMS\Core\Mail\Mailer;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_locator;

return static function (ContainerConfigurator $containerConfigurator, ContainerBuilder $containerBuilder): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->public()
        ->autowire()
        ->autoconfigure();

    // Test configuration ignore
    $containerConfigurator->import(__DIR__ . '/../Classes/Test/Configuration/Services.php', null, true);

    $services->load('Ssch\\T3Notifier\\', __DIR__ . '/../Classes/')->exclude([
        __DIR__ . '/../Classes/DependencyInjection',
        __DIR__ . '/../Classes/Test',
    ]);

    $services->set('notifier.mailer.transport', TransportInterface::class)
        ->factory([service(TransportFactory::class), 'get']);

    $services->set('event_dispatcher', EventDispatcher::class);

    if (interface_exists(\TYPO3\CMS\Core\Mail\MailerInterface::class)) {
        $services->set('mailer', \TYPO3\CMS\Core\Mail\MailerInterface::class);
    } else {
        $services->set('mailer', Mailer::class);
    }

    $services->set('notifier', Notifier::class)
        ->args([tagged_locator('notifier.channel', 'channel'), service('notifier.channel_policy')->ignoreOnInvalid()])
        ->alias(NotifierInterface::class, 'notifier')

        ->set('notifier.channel_policy', ChannelPolicy::class)
        ->args([[]])

        ->set('notifier.channel.browser', BrowserChannel::class)
        ->tag('notifier.channel', [
            'channel' => 'browser',
        ])

        ->set('notifier.channel.chat', ChatChannel::class)
        ->args([service('chatter.transports'), service('messenger.default_bus')->ignoreOnInvalid()])
        ->tag('notifier.channel', [
            'channel' => 'chat',
        ])

        ->set('notifier.channel.sms', SmsChannel::class)
        ->args([service('texter.transports'), service('messenger.default_bus')->ignoreOnInvalid()])
        ->tag('notifier.channel', [
            'channel' => 'sms',
        ])

        ->set('notifier.channel.email', EmailChannel::class)
        ->args([service('notifier.mailer.transport'), service('messenger.default_bus')->ignoreOnInvalid()])
        ->tag('notifier.channel', [
            'channel' => 'email',
        ])

        ->set('notifier.channel.push', PushChannel::class)
        ->args([service('texter.transports'), service('messenger.default_bus')->ignoreOnInvalid()])
        ->tag('notifier.channel', [
            'channel' => 'push',
        ])

        ->set('notifier.failed_message_listener', SendFailedMessageToNotifierListener::class)
        ->args([service('notifier')])

        ->set('chatter', Chatter::class)
        ->args([
            service('chatter.transports'),
            service('messenger.default_bus')
                ->ignoreOnInvalid(),
            service('event_dispatcher')
                ->ignoreOnInvalid(),
        ])

        ->alias(ChatterInterface::class, 'chatter')

        ->set('chatter.transports', Transports::class)
        ->factory([service('chatter.transport_factory'), 'fromStrings'])
        ->args([[]])

        ->set('chatter.transport_factory', Transport::class)
        ->args([tagged_iterator('chatter.transport_factory')])

        ->set('chatter.messenger.chat_handler', MessageHandler::class)
        ->args([service('chatter.transports')])
        ->tag('messenger.message_handler', [
            'handles' => ChatMessage::class,
        ])

        ->set('texter', Texter::class)
        ->args([
            service('texter.transports'),
            service('messenger.default_bus')
                ->ignoreOnInvalid(),
            service('event_dispatcher')
                ->ignoreOnInvalid(),
        ])

        ->alias(TexterInterface::class, 'texter')

        ->set('texter.transports', Transports::class)
        ->factory([service('texter.transport_factory'), 'fromStrings'])
        ->args([[]])

        ->set('texter.transport_factory', Transport::class)
        ->args([tagged_iterator('texter.transport_factory')])

        ->set('texter.messenger.sms_handler', MessageHandler::class)
        ->args([service('texter.transports')])
        ->tag('messenger.message_handler', [
            'handles' => SmsMessage::class,
        ])

        ->set('texter.messenger.push_handler', MessageHandler::class)
        ->args([service('texter.transports')])
        ->tag('messenger.message_handler', [
            'handles' => PushMessage::class,
        ])

        ->set('notifier.logger_notification_listener', NotificationLoggerListener::class)
        ->tag('kernel.event_subscriber')
    ;

    $containerConfigurator->import(__DIR__ . '/Services/Transports.php');

    $shouldAddRegisterListenersPass = true;
    $beforeRemovingPasses = $containerBuilder->getCompilerPassConfig()
        ->getBeforeRemovingPasses();
    foreach ($beforeRemovingPasses as $beforeRemovingPass) {
        if ($beforeRemovingPass instanceof RegisterListenersPass) {
            $shouldAddRegisterListenersPass = false;
            break;
        }
    }

    if ($shouldAddRegisterListenersPass) {
        // Compiler passes
        $registerListenersPass = new RegisterListenersPass();
        if (class_exists(ConsoleEvents::class) && method_exists($registerListenersPass, 'setNoPreloadEvents')) {
            $registerListenersPass->setNoPreloadEvents([
                ConsoleEvents::COMMAND,
                ConsoleEvents::TERMINATE,
                ConsoleEvents::ERROR,
            ]);
        }
        // must be registered before removing private services as some might be listeners/subscribers
        // but as late as possible to get resolved parameters
        $containerBuilder->addCompilerPass($registerListenersPass, PassConfig::TYPE_BEFORE_REMOVING);
    }

    $containerBuilder->addCompilerPass(new NotifierCompilerPass(new NotifierConfigurationResolver()));
};
