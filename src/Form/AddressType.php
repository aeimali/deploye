<?php

namespace App\Form;

use App\Entity\Address;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name',TextType::class , [
                'label' => 'Votre nom d\'adresse', 'attr' => [
                    'placeholder' => 'Nommez votre adresse'
                ]
            ])
            ->add('firstname',TextType::class , [
                'label' => 'Votre prénom', 'attr' => [
                    'placeholder' => 'Entrez votre prénom'
                ]
            ])
            ->add('lastname',TextType::class , [
                'label' => 'Votre nom', 'attr' => [
                    'placeholder' => 'Entrez votre nom'
                ]
            ])
            ->add('compagny',TextType::class , [
                'label' => 'Votre société',
                'required' => false, 'attr' => [
                    'placeholder' => '(facultatif) Nom de votre société'
                ]
            ])
            ->add('address',TextType::class , [
                'label' => 'Votre adresse', 'attr' => [
                    'placeholder' => 'porte 141 rue 14 klbcoro'
                ]
            ])
            ->add('postal',TextType::class , [
                'label' => 'Votre code postale',
                'required' => false,'attr' => [
                    'placeholder' => '(facultatif) votre code postal'
                ]
            ])
            ->add('city',TextType::class , [
                'label' => 'Votre ville', 'attr' => [
                    'placeholder' => 'Nom de votre ville'
                ]
            ])
            ->add('country',TextType::class , [
                'label' => 'Votre pays', 'attr' => [
                    'placeholder' => 'Nom de votre pays'
                ]
            ])
            ->add('phone',TelType::class , [
                'label' => 'Numéros téléphone', 'attr' => [
                    'placeholder' => 'Votre numéro'
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Valider',
                'attr' => [
                    'class' => 'btn-block btn-info'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
        ]);
    }
}
