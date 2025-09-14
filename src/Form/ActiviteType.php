<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ActiviteType extends EspaceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder->add('prix', ChoiceType::class, [
            'choices' => [
                'Gratuit' => 'gratuit',
                'Payant' => 'payant',
            ],
            'multiple' => false,
            'required' => false,
            'mapped' => false,
            'placeholder' => 'Tous',
        ]);
    }
}
