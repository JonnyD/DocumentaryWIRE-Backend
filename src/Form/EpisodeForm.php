<?php

namespace App\Form;

use App\Entity\Documentary;
use App\Entity\Episode;
use App\Entity\Season;
use App\Entity\VideoSource;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class EpisodeForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('number', IntegerType::class)
            ->add('title', TextType::class)
            ->add('storyline', TextType::class)
            ->add('summary', TextType::class, [
                'empty_data' => '',
                'required' => true
            ])
            ->add('year', IntegerType::class)
            ->add('length', TextType::class)
            ->add('imdbId', TextType::class)
            ->add('videoSource', EntityType::class, [
                'class' => VideoSource::class,
                'choice_label' => 'id',
            ])
            ->add('videoId', TextType::class)
            ->add('thumbnail', TextType::class, [
                'mapped' => false,
                'required' => true
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'allow_extra_fields' => true,
            'data_class' => Episode::class,
        ]);
    }

    public function getName()
    {
        return "episode";
    }
}