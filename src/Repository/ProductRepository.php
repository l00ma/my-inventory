<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 *
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */

class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function save(Product $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Product $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    // creer une query pour avoir les id produits par user

    /**
     * @return Product[]
     */
    public function findProductByDate($date_min, $date_max, $user): array
    {
        return $this->createQueryBuilder('p')
            ->select('p')
            ->where('p.limit_date < :date_min')
            ->andWhere('p.limit_date >= :date_max')
            ->andWhere('p.user = :user')
            ->setParameter('date_min', $date_min)
            ->setParameter('date_max', $date_max)
            ->setParameter('user', $user)
            ->orderBy('p.limit_date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Product[]
     */
    public function findAllProductByDate($user): array
    {
        return $this->createQueryBuilder('p')
            ->select('p')
            ->where('p.user = :user')
            ->orderBy('p.limit_date', 'ASC')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    // Find/search products by name or brand
    /**
     * @return Product[]
     */
    public function findSearchQuery($user, string $query)
    {
        $qb = $this->createQueryBuilder('p');
        $qb
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->orX(
                        $qb->expr()->like('p.name', ':query'),
                        $qb->expr()->like('p.brand', ':query'),
                    ),
                    $qb->expr()->isNotNull('p.limit_date')
                )
            )
            ->andWhere('p.user = :user')
            ->setParameter('query', '%' . $query . '%')
            ->setParameter('user', $user)
            ->orderBy('p.limit_date', 'ASC');

        return $qb

            ->getQuery()
            ->getResult();
    }
    //  echo "Dead code for maxdlr review ;-)";
}
