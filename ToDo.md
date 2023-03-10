BrowserChannel für FlashMessages
BrowserNotifications für FlashMessages

Für EmailChannel die TransportFactory injecten für den Mailer

->set('notifier.monolog_handler', NotifierHandler::class)
->args([service('notifier')])


