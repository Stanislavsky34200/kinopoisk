<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TopApiController extends Controller
{
    /**
     * @Route("/api/top", name="kinopoisk top api")
     */
    public function apiInfoAction(Request $request)
    {
		$apis = array(
			'Get film data' => array(
				'path' => '/api/top/get_film_data/{id}',
				'params' => array(
					'id' => array('type' => 'int positive', 'decription' => 'film id',)
				),
			),
			'Get top data' => array(
				'path' => '/api/top/get_top_data/{date}',
				'params' => array(
					'date' => array('type' => 'string', 'decription' => 'Date in valid iso format yyyy-MM-dd',)
				),
			),
		);
        return new Response($this->render('api/api-info.html.twig', array(
			'title' => 'Top 10 api',
			'apis' => $apis,
			'css' => array('css/table-styles.css',),	
		)));
    }
    /**
     * @Route("/api/top/get_film_data/{filmId}", name="Get film")
     */
    public function getFilmAction($filmId, Request $request)
    {
		$film = $this
			->getDoctrine()
			->getRepository('AppBundle:Film')
			->find($filmId)
		;
		if ($film){
			$result['film'] = (object)$film;
			$result['statistics'] = $this
				->getDoctrine()
				->getRepository('AppBundle:TopPosition')
				->findBy(
					array('film' => $film,), 
					array('date' => 'DESC',),
					10
				)
			;
		} else {
			$result = false;
		}
		$response = new Response(json_encode((object)$result));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}
	/**
     * @Route("/api/top/get_top_data/{date}", name="Get top")
     */
	public function getTopAction($date, Request $request)
    {
		$date = \DateTime::createFromFormat('Y-m-d', $date);
		$errors = \DateTime::getLastErrors();
		if ($errors['warning_count'] || $errors['error_count'])	{
			$result = false;
		} else {
			$date->setTime(0,0,0);
			$positions = $this
				->getDoctrine()
				->getRepository('AppBundle:TopPosition')
				->findBy(
					array('date' => $date,), 
					array('place' => 'ASC',),
					10
				)
			;
			if (empty($positions)){
				$result = false;
			} else {
				$posIds = array();
				foreach ($positions as $position){
					$films[] = $position->getFilm()->getId();
				}
				$positionsBefore = $this
					->getDoctrine()
					->getRepository('AppBundle:TopPosition')
					->findBy(
						array('film' => $films, 'date' => $date->modify('-1 day'),), 
						array('place' => 'ASC',),
						10
					)
				;
				$result['before'] = $positionsBefore;
				$setDeltas = function($position) use ($positionsBefore) {
					foreach ($positionsBefore as $i => $pos){
						if ($pos->getFilm()->getId() === $position->getFilm()->getId()){						
							return array(
								'delta_raiting' => $position->getRaiting() - $pos->getRaiting(),
								'delta_votes' => $position->getVotes() - $pos->getVotes(),
							);
						}
					}
					return array(
						'delta_raiting' => null,
						'delta_votes' => null,
					);
				};
				$result = array();
				foreach ($positions as $position){
					$pos = array(
						'film' => $position->getFilm(),
						'votes' => $position->getVotes(),
						'raiting' => $position->getRaiting(),
						'place' => $position->getPlace(),
					);
					$pos += $setDeltas($position);
					$result[] = (object)$pos;
				}
			}
		}
		$response = new Response(json_encode($result));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}
}
