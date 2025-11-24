<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\User;
use Kreait\Firebase\Factory;
use Spatie\Permission\Models\Role;

class FirebaseController extends Controller
{
	public function login(Request $request)
	{
		$request->validate([
			'idToken' => 'required|string',
		]);

		try {
			$factory = (new Factory())->withServiceAccount(env('FIREBASE_CREDENTIALS'));

			if ($projectId = env('FIREBASE_PROJECT_ID')) {
				$factory = $factory->withProjectId($projectId);
			}

			$auth = $factory->createAuth();

			$verifiedIdToken = $auth->verifyIdToken($request->idToken);
			$claims = $verifiedIdToken->claims();

			$email = $claims->get('email');
			$displayName = trim((string) ($claims->get('name') ?? ''));
			$emailVerified = (bool) ($claims->get('email_verified') ?? false);

			if (empty($email) || !str_ends_with($email, '@pcu.edu.ph')) {
				abort(403, 'Unauthorized: Only @pcu.edu.ph emails allowed');
			}

			if ($displayName === '') {
				$displayName = strstr($email, '@', true) ?: $email;
			}

			$firstName = '';
			$lastName = '';

			if ($displayName !== '') {
				$parts = preg_split('/\s+/', $displayName);
				if (!empty($parts)) {
					$firstName = array_shift($parts) ?? '';
					$lastName = trim(implode(' ', $parts));
				}
			}

			if ($firstName === '') {
				[$firstName] = explode('@', $email, 2);
			}

			$user = User::firstOrNew(['email' => $email]);
			$user->first_name = $firstName;
			$user->last_name = $lastName;

			if ($emailVerified && empty($user->email_verified_at)) {
				$user->email_verified_at = now();
			}

			if (! $user->exists || empty($user->password)) {
				$user->password = Hash::make(Str::random(40));
			}

			$user->save();

			$localPart = Str::before($email, '@');
			$roleName = str_contains($localPart, '.') ? 'faculty' : 'student';

			Role::findOrCreate($roleName);

			if (! $user->hasRole($roleName) || $user->roles->count() > 1) {
				$user->syncRoles([$roleName]);
			}

			Auth::login($user);

			if ($user->can('view faculty')) {
				// return redirect('/faculty');
                return response()->json(['redirect' => '/faculty'], 200);
			}

			if ($user->can('view student')) {
				// return redirect('/student');
                return response()->json(['redirect' => '/student'], 200);
			}

			return redirect('/');
		} catch (\Throwable $e) {
			\Log::error('Firebase login failed', [
				'project_id' => env('FIREBASE_PROJECT_ID'),
				'credentials' => env('FIREBASE_CREDENTIALS'),
				'exception' => $e,
			]);

			return response()->json(['error' => $e->getMessage()], 401);
		}
	}
}