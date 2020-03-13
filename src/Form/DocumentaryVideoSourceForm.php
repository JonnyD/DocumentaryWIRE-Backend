<?php

namespace App\Form;

use App\Entity\Documentary;
use App\Entity\DocumentaryVideoSource;
use App\Entity\Episode;
use App\Entity\VideoSource;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class DocumentaryVideoSourceForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('videoSource', EntityType::class, [
                'class' => VideoSource::class,
                'choice_label' => 'id',
            ])
            ->add('documentary', EntityType::class, [
                'class' => Documentary::class,
                'choice_label' => 'id',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'allow_extra_fields' => true,
            'data_class' => DocumentaryVideoSource::class,
        ]);
    }

    public function getName()
    {
        return "documentary_video_source";
    }
}