<?php

declare(strict_types=1);

/*
 * This file is part of the "t3_notifier" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Ssch\T3Notifier\DependencyInjection\Compiler;

use Ssch\T3Notifier\DependencyInjection\NotifierConfigurationCollector;
use Ssch\T3Notifier\DependencyInjection\NotifierConfigurationResolver;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Notifier\Bridge\AllMySms\AllMySmsTransportFactory;
use Symfony\Component\Notifier\Bridge\AmazonSns\AmazonSnsTransportFactory;
use Symfony\Component\Notifier\Bridge\Chatwork\ChatworkTransportFactory;
use Symfony\Component\Notifier\Bridge\Clickatell\ClickatellTransportFactory;
use Symfony\Component\Notifier\Bridge\ContactEveryone\ContactEveryoneTransportFactory;
use Symfony\Component\Notifier\Bridge\Discord\DiscordTransportFactory;
use Symfony\Component\Notifier\Bridge\Engagespot\EngagespotTransportFactory;
use Symfony\Component\Notifier\Bridge\Esendex\EsendexTransportFactory;
use Symfony\Component\Notifier\Bridge\Expo\ExpoTransportFactory;
use Symfony\Component\Notifier\Bridge\FakeChat\FakeChatTransportFactory;
use Symfony\Component\Notifier\Bridge\FakeSms\FakeSmsTransportFactory;
use Symfony\Component\Notifier\Bridge\Firebase\FirebaseTransportFactory;
use Symfony\Component\Notifier\Bridge\FortySixElks\FortySixElksTransportFactory;
use Symfony\Component\Notifier\Bridge\FreeMobile\FreeMobileTransportFactory;
use Symfony\Component\Notifier\Bridge\GatewayApi\GatewayApiTransportFactory;
use Symfony\Component\Notifier\Bridge\Gitter\GitterTransportFactory;
use Symfony\Component\Notifier\Bridge\GoogleChat\GoogleChatTransportFactory;
use Symfony\Component\Notifier\Bridge\Infobip\InfobipTransportFactory;
use Symfony\Component\Notifier\Bridge\Iqsms\IqsmsTransportFactory;
use Symfony\Component\Notifier\Bridge\KazInfoTeh\KazInfoTehTransportFactory;
use Symfony\Component\Notifier\Bridge\LightSms\LightSmsTransportFactory;
use Symfony\Component\Notifier\Bridge\LinkedIn\LinkedInTransportFactory;
use Symfony\Component\Notifier\Bridge\Mailjet\MailjetTransportFactory as MailjetNotifierTransportFactory;
use Symfony\Component\Notifier\Bridge\Mattermost\MattermostTransportFactory;
use Symfony\Component\Notifier\Bridge\Mercure\MercureTransportFactory;
use Symfony\Component\Notifier\Bridge\MessageBird\MessageBirdTransport;
use Symfony\Component\Notifier\Bridge\MessageMedia\MessageMediaTransportFactory;
use Symfony\Component\Notifier\Bridge\MicrosoftTeams\MicrosoftTeamsTransportFactory;
use Symfony\Component\Notifier\Bridge\Mobyt\MobytTransportFactory;
use Symfony\Component\Notifier\Bridge\Octopush\OctopushTransportFactory;
use Symfony\Component\Notifier\Bridge\OneSignal\OneSignalTransportFactory;
use Symfony\Component\Notifier\Bridge\OrangeSms\OrangeSmsTransportFactory;
use Symfony\Component\Notifier\Bridge\OvhCloud\OvhCloudTransportFactory;
use Symfony\Component\Notifier\Bridge\RocketChat\RocketChatTransportFactory;
use Symfony\Component\Notifier\Bridge\Sendberry\SendberryTransportFactory;
use Symfony\Component\Notifier\Bridge\Sendinblue\SendinblueTransportFactory as SendinblueNotifierTransportFactory;
use Symfony\Component\Notifier\Bridge\Sinch\SinchTransportFactory;
use Symfony\Component\Notifier\Bridge\Slack\SlackTransportFactory;
use Symfony\Component\Notifier\Bridge\Sms77\Sms77TransportFactory;
use Symfony\Component\Notifier\Bridge\Smsapi\SmsapiTransportFactory;
use Symfony\Component\Notifier\Bridge\SmsBiuras\SmsBiurasTransportFactory;
use Symfony\Component\Notifier\Bridge\Smsc\SmscTransportFactory;
use Symfony\Component\Notifier\Bridge\SmsFactor\SmsFactorTransportFactory;
use Symfony\Component\Notifier\Bridge\SpotHit\SpotHitTransportFactory;
use Symfony\Component\Notifier\Bridge\Telegram\TelegramTransportFactory;
use Symfony\Component\Notifier\Bridge\Telnyx\TelnyxTransportFactory;
use Symfony\Component\Notifier\Bridge\TurboSms\TurboSmsTransport;
use Symfony\Component\Notifier\Bridge\Twilio\TwilioTransportFactory;
use Symfony\Component\Notifier\Bridge\Vonage\VonageTransportFactory;
use Symfony\Component\Notifier\Bridge\Yunpian\YunpianTransportFactory;
use Symfony\Component\Notifier\Bridge\Zendesk\ZendeskTransportFactory;
use Symfony\Component\Notifier\Bridge\Zulip\ZulipTransportFactory;
use Symfony\Component\Notifier\ChatterInterface;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Component\Notifier\TexterInterface;
use Symfony\Component\Notifier\Transport\TransportFactoryInterface;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MailUtility;

final class NotifierCompilerPass implements CompilerPassInterface
{
    private NotifierConfigurationResolver $notifierConfigurationResolver;

    public function __construct(NotifierConfigurationResolver $notifierConfigurationResolver)
    {
        $this->notifierConfigurationResolver = $notifierConfigurationResolver;
    }

    public function process(ContainerBuilder $container)
    {
        $config = $this->collectNotifierConfigurationsFromPackages();

        if (count($config) === 0) {
            return;
        }

        if ($config['chatter_transports']) {
            $container->getDefinition('chatter.transports')
                ->setArgument(0, $config['chatter_transports']);
        } else {
            $container->removeDefinition('chatter');
            $container->removeAlias(ChatterInterface::class);
        }
        if ($config['texter_transports']) {
            $container->getDefinition('texter.transports')
                ->setArgument(0, $config['texter_transports']);
        } else {
            $container->removeDefinition('texter');
            $container->removeAlias(TexterInterface::class);
        }

        $container->getDefinition('notifier.channel.email')
            ->setArgument(2, MailUtility::getSystemFromAddress());

        if (ExtensionManagementUtility::isLoaded('t3_messenger')) {
            if ($config['notification_on_failed_messages']) {
                $container->getDefinition('notifier.failed_message_listener')
                    ->addTag('kernel.event_subscriber');
            }

            // as we have a bus, the channels don't need the transports
            $container->getDefinition('notifier.channel.chat')
                ->setArgument(0, null);
            if ($container->hasDefinition('notifier.channel.email')) {
                $container->getDefinition('notifier.channel.email')
                    ->setArgument(0, null);
            }
            $container->getDefinition('notifier.channel.sms')
                ->setArgument(0, null);
            $container->getDefinition('notifier.channel.push')
                ->setArgument(0, null);
        }

        $container->getDefinition('notifier.channel_policy')
            ->setArgument(0, $config['channel_policy']);

        $container->registerForAutoconfiguration(TransportFactoryInterface::class)
            ->addTag('chatter.transport_factory');

        $container->registerForAutoconfiguration(TransportFactoryInterface::class)
            ->addTag('texter.transport_factory');

        $classToServices = [
            AllMySmsTransportFactory::class => 'notifier.transport_factory.all-my-sms',
            AmazonSnsTransportFactory::class => 'notifier.transport_factory.amazon-sns',
            ChatworkTransportFactory::class => 'notifier.transport_factory.chatwork',
            ClickatellTransportFactory::class => 'notifier.transport_factory.clickatell',
            ContactEveryoneTransportFactory::class => 'notifier.transport_factory.contact-everyone',
            DiscordTransportFactory::class => 'notifier.transport_factory.discord',
            EngagespotTransportFactory::class => 'notifier.transport_factory.engagespot',
            EsendexTransportFactory::class => 'notifier.transport_factory.esendex',
            ExpoTransportFactory::class => 'notifier.transport_factory.expo',
            FakeChatTransportFactory::class => 'notifier.transport_factory.fake-chat',
            FakeSmsTransportFactory::class => 'notifier.transport_factory.fake-sms',
            FirebaseTransportFactory::class => 'notifier.transport_factory.firebase',
            FortySixElksTransportFactory::class => 'notifier.transport_factory.forty-six-elks',
            FreeMobileTransportFactory::class => 'notifier.transport_factory.free-mobile',
            GatewayApiTransportFactory::class => 'notifier.transport_factory.gateway-api',
            GitterTransportFactory::class => 'notifier.transport_factory.gitter',
            GoogleChatTransportFactory::class => 'notifier.transport_factory.google-chat',
            InfobipTransportFactory::class => 'notifier.transport_factory.infobip',
            IqsmsTransportFactory::class => 'notifier.transport_factory.iqsms',
            KazInfoTehTransportFactory::class => 'notifier.transport_factory.kaz-info-teh',
            LightSmsTransportFactory::class => 'notifier.transport_factory.light-sms',
            LinkedInTransportFactory::class => 'notifier.transport_factory.linked-in',
            MailjetNotifierTransportFactory::class => 'notifier.transport_factory.mailjet',
            MattermostTransportFactory::class => 'notifier.transport_factory.mattermost',
            MercureTransportFactory::class => 'notifier.transport_factory.mercure',
            MessageBirdTransport::class => 'notifier.transport_factory.message-bird',
            MessageMediaTransportFactory::class => 'notifier.transport_factory.message-media',
            MicrosoftTeamsTransportFactory::class => 'notifier.transport_factory.microsoft-teams',
            MobytTransportFactory::class => 'notifier.transport_factory.mobyt',
            OctopushTransportFactory::class => 'notifier.transport_factory.octopush',
            OneSignalTransportFactory::class => 'notifier.transport_factory.one-signal',
            OrangeSmsTransportFactory::class => 'notifier.transport_factory.orange-sms',
            OvhCloudTransportFactory::class => 'notifier.transport_factory.ovh-cloud',
            RocketChatTransportFactory::class => 'notifier.transport_factory.rocket-chat',
            SendberryTransportFactory::class => 'notifier.transport_factory.sendberry',
            SendinblueNotifierTransportFactory::class => 'notifier.transport_factory.sendinblue',
            SinchTransportFactory::class => 'notifier.transport_factory.sinch',
            SlackTransportFactory::class => 'notifier.transport_factory.slack',
            Sms77TransportFactory::class => 'notifier.transport_factory.sms77',
            SmsapiTransportFactory::class => 'notifier.transport_factory.smsapi',
            SmsBiurasTransportFactory::class => 'notifier.transport_factory.sms-biuras',
            SmscTransportFactory::class => 'notifier.transport_factory.smsc',
            SmsFactorTransportFactory::class => 'notifier.transport_factory.sms-factor',
            SpotHitTransportFactory::class => 'notifier.transport_factory.spot-hit',
            TelegramTransportFactory::class => 'notifier.transport_factory.telegram',
            TelnyxTransportFactory::class => 'notifier.transport_factory.telnyx',
            TurboSmsTransport::class => 'notifier.transport_factory.turbo-sms',
            TwilioTransportFactory::class => 'notifier.transport_factory.twilio',
            VonageTransportFactory::class => 'notifier.transport_factory.vonage',
            YunpianTransportFactory::class => 'notifier.transport_factory.yunpian',
            ZendeskTransportFactory::class => 'notifier.transport_factory.zendesk',
            ZulipTransportFactory::class => 'notifier.transport_factory.zulip',
        ];

        foreach ($classToServices as $class => $service) {
            if (! class_exists($class)) {
                $container->removeDefinition($service);
            }
        }

        if (class_exists(FakeChatTransportFactory::class)) {
            $container->getDefinition($classToServices[FakeChatTransportFactory::class])
                ->replaceArgument('$mailer', new Reference('mailer'))
                ->replaceArgument('$logger', $this->createLogger(FakeChatTransportFactory::class));
        }

        if (class_exists(FakeSmsTransportFactory::class)) {
            $container->getDefinition($classToServices[FakeSmsTransportFactory::class])
                ->replaceArgument('$mailer', new Reference('mailer'))
                ->replaceArgument('$logger', $this->createLogger(FakeSmsTransportFactory::class));
        }

        if (isset($config['admin_recipients'])) {
            $notifier = $container->getDefinition('notifier');
            foreach ($config['admin_recipients'] as $i => $recipient) {
                $id = 'notifier.admin_recipient.' . $i;
                $container->setDefinition(
                    $id,
                    new Definition(Recipient::class, [$recipient['email'], $recipient['phone']])
                );
                $notifier->addMethodCall('addAdminRecipient', [new Reference($id)]);
            }
        }
    }

    private function collectNotifierConfigurationsFromPackages(): array
    {
        $versionInformation = GeneralUtility::makeInstance(Typo3Version::class);
        if ($versionInformation->getMajorVersion() >= 11) {
            $coreCache = Bootstrap::createCache('core');
            $packageCache = Bootstrap::createPackageCache($coreCache);
            $packageManager = Bootstrap::createPackageManager(PackageManager::class, $packageCache);
        } else {
            $coreCache = Bootstrap::createCache('core');
            $packageManager = Bootstrap::createPackageManager(PackageManager::class, $coreCache);
        }

        $config = (new NotifierConfigurationCollector($packageManager))->collect();

        return $this->notifierConfigurationResolver->resolve($config->getArrayCopy());
    }

    /**
     * @param class-string $className
     */
    private function createLogger(string $className): Definition
    {
        $logger = new Definition(Logger::class);
        $logger->setFactory([new Reference(LogManager::class), 'getLogger']);
        $logger->setArguments([$className]);
        $logger->setShared(false);

        return $logger;
    }
}
