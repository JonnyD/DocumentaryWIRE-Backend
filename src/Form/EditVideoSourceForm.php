<?php

namespace App\Form;

use App\Entity\VideoSource;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditVideoSourceForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', IntegerType::class)
            ->add('name', TextType::class)
            ->add('embedAllowed', ChoiceType::class, [
                'choices'  => [
                    'Yes' => 'yes',
                    'No' => 'no'
                    ]
                ])
            ->add('embedCode', TextType::class)
            ->add('status', ChoiceType::class, [
                'choices'  => [
                    'Enabled' => 'enabled',
                    'Disabled' => 'disabled'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => VideoSource::class,
        ]);
    }
}