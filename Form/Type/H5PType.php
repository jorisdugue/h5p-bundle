<?php

namespace Studit\H5PBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class H5PType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('library', HiddenType::class)
            ->add('parameters', HiddenType::class)
            ->add('save', SubmitType::class, ['label' => 'Save']);
    }
}
