<?php

namespace DW\DocumentaryBundle\Form;

use DW\DocumentaryBundle\Entity\Documentary;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DocumentaryType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class)
            ->add('storyline', TextareaType::class)
            ->add('summary', TextareaType::class)
            ->add('year', IntegerType::class)
            ->add('length', IntegerType::class)
            ->add('status', TextType::class)
            ->add('views', IntegerType::class)
            ->add('shortUrl', TextType::class)
            ->add('videoId', TextType::class)
            ->add('videoSource', TextType::class)
            ->add('poster', FileType::class, [
                'label' => 'Poster',
                'data_class' => null
            ])
            ->add('category', EntityType::class, [
                'class' => 'CategoryBundle:Category',
                'choice_label' => 'name',
            ])
            ->add('submit', SubmitType::class);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Documentary::class,
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return "documentary";
    }
}