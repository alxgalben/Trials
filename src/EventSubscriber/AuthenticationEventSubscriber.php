<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthenticationEventSubscriber implements EventSubscriberInterface
{
    private $logger;
    private $router;
    private $requestStack;

    public function __construct(LoggerInterface $logger, RouterInterface $router, RequestStack $requestStack)
    {
        $this->logger = $logger;
        $this->router = $router;
        $this->requestStack = $requestStack;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AuthenticationEvents::AUTHENTICATION_FAILURE => 'onAuthenticationFailure',
            AuthenticationEvents::AUTHENTICATION_SUCCESS => 'onAuthenticationSuccess',
        ];
    }

    public function onAuthenticationFailure(AuthenticationFailureEvent $event)
    {
        $exception = $event->getAuthenticationException();
        $request = $this->requestStack->getCurrentRequest();
        $this->logAuthenticationFailure($request, $exception);
    }

    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event)
    {
        $token = $event->getAuthenticationToken();
        $user = $token->getUser();
        if ($user instanceof UserInterface) {
            $request = $this->requestStack->getCurrentRequest();
            $this->logAuthenticationSuccess($request);
        }
    }

    private function logAuthenticationFailure($request, $exception)
    {
        $this->logger->info('Autentificare nereușită', [
            'date' => new \DateTime(),
            'ip' => $request->getClientIp(),
            'status' => 'nereușită',
            'error_message' => $exception->getMessage(),
        ]);
    }

    private function logAuthenticationSuccess($request)
    {
        $this->logger->info('Autentificare reușită', [
            'date' => new \DateTime(),
            'ip' => $request->getClientIp(),
            'status' => 'reușită',
        ]);
    } // duplicate code corecteaza cu o singura functie
}


