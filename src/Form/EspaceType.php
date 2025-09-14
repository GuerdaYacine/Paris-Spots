<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class EspaceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $choices = [];
        for ($i = 1; $i <= 20; $i++) {
            $choices[$i . 'ᵉ'] = (string)$i;
        }

        $builder
            ->add('arrondissement', ChoiceType::class, [
                'choices' => $choices,
                'required' => false,
                'placeholder' => 'Tous les arrondissements',
            ]);
    }
}
