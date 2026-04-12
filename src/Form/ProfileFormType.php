<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProfileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'constraints' => [new NotBlank()],
            ])
            ->add('email', EmailType::class, [
                'constraints' => [new NotBlank(), new Email()],
            ])
            ->add('plainPassword', PasswordType::class, [
                'mapped'   => false,
                'required' => false,
                'label'    => 'Nouveau mot de passe (laisser vide pour ne pas changer)',
            ])
            ->add('address', TextType::class, [
                'mapped'      => false,
                'constraints' => [new NotBlank()],
            ])
            ->add('cp', TextType::class, [
                'mapped'      => false,
                'constraints' => [new NotBlank()],
            ])
            ->add('city', TextType::class, [
                'mapped'      => false,
                'constraints' => [new NotBlank()],
            ])
            ->add('country', TextType::class, [
                'mapped'      => false,
                'constraints' => [new NotBlank()],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => \App\Entity\User::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'profile_form';
    }
}
