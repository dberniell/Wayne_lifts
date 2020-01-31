<?php

namespace App\Repository;

use App\Entity\Lift;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Join;

/**
 * @method Lift|null find($id, $lockMode = null, $lockVersion = null)
 * @method Lift|null findOneBy(array $criteria, array $orderBy = null)
 * @method Lift[]    findAll()
 * @method Lift[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LiftRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lift::class);
    }

    public function deleteTable()
    {
        return $this->createQueryBuilder('l')
            ->delete('App\Entity\Lift')
            ->getQuery()
            ->getResult()
        ;
    }



    public function findOneByLessFloorDistanceAndTimeLessThanActual($callingFloor, $floorDistance, $actualTime): ?Lift
    {
        $rsm = $this->createResultSetMappingBuilder('l');
        $select = $rsm->generateSelectClause(['l']);

        $sql = "    select $select 
    from lift l 
    inner join 
        (select id, MAX(minute_of_day) as minute from lift group by id) as li 
        on l.id = li.id and l.minute_of_day = minute 
    where (l.floor = :distanceUpper or l.floor = :distanceUnder) and l.minute_of_day < :time order by minute desc limit 1";

        $conn = $this->getEntityManager()
            ->createNativeQuery($sql, $rsm);
        $conn->setParameters(array(
                'distanceUpper' => $callingFloor + $floorDistance,
                'distanceUnder' => $callingFloor - $floorDistance,
                'time' => $actualTime
            ));
        return $conn->getOneOrNullResult();
    }

    public function findOneByLessFloorDistance($callingFloor, $floorDistance): ?Lift
    {
        $rsm = $this->createResultSetMappingBuilder('l');
        $select = $rsm->generateSelectClause(['l']);

        $sql = "    select $select
                    from lift l
                    inner join (select id, MAX(total_floors_traveled_by_day) as ttd from lift group by id) as li
                    on li.id = l.id and l.total_floors_traveled_by_day = li.ttd
                    where (l.floor = :distanceUpper or l.floor = :distanceUnder)
                    order by l.minute_of_day desc, l.total_floors_traveled_by_day asc limit 1";

        $conn = $this->getEntityManager()
            ->createNativeQuery($sql, $rsm);
        $conn->setParameters(array(
                'distanceUpper' => $callingFloor + $floorDistance,
                'distanceUnder' => $callingFloor - $floorDistance
        ));
        return $conn->getOneOrNullResult();
    }
}
