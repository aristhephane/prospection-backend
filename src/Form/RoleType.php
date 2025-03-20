<?php

namespace App\Form;

use App\Entity\Role;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class RoleType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    $builder
      ->add('name', TextType::class, [
        'label' => 'Nom du rôle',
        'attr' => [
          'placeholder' => 'Exemple : ROLE_ADMIN',
        ],
      ])
      ->add('description', TextType::class, [
        'label' => 'Description',
        'required' => false,
        'attr' => [
          'placeholder' => 'Description du rôle (facultatif)',
        ],
      ])
      ->add('permissions', CollectionType::class, [
        'label' => 'Permissions associées',
        'entry_type' => TextType::class, // Si les permissions sont des chaînes de caractères
        'allow_add' => true,
        'allow_delete' => true,
        'required' => false,
        'entry_options' => [
          'attr' => [
            'placeholder' => 'Nom de la permission',
          ],
        ],
      ])
      ->add('isActive', CheckboxType::class, [
        'label' => 'Activer ce rôle',
        'required' => false,
      ]);
  }

  public function configureOptions(OptionsResolver $resolver): void
  {
    $resolver->setDefaults([
      'data_class' => Role::class,
    ]);
  }
}