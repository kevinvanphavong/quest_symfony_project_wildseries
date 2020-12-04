<?php


namespace App\Controller;

use App\Entity\Episode;
use App\Entity\Program;
use App\Entity\Season;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ProgramController
 * @Route("/programs", name="program_")
 */

Class ProgramController extends AbstractController
{
    /**
     * @Route("/", name="index")
     *
     * @return Response A response instance
     */
    public function index(): Response
    {
        $programs = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findAll();

        return $this->render(
            'program/index.html.twig',
            [
                'website' => 'Wild Séries',
                'programs' => $programs]
        );
    }

    /**
     * @Route("/show/{id}",requirements={"id"="\d+"},
     *     methods={"GET"},
     *     name="show")
     *
     * @return Response
     */
    public function show(Program $program):Response
    {
//        $program = $this->getDoctrine()
//            ->getRepository(Program::class)
//            ->findOneBy(['id' => $id]);

        if (!$program) {
            throw $this->createNotFoundException(
                'No program with id : '.$program.' found in program\'s table.'
            );
        }
//        var_dump($program);die();
        $seasons = $program->getSeasons();
        return $this->render('program/show.html.twig', [
            'program' => $program,
            'seasons' => $seasons,
        ]);
    }

    /**
     * @Route("/{program}/seasons/{season}",
     *     requirements={"program"="\d+", "season"="\d+"},
     *     methods={"GET"},
     *     name="season_show")
     *
     * @param Program $program
     * @param Season $season
     *
     * @return Response
     */
    public function showSeason(Program $program, Season $season): Response
    {
//        $program = $this->getDoctrine()
//            ->getRepository(Program::class)
//            ->findOneBy(['id' => $programId]);
//
//        $season = $this->getDoctrine()
//            ->getRepository(Season::class)
//            ->findOneBy(['id' => $seasonId]);

        if (!$season) {
            throw $this->createNotFoundException(
                'No seasons with id : '.$program.' found in season\'s table.'
            );
        }

        $episodes = $season->getEpisodes();

        return $this->render('program/season_show.html.twig', [
            'program' => $program,
            'episodes' => $episodes,
            'season' => $season,
        ]);
    }

    /**
     * @Route("/{program}/seasons/{season}/episode/{episode}",
     *     requirements={"program"="\d+", "season"="\d+", "episode"="\d+"},
     *     methods={"GET"},
     *     name="episode_show")
     *
     * @param Program $program
     * @param Season $season
     * @param Episode $episode
     *
     * @return Response
     */
    public function showEpisodes(Program $program, Season $season, Episode $episode): Response
    {
//        var_dump(
//            $program,
//            $season,
//            $episode
//        );die();
        return $this->render('program/episode_show.html.twig', [
            'program' => $program,
            'episode' => $episode,
            'season' => $season,
        ]);
    }
}