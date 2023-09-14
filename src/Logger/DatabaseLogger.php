<?php

namespace App\Logger;

use App\Entity\Logs;
use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;
use Doctrine\ORM\EntityManagerInterface;

class DatabaseLogger extends AbstractProcessingHandler
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager, int $level = Logger::DEBUG, bool $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->entityManager = $entityManager;
    }

    protected function write(array $record): void
    {
        $logEntry = new Logs();
        $logEntry->setMessage($record['message']);
        $remote = $_SERVER["REMOTE_ADDR"] ?? '127.0.0.1';
        $logEntry->setIp($remote);

        $status = $record['context']['status'] ?? 'default';
        $logEntry->setStatus($status);
        $logEntry->setLogTime(new \DateTime());

        $this->entityManager->persist($logEntry);
        $this->entityManager->flush();
    }


}
