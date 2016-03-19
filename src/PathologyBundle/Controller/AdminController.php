<?php

namespace PathologyBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use PathologyBundle\Entity\User;
use PathologyBundle\Form\UserOperatorType;
use PathologyBundle\Form\PathologyTestType;
use PathologyBundle\Entity\PathologyTest;

class AdminController extends Controller
{
    const USER_ROLE_OPERATOR = 'ROLE_OPERATOR';

    /**
     * Callback for create Operation action.
     */
    public function createOperatorAction(Request $request)
    {
        // Build the form.
        $user = new User();
        $form = $this->createForm(UserOperatorType::class, $user);

        // Handle the submit (will only happen on POST).
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // Encode the password.
            $password = $this->get('security.password_encoder')
                ->encodePassword($user, $user->getPassword());
            $user->setPassword($password);
            $user->setRole(self::USER_ROLE_OPERATOR);

            // Flush user entity to DB.
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            // Redirect to view operator route.
            return $this->redirectToRoute('view_operators');
        }

        return $this->render('PathologyBundle:Admin:create_operator.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * List all operators.
     */
    public function viewOperatorsAction()
    {
        // Find all users with role Operator.
        $operators = $this->getDoctrine()
            ->getRepository('PathologyBundle:User')
            ->findUsersByRole(self::USER_ROLE_OPERATOR);

        return $this->render('PathologyBundle:Admin:view_operators.html.twig', array(
            'operators' => $operators,
        ));
    }

    /**
     * Create pathology lab test.
     */
    public function createPathologyTestAction(Request $request)
    {
        // Build the form.
        $pathlogyTest = new PathologyTest();
        $form = $this->createForm(PathologyTestType::class, $pathlogyTest);

        // Handle the submit (will only happen on POST).
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // Flush PathologyTest entity to DB.
            $em = $this->getDoctrine()->getManager();
            $em->persist($pathlogyTest);
            $em->flush();

            return $this->redirectToRoute('view_pathology_tests');
        }

        return $this->render('PathologyBundle:Admin:create_pathology_test.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * List all pathology tests.
     */
    public function viewPathologyTestsAction(Request $request)
    {
        // Find all pathology tests.
        $pathlogyTests = $this->getDoctrine()
            ->getRepository('PathologyBundle:PathologyTest')
            ->findAll();

        return $this->render('PathologyBundle:Admin:view_pathology_tests.html.twig', array(
            'pathlogyTests' => $pathlogyTests,
        ));
    }


}
