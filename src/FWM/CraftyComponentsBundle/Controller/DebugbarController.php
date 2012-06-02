<?php

namespace FWM\CraftyComponentsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use FWM\ServicesBundle\Services\ArrayService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class DebugbarController extends Controller
{
    /**
     * @Route("/debugbar", name="fwm_crafty_debugbar")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        return array();
    }
}