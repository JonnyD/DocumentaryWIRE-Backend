<?php

namespace App\Form;

use App\Entity\Standalone;
use App\Entity\VideoSource;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StandaloneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('videoSource', EntityType::class, [
                'class' => VideoSource::class,
                'choice_label' => 'id',
            ])
            ->add('videoId', TextType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'allow_extra_fields' => true,
            'data_class' => Standalone::class,
        ]);
    }

    public function getName()
    {
        return "standalone_documentary";
    }
}