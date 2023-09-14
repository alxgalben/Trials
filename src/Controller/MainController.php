<?php

namespace App\Controller;

use App\Entity\ApiKey;
use App\Entity\RateLimit;
use App\Entity\Participant;
use App\Entity\ShortTimeToken;
use App\Form\ParticipantFormType;
use App\Repository\ParticipantRepository;
use App\Repository\RateLimitRepository;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use function is_resource;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MainController extends AbstractController
{

    /**
     * @Route("/main", name="homepage")
     */

    public function index(Request $request, LoggerInterface $logger): Response
    {

        $logger->info('Acces pe homepage', [
            'date' => new DateTime(),
            'ip' => $request->getClientIp(),
        ]);
        //$this->denyAccessUnlessGranted('ROLE_USER');
        $participant = new Participant();
        $participantForm = $this->createForm(ParticipantFormType::class, $participant);

        return $this->render('main/index.html.twig', [
            'participantForm' => $participantForm->createView(),
        ]);
    }

    /**
     * @Route("/api/register", name="api-register")
     * @throws Exception
     */
    public function register(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $apiKeyValue = $request->get('api-key');
        $apiKey = $em->getRepository(ApiKey::class)->findOneBy(['apiKey' => $apiKeyValue]);

        if (!$apiKey) {
            return new JsonResponse(['error' => 'Invalid API key'], 401);
        }

        $token = bin2hex(random_bytes(32));
        $refreshToken = bin2hex(random_bytes(32));

        $tokenExpiration = new DateTime('now +1 hour');
        $refreshTokenExpiration = new DateTime('now +5 days');

        $shortLivedToken = new ShortTimeToken();
        $shortLivedToken->setToken($token);
        $shortLivedToken->setRefreshToken($refreshToken);
        $shortLivedToken->setTokenExpiration($tokenExpiration);
        $shortLivedToken->setRefreshTokenExpiration($refreshTokenExpiration);
        $shortLivedToken->setApiKey($apiKey);

        $em->persist($shortLivedToken);
        $em->flush();

        return new JsonResponse([
            'token' => $token,
            'refresh_token' => $refreshToken,
            'token_validity' => $tokenExpiration->getTimestamp(),
            'refresh_token_validity' => $refreshTokenExpiration->getTimestamp(),
        ]);
    }

    /**
     * @Route("/api/refresh-token", name="api-refresh-token")
     * @throws Exception
     */
    public function refreshToken(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $refreshToken = $request->request->get('refresh_token');

        $shortTimeToken = $em->getRepository(ShortTimeToken::class)->findOneBy(['refreshToken' => $refreshToken]);

        if (!$shortTimeToken) {
            return new JsonResponse(['error' => 'Invalid refresh token'], 401);
        }

        $newToken = bin2hex(random_bytes(32));

        $newTokenExpiration = new DateTime('now +1 hour');
        $shortTimeToken->setToken($newToken);
        $shortTimeToken->setTokenExpiration($newTokenExpiration);

        $em->persist($shortTimeToken);
        $em->flush();

        return new JsonResponse([
            'token' => $newToken,
            'token_validity' => $newTokenExpiration->getTimestamp(),
        ]);
    }

    /**
     * @Route("/api", name="api")
     */
    public function indexApi(Request $request, EntityManagerInterface $em, ParticipantRepository $participantRepository, LoggerInterface $logger, RateLimitRepository $rateLimitRepository): Response
    {
        //$this->denyAccessUnlessGranted('ROLE_USER');

        if ($request->attributes->get('rate_limit_exceeded')) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Too Many Requests',
            ], 429);
        }

        $apiKey = $request->get('api-key');
        $envKeys = $this->getParameter("api_keys");
        $validApiKeys = explode(',', $envKeys);

        if (empty($apiKey)) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Please enter an API key.'
            ], 200);
        } else if (!in_array($apiKey, $validApiKeys)) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        } //basic Auth algorithm symfony!!!!!!!!!!!!!!!!!!!!!


        $participant = new Participant();
        $participantForm = $this->createForm(ParticipantFormType::class, $participant);
        $participantForm->handleRequest($request);

        if ($participantForm->isSubmitted()) {
            if ($participantForm->isValid()) {

                $minute = (int)date('i');
                $prizes = array('casti', 'ghiozdan', 'mouse');

                if ($minute < 15) {
                    $randomWordIndex = array_rand($prizes);
                    $randomWord = $prizes[$randomWordIndex];
                    $this->addFlash('success', 'You have won a special prize: ' . $randomWord);
                }

                $currentDate = new DateTimeImmutable();
                $participant->setSubmittedAt($currentDate);
                $userReceiptCountToday = $participantRepository->countUserReceiptCountToday($currentDate, $participant->getEmail());

                if ($userReceiptCountToday >= 2) {
                    $this->addFlash('warning', 'You have already entered 2 receipts today. Come back tomorrow');
                }

                if ($userReceiptCountToday <= 2) {
                    $em->persist($participant);
                    $em->flush();
                }

                $logger->info('Salvare cu succes în API', [
                    'date' => new DateTime(),
                    'ip' => $request->getClientIp(),
                ]);

                return new JsonResponse([
                    'success' => true,
                    'message' => 'Datele au fost salvate cu succes!',
                ], 200);
            } else {
                $errors = [];
                foreach ($participantForm->getErrors(true, true) as $formError) {
                    $field = $formError->getOrigin()->getName();
                    $message = $formError->getMessage();
                    $errors[] = sprintf('Field "%s": %s', $field, $message);
                }
                return new JsonResponse([
                    'success' => false,
                    'errors' => $errors,
                ], 200);
            }

        }

        return new JsonResponse([
            'success' => false,
            'errors' => [],
        ], 400);
    }

    /**
     * @Route("/login", name="login")
     */

    public function login(AuthenticationUtils $authenticationUtils, LoggerInterface $logger): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        /*if ($error) {
            $logger->info('Autentificare nereușită', [
                'date' => new \DateTime(),
                'ip' => $request->getClientIp(),
                'status' => 'nereușită',
            ]);
        } else {
            $logger->info('Autentificare reușită', [
                'date' => new \DateTime(),
                'ip' => $request->getClientIp(),
                'status' => 'reușită',
            ]);
        }*/

        return $this->render('security/login.html.twig', [
            'lastUsername' => $lastUsername,
            'error' => $error,
        ]);
    }

    /**
     * @Route("/logout", name="logout")
     */

    public function logout(AuthenticationUtils $authenticationUtils)
    {

    }

    /**
     * @Route("/export-csv/", name="export-csv")
     */
    public function exportCsv(Request $request, EntityManagerInterface $entityManager)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $tmpFileName = (new Filesystem())->tempnam(sys_get_temp_dir(), 'sb_');
        $tmpFile = fopen($tmpFileName, 'wb+');

        $startDateParam = $request->query->get('start_date');
        $endDateParam = $request->query->get('end_date');

        if (!$startDateParam || !$endDateParam) {
            throw new InvalidArgumentException('Both start_date and end_date parameters are required.');

        }

        $startDate = DateTime::createFromFormat('Y-m-d', $startDateParam);
        //dd($startDate);
        $endDate = DateTime::createFromFormat('Y-m-d', $endDateParam);


        if (!$startDate instanceof DateTime || !$endDate instanceof DateTime) {
            throw new InvalidArgumentException('Invalid date format. The dates should be in the format: "YYYY-MM-DD".');
        }
        $startDate->setTime(0, 0, 1);
        $endDate->setTime(23, 59, 59);

        if (!is_resource($tmpFile)) {
            throw new RuntimeException('Unable to create a temporary file.');
        }

        $data = [
            ['id', 'firstName', 'lastName', 'email', 'phoneNumber', 'receiptCode'],
        ];

        $repository = $entityManager->getRepository(Participant::class);
        $registrations = $repository->findByDateInterval($startDate, $endDate);

        foreach ($registrations as $registration) {
            $data[] = [
                strtoupper((string)$registration->getId()),
                strtoupper((string)$registration->getFirstName()),
                strtoupper((string)$registration->getLastName()),
                strtoupper((string)$registration->getEmail()),
                strtoupper((string)$registration->getPhoneNumber()),
                strtoupper((string)$registration->getReceipt()->getName()),
            ];
        }

        foreach ($data as $el) {
            fputcsv($tmpFile, $el, ';', '"');
        }

        $response = $this->file($tmpFileName, 'export.csv');
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="export.csv"');

        fclose($tmpFile);

        return $response;
    }

    /**
     * @Route("/winner/{id}", name="winner")
     */
    public function markWinner(EntityManagerInterface $em, $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $participant = $em->getRepository(Participant::class)->find($id);

        if (!$participant) {
            throw $this->createNotFoundException('Participant not found');
        }

        $participant->setIsWinner(true);
        $em->flush();

        return $this->redirectToRoute('listing');
    }

    /**
     * @Route("/winners-week", name="winner-week")
     */
    public function winnerPerWeek(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $selectedWeek = $request->query->get('week', 1);
        //$winnersPerWeek = $em->getRepository(Participant::class)->findWinnersPerWeek();
        $winnersPerWeek = $em->getRepository(Participant::class)->findWinnersPerWeekByNumber($selectedWeek);
        return $this->render('listing/winners_per_week.html.twig', [
            'winnersPerWeek' => $winnersPerWeek
        ]);
    }
}
