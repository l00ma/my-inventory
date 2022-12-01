<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;


class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name',  TextType::class, [
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('brand',  TextType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'required'   => false,
            ])
            ->add('u_weight', IntegerType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'required'   => false,
            ])
            ->add('price', IntegerType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'required'   => false,
            ])
            ->add('quantity', IntegerType::class, [
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('limit_date', DateType::class, [
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('location',  TextType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'required'   => false,
            ])
            ->add('remark',  TextareaType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'required'   => false,
            ])
            ->add(
                'category',
                EntityType::class,
                [
                    'class' => Category::class,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('u')
                            ->orderBy('u.name', 'ASC');
                    },
                    'choice_label' => 'name',
                    'expanded' => false,
                    'multiple' => false,
                    'required' => true,
                    'attr' => [
                        'class' => 'form-control'
                    ]
                ]
            )
            ->add('photo', FileType::class, [
                'required' => false,
                'attr' => [
                    'accept' => 'image/jpeg',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new Assert\File([
                        'maxSize' => '1024k',
                        'mimeTypes' => 'image/jpeg',
                    ]),
                ],
                'label' => 'Ajouter une image',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
