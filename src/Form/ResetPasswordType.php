<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ResetPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options['reset_request']) {
            // Formulaire pour la demande de réinitialisation
            $builder->add('email', EmailType::class, [
                'label' => 'Adresse e-mail',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez entrer une adresse e-mail.']),
                    new Assert\Email(['message' => 'Veuillez entrer une adresse e-mail valide.']),
                ],
            ]);
        } else {
            // Formulaire pour la confirmation et la modification du mot de passe
            $builder->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'Nouveau mot de passe',
                    'constraints' => [
                        new Assert\NotBlank(['message' => 'Veuillez entrer un mot de passe.']),
                        new Assert\Length([
                            'min' => 6,
                            'minMessage' => 'Le mot de passe doit contenir au moins 6 caractères.',
                        ]),
                    ],
                ],
                'second_options' => [
                    'label' => 'Confirmez le mot de passe',
                ],
                'invalid_message' => 'Les mots de passe doivent être identiques.',
            ]);
        }

        $builder->add('submit', SubmitType::class, [
            'label' => 'Valider',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'reset_request' => false, // Détermine si c'est la demande de reset ou la confirmation
        ]);
    }
}
