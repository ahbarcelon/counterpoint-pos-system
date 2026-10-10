<?php

namespace App\Commands;

use App\Models\UserModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CreateStaff extends BaseCommand
{
    protected $group = 'POS';
    protected $name = 'pos:create-staff';
    protected $description = 'Create the first or an additional staff account interactively.';

    public function run(array $params)
    {
        $password = getenv('POS_INITIAL_PASSWORD');
        if (! is_string($password) || strlen($password) < 8) {
            CLI::error('Set POS_INITIAL_PASSWORD to a temporary password of at least 8 characters before running this command.');
            return EXIT_ERROR;
        }
        $username = trim(CLI::prompt('Username'));
        $fullName = trim(CLI::prompt('Full name'));
        if (! preg_match('/\A[A-Za-z0-9_.-]{1,50}\z/', $username) || $fullName === '' || mb_strlen($fullName) > 100) {
            CLI::error('Use a username of 1–50 letters, numbers, dots, underscores, or hyphens and a full name of 1–100 characters.');
            return EXIT_ERROR;
        }
        $users = new UserModel();
        if ($users->where('username', $username)->first()) {
            CLI::error('That username already exists.');
            return EXIT_ERROR;
        }
        $created = $users->insert([
            'username' => $username,
            'full_name' => $fullName,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        if ($created === false) {
            CLI::error('The staff account could not be saved.');
            return EXIT_ERROR;
        }
        CLI::write('Staff account created. The password was stored as a one-way hash.', 'green');
        return EXIT_SUCCESS;
    }
}
