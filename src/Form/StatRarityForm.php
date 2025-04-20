<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StatRarityForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('C', TextType::class)
            ->add('PC', TextType::class)
            ->add('R', TextType::class)
            ->add('TR', TextType::class)
            ->add('ME', TextType::class)
            ->add('GMAX', TextType::class)
            ->add('EX', TextType::class)
            ->add('SR', TextType::class)
            ->add('UR', TextType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
