<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TopAppController extends Controller
{
    /**
     * @Route("/", name="kinopoisk top 10")
     */
    public function indexAction(Request $request)
    {
		$js = array(
			'js/libs/angular.js',
			'js/libs/angular-route.js',
			'js/apps/TopApp.js',
			'js/apps/controllers/FilmController.js',
			'js/apps/controllers/TopController.js',
		);
		$css = array(
			'css/TopApp.css',
			'css/table-styles.css',
		);
        return new Response($this->render('app/top-app.html.twig', array('js' => $js, 'css' => $css, 'title' => 'Top 10',)));
    }
}
