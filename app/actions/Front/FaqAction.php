<?php
declare(strict_types=1);

namespace App\Actions\Front;

use App\Actions\BaseAction;
use App\Models\FaqsModel;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class FaqAction extends BaseAction
{
    public function index(Request $request, Response $response, array $args): Response
    {
        $faqModel = new FaqsModel($this->conn);
        $faqs = $faqModel->getPublished();

        return $this->view->render($response, 'frontend/faq/index.twig', [
            'title' => '常見問題',
            'faqs' => $faqs,
        ]);
    }
}
