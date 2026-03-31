<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
use Symfony\Component\Form\Extension\Core\Type\WeekType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\Organization\EmployeeType;
use App\Entity\Organization\Employee;

class TestFormDatesController extends AbstractController
{
    #[Route('/test-form-dates-check', name: 'test_form_dates_check')]
    public function index(Request $request): Response
    {
        $employee = new Employee();
        $employee->setName('Test User');
        $employee->setEmployeeNo('TEST-001');
        $form = $this->createForm(EmployeeType::class, $employee);

        $form->handleRequest($request);

        return $this->render('employee/edit.html.twig', [
            'form' => $form->createView(),
            'employee' => $employee,
        ]);
    }
}
