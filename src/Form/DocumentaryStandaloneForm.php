<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Documentary;
use App\Entity\DocumentaryVideoSource;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DocumentaryStandaloneForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class)
            ->add('storyline', TextType::class)
            ->add('summary', TextType::class, [
                'empty_data' => '',
                'required' => true
            ])
            ->add('year', IntegerType::class)
            ->add('length', TextType::class)
            ->add('imdbId', TextType::class)
            ->add('standalone', StandaloneType::class)
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'id',
            ])
            ->add('poster', FileType::class, [
                'mapped' => false,
                'required' => true
            ])
            ->add('wideImage', FileType::class, [
                'mapped' => false,
                'required' => true
            ])
            ->add('documentaryVideoSources', CollectionType::class, [
                'entry_type' => DocumentaryVideoSourceForm::class,
                'entry_options' => ['label' => false],
                'allow_add' => true
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'allow_extra_fields' => true,
            'data_class' => Documentary::class,
        ]);
    }

    public function getName()
    {
        return "standalone_documentary";
    }
}