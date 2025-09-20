<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('handle', TextType::class, [
                'label' => 'Handle',
                'attr' => [
                    'autocomplete' => 'username',
                    'placeholder' => 'typo3-wizard',
                    'pattern' => '^[a-z0-9_-]{3,32}$',
                    'inputmode' => 'text',
                ],
                'help' => '3-32 characters — lowercase letters, numbers, hyphens, underscores.',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Pick a handle so the community can recognise you.',
                    ]),
                    new Length([
                        'min' => 3,
                        'max' => 32,
                        'minMessage' => 'Your handle must contain at least {{ limit }} characters.',
                        'maxMessage' => 'Handles cannot be longer than {{ limit }} characters.',
                    ]),
                    new Regex([
                        'pattern' => '/^[a-z0-9_-]+$/',
                        'message' => 'Only lowercase letters, numbers, hyphens, and underscores are allowed.',
                    ]),
                ],
            ])
            ->add('email', null, [
                'label' => 'Email',
                'attr' => [
                    'autocomplete' => 'email',
                    'placeholder' => 'you@example.com',
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'label' => 'I accept the community guidelines and data usage policy.',
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Please agree to the guidelines before continuing.',
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Password',
                'mapped' => false,
                'attr' => [
                    'autocomplete' => 'new-password',
                    'placeholder' => 'Minimum 6 characters',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        'max' => 4096,
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
