<?php

declare(strict_types=1);

/*
 * This file is part of the slurp package.
 * (c) Al Poole <netstar@gmail.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form;

use App\Entity\RobotSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RobotSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('domainName', TextType::class, [
                'attr' => [
                    'readonly' => $options['domain_readonly'],
                ],
            ])
            ->add('scheme', ChoiceType::class, [
                'choices' => [
                    'http' => 'http',
                    'https' => 'https',
                ],
            ])
            ->add('userAgent', TextType::class)
            ->add('importSitemaps', CheckBoxType::class, [
                'label' => 'Import sitemaps?',
                'required' => false,
                'data' => $options['import_sitemaps'],
            ])
            ->add('retryMax', NumberType::class, [
                'html5' => true,
                'data' => 5,
            ])
            ->add('scanDelay', NumberType::class, [
                'html5' => true,
                'data' => 1,
            ])
            ->add('startTime')
            ->add('save', SubmitType::class, [
                'label' => $options['save_button_label'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RobotSettings::class,
            'import_sitemaps' => true,
            'save_button_label' => 'Create',
            'domain_readonly' => false,
        ]);
    }
}
