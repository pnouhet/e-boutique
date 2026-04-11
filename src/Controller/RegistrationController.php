<?php

namespace App\Controller;

use App\Entity\CustomerAddress;
use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em,
    ): Response {
        $form = $this->createForm(RegistrationFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $user = new User();
            $user->setName($data['name']);
            $user->setEmail($data['email']);
            $user->setRoles(['ROLE_USER']);
            $user->setPassword(
                $passwordHasher->hashPassword($user, $form->get('plainPassword')->getData())
            );

            $address = new CustomerAddress();
            $address->setType('shipping');
            $address->setName($data['name']);
            $address->setFirstName('');
            $address->setPhone('');
            $address->setAddress($data['address']);
            $address->setCp($data['cp']);
            $address->setCity($data['city']);
            $address->setCountry($data['country']);

            $user->addAddress($address);

            $em->persist($user);
            $em->flush();

            return $this->redirect('/checkout');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
