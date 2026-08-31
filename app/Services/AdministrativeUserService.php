<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdministrativeUserService
{
    public function create(array $data, User $actor, Request $request): User
    {
        return DB::transaction(function () use ($data, $actor, $request): User {
            $user = User::query()->create([
                ...Arr::only($data, ['name', 'email', 'password', 'role', 'is_active']),
                'must_change_password' => true,
                'email_verified_at' => now(),
                'created_by' => $actor->id,
            ]);

            $this->audit($actor, $user, 'created', [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'is_active' => $user->is_active,
            ], $request);

            return $user;
        });
    }

    public function update(User $user, array $data, User $actor, Request $request): User
    {
        return DB::transaction(function () use ($user, $data, $actor, $request): User {
            $this->guardProtectedMaster($user, $data, $actor);
            $before = Arr::only($user->toArray(), ['name', 'email', 'role', 'is_active', 'must_change_password']);
            $attributes = Arr::only($data, ['name', 'email', 'role', 'is_active']);
            $resetPassword = (bool) ($data['reset_password'] ?? false);
            $unlockAccess = (bool) ($data['unlock_access'] ?? false) && $user->login_locked_until?->isFuture();

            if ($resetPassword) {
                $attributes['password'] = $data['password'];
                $attributes['must_change_password'] = true;
            }

            $user->update($attributes);
            $changes = [];

            foreach ($before as $field => $oldValue) {
                $newValue = $user->getAttribute($field);
                if ($oldValue != $newValue) {
                    $changes[$field] = ['from' => $oldValue, 'to' => $newValue];
                }
            }

            if ($resetPassword) {
                $changes['password'] = ['from' => 'unchanged', 'to' => 'temporary_password_assigned'];
            }

            if ($changes !== []) {
                $this->audit($actor, $user, 'updated', $changes, $request);
            }

            if ($unlockAccess) {
                $lockChanges = [
                    'reason' => $user->login_lock_reason,
                    'locked_at' => $user->login_locked_at?->toDateTimeString(),
                    'locked_until' => $user->login_locked_until?->toDateTimeString(),
                ];

                $user->forceFill([
                    'failed_login_attempts' => 0,
                    'login_locked_at' => null,
                    'login_locked_until' => null,
                    'login_lock_reason' => null,
                ])->save();

                $this->audit($actor, $user, 'account_unlocked', $lockChanges, $request);
            }

            return $user;
        });
    }

    private function guardProtectedMaster(User $user, array $data, User $actor): void
    {
        $removesMasterAccess = $user->isMaster()
            && ($data['role'] !== 'master' || ! $data['is_active']);

        if ($user->is($actor) && $removesMasterAccess) {
            throw ValidationException::withMessages([
                'role' => 'No puedes retirar tu propio acceso master ni desactivar tu cuenta.',
            ]);
        }

        if ($removesMasterAccess) {
            $otherActiveMasters = User::query()
                ->where('id', '!=', $user->getKey())
                ->where('role', 'master')
                ->where('is_active', true)
                ->lockForUpdate()
                ->exists();

            if (! $otherActiveMasters) {
                throw ValidationException::withMessages([
                    'role' => 'Debe permanecer al menos un usuario master activo.',
                ]);
            }
        }
    }

    private function audit(User $actor, User $target, string $action, array $changes, Request $request): void
    {
        DB::table('admin_user_audits')->insert([
            'actor_user_id' => $actor->id,
            'target_user_id' => $target->id,
            'action' => $action,
            'changes' => json_encode($changes, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
            'created_at' => now(),
        ]);
    }
}
