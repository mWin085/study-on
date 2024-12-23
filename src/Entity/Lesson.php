<?php

namespace App\Entity;

use App\Repository\LessonRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\NotBlank;

#[ORM\Entity(repositoryClass: LessonRepository::class)]
class Lesson
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'lessons')]
    #[ORM\JoinColumn(name: 'course_id_id', referencedColumnName: 'id', nullable: false)]
    private ?Course $course_id = null;

    #[ORM\Column(length: 255)]
    #[NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[NotBlank]
    private ?string $text = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\LessThan(1000)]

    private ?int $number = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCourseId(): ?Course
    {
        return $this->course_id;
    }

    public function setCourseId(?Course $course_id): static
    {
        $this->course_id = $course_id;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): static
    {
        $this->text = $text;

        return $this;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(int $number): static
    {
        $this->number = $number;

        return $this;
    }
}
