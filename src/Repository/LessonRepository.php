<?php

namespace App\Repository;

use App\Entity\Lesson;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Lesson>
 */
class LessonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lesson::class);
    }

    //    /**
    //     * @return Lesson[] Returns an array of Lesson objects
    //     */
        public function findOneByCode($value): array
        {
            return $this->createQueryBuilder('l')
                ->innerJoin('l.course_id', 'c')
                ->andWhere('c.code = :val')
                ->setParameter('val', $value)
                ->orderBy('l.id', 'ASC')
                ->setMaxResults(1)
                ->getQuery()
                ->getResult()
            ;
        }

    //    public function findOneBySomeField($value): ?Lesson
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
