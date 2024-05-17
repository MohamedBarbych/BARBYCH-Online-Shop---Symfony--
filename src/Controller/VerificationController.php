<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use DateTime;

class VerificationController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/verification', name: 'app_verification')]
    public function index(Request $request): Response
    {
        $form = $this->createFormBuilder()
            ->add('verifCode', TextType::class, [
                'label' => 'Verification Code:',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Confirm Code',
            ])
            ->getForm();

        // Traitez le formulaire soumis
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $verificationCode = $form->get('verifCode')->getData();
            
            // Récupérer l'utilisateur correspondant au code de confirmation
            $userRepository = $this->entityManager->getRepository(User::class);
            $user = $userRepository->findOneBy(['confirmationCode' => $verificationCode]);

            if ($user && $this->isConfirmationCodeValid($user)) {
                // Redirection vers la page de succès si l'utilisateur est trouvé et que le code est valide
                return $this->redirectToRoute('verification_success');
            } else {
                // Redirection vers la page d'échec si l'utilisateur n'est pas trouvé ou que le code est invalide
                return $this->redirectToRoute('verification_failure');
            }
        }

        // Affichez le formulaire
        return $this->render('verification/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Méthode pour vérifier si le code de confirmation est toujours valide (dans les 10 minutes)
    private function isConfirmationCodeValid(User $user): bool
    {
        $currentDateTime = new DateTime();
        $codeCreatedAt = $user->getConfirmationCodeCreatedAt();

        // Calculer la différence entre les deux dates et heures
        $interval = $currentDateTime->diff($codeCreatedAt);

        // Vérifier si la différence dépasse 10 minutes
        return ($interval->i <= 10);
    }

    #[Route('/verification/success', name: 'verification_success')]
    public function verificationSuccess(): Response
    {
        // Affichez la page de succès du paiement
        return $this->render('verification/verification_success.html.twig');
    }

    #[Route('/verification/failure', name: 'verification_failure')]
    public function verificationFailure(): Response
    {
        // Affichez la page d'échec du paiement
        return $this->render('verification/verification_failure.html.twig');
    }
}
