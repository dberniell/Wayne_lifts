<?php

namespace App\Controller;

use App\Repository\LiftRepository;
use App\Services\GenerateDayReportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Routing\Annotation\Route;

class GenerateReportController extends AbstractController
{
    /**
     * @Route("/{numLifts}", defaults={"numLifts"=3}, requirements={"numLifts"="\d+"}, name="getReport")
     *
     * @param int $numLifts
     * @param LiftRepository $liftRepository
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getReport(int $numLifts, LiftRepository $liftRepository)
    {
        $numLifts = $_GET['form']['numLifts'] ?? $numLifts;

        $em = $this->getDoctrine()->getManager();
        $generateDayReportService = new GenerateDayReportService(
            $numLifts,
            $em,
            $liftRepository
        );
        $generateDayReportService->simulateDailyLiftMovements();
        $requests = $generateDayReportService->getTableOfLiftMovements();

        // Creo formulario para poder cambiar el número de ascensores dinámicamente
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('getReport'))
            ->setMethod('GET')
            ->add('numLifts', NumberType::class, ['data' => $numLifts])
            ->add('save', SubmitType::class)
            ->getForm();

        return $this->render('Lifts/reportTable.html.twig', [
            'numLifts' => $numLifts,
            'requests' => $requests,
            'form' => $form->createView()
        ]);
    }
}