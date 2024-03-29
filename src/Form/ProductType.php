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
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Positive;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name',  TextType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'constraints' => new Length(['min' => 2, 'max' => 255])
            ])
            ->add('brand',  TextType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'required'   => false,
            ])
            ->add('u_weight', IntegerType::class, [
                'label' => 'Weight(g)',
                'attr' => [
                    'class' => 'form-control'
                ],
                'required'   => false,
                'constraints' => new Positive()
            ])
            ->add('price', MoneyType::class, [
                'currency' => null,
                'attr' => [
                    'class' => 'form-control'
                ],
                'required'   => false,
                'divisor' => 100,
                'constraints' => new Positive()
            ])
            ->add('quantity', IntegerType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'constraints' => new Positive()
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
            ->add('image', FileType::class, [
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new Image([
                        'minWidth' => '400',
                        'maxWidth' => '1024',
                        'minHeight' => '200',
                        'maxHeight' => '1024',
                        'mimeTypes' => array(
                            'image/jpeg',
                            'image/jpg',
                            'image/png'
                        )
                    ])
                ],
                'label' => 'Add a picture'
            ])
            ->add('save', SubmitType::class, [
                'label' => '<i class="fa-regular fa-floppy-disk"></i>',
                'label_html' => true,
                'attr' => [
                    'class' => 'form-control btn btn-primary text-btn',
                    'title' => 'Save this product',
                ],
            ])
            ->add('delete', SubmitType::class, [
                'label' => '<i class="bi bi-trash"></i>',
                'label_html' => true,
                'attr' => [
                    'class' => 'form-control btn btn-danger text-btn',
                    'title' => 'Delete this product',
                ],
            ])
            ->add('back', SubmitType::class, [
                'label' => '<i class="fa-solid fa-arrow-left"></i>',
                'label_html' => true,
                'attr' => [
                    'class' => 'form-control btn btn-primary text-btn',
                    'title' => 'Go back',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
