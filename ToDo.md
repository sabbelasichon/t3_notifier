->set('notifier.monolog_handler', NotifierHandler::class)
->args([service('notifier')])


FakeChatTransportFactory
FakeSmsTransportFactory
