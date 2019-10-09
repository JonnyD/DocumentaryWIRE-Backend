<?php

namespace App\Form;

use App\Entity\Subscription;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SubscriptionForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('userFrom', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
            ])
            ->add('userTo', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class' => Subscription::class,
        ]);
    }

    public function getName()
    {
        return "subscription";
    }
}