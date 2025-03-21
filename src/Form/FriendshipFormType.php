<?php

namespace App\Form;

use App\Entity\Friendship;
use App\Form\DataTransformer\UserToIdTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FriendshipFormType extends AbstractType
{
    public function __construct(
        private UserToIdTransformer $userToIdTransformer
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('friendB', SearchType::class, [
                'data_class' => null,
                'label'      => false,
                'attr'       => [
                    'placeholder'       => 'Pseudo',
                    'data-search'       => true,
                    'data-route'        => '/users-api',
                    'data-search-label' => 'Ajouter',
                    'class'             => 'search-input'
                ]
            ])
            ;

        $builder->get('friendB')
            ->addModelTransformer($this->userToIdTransformer);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Friendship::class,
        ]);
    }
}
