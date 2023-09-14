<?php

namespace App\Validator;

use App\Entity\ReceiptCode;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ReceiptCodeExistsValidator extends ConstraintValidator
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    public function validate($value, Constraint $constraint)
    {
        $existingReceiptCode = $this->entityManager->getRepository(ReceiptCode::class)->findOneBy(['name' => $value]);

        if($existingReceiptCode) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value->getName())
                ->addViolation();
        }
    }
}
