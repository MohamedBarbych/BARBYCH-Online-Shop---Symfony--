<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Intl\Countries;

class PayPalController extends AbstractController
{
    #[Route('/pay_pal/index', name: 'paypal_payment')]
    public function index(Request $request): Response
    {
        $countries = Countries::getNames();
        $countries = array_flip($countries);

        $form = $this->createFormBuilder()
            ->add('fullName', TextType::class, [
                'label' => 'Full Name',
            ])
            ->add('nationality', ChoiceType::class, [
                'label' => 'Nationality',
                'choices' => array_flip(Countries::getNames()),
            ])
            ->add('address', TextType::class, [
                'label' => 'Address',
            ])
            ->add('paypalCardNumber', TextType::class, [
                'label' => 'PayPal card number',
            ])
            ->add('expirationDate', TextType::class, [
                'label' => 'Expiration date',
            ])
            ->add('cvc', TextType::class, [
                'label' => 'CVC',
            ])
            ->add('codepostal', TextType::class, [
                'label' => 'Postal Code',
            ])
            ->add('city', TextType::class, [
                'label' => 'City',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Confirmer le paiement',
            ])
            ->getForm();

        // Traitez le formulaire soumis
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Simulez le succès ou l'échec du paiement
            $paymentSuccess = true; // Mettez à true ou false en fonction du résultat du paiement

            if ($paymentSuccess) {
                // Redirection vers la page de succès
                return $this->redirectToRoute('payment_success');
            } else {
                // Redirection vers la page d'échec
                return $this->redirectToRoute('payment_failure');
            }
        }

        // Affichez le formulaire
        return $this->render('paypal/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/payment/success', name: 'payment_success')]
    public function paymentSuccess(): Response
    {
        // Affichez la page de succès du paiement
        return $this->render('paypal/payment_success.html.twig');
    }

    #[Route('/payment/failure', name: 'payment_failure')]
    public function paymentFailure(): Response
    {
        // Affichez la page d'échec du paiement
        return $this->render('paypal/payment_failure.html.twig');
    }
}