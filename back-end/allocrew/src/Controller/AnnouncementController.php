<?php

namespace App\Controller;

use DateTime;
use App\Entity\User;
use App\Entity\Announcement;
use App\Form\AnnouncementType;
use App\Repository\UserRepository;
use App\Repository\AnnouncementRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;




/**
 * @Route("/api/announcements", name="api_announcements_")
 */
class AnnouncementController extends AbstractController
{
    /**
     * @Route("/", name="browse", methods={"GET"})
     */
    public function browse(AnnouncementRepository $announcementRepository,  SerializerInterface $serializer)
    {
        $announcements = $announcementRepository->findAll();

        return $this->json($serializer->normalize(
            $announcements,
            null,
            ['groups' => ['announcement']]
        ));
    }

    /**
     * @Route("/{id}", name="read", requirements={"id": "\d+"},  methods={"GET"})
     */
    public function read(AnnouncementRepository $AnnouncementRepository, $id, SerializerInterface $serializer)
    {
        $announcement = $AnnouncementRepository->findBy(array('id' => $id));

        if (!empty($announcement)) {
            return $this->json($serializer->normalize(
                $announcement,
                null,
                ['groups' => ['announcement']]
            ));
        } else {
            return new Response("L'annonce n'est pas en base de données  ", 404);
        }
    }

    /**
     * @Route("/{id}", name="edit", methods={"PATCH"}, requirements={"id": "\d+"})
     *
     */
    public function edit(Announcement $announcement, Request $request, $id, UserRepository $userRepository, AnnouncementRepository $announcementRepository)
    {
        $announcement = $this->getDoctrine()->getRepository(Announcement::class)->findOneBy(["id" => $id]);
        $announcement->setUser($announcement->getUser());

        // On décode les données envoyées
        $donnees = json_decode($request->getContent(), true);
        /** On verifie si la propriété est envoyé dans le json si oui on hydrate l'objet 
         * sinon on passe à la suite */
        $form = $this->createForm(AnnouncementType::class, $announcement);

        $user = $userRepository->find($form['user']->getData());
        $category = $announcementRepository->find($announcement)->getCategory();
        $active = $announcementRepository->find($announcement)->getActive();
        $voluntary = $announcementRepository->find($announcement)->getVoluntary();
        $date_start =  $announcementRepository->find($announcement)->getDateStart();

        $date_end =  $announcementRepository->find($announcement)->getDateEnd();

        $location = $announcementRepository->find($announcement)->getLocation();

        $title = $announcementRepository->find($announcement)->getTitle();

        $description = $announcementRepository->find($announcement)->getDescription();

        if ($announcementRepository->find($announcement)->getPicture() !== null) {
            $picture = $announcementRepository->find($announcement)->getPicture();
        } else {
            $picture = 'default';
        }
        $form->submit($donnees);

        if ($form->isSubmitted()) {
            if ($form['category'] !== null) {
                if ($form['category']->isValid()) {
                    $announcement->setCategory($form['category']->getData());
                } else return new Response('Catégorie Invalide', 400);
            } else {
                $form['category'] = $category;
            }

            if ($form['active'] !== null) {
                if ($form['active']->isValid()) {
                    $announcement->setActive($form['active']->getData());
                } else return new Response('Statut Invalide', 400);
            } else {
                $announcement->setActive($active);
            }

            if ($form['voluntary'] !== null) {
                if ($form['voluntary']->isValid()) {

                    $announcement->setVoluntary($form['voluntary']->getData());
                } else return new Response('Statut Voluntary Invalide', 400);
            } else {
                $announcement->setVoluntary($voluntary);
            }


            if ($form['date_start'] !== null) {
                $date = new DateTime($form['date_start']->getViewData());
                $announcement->setDateStart($date);
            } else $announcement->setDateStart($date_start);


            if ($form['date_end'] !== null) {
                $date = new DateTime($form['date_end']->getViewData());
                $announcement->setDateEnd($date);
            } else $announcement->setDateEnd($date_end);

            if ($form['location'] !== null) {
                if ($form['location']->isValid()) {

                    $announcement->setLocation($form['location']->getData());
                } else return new Response('Ville Invalide', 400);
            } else {
                $announcement->setLocation($location);
            }
            if ($form['title'] !== null) {
                if ($form['title']->isValid()) {

                    $announcement->setTitle($form['title']->getData());
                } else return new Response('Titre Invalide', 400);
            } else {
                $announcement->setTitle($title);
            }
            if ($form['description'] !== null) {
                if ($form['description']->isValid()) {

                    $announcement->setVoluntary($form['description']->getData());
                } else return new Response('Description Invalide', 400);
            } else {
                $announcement->setDescription($description);
            }
            // if ($form['picture'] !== null) {
            //     if ($form['picture']->isValid()) {
            //         /** @var UploadImage 
            //          * $uploadedFile */
            //         $uploadedFile = $form['picture']->getData();


            //         $destination = $this->getParameter('kernel.project_dir') . '/public/uploads/Picture';
            //         $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            //         $newFilename = $originalFilename . '-' . uniqid() . '.' . $uploadedFile->guessExtension();
            //         $uploadedFile->move(
            //             $destination,
            //             $newFilename
            //         );
            //         $announcement->setPicture($newFilename);
            //     } else return new Response('Photo Invalide', 400);
            // } else { $announcement->setPicture($picture); } 
            // $announcement->setUser($user);
        };

        $announcement->setUpdatedAt(new \Datetime());


        // On sauvegarde en base
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($announcement);
        $entityManager->flush();

        // On retourne la confirmation
        return new Response('ok', 204);
    }

