<?php

namespace PathologyBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('PathologyBundle:Default:index.html.twig');
    }
}
