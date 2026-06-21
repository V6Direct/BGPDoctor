<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\AgentKey;
use App\Models\Router;
use App\Models\RouterGroup;

final class RouterController
{
    public function index(): void
    {
        $routers = (new Router())->allWithSummary();
        $groups  = (new RouterGroup())->all();
        View::render('routers/index', [
            'routers' => $routers,
            'groups'  => $groups,
            'csrf'    => Csrf::token(),
            'user'    => Auth::user(),
        ]);
    }

    public function create(): void
    {
        $groups  = (new RouterGroup())->all();
        $apiKeys = (new AgentKey())->all();
        View::render('routers/form', [
            'router'  => null,
            'groups'  => $groups,
            'apiKeys' => $apiKeys,
            'csrf'    => Csrf::token(),
            'user'    => Auth::user(),
            'errors'  => [],
        ]);
    }

    public function store(): void
    {
        if (!Csrf::validate(Request::input('_csrf'))) {
            Response::redirect('/routers.php?error=csrf');
        }

        $hostname = trim((string) Request::input('hostname'));
        $asn      = Request::input('asn') !== null && Request::input('asn') !== '' ? (int) Request::input('asn') : null;
        $groupId  = Request::input('group_id') ? (int) Request::input('group_id') : null;
        $software = trim((string) Request::input('software', 'bird2/pathvector'));
        $apiKeyId = (int) Request::input('api_key_id', 1);

        $errors = [];
        if ($hostname === '') {
            $errors[] = 'Hostname is required.';
        }
        if ($asn !== null && ($asn < 1 || $asn > 4294967295)) {
            $errors[] = 'ASN must be between 1 and 4294967295.';
        }

        if ($errors) {
            $groups  = (new RouterGroup())->all();
            $apiKeys = (new AgentKey())->all();
            View::render('routers/form', compact('errors', 'groups', 'apiKeys') + ['router' => null, 'csrf' => Csrf::token(), 'user' => Auth::user()]);
            return;
        }

        (new Router())->create($hostname, $asn, $groupId, $software, $apiKeyId);
        Response::redirect('/routers.php?success=created');
    }

    public function edit(int $id): void
    {
        $router  = (new Router())->findById($id);
        if (!$router) {
            Response::redirect('/routers.php?error=notfound');
        }
        $groups  = (new RouterGroup())->all();
        $apiKeys = (new AgentKey())->all();
        View::render('routers/form', [
            'router'  => $router,
            'groups'  => $groups,
            'apiKeys' => $apiKeys,
            'csrf'    => Csrf::token(),
            'user'    => Auth::user(),
            'errors'  => [],
        ]);
    }

    public function update(int $id): void
    {
        if (!Csrf::validate(Request::input('_csrf'))) {
            Response::redirect('/routers.php?error=csrf');
        }

        $hostname = trim((string) Request::input('hostname'));
        $asn      = Request::input('asn') !== '' ? (int) Request::input('asn') : null;
        $groupId  = Request::input('group_id') ? (int) Request::input('group_id') : null;
        $software = trim((string) Request::input('software', 'bird2/pathvector'));

        if ($hostname === '') {
            Response::redirect('/routers.php?error=validation');
        }

        (new Router())->update($id, $hostname, $asn, $groupId, $software);
        Response::redirect('/routers.php?success=updated');
    }

    public function delete(int $id): void
    {
        if (!Csrf::validate(Request::input('_csrf'))) {
            Response::redirect('/routers.php?error=csrf');
        }
        (new Router())->delete($id);
        Response::redirect('/routers.php?success=deleted');
    }
}
