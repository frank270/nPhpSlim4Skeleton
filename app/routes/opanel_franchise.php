<?php
use Slim\Routing\RouteCollectorProxy;
use App\Actions\Opanel\FranchiseInquiryAction;

return function (RouteCollectorProxy $group) {
    $group->group('/franchise-inquiries', function (RouteCollectorProxy $group) {
        $group->get('', [FranchiseInquiryAction::class, 'index']); // Render React Container
        $group->get('/list', [FranchiseInquiryAction::class, 'list']); // API List
        $group->post('/{id:[0-9]+}/status', [FranchiseInquiryAction::class, 'toggleStatus']); // API Status Toggle
    });
};
