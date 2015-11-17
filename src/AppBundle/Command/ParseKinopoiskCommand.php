<?php
namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use \AppBundle\Entity\Film;
use \AppBundle\Entity\TopPosition;

class ParseKinopoiskCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('kinopoisk:parse')
            ->setDescription('Parse Kinopoisk.ru and load top')
			->addArgument(
                'date',
                InputArgument::OPTIONAL,
                'Date in format 2015-11-13'
            )
			->addOption(
                'rewrite',
                null,
                InputOption::VALUE_NONE,
                'Rewrite top if it already exist'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
		$date = $input->getArgument('date');
		$rewrite = $input->getOption('rewrite');
		
		
        $output->writeln('Scenario started at '. date('d.m.Y H:i'));
		if (!$date){
			$date = new \DateTime();
			$url = 'http://www.kinopoisk.ru/top';
		} else {
			$url = 'http://www.kinopoisk.ru/top/day/' . $date;
			$date = \DateTime::createFromFormat('Y-m-d', $date);
			$errors = \DateTime::getLastErrors();
			if ($errors['warning_count'] || $errors['error_count']){
				$output->writeln('Bad date format');
				return;
			}
		}
		
		$output->writeln('Getting data from ' . $url);
		try{
			$data = $this->ParseTop($url);
			$date->setTime(0, 0, 0);
			
			$top = $this
				->getContainer()
				->get('doctrine')
				->getEntityManager()
				->getRepository('AppBundle:TopPosition')
				->findBy(array('date' => $date,))
			;

			$topEntityManager = $this
				->getContainer()
				->get('doctrine')
				->getEntityManager()
			;

			if ($rewrite && !empty($top)){//If we want to rewrite existing top
				$output->writeln('Deleting existing top for ' . $date->format('Y-m-d'));
				foreach ($top as $topPosition){
					$topEntityManager->remove($topPosition);
				}
				$topEntityManager->flush();
				$topEntityManager->clear();
			} else if (!empty($top)) {
				$output->writeln('Top for this date ' . $date->format('Y-m-d') . ' already exists use --rewrite flag to update it');
				return;
			}
			
			$filmEntityManager = $this
				->getContainer()
				->get('doctrine')
				->getEntityManager()
				->getRepository('AppBundle:Film')
			;
			$filmsExist = $filmEntityManager->findByKinopoiskId($data['film_ids']);
			
			$findFilm = function ($id) use ($filmsExist) {
				foreach ($filmsExist as $index => $film){
					if ((int)$id === $film->getKinopoiskId()){
						return $filmsExist[$index];
					}
				}
				return false;			
			};
			
			$filmsCreated = 0;
			foreach ($data['top'] as $topPositionData){
				$topPosition = new TopPosition();
				$topPosition->setPlace($topPositionData['position']);
				$topPosition->setVotes($topPositionData['votes']);
				$topPosition->setRaiting($topPositionData['raiting']);
				$topPosition->setDate($date);

				if (!$film = $findFilm($topPositionData['id'])){
					$film = new Film();
					$filmsCreated++;

					$film->setOriginalName($topPositionData['original_name']);
					$film->setName($topPositionData['name']);
					$film->setYear($topPositionData['year']);
					$film->setKinopoiskId($topPositionData['id']);
				}
				$topPosition->setFilm($film);

				$topEntityManager->persist($topPosition);
			}
			$output->writeln("$filmsCreated films created");
			$topEntityManager->flush();
			$topEntityManager->clear();
		}
		catch (Exception $e){//If something went wrong on site parsing or else
			$output->writeln($e->getMessage());
			return;
		}
		
        $output->writeln('Success!');
    }

	private function getRegexpForTag($tag, array $attrs, $text = null)
	{
		if (!$text){
			$text = "([\S\s]*?)";
		}
		$attrs_str = '';
		foreach ($attrs as $attr => $value){
			$attrs_str .= $attr . '="' . $value . '"[^>]*';
		}
		return '#<' . $tag . '[^>]*'. $attrs_str . '>' . $text . '</' . $tag . '>#';
	}

	private function parseTop($topPage)
	{
		$top_position_regex = $this->getRegexpForTag('tr', array('id' => 'top250_place_(\d{1,3})'));	
		$name_and_year_regex = $this->getRegexpForTag('a', array('href' => '/film/(\d+)/', 'class' => 'all',), '(.*)\s\((\d{4,4})\)');
		$raiting_regex = $this->getRegexpForTag('a', array('class' => 'continue'), '(\d\.\d{3,3})');
		$votes_regex = $this->getRegexpForTag('span', array('style' => 'color: \#777'), '\((\d{1,3})\&nbsp;(\d{3,3})\)');
		$original_name_regex = $this->getRegexpForTag('span', array('class' => 'text-grey'));

		if (!$content = file_get_contents($topPage)){
			throw new Exception('Can\'t connect to ' . $topPage);
		}
		$top_positions = array();

		preg_match_all($top_position_regex, $content, $top_positions);
				
		if (count($top_positions[1]) != 250){
			throw new Exception('Can\'t find top on page ' . $topPage);
		}		

		$result = array();
		for ($i = 0; $i < 249; $i++){
			$content = $top_positions[2][$i];
			$place = $top_positions[1][$i];
			
			preg_match($name_and_year_regex, $content, $matches);
			$result['top'][$place] = array(
				'position' => $place,
				'id' => $matches[1],
				'name' => $matches[2],
				'year' => $matches[3],
			);
			$result['film_ids'][] = $matches['1'];
			
			preg_match($raiting_regex, $content, $matches);
			$result['top'][$place] += array(
				'raiting' => $matches[1],
			);
			
			preg_match($votes_regex, $content, $matches);
			$result['top'][$place] += array(
				'votes' => $matches[1] * 1000 + $matches[2],
			);
			
			if (preg_match($original_name_regex, $content, $matches)){
				$result['top'][$place] += array(
					'original_name' => $matches[1],
				);
			} else {
				$result['top'][$place] += array(
					'original_name' => null,
				);
			}
		}
		return $result;
	}
}

