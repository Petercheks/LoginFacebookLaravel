<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Socialite;
use Auth;
use Exception;
use App\Models\User;
use Google_Client;
use Google_Service_People;
use Google_Service_PeopleService;
use Google_Service_PeopleService_Resource_OtherContacts;


class GoogleSocialiteController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function handleCallback()
    {

        try {

            $user = Socialite::driver('google')->user();

            $google_client_token = [
                'client_id' => $user->id,
                'access_token' => $user->token,
                'refresh_token' => $user->refreshToken,
                'expires_in' => $user->expiresIn
            ];


            $client = new Google_Client($google_client_token);
            $client->setApplicationName("LoginFacebookLaravel");
            $client->setDeveloperKey('AIzaSyBkNcbcNPyh8dnaOarxyBLj1mGnwOAqdGE');
            $client->setAccessToken($user->token);
            $client->getOAuth2Service();
            $client->addScope('https://www.googleapis.com/auth/contacts.other.readonly');

            dd($user, $client);
            #$service = new Google_Service_People($client);
            $peopleService = new Google_Service_PeopleService($client);

            $otherContacts = $peopleService->otherContacts;
            $optParams = array('readMask' => 'person.email_addresses');
            $contacts = $otherContacts->listOtherContacts($optParams);

            dd($user,$peopleService, $otherContacts, $contacts);


            $finduser = User::where('social_id', $user->id)->first();

            if($finduser){

                Auth::login($finduser);

                return redirect('/dashboard');

            }else{
                $newUser = User::create([
                    'name' => $user->name,
                    'email' => $user->email,
                    'social_id'=> $user->id,
                    'social_type'=> 'google',
                    'password' => encrypt('my-google')
                ]);

                Auth::login($newUser);

                return redirect('/dashboard');
            }

        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }
}
