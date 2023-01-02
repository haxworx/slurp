<?php

namespace App\Form;

use App\Entity\RobotSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RobotSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('domainName', TextType::class)
            ->add('scheme', ChoiceType::class, [
                'choices' => [
                    'http' => 'http',
                    'https' => 'https',
                ],
            ],)
            ->add('userAgent', TextType::class)
            ->add('importSitemaps', CheckBoxType::class)
            ->add('retryMax', NumberType::class)
            ->add('scanDelay', NumberType::class)
            ->add('startTime')
            ->add('save', SubmitType::class, [
                'label' => 'Save',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RobotSettings::class,
        ]);
    }
}
