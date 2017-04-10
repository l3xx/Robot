<?php

namespace Letunovskiymn\KorablikBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;



class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {

        $conn=$this->getDoctrine();
        return $this->render('LetunovskiymnKorablikBundle:Default:index.html.twig');
    }
}
