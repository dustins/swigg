<?php

namespace Swigg\Bundle\TwigBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class DefaultController extends Controller
{
    
    public function indexAction($name)
    {
        return $this->render('SwiggTwigBundle:Default:index.html.twig', array('name' => $name));
    }
}
