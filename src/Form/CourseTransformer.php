<?php
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class CourseTransformer implements DataTransformerInterface
{
    private $manager;

    public function __construct(\Doctrine\ORM\EntityManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Transforms an object (course) to a string (id).
     */
    public function transform($course): mixed
    {
        if (null === $course) return '';
        return $course->getId();
    }

    /**
     * Transforms a string (id) to an object (course).
     */
    public function reverseTransform($courseId): mixed
    {
        if (!$courseId) {
            return null;
        }

        $course = $this->manager
            ->getRepository(\App\Entity\Course::class)
            ->find($courseId);

        if (null === $course) {
            throw new TransformationFailedException(sprintf(
                'An course with id "%s" does not exist!',
                $courseId
            ));
        }

        return $course;
    }
}