<?php

declare(strict_types=1);

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

/**
 * Class AuthCheck
 *
 * This filter ensures that a user is authenticated before accessing
 * certain routes. If the user is not logged in, they will be redirected
 * to the login page.
 *
 * @package App\Filters
 * @author Vinothkumar Jeyaraman
 */
class AuthCheck implements FilterInterface
{
    /**
     * Executed before the controller.
     *
     * Checks if the user is logged in. If not, redirects to the login page.
     *
     * @param RequestInterface $request The incoming request instance
     * @param array|null $arguments Optional arguments passed to the filter
     * @return ResponseInterface|null Returns a redirect response if user is not logged in, otherwise null
     */
    public function before(RequestInterface $request, $arguments = null): ?ResponseInterface
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $referenceCode = (string) (session()->get('auth_reference') ?? '');
        if ($referenceCode === '') {
            session()->destroy();
            return redirect()->to('/login');
        }

        $row = db_connect()->table('users u')
            ->select('u.user_id, u.reference_code, u.name, u.email, u.profile_image, u.user_type, u.shop_id')
            ->join('shops s', 's.shop_id = u.shop_id', 'inner')
            ->where('u.reference_code', $referenceCode)
            ->where('u.is_active', 1)
            ->where('s.is_active', 1)
            ->get()
            ->getRowArray();

        if (empty($row) || empty($row['shop_id'])) {
            session()->destroy();
            return redirect()->to('/login');
        }

        session()->set([
            'auth_id' => (int) $row['user_id'],
            'auth_reference' => (string) $row['reference_code'],
            'auth_name' => (string) ($row['name'] ?? ''),
            'auth_email' => (string) ($row['email'] ?? ''),
            'auth_profile_image' => $row['profile_image'] ?? null,
            'auth_shop_id' => (int) $row['shop_id'],
            'auth_usertype' => (string) ($row['user_type'] ?? 'staff'),
            'isLoggedIn' => true,
        ]);

        return null;
    }

    /**
     * Executed after the controller.
     *
     * Can be used to modify the response before it's sent to the client.
     *
     * @param RequestInterface $request The incoming request instance
     * @param ResponseInterface $response The outgoing response instance
     * @param array|null $arguments Optional arguments passed to the filter
     * @return void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void
    {
        // Nothing to do after the response
    }
}
