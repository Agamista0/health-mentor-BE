<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth;
use Exception;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    protected $auth;
    protected $firebase;
    protected $defaultCountryCode = '+966';

    public function __construct()
    {
        $this->firebase = (new Factory)
            ->withServiceAccount(storage_path('app/firebase-credentials.json'))
            ->withDatabaseUri('https://mohja-app.firebaseio.com');
            
        $this->auth = $this->firebase->createAuth();
    }

    /**
     * Format phone number to E.164 format
     */
    private function formatPhoneNumber($phone, $countryCode)
    {
        // Remove any non-digit characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // If phone starts with 0, remove it
        if (substr($phone, 0, 1) === '0') {
            $phone = substr($phone, 1);
        }
        
        // Add country code if not present
        if (substr($phone, 0, 1) !== '+') {
            // Remove + from country code if present
            $countryCode = ltrim($countryCode, '+');
            $phone = '+' . $countryCode . $phone;
        }
        
        Log::info('Formatted phone number: ' . $phone);
        return $phone;
    }

    /**
     * Generate a unique Firebase UID
     */
    private function generateUid($type, $identifier)
    {
        // Remove any non-alphanumeric characters and limit length
        $cleanIdentifier = preg_replace('/[^a-zA-Z0-9]/', '', $identifier);
        $truncatedIdentifier = substr($cleanIdentifier, 0, 100); // Firebase UID max length is 128
        return $type . '_' . $truncatedIdentifier . '_' . time();
    }

    /**
     * Start phone number verification
     */
    public function startPhoneVerification($phone, $countryCode = null)
    {
        try {
            // Format phone number
            $phoneNumber = $this->formatPhoneNumber($phone, $countryCode ?? $this->defaultCountryCode);
            
            Log::info('Attempting phone verification for: ' . $phoneNumber);
            
            // Generate a unique UID for this verification attempt
            $uid = $this->generateUid('phone', $phoneNumber);
            
            // Generate a 6-digit OTP
            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            
            try {
                // Try to create a new user
                $user = $this->auth->createUser([
                    'uid' => $uid,
                    'phoneNumber' => $phoneNumber
                ]);
            } catch (Exception $e) {
                // If user exists with this phone, get the user
                try {
                    $user = $this->auth->getUserByPhoneNumber($phoneNumber);
                    $uid = $user->uid;
                } catch (Exception $innerE) {
                    // If we can't find or create user, throw the original error
                    throw $e;
                }
            }
            
            // Store OTP in user's custom claims
            $this->auth->setCustomUserClaims($uid, [
                'phoneNumber' => $phoneNumber,
                'otp' => $otp,
                'otpExpiry' => time() + (5 * 60) // 5 minutes expiry
            ]);
            
            // Create a custom token for the session
            $customToken = $this->auth->createCustomToken($uid);
            
            // TODO: Implement SMS sending here
            // For now, we'll return the OTP in the response (remove in production)
            
            Log::info('Phone verification OTP generated successfully');
            
            return [
                'success' => true,
                'sessionInfo' => $customToken->toString(),
                'verification_type' => 'phone',
                'otp' => $otp // Remove this in production
            ];
        } catch (Exception $e) {
            Log::error('Phone verification failed: ' . $e->getMessage(), [
                'phone' => $phone,
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'verification_type' => 'phone'
            ];
        }
    }

    /**
     * Start email verification process
     */
    public function startEmailVerification($email, $name = null)
    {
        try {
            // Check if user exists in Firebase
            try {
                $firebaseUser = $this->auth->getUserByEmail($email);
                // User exists, send sign in link
                $actionCodeSettings = [
                    'url' => config('app.url') . '/verify-email',
                    'handleCodeInApp' => true,
                ];
                $this->auth->sendSignInLinkToEmail($email, $actionCodeSettings);
            } catch (Exception $e) {
                // User doesn't exist, create new user
                $userProperties = [
                    'email' => $email,
                    'emailVerified' => false,
                    'displayName' => $name,
                ];
                $firebaseUser = $this->auth->createUser($userProperties);
                
                // Send verification email
                $actionCodeSettings = [
                    'url' => config('app.url') . '/verify-email',
                    'handleCodeInApp' => true,
                ];
                $this->auth->sendEmailVerificationLink($email, $actionCodeSettings);
            }
            
            return [
                'success' => true,
                'verification_type' => 'email',
                'uid' => $firebaseUser->uid
            ];
        } catch (Exception $e) {
            Log::error('Email verification failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Failed to send verification email.',
                'verification_type' => 'email'
            ];
        }
    }

    /**
     * Verify phone number with code
     */
    public function verifyPhoneNumber($sessionInfo, $code)
    {
        try {
            $verifiedNumber = $this->auth->verifyPhoneNumber([
                'sessionInfo' => $sessionInfo,
                'code' => $code,
            ]);

            // Create or get Firebase user
            $firebaseUser = $this->auth->getUserByPhoneNumber($verifiedNumber->phoneNumber());

            return [
                'success' => true,
                'uid' => $firebaseUser->uid,
                'phoneNumber' => $verifiedNumber->phoneNumber(),
                'verification_type' => 'phone'
            ];
        } catch (Exception $e) {
            Log::error('Phone verification failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Invalid verification code.',
                'verification_type' => 'phone'
            ];
        }
    }

    /**
     * Verify email with sign-in link
     */
    public function verifyEmail($email, $signInLink)
    {
        try {
            // Verify the email sign-in link
            $signInResult = $this->auth->signInWithEmailLink($email, $signInLink);
            $firebaseUser = $signInResult->data()['user'];

            return [
                'success' => true,
                'uid' => $firebaseUser->uid,
                'email' => $firebaseUser->email,
                'verification_type' => 'email'
            ];
        } catch (Exception $e) {
            Log::error('Email verification failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Invalid verification link.',
                'verification_type' => 'email'
            ];
        }
    }

    /**
     * Create custom token for authenticated session
     */
    public function createCustomToken($uid)
    {
        try {
            $customToken = $this->auth->createCustomToken($uid);
            return [
                'success' => true,
                'token' => $customToken->toString()
            ];
        } catch (Exception $e) {
            Log::error('Custom token creation failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Failed to create authentication token.'
            ];
        }
    }

    /**
     * Send phone verification code
     */
    public function sendPhoneOTP(string $phone, string $countryCode)
    {
        try {
            // Format phone number
            $phoneNumber = $this->formatPhoneNumber($phone, $countryCode);
            
            Log::info('Attempting phone verification for: ' . $phoneNumber);
            
            // Generate a unique UID for this verification attempt
            $uid = $this->generateUid('phone', $phoneNumber);
            
            // Create or update user in Firebase
            try {
                $user = $this->auth->createUser([
                    'uid' => $uid,
                    'phoneNumber' => $phoneNumber
                ]);
            } catch (Exception $e) {
                // User might already exist, try to update
                $user = $this->auth->updateUser($uid, [
                    'phoneNumber' => $phoneNumber
                ]);
            }
            
            // Generate a 6-digit OTP
            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            
            // Set custom claims for the user
            $this->auth->setCustomUserClaims($uid, [
                'phoneNumber' => $phoneNumber,
                'otp' => $otp,
                'otpExpiry' => time() + (5 * 60) // 5 minutes expiry
            ]);
            
            // Create a custom token for this session
            $customToken = $this->auth->createCustomToken($uid);
            
            Log::info('Phone verification OTP generated successfully');
            
            return [
                'success' => true,
                'sessionInfo' => $customToken->toString(),
                'verification_type' => 'phone',
                'otp' => $otp // Remove this in production
            ];
        } catch (Exception $e) {
            Log::error('Phone verification failed: ' . $e->getMessage(), [
                'phone' => $phone,
                'country_code' => $countryCode,
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'verification_type' => 'phone'
            ];
        }
    }

    /**
     * Verify phone OTP
     */
    public function verifyPhoneOTP(string $sessionInfo, string $otp)
    {
        try {
            // Verify and decode the token
            $decodedToken = $this->auth->verifyIdToken($sessionInfo);
            $uid = $decodedToken->claims()->get('sub');
            
            // Get user and their claims
            $user = $this->auth->getUser($uid);
            $claims = $user->customClaims ?? [];
            
            // Verify OTP
            if (!isset($claims['otp']) || $claims['otp'] !== $otp) {
                throw new Exception('Invalid OTP');
            }
            
            // Check OTP expiry
            if (!isset($claims['otpExpiry']) || time() > $claims['otpExpiry']) {
                throw new Exception('OTP has expired');
            }
            
            // Clear the OTP claims
            $this->auth->setCustomUserClaims($uid, [
                'phoneNumber' => $claims['phoneNumber'],
                'phoneVerified' => true,
                'verifiedAt' => time()
            ]);
            
            // Create a new token for authenticated session
            $customToken = $this->auth->createCustomToken($uid);

            return [
                'success' => true,
                'uid' => $uid,
                'token' => $customToken->toString()
            ];
        } catch (Exception $e) {
            Log::error('Phone OTP verification failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send email verification code
     */
    public function sendEmailOTP(string $email)
    {
        try {
            // Generate a unique UID for this verification attempt
            $uid = $this->generateUid('email', $email);
            
            // Create or update user in Firebase
            try {
                $user = $this->auth->createUser([
                    'uid' => $uid,
                    'email' => $email
                ]);
            } catch (Exception $e) {
                // User might already exist, try to update
                $user = $this->auth->updateUser($uid, [
                    'email' => $email
                ]);
            }
            
            // Generate a 6-digit OTP
            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            
            // Set custom claims for the user
            $this->auth->setCustomUserClaims($uid, [
                'email' => $email,
                'otp' => $otp,
                'otpExpiry' => time() + (5 * 60) // 5 minutes expiry
            ]);
            
            // Create a custom token for this session
            $customToken = $this->auth->createCustomToken($uid);
            
            // TODO: Send OTP via email using your email provider
            
            return [
                'success' => true,
                'otp' => $otp, // Remove this in production
                'token' => $customToken->toString(),
                'verification_type' => 'email'
            ];
        } catch (Exception $e) {
            Log::error('Email OTP generation failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Verify email OTP
     */
    public function verifyEmailOTP(string $email, string $otp)
    {
        try {
            // Get user by email
            $user = $this->auth->getUserByEmail($email);
            $claims = $user->customClaims ?? [];
            
            // Verify OTP
            if (!isset($claims['otp']) || $claims['otp'] !== $otp) {
                throw new Exception('Invalid OTP');
            }
            
            // Check OTP expiry
            if (!isset($claims['otpExpiry']) || time() > $claims['otpExpiry']) {
                throw new Exception('OTP has expired');
            }
            
            // Clear the OTP claims and mark email as verified
            $this->auth->setCustomUserClaims($user->uid, [
                'email' => $email,
                'emailVerified' => true,
                'verifiedAt' => time()
            ]);

            // Create custom token for authentication
            $customToken = $this->auth->createCustomToken($user->uid);

            return [
                'success' => true,
                'uid' => $user->uid,
                'token' => $customToken->toString()
            ];
        } catch (Exception $e) {
            Log::error('Email OTP verification failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Verify Firebase ID token
     */
    public function verifyToken(string $idToken)
    {
        try {
            return $this->auth->verifyIdToken($idToken);
        } catch (Exception $e) {
            throw new Exception('Invalid Firebase token: ' . $e->getMessage());
        }
    }

    /**
     * Get user by phone number
     */
    public function getUserByPhone(string $phoneNumber)
    {
        try {
            return $this->auth->getUserByPhoneNumber($phoneNumber);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Create or update user with phone number
     */
    public function createOrUpdateUser(string $phoneNumber, array $userData = [])
    {
        try {
            $properties = array_merge([
                'phoneNumber' => $phoneNumber,
                'disabled' => false
            ], $userData);

            // Try to get existing user
            try {
                $user = $this->auth->getUserByPhoneNumber($phoneNumber);
                // Update user if exists
                return $this->auth->updateUser($user->uid, $properties);
            } catch (\Exception $e) {
                // Create new user if doesn't exist
                return $this->auth->createUser($properties);
            }
        } catch (Exception $e) {
            throw new Exception('Failed to create/update Firebase user: ' . $e->getMessage());
        }
    }
} 