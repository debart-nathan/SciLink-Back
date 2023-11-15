<?php
namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class LogoutResponseSubscriber implements EventSubscriberInterface
{
    public function onLogoutSuccess(LogoutEvent $event)
    {
        $event->setResponse(new JsonResponse(['status' => 'ok']));
    }

    public static function getSubscribedEvents()
    {
        return [
            LogoutEvent::class => 'onLogoutSuccess',
        ];
    }
}