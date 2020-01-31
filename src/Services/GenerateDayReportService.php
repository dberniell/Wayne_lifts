<?php


namespace App\Services;


use App\Entity\Lift;
use App\Repository\LiftRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

class GenerateDayReportService
{
    /**
     * @var array
     */
    private $lifts;
    /**
     * @var EntityManager
     */
    private $em;
    /**
     * @var LiftRepository
     */
    private $liftRepository;
    /**
     * @var array
     */
    private $rangeOfWorkingTimes;
    /**
     * @var array
     */
    private $queryRangeTimes1;
    /**
     * @var array
     */
    private $queryRangeTimes2;
    /**
     * @var array
     */
    private $queryRangeTimes3;
    /**
     * @var array
     */
    private $queryRangeTimes4;
    /**
     * @var array
     */
    private $queryRangeTimes5;
    /**
     * @var array
     */
    private $queryRangeTimes6;
    /**
     * @var array
     */
    private $queryRangeTimes7;
    /**
     * @var array
     */
    private $queryRangeTimes8;
    /**
     * @var array
     */
    private $queriesDeparture1;
    /**
     * @var array
     */
    private $queriesDestination1;
    /**
     * @var array
     */
    private $queriesDeparture2;
    /**
     * @var array
     */
    private $queriesDestination2;
    /**
     * @var array
     */
    private $queriesDeparture3;
    /**
     * @var array
     */
    private $queriesDestination3;
    /**
     * @var array
     */
    private $queriesDeparture4;
    /**
     * @var array
     */
    private $queriesDestination4;
    /**
     * @var array
     */
    private $queriesDeparture5;
    /**
     * @var array
     */
    private $queriesDestination5;
    /**
     * @var array
     */
    private $queriesDeparture6;
    /**
     * @var array
     */
    private $queriesDestination6;
    /**
     * @var array
     */
    private $queriesDeparture7;
    /**
     * @var array
     */
    private $queriesDestination7;
    /**
     * @var array
     */
    private $queriesDeparture8;
    /**
     * @var array
     */
    private $queriesDestination8;

    public function __construct(
        int $numberOfLifts,
        ObjectManager $em,
        LiftRepository $liftRepository
    )
    {
        $this->em = $em;
        $this->liftRepository = $liftRepository;

        $this->liftRepository->deleteTable();

        for ($i = 0; $i < $numberOfLifts; $i++) {
            $lift = Lift::create();
            $lift->setId($i + 1);
            $lift->setFloorsTraveled(0);
            $this->lifts[] = $lift;
            $this->em->persist($lift);
        }

        $this->em->flush();

        $this->initQueriesData();
    }

    private function hoursToMinutes(int $hour): int {
        return $hour * 60;
    }

    /**
     * Se crean rangos de intervalos convirtiendo horas a minutos del día.
     *
     * @param $initHour
     * @param $finalHour
     * @param $step
     * @return array
     */
    private function calculateRangesOfQueries($initHour, $finalHour, $step): array {
        return range(
            $this->hoursToMinutes($initHour),
            $this->hoursToMinutes($finalHour),
            $step
        );
    }

    /**
     * Iniciliza los valores de las peticiones posibles a lo largo del horario laboral.
     * Se crea un rango para cada intervalo de peticiones.
     * Se crea un array por cada salidas y destinos de los intervalos de las peticiones
     */
    private function initQueriesData(): void
    {
        $this->rangeOfWorkingTimes = $this->calculateRangesOfQueries(9, 20, 1);
        $this->queryRangeTimes1 = $this->calculateRangesOfQueries(9, 11, 5);
        $this->queryRangeTimes2 = $this->calculateRangesOfQueries(9, 11, 5);
        $this->queryRangeTimes3 = $this->calculateRangesOfQueries(9, 10, 10);
        $this->queryRangeTimes4 = $this->calculateRangesOfQueries(11, 18 + 1/3, 20);
        $this->queryRangeTimes5 = $this->calculateRangesOfQueries(14, 15, 4);
        $this->queryRangeTimes6 = $this->calculateRangesOfQueries(15, 16, 7);
        $this->queryRangeTimes7 = $this->calculateRangesOfQueries(15, 16, 7);
        $this->queryRangeTimes8 = $this->calculateRangesOfQueries(18, 20, 3);

        $this->queriesDeparture1 = [0];
        $this->queriesDestination1 = [2];
        $this->queriesDeparture2 = [0];
        $this->queriesDestination2 = [3];
        $this->queriesDeparture3 = [0];
        $this->queriesDestination3 = [1];
        $this->queriesDeparture4 = [0];
        $this->queriesDestination4 = [1,2,3];
        $this->queriesDeparture5 = [1,2,3];
        $this->queriesDestination5 = [0];
        $this->queriesDeparture6 = [2,3];
        $this->queriesDestination6 = [0];
        $this->queriesDeparture7 = [0];
        $this->queriesDestination7 = [1,3];
        $this->queriesDeparture8 = [1,2,3];
        $this->queriesDestination8 = [0];
    }

