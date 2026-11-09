<?php
namespace App\Actions\Fortify;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Laravel\Fortify\Contracts\CreatesNewUsers;
class CreateNewUser implements CreatesNewUsers
{
    /**
     * Validate and create a newly registered user.
     * Self-registered accounts always get the "user" role.
     *
     * @param array<string, string> $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email'
            ],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
        ])->validate();
        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
            'role' => UserRole::User,
        ]);
    }
}