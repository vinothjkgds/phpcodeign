<?php

declare(strict_types=1);

namespace App\Filters;

use App\Models\SaasUserModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class SaasAuthCheck implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null): ?ResponseInterface
    {
        if (!session()->get('saas_is_logged_in')) {
            return redirect()->to('/saas/login');
        }

        $referenceCode = (string) (session()->get('saas_reference') ?? '');
        if ($referenceCode === '') {
            session()->remove(['saas_is_logged_in', 'saas_user_id', 'saas_reference', 'saas_role']);
            return redirect()->to('/saas/login');
        }

        $model = new SaasUserModel();
        $user = $model->getActiveByReferenceCode($referenceCode);

        if (!$user) {
            session()->remove(['saas_is_logged_in', 'saas_user_id', 'saas_reference', 'saas_role']);
            return redirect()->to('/saas/login');
        }

        session()->set([
            'saas_is_logged_in' => true,
            'saas_user_id' => (int) $user['saas_user_id'],
            'saas_reference' => (string) $user['reference_code'],
            'saas_role' => (string) $user['role'],
            'auth_name' => (string) $user['name'],
            'auth_email' => (string) $user['email'],
            'auth_usertype' => 'saas',
        ]);

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void
    {
    }
}
