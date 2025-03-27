<?php

namespace App\Form;

use App\Entity\Course;
use App\Entity\Lesson;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class LessonType extends AbstractType
{

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Поле Название обязательно к заполнению',
                    ]),
                ],
            ])
            ->add('text', TextareaType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Поле Текст обязательно к заполнению',
                    ]),
                ],
            ])
            ->add('number', NumberType::class, [
                'invalid_message' => 'Введите целое число',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Поле Номер обязательно к заполнению',
                    ]),
                ],
            ])
            ->add('course_id', HiddenType::class);

        $builder->get('course_id')
            ->addModelTransformer(new \CourseTransformer($this->entityManager));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Lesson::class,
        ]);
    }
}
