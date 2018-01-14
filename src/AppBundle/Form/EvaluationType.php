<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class EvaluationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder 
                  ->add('user',      TextType::class,        array('label' => 'Utilisateur', 'disabled' => true))
                  ->add('product',   TextType::class,        array('label' => 'Produit', 'disabled' => true))
                  ->add('mark',      ChoiceType::class,      array( 'label' => "Note", 
                                                                   'choices' => array('0' => '0',
                                                                                      '1' => '1',
                                                                                      '2' => '2',
                                                                                      '3' => '3',
                                                                                      '4' => '4',
                                                                                      '5' => '5' )))
                  ->add('comment',   TextareaType::class,    array('label' => 'Commentaire'));
    }
}
