<?php

namespace App\Controller;

use App\Entity\CustomerAddress;
use App\Form\ProfileFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function index(): Response
    {
        $user = $this->getUser();

        return $this->render('profile/index.html.twig', [
            'user'    => $user,
            'address' => $user->getAddresses()->first() ?: null,
            'orders'  => $user->getOrders(),
        ]);
    }

    #[Route('/profile/edit', name: 'app_profile_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
    ): Response {
        $user    = $this->getUser();
        $address = $user->getAddresses()->first();

        $form = $this->createForm(ProfileFormType::class, $user, [
            'data' => $user,
        ]);

        // Pre-fill unmapped address fields
        if ($address) {
            $form->get('address')->setData($address->getAddress());
            $form->get('cp')->setData($address->getCp());
            $form->get('city')->setData($address->getCity());
            $form->get('country')->setData($address->getCountry());
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plain = $form->get('plainPassword')->getData();
            if ($plain) {
                $user->setPassword($passwordHasher->hashPassword($user, $plain));
            }

            // Update or create shipping address
            if (!$address) {
                $address = new CustomerAddress();
                $address->setType('shipping')->setName($user->getName())->setFirstName('');
                $user->addAddress($address);
            }
            $address->setAddress($form->get('address')->getData());
            $address->setCp($form->get('cp')->getData());
            $address->setCity($form->get('city')->getData());
            $address->setCountry($form->get('country')->getData());

            $em->flush();

            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/edit.html.twig', [
            'form' => $form,
        ]);
    }
}
