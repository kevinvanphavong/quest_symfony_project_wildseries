<?php


namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Episode;
use App\Entity\Program;
use App\Entity\Season;
use App\Form\CommentType;
use App\Form\EpisodeType;
use App\Form\ProgramType;
use App\Service\Slugify;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ProgramController
 * @Route("/programs", name="program_")
 */

Class ProgramController extends AbstractController
{
    /**
     * The controller for the category add form
     * @param Request $request
     * @param Slugify $slugify
     * @param MailerInterface $mailer
     * @return Response
     * @Route("/new", name="new")
     * @throws TransportExceptionInterface
     */
    public function new(Request $request, Slugify $slugify, MailerInterface $mailer) : Response
    {
        // Create a new Category Object
        $program = new Program();
        // Create the associated Form
        $form = $this->createForm(ProgramType::class, $program);
        // Render the form
        // Get data from HTTP request
        $form->handleRequest($request);
        // Was the form submitted ?
        if ($form->isSubmitted() && $form->isValid()) {
            // Deal with the submitted data
            $entityManager = $this->getDoctrine()->getManager();
            $slug = $slugify->generate($program->getTitle());
            $program->setSlug($slug);
            // Persist Category Object
            $entityManager->persist($program);
            // Flush the persisted object
            $entityManager->flush();
            $email = (new Email())
                ->from($this->getParameter('mailer_from'))
                ->to('your_email@example.com')
                ->subject('Nouvelle série, en avant !')
                ->html($this->renderView('program/newProgramEmail.html.twig', ['program' => $program]));

            $mailer->send($email);
            // [...]
            // Finally redirect to categories list
            return $this->redirectToRoute('program_index');
        }
        return $this->render('program/new.html.twig', [
            "form" => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="edit", methods={"GET","POST"})
     * @param Request $request
     * @param Program $program
     * @return Response
     */
    public function edit(Request $request, Program $program): Response
    {
        $form = $this->createForm(ProgramType::class, $program);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_programs');
        }

        return $this->render('program/edit.html.twig', [
            'program' => $program,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="delete", methods={"DELETE"})
     * @param Request $request
     * @param Program $program
     * @return Response
     */
    public function delete(Request $request, Program $program): Response
    {
        if ($this->isCsrfTokenValid('delete'.$program->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($program);
            $entityManager->flush();
        }

            return $this->redirectToRoute('admin_program');
    }

    /**
     * @Route("/", name="index")
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
     * @Route("/{slug}",
     *     methods={"GET"},
     *     name="show")
     * @param Program $program
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
     *     methods={"GET", "POST"},
     *     name="episode_show")
     *
     * @param Program $program
     * @param Season $season
     * @param Episode $episode
     * @param Request $request
     * @return Response
     */
    public function showEpisodes(Program $program, Season $season, Episode $episode, Request $request): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $comment->setEpisode($episode);
            $comment->setAuthor($this->getUser());
            $entityManager->persist($comment);
            $entityManager->flush();
            return $this->redirectToRoute('program_index');
        }
        return $this->render('program/episode_show.html.twig', [
            'program' => $program,
            'episode' => $episode,
            'season' => $season,
            'form' => $form->createView(),
        ]);
    }
}
