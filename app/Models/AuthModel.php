<?php

namespace App\Models;

use App\Traits\IsActiveTrait;
use CodeIgniter\Model;

/**
 * Class AuthModel
 *
 * Handles database operations related to user authentication.
 * Provides methods to fetch and manage user data for login and user management.
 *
 * @package App\Models
 * @author  Vinothkumar J
 * @version 1.0
 */
class AuthModel extends Model
{
    use IsActiveTrait;

    /**
     * Database table name.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * Primary key column.
     *
     * @var string
     */
    protected $primaryKey = 'reference_code'; // Using UUID-based reference code as primary key

    /**
     * Allowed fields for mass assignment.
     *
     * These fields are permitted to be inserted or updated
     * via the model’s save/insert/update methods.
     *
     * @var array
     */
    protected $allowedFields = [
        'shop_id',
        'reference_code',
        'name',
        'email',
        'password_hash',
        'mobileno',
        'profile_image',
        'id_proof_type',
        'id_proof_number',
        'id_proof_front_image',
        'id_proof_back_image',
        'user_type',
        'is_active',
        'last_login_at',
    ];

    /**
     * Enable automatic timestamp management.
     *
     * @var bool
     */
    protected $useTimestamps = true;

    /**
     * Database field used to store record creation timestamp.
     *
     * @var string
     */
    protected $createdField = 'created_at';

    /**
     * Database field used to store record update timestamp.
     *
     * @var string
     */
    protected $updatedField = 'updated_at';

    /**
     * Retrieve user authentication record by email.
     *
     * @param string $email The user email address.
     * @return array|null Returns user data as an associative array if found, otherwise null.
     */
    public function getAuthByEmail(string $email): ?array
    {
        $row = $this->db->table('users u')
            ->select('u.user_id, u.reference_code, u.shop_id, u.name, u.email, u.password_hash, u.profile_image, u.user_type')
            ->join('shops s', 's.shop_id = u.shop_id', 'inner')
            ->where('u.email', $email)
            ->where('u.is_active', 1)
            ->where('s.is_active', 1)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }
}
