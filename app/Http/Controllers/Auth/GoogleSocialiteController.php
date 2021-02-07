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
                'access_token' => $user->token,
                'refresh_token' => $user->refreshToken,
                'expires_in' => $user->expiresIn
            ];

            $client = new Google_Client();
            $client->setApplicationName("Laravel");
            $client->setDeveloperKey('AIzaSyBkNcbcNPyh8dnaOarxyBLj1mGnwOAqdGE');
           
            
            $service = new Google_Service_People($client);
            $optParams = array('requestMask.includeField' => 'person.phone_numbers,person.names,person.email_addresses');
            $results = $service->people_connections->listPeopleConnections('people/me',$optParams);
           


            #$otherContacts = $peopleService->otherContacts;
            #$optParams = array('requestMask.includeField' => 'person.phone_numbers,person.names,person.email_addresses');

            #$result = $otherContacts->listOtherContacts($optParams);

            dd($user,$client, $results);    



            #$contactsUser = new Google_Service_PeopleService_Resource_OtherContacts; 
            #$optParams = array('requestMask.includeField' => 'person.phone_numbers,person.names,person.email_addresses');
            #$result = $contactsUser->listOtherContacts($optParams);

            #dd($user, $result);

            #Google_Service_PeopleService_Resource_OtherContacts
            #listOtherContacts

            #$client = new Google_Client();
            #$client->setApplicationName("Laravel");
            #$client->setDeveloperKey('AIzaSyBkNcbcNPyh8dnaOarxyBLj1mGnwOAqdGE');
            #$client->setAccessToken(json_encode($google_client_token));
            
            
        

            $finduser = User::where('social_id', $user->id)->first();
      
            if($finduser){
      
                Auth::login($finduser);
     
                return redirect('/home');
      
            }else{
                $newUser = User::create([
                    'name' => $user->name,
                    'email' => $user->email,
                    'social_id'=> $user->id,
                    'social_type'=> 'google',
                    'password' => encrypt('my-google')
                ]);
     
                Auth::login($newUser);
      
                return redirect('/home');
            }
     
        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }

    public function getGoogleContacts()
    {
        $code = Input::get('code');

        $googleService = Auth::consumer('Google');

        if (!is_null($code)) {
            
            $token = $googleService->requestAccessToken($code);
    
            
            $result = json_decode($googleService->request('https://www.google.com/m8/feeds/contacts/default/full?alt=json&amp;max-results=400'), true);
        }else{
            
            $url = $googleService->getAuthorizationUri();
    
            
            return redirect((string)$url);
        }
    }
}