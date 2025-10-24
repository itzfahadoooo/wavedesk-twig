<?php

namespace App\Controller;

use App\Service\ToastService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SomeController extends AbstractController
{
    #[Route('/demo-toast', name: 'demo_toast')]
    public function demo(ToastService $toastService): Response
    {
        $toastService->showToast('Welcome back!', 'success');
        $toastService->showToast('Something went wrong.', 'error');
        $toastService->showToast('Just a heads-up!', 'info');

        return $this->redirectToRoute('home');
    }
}
