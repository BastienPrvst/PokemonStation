<?php

namespace App\Controller;

use App\Entity\Friendship;
use App\Entity\User;
use App\Form\DataTransformer\UserToIdTransformer;
use App\Form\FriendshipFormType;
use App\Repository\FriendshipRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FriendshipController extends AbstractController
{
    public function __construct(
        private UserToIdTransformer $userToIdTransformer,
        private EntityManagerInterface $em
    ) {}

    #[Route('/friendship', name: 'app_friendship')]
    public function index(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $friendship = (new Friendship)
            ->setFriendA($user);

        $form = $this->createForm(FriendshipFormType::class, $friendship);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var FriendshipRepository $friendshipRepository */
            $friendshipRepository = $this->em->getRepository(Friendship::class);
            $existingFriendship = $friendshipRepository->existingFriendship($user, $form->getData()->getFriendB());

            if ($existingFriendship) {
                $this->addFlash('error', 'Vous êtes déjà ami avec cet utilisateur.');
                return $this->redirectToRoute('app_friendship');
            }

            /** @var Friendship $friendship */
            $friendship = $form->getData()
                ->setCreatedAt(new \DateTimeImmutable);

            $this->em->persist($friendship);
            $this->em->flush();

            // Message flash de succès
            $this->addFlash('success', 'Demande envoyée avec succès.');

            return $this->redirectToRoute('app_friendship');
        }

        return $this->render('main/friend.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    #[Route('/friendship/remove/{friendship}', name: 'app_friendship_remove')]
    public function remove(Friendship $friendship): Response
    {
        $this->em->remove($friendship);
        $this->em->flush();

        return $this->redirectToRoute('app_friendship');
    }

    #[Route('/friendship/accept/{friendship}', name: 'app_friendship_accept')]
    public function accept(Friendship $friendship): Response
    {
        $friendship->setAccepted(true);

        $this->em->persist($friendship);
        $this->em->flush();

        return $this->redirectToRoute('app_friendship');
    }
}
