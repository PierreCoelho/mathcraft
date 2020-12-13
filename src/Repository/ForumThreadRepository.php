<?php

namespace App\Repository;

use App\Entity\Forum;
use App\Entity\ForumThread;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method ForumThread|null find($id, $lockMode = null, $lockVersion = null)
 * @method ForumThread|null findOneBy(array $criteria, array $orderBy = null)
 * @method ForumThread[]    findAll()
 * @method ForumThread[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ForumThreadRepository extends ServiceEntityRepository
{
    public const PAGINATOR_PER_PAGE = 15;
    
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ForumThread::class);
    }

    /**
    * @return ForumThread[] Returns an array of ForumThread objects
    */
    public function findOrderedThreadsInForumPaginator(Forum $forum, int $offset): Paginator
    {
        $query = $this->createQueryBuilder('t')
            ->where('t.forum = :forum')
            ->setParameter('forum', $forum)
            ->orderBy('t.lastPostCreatedAt', 'DESC')
            ->setMaxResults(self::PAGINATOR_PER_PAGE)
            ->setFirstResult($offset)
            ->getQuery()
        ;

        return new Paginator($query);
    }

    // /**
    //  * @return ForumThread[] Returns an array of ForumThread objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ForumThread
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
