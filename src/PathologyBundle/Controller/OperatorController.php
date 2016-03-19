<?php

namespace PathologyBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use PathologyBundle\Entity\User;
use PathologyBundle\Form\UserOperatorType;
use PathologyBundle\Form\UserPatientType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class OperatorController extends Controller
{
    const USER_ROLE_PATIENT = 'ROLE_PATIENT';

    /**
     * Add patient.
     */
    public function registerPatientAction(Request $request)
    {
        // Build the form.
        $user = new User();
        $form = $this->createForm(UserPatientType::class, $user);

         // Add pathology tests option to form.
        $pathlogyTests = $this->getDoctrine()
            ->getRepository('PathologyBundle:PathologyTest')
            ->findAll();
        foreach ($pathlogyTests as $pathlogyTest) {
            $pathlogyTestsName[$pathlogyTest->getId()] = $pathlogyTest->getName();
        }
        $form->add('tests', ChoiceType::class, array(
            'choices' => $pathlogyTestsName,
            'mapped' => false,
            'multiple' => true,
            'expanded' => true,
        ));

        // Handle the submit (will only happen on POST).
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $tests = $form['tests']->getData();
            foreach ($tests as $key => $selected_test) {
                $pathlogyTest = $this->getDoctrine()
                    ->getRepository('PathologyBundle:PathologyTest')
                    ->find($selected_test);
                $user->addPathologyTest($pathlogyTest);
            }
            // Encode the password.
            $password = $this->get('security.password_encoder')
                ->encodePassword($user, $user->getPassword());
            $user->setPassword($password);
            $user->setRole(self::USER_ROLE_PATIENT);

            // Flush User entity to db.
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('view_patients');
        }

        return $this->render('PathologyBundle:Operator:register_patient.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * Edit patient action.
     */
    public function editPatientAction(Request $request)
    {
        $patient_id = trim($request->get('patientId'));
        $patient = $this->getDoctrine()
            ->getRepository('PathologyBundle:User')
            ->find($patient_id);

        // Build the form.
        $form = $this->createForm(UserPatientType::class, $patient);
        $form->remove('password');

         // Add pathology tests option to form.
        $pathlogyTests = $this->getDoctrine()
            ->getRepository('PathologyBundle:PathologyTest')
            ->findAll();
        foreach ($pathlogyTests as $pathlogyTest) {
            $pathlogyTestsName[$pathlogyTest->getId()] = $pathlogyTest->getName();
        }

        $selected_tests = $patient->getPathologyTest();
        $default_values = array();
        if (!empty($selected_tests)) {
            foreach ($selected_tests as $selected_test) {
                $default_values[] = $selected_test->getId();

                // Remove all existing patholoy test from patient.
                $patient->removePathologyTest($selected_test);
            }
        }
        $form->add('tests', ChoiceType::class, array(
            'choices' => $pathlogyTestsName,
            'mapped' => false,
            'multiple' => true,
            'expanded' => true,
            'data' => $default_values,
        ));

        // Handle the submit (will only happen on POST).
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // Set newly selected tests.
            $tests = $form['tests']->getData();
            foreach ($tests as $key => $selected_test) {
                $pathlogyTest = $this->getDoctrine()
                    ->getRepository('PathologyBundle:PathologyTest')
                    ->find($selected_test);
                $patient->addPathologyTest($pathlogyTest);
            }

            // Flush updated patient to DB.
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('view_patients');
        }

        return $this->render('PathologyBundle:Operator:register_patient.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * View all registered Patients.
     */
    public function viewPatientsAction(Request $request)
    {
        $patients = $this->getDoctrine()
            ->getRepository('PathologyBundle:User')
            ->findUsersByRole(self::USER_ROLE_PATIENT);

        return $this->render('PathologyBundle:Operator:view_patients.html.twig', array(
            'patients' => $patients
        ));
    }

    /**
     * Delete patient.
     */
    public function deletePatientAction(Request $request)
    {
        $patient_id = trim($request->get('patientId'));
        $docrtine = $this->getDoctrine();
        $patient = $docrtine->getRepository('PathologyBundle:User')
            ->find($patient_id);

        if (!empty($patient)) {
            $em = $docrtine->getManager();
            $em->remove($patient);
            $em->flush();
        }

        return $this->redirectToRoute('view_patients');
    }

}
