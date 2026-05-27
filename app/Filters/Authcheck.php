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
