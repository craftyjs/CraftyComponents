<?php

namespace FWM\CraftyComponentsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use FWM\ServicesBundle\Services\ArrayService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class BoilerplateController extends Controller
{
    /**
     * @Route("/boilerplate", name="fwm_crafty_boilerplate")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        return array();
    }
}