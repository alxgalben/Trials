<?php

namespace App\Form\ModelTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\ReceiptCode;
use Symfony\Component\Form\Exception\TransformationFailedException;

class CodeToStringTransformer implements DataTransformerInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function transform($value): string //trans in string
    {
        if (null === $value) {
            return '';
        }

        return $value->getName();
    }

    public function reverseTransform($value): ?ReceiptCode
    {
        if (!$value) {
            return null;
        }

        $code = $this->entityManager
            ->getRepository(ReceiptCode::class)
            ->findOneBy(['name' => $value]);

        if (null === $code) {
            throw new TransformationFailedException(
                sprintf(
                    'An entity with code "%s" does not exist!',
                    $value
                )
            );
        }

        return $code;
    }
}
