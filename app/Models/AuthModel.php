<?php

namespace App\Models;

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
        'business_id',
        'branch_id',
        'name',
        'email',
        'password_hash',
        'mobileno',
        'user_type',
        'is_active',
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
        return $this->where('is_active', true)->where('email', $email)->first();
    }
}