    /**
     * @Route("/", name="add", methods={"POST"})
     */
    public function add(Request $request, UserRepository $userRepository)
    {
        $announcement = new Announcement();

        // On décode les données envoyées
        $donnees = json_decode($request->getContent(), true);
        /** On verifie si la propriété est envoyé dans le json si oui on hydrate l'objet 
         * sinon on passe à la suite */
        $form = $this->createForm(AnnouncementType::class, $announcement);
        $form->submit($donnees);

        if ($form->isSubmitted()) {
            if (isset($form['category'])) {
                if ($form['category']->isValid()) {
                    $announcement->setCategory($form['category']->getData());
                } else return new Response('Catégorie Invalide', 400);
            }

            if (isset($form['active'])) {
                if ($form['active']->isValid()) {
                    $announcement->setActive($form['active']->getData());
                } else return new Response('Statut Invalide', 400);
            }

            if (isset($form['voluntary'])) {
                if ($form['voluntary']->isValid()) {

                    $announcement->setVoluntary($form['voluntary']->getData());
                } else return new Response('Statut Volontary Invalide', 400);
            }


            if (isset($form['date_start'])) {
                $date = new DateTime($form['date_start']->getViewData());
                $announcement->setDateStart($date);
            } else return new Response('Date de départ invalide', 400);


            if (isset($form['date_end'])) {

                $date = new DateTime($form['date_end']->getViewData());
                $announcement->setDateEnd($date);
            } else return new Response('Date de fin invalide', 400);
            if (isset($form['location'])) {
                if ($form['location']->isValid()) {

                    $announcement->setLocation($form['location']->getData());
                } else return new Response('Ville Invalide', 400);
            }
            if (isset($form['title'])) {
                if ($form['title']->isValid()) {

                    $announcement->setTitle($form['title']->getData());
                } else return new Response('Titre Invalide', 400);
            }
            if (isset($form['description'])) {
                if ($form['description']->isValid()) {

                    $announcement->setDescription($form['description']->getData());
                } else return new Response('Description Invalide', 400);
            }
            // if (isset($form['picture'])) {
            //     if ($form['picture']->isValid()) {
            //         /** @var UploadImage 
            //          * $uploadedFile */
            //         $uploadedFile = $form['picture']->getData();

            //         dd($form['picture']);
            //         $destination = $this->getParameter('kernel.project_dir') . '/public/uploads/AnnouncementPicture';
            //         $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            //         $newFilename = $originalFilename . '-' . uniqid() . '.' . $uploadedFile->guessExtension();
            //         $uploadedFile->move(
            //             $destination,
            //             $newFilename
            //         );
            //         $announcement->setPicture($newFilename);
            //     } else return new Response('Photo Invalide', 400);
            // }
            if (isset($form['user'])) {
                if ($form['user']->isValid()) {
                    $user = $userRepository->find($form['user']->getData());
                    $announcement->setUser($user);
                } else return new Response('Utilisateur Invalide', 400);
            }
            $announcement->setCreatedAt(new \Datetime());

            // On sauvegarde en base
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($announcement);
            $entityManager->flush();

            // On retourne la confirmation
            return new Response('ok', 201);
        } else return new Response('Données non envoyées', 400);
    }

    /**
     * @Route("/{id}", name="delete", requirements={"id": "\d+"}, methods={"DELETE"})
     */
    public function delete(Announcement $announcement)
    {
        // TODO VOTER 
        /**  // Ici on utilise un voter
         * // Cette fonction va émettre une exception Access Forbidden pour interdire l'accès au reste du contrôleur
         * // Les conditions pour lesquelles le droit MOVIE_DELETE est applicable sur $movie pour l'utilisateur connecté
         * // sont définies dans les voters, dans leurs méthodes voteOnAttribute()*/
        // $this->denyAccessUnlessGranted('MOVIE_DELETE', $movie);

        $em = $this->getDoctrine()->getManager();

        $em->remove($announcement);
        $em->flush();

        // On retourne la confirmation
        return new Response('supression ok', 200);
    }
}
