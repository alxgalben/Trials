<?php

namespace App\Form;

use App\Entity\Participant;
use App\Form\ModelTransformer\CodeToStringTransformer;
use App\Validator\ReceiptCodeExists;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Email;

class ParticipantFormType extends AbstractType
{
    private $codeToStringTransformer;

    public function __construct(CodeToStringTransformer $codeToStringTransformer)
    {
        $this->codeToStringTransformer = $codeToStringTransformer;
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'First name is required!'
                    ]),
                    new Regex([
                        'pattern' => '/^[a-zA-Z\'\- ]+$/',
                        'message' => 'Invalid first name.'
                    ]),
                ],
            ])
            ->add('lastName', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Last name is required!'
                    ]),
                    new Regex([
                        'pattern' => '/^[a-zA-Z\'\- ]+$/',
                        'message' => 'Invalid last name.'
                    ]),
                ],
            ])
            ->add($builder->create('phoneNumber', TextType::class, [
                'label' => 'Phone Number',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Phone number is required!'
                    ]),
                    new Regex([
                        'pattern' => '/^(\+?4|0)7\d{8}$/',
                        'message' => 'The phone number must be a valid romanian phone number.',
                    ]),
                    new Length([
                        'min' => 10,
                        'max' => 13,
                        'minMessage' => 'Your phone number must be at least {{ limit }} characters long',
                        'maxMessage' => 'Your phone number cannot be longer than {{ limit }} characters'
                    ])
                ]
            ]))
            ->add('email', EmailType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Email is required.'
                    ]),
                    new Email(['message' => 'Invalid email.']),
                ],
            ])
            ->add('receipt', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Receipt number is required.'
                    ]),
                    new Regex([
                        'pattern' => '/^[a-zA-Z0-9]{2}-\d{3}-\d{4}$/',
                        'message' => 'Invalid receipt.'
                    ]),
                    new ReceiptCodeExists()
                ],
            ]);

        $builder
            ->get('receipt')
            ->addModelTransformer($this->codeToStringTransformer);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
            'csrf_protection' => false,
        ]);
    }
}
