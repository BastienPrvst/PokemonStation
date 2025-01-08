<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /**
         * Création du formulaire qui gèrera toute les page incluant un formulaire
         */
        $builder
            // Champ email
            ->add('email', EmailType::class, [
                'label' => 'Adresse Email :',
                'attr' => [
                    'placeholder' => 'ex : alice@gmail.com',
                ],
                'constraints' => [

                    new NotBlank([
                        'message' => 'Merci de renseigner une adresse email',
                    ]),
                    new Email([
                        'message' => 'L\'adresse email {{value}} n\'est pas valide',
                    ]),
                ],
            ])
            // Champ mots de passe (en double)
            ->add('plainPassword', RepeatedType::class ,[
                'type' => PasswordType::class,
                'invalid_message' => 'Le mot de passe ne correspond pas à sa confirmation',
                'first_options' => [
                    'label' => 'Nouveau mot de passe :',
                ],
                'second_options' => [
                    'label' => 'Confirmation du mot de passe :',
                ],
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Merci de renseigner un mot de passe',
                    ]),
                    new Length([
                        'min' => 8,
                        'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Votre mot de passe doit contenir au maximum {{ limit }} caractères',
                        'max' => 4096,
                    ]),
                    // regex de verification de mot de passe
                    new Regex([
                        'pattern' => "/^(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])(?=.*[ !\"#\$%&\'()*+,\-.\/:;<=>?@[\\\\\]\^_`{\|}~]).{8,4096}$/u",
                        'message' => 'Votre mot de passe doit contenir obligatoirement 8 caractères dont une minuscule, une majuscule, un chiffre et un caractère spécial'
                    ]),
                ],
            ])



            // Champ pseudonym
            ->add('pseudonym', TextType::class, [
                'label' => 'Pseudonyme :',
                'attr' => [
                    'placeholder' => 'ex : Sasha',
                ],
                'constraints' => [
                    new NotBlank([
                                'message' => 'Merci de renseigner un pseudonyme',
                    ]),
                    new Length([
                        'min' => 2,
                        'max' => 40,
                        'minMessage' => 'Votre pseudonyme doit contenir au moins {{limit}} caractères',
                        'maxMessage' => 'Votre pseudonyme doit contenir au maximum {{limit}} caractères',
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
