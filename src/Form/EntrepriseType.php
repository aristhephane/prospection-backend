<?php

namespace App\Form;

use App\Entity\Entreprise;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class EntrepriseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('raisonSociale', TextType::class, [
                'label' => 'Raison sociale',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La raison sociale est obligatoire']),
                    new Assert\Length(['max' => 255]),
                ],
            ])
            ->add('adresse', TextType::class, [
                'label' => 'Adresse',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'L’adresse est obligatoire']),
                ],
            ])
            ->add('telephone', TextType::class, [
                'label' => 'Téléphone',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le téléphone est obligatoire']),
                    new Assert\Length(['max' => 50]),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'required' => false,
                'constraints' => [
                    new Assert\Email(['message' => 'Veuillez entrer un email valide']),
                ],
            ])
            ->add('secteurActivite', TextType::class, [
                'label' => 'Secteur d\'activité',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le secteur d\'activité est obligatoire']),
                ],
            ])
            ->add('tailleEntreprise', IntegerType::class, [
                'label' => 'Taille de l\'entreprise',
                'required' => false,
            ])
            ->add('siteWeb', TextType::class, [
                'label' => 'Site Web',
                'required' => false,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Entreprise::class,
        ]);
    }
}
