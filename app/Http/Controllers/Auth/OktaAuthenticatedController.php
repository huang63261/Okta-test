<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OktaAuthenticatedController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request)
    {
        session([
            // Generate a random state parameter for CSRF security
            'oauth_state' => bin2hex(random_bytes(10)),
            // Create the PKCE code verifier and code challenge
            'oauth_code_verifier' => bin2hex(random_bytes(50))
        ]);

        $hash = hash('sha256', $request->session()->get('oauth_code_verifier'), true);
        $code_challenge = rtrim(strtr(base64_encode($hash), '+/', '-_'), '=');

        // Build the authorization URL by starting with the authorization endpoint
        $authorization_endpoint = env('OKTA_OAUTH2_ISSUER').'/v1/authorize';
        $authorize_url = $authorization_endpoint.'?'.http_build_query([
            'response_type' => 'code',
            'client_id' => env('OKTA_OAUTH2_CLIENT_ID'),
            'state' => $request->session()->get('oauth_state'),
            'redirect_uri' => env('OKTA_OAUTH2_REDIRECT_URI'),
            'code_challenge' => $code_challenge,
            'code_challenge_method' => 'S256',
            'scope' => 'openid profile email',
        ]);

        return redirect($authorize_url);
    }


    public function authorization_code_callback_handler(Request $request)
    {
        if(empty($request->get('state')) || $request->get('state') != $request->session()->get('oauth_state')) {
            throw new Exception("state does not match");
        }

        if(!empty($request->get('error'))) {
            throw new Exception("authorization server returned an error: ".$request->get('error'));
        }

        if(empty($request->get('code'))) {
            throw new Exception("this is unexpected, the authorization server redirected without a code or an error");
        }

        // Exchange the authorization code for an access token and ID token
        // by making a request to the token endpoint
        $token_endpoint = env('OKTA_OAUTH2_ISSUER').'/v1/token';

        $ch = curl_init($token_endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'authorization_code',
            'code' => $request->get('code'),
            'code_verifier' => $request->session()->get('oauth_code_verifier'),
            'redirect_uri' => env('OKTA_OAUTH2_REDIRECT_URI'),
            'client_id' => env('OKTA_OAUTH2_CLIENT_ID'),
            'client_secret' => env('OKTA_OAUTH2_CLIENT_SECRET'),
        ]));
        $response = json_decode(curl_exec($ch), true);

        if(isset($response['error'])) {
            throw new Exception("token endpoint returned an error: ".$response['error']);
        }

        if(!isset($response['access_token'])) {
            throw new Exception("token endpoint did not return an error or an access token");
        }

        // Save the tokens in the session
        session(['okta_access_token' => $response['access_token']]);

        if(isset($response['refresh_token'])) {
            session(['okta_refresh_token' => $response['refresh_token']]);
        }

        if(isset($response['id_token'])) {
            session(['okta_id_token' => $response['id_token']]);
        }


        /**
         * 顯示Okta 使用者 identity
         */
        $claims = json_decode(base64_decode(explode('.', $request->session()->get('okta_id_token'))[1]), true);

        // 以Email於系統中查詢是否存在使用者
        $localUser = User::where('email', $claims['email'])->first();

        // 登入
        try {
            Auth::login($localUser);
        } catch (\Throwable $e) {
            return redirect('/register');
        }

        return redirect(RouteServiceProvider::HOME);
    }
}