    /**
     * Primero trata de seleccionar el ascensor más cercano que todavía no haya hecho ningún movimiento,
     * y repite en bucle hasta que encuentra uno a más distancia. Si no lo encuentra, seleccionará el
     * más cercano con menos movimientos totales.
     *
     * @param $queryCallFloor
     * @param $actualTime
     * @return Lift|null
     */
    private function findNearestLift($queryCallFloor, $actualTime) {
        $floorDistance = 0;
        $maxDistance = 3;
        $liftSelected = NULL;

        while (true) {
            /** @var Lift */
            $liftSelected = $this->liftRepository->findOneByLessFloorDistanceAndTimeLessThanActual(
                $queryCallFloor,
                $floorDistance,
                $actualTime
            );

            if ($liftSelected ||
                (!$liftSelected && $floorDistance === $maxDistance  )
            ){
                $floorDistance = 0;

                break;
            }

            $floorDistance++;
        }

        while ($liftSelected === NULL) {
            $liftSelected = $this->liftRepository->findOneByLessFloorDistance(
                $queryCallFloor,
                $floorDistance
            );

            $floorDistance++;
        }

        return $liftSelected;
    }

    /**
     * Procesa todas las peticiones de ese instante
     *
     * @param array $queriesAtActualTime
     * @param int $actualTime
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function processActualQueries(array $queriesAtActualTime, int $actualTime): void
    {
        foreach ($queriesAtActualTime as $query) {
            $liftSelected = clone $this->findNearestLift($query[0], $actualTime);
            $liftSelected->moveFloorToCallingFloor($query[0]);
            $liftSelected->moveFloorToDestination($query[1]);
            $liftSelected->setMinuteOfDay($actualTime);

            $this->em->persist($liftSelected);
            $this->em->flush();
        }
    }

    /**
     * Función principal para calcular los movimientos de los ascensores y lalmacenarlos en la base de datos
     */
    public function simulateDailyLiftMovements(): void
    {
        for ($actualTime = $this->rangeOfWorkingTimes[0];
             $actualTime <= $this->rangeOfWorkingTimes[count($this->rangeOfWorkingTimes) - 1];
             $actualTime++) {

            $queriesAtActualTime = [];
            for ($j = 1; $j <= 8; $j++) {
                $queryRangeTimes = "queryRangeTimes" . $j;
                if (in_array($actualTime, $this->$queryRangeTimes)) {
                    $queriesDeparture = "queriesDeparture" . $j;
                    $queriesDestination = "queriesDestination" . $j;

                    foreach ($this->$queriesDeparture as $queryDeparture) {
                        foreach ($this->$queriesDestination as $queryDestination) {
                            $queriesAtActualTime[] = [$queryDeparture, $queryDestination];
                        }
                    }
                }
            }

            if (count($queriesAtActualTime)) {
                $this->processActualQueries($queriesAtActualTime, $actualTime);
            }
        }
    }

    /**
     * Devuelve todas las peticiones y su respectiva información
     *
     * @return Lift[]
     */
    public function getTableOfLiftMovements()
    {
        return $this->liftRepository->findBy(
            [],
            ['minuteOfDay' => 'ASC', 'id' => 'ASC', 'totalFloorsTraveledByDay' => 'ASC'],
            null,
            null);
    }
}