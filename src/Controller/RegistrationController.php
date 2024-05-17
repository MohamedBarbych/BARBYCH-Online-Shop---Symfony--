<?php

namespace App\Controller;

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;


class RegistrationController extends AbstractController
{
    private $httpClient;
    private $entityManager;

    public function __construct(HttpClientInterface $httpClient, EntityManagerInterface $entityManager)
    {
        $this->httpClient = $httpClient;
        $this->entityManager = $entityManager;
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $confirmationCode = $this->generateConfirmationCode($user, $form->get('username')->getData());

            // Enregistrer l'utilisateur avec le code de confirmation dans la base de données
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            // Envoyer le code de confirmation
            if ($form->get('verificationMethod')->getData() === 'whatsapp') {
                $this->sendWhatsAppConfirmation($user->getPhoneNumber(), $confirmationCode, $form->get('username')->getData());
            } elseif ($form->get('verificationMethod')->getData() === 'voice_call') {
                $this->sendVoiceCallConfirmation($user->getPhoneNumber(), $confirmationCode, $form->get('username')->getData());
            }

            return $this->redirectToRoute('app_verification');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    private function sendWhatsAppConfirmation(string $phoneNumber, string $confirmationCode, string $username): void
{
    $message = "BARBYCH_BIO.ma: Welcome dear new customer $username !! This is your verification code: $confirmationCode. Please note that this code will expire in 10 minutes.";

    try {
        // Send the HTTP request to the Nexmo Messages API
        $response = $this->httpClient->request('POST', 'https://messages-sandbox.nexmo.com/v1/messages', [
            'auth_basic' => ['067a53b6', 'KZGmG93CWL0bzYWX'], // Make sure these credentials are correct
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'json' => [
                'from' => '14157386102',
                'to' => $phoneNumber,
                'message_type' => 'text',
                'text' => $message,
                'channel' => 'whatsapp'
            ]
        ]);

        // Check the response status code
        if ($response->getStatusCode() === 200) {
            echo "The WhatsApp message was successfully sent.";
        } else {
            echo "There was an error sending the WhatsApp message: " . $response->getStatusCode();
        }
    } catch (\Exception $e) {
        echo "An error occurred: " . $e->getMessage();
    }
}


    private function sendVoiceCallConfirmation(string $phoneNumber, string $confirmationCode, string $username): void
    {
        $message = "Welcome dear new customer $username !! This is your verification code: $confirmationCode. Please note that this code will expire in 10 minutes.";
    
        // Make the request to Infobip's API
        $response = $this->httpClient->request('POST', 'https://y3lxnp.api.infobip.com/tts/3/advanced', [
            'headers' => [
                'Authorization' => 'App 3bf459fabf98a25c3ad58db591dc9e0f-83d19640-8e8f-4e10-a11a-4221d90c3538',
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'json' => [
                'messages' => [
                    [
                        'from' => '212610626226',
                        'destinations' => [
                            ['to' => $phoneNumber]
                        ],
                        'text' => $message,
                        'language' => 'en',
                        'voice' => [
                            'name' => 'Joanna',
                            'gender' => 'female'
                        ],
                        'speechRate' => 1
                    ]
                ]
            ]
        ]);
    
        if ($response->getStatusCode() === 200) {
            // The voice call has been sent successfully
        } else {
            // There was an error sending the voice call
        }
    }
    

    private function generateConfirmationCode(User $user, string $username): string
    {
        $confirmationCode = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
        
        $user->setConfirmationCode($confirmationCode);
        $user->setConfirmationCodeCreatedAt(new DateTime());
    
        $message = "BARBYCH_BIO.ma; Welcome dear new customer $username !! 
        This is your verification code: $confirmationCode";
    
        return $confirmationCode;
    }
    
}
