<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\ShortTimeToken;
use DateTime;

class TokenValidation
{
    private $doctrine;

    public function __construct(\Doctrine\Persistence\ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        $token = $request->query->get('token');

        if (!$token) {
            $response = new JsonResponse(['error' => 'Missing token'], 401);
            $event->setResponse($response);
            return;
        }

        $shortLivedToken = $this->doctrine->getRepository(ShortTimeToken::class)->findOneBy(['token' => $token]);

        if (!$shortLivedToken) {
            $response = new JsonResponse(['error' => 'Invalid token'], 401);
            $event->setResponse($response);
            return;
        }

        $currentDateTime = new DateTime('now');
        if ($shortLivedToken->getTokenExpiration() <= $currentDateTime) {
            $response = new JsonResponse(['error' => 'Token has expired'], 401);
            $event->setResponse($response);
        }

    }
}