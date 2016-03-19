<?php

namespace PathologyBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    public function indexAction(Request $request)
    {
        // Load current user and redirect to appropriate template on the basis of role.
        $current_user = $this->get('security.token_storage')->getToken()->getUser();
        if ($this->isGranted(IS_AUTHENTICATED_FULLY)) {
            $roles = $current_user->getRoles();
            if (in_array('ROLE_ADMIN', $roles)) {
                return $this->render('PathologyBundle:admin_dashboard.html.twig');
            }
            elseif (in_array('ROLE_PATIENT', $roles)) {
                return $this->render('PathologyBundle:patient_dashboard.html.twig');
            }
            elseif (in_array('ROLE_OPERATOR', $roles)) {
                return $this->render('PathologyBundle:operator_dashboard.html.twig');
            }
        }

        return $this->render('PathologyBundle:Default:index.html.twig');
    }
}
