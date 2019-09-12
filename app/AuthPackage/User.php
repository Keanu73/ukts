<?php


namespace App\AuthPackage;


use App\Booking;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class User extends Model
{

    protected static $unguarded = true;

    private static $defaultFields = [
        "name_first",
        "name_last",
    ];

    /*
     * Per-Service Relationships
     */

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /*
     * Default Function Overrides
     */

    public static function find($id, $fields = null)
    {
        $query =
            "{
                    user(id:$id){
                        " . self::generateParams($fields) . "
                    }
                }
            ";
        $response = self::makeApiCall($query);
        return isset($response->data->user) ? self::initModelWithData($response->data->user) : null;

    }

    /*
     * Backend API Functions
     */

    private static function generateParams($fields)
    {
        return implode(",", array_merge(['id'], $fields ? $fields : self::$defaultFields));
    }

    private static function makeApiCall($query)
    {
        $token = self::getAuthAccessToken();
        if (!$token) {
            return null;
        }

        $client = new Client([
            'headers' => [
                'Authorization' => "Bearer $token"
            ]
        ]);

        return json_decode($client->post(env('AUTH_ENDPOINT') . '/api', ['form_params' => ['query' => $query]])->getBody()->getContents());
    }

    /**
     * Generates or fetches Auth service token
     *
     * @return string|null
     */
    private static function getAuthAccessToken()
    {
        $token = Cache::get('AUTH_API_TOKEN');
        $token = null;
        if (!$token) {
            $guzzle = new Client;

            $response = $guzzle->post(env('AUTH_ENDPOINT') . '/oauth/token', [
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => env('AUTH_CLIENT_ID'),
                    'client_secret' => env('AUTH_SECRET'),
                    'scope' => '*'
                ]
            ]);

            try {
                $token = json_decode((string)$response->getBody(), true)['access_token'];
                Cache::put('AUTH_API_TOKEN', $token, \DateInterval::createFromDateString("1 day"));
            } catch (\Exception $e) {
                // TODO: Log Exception
                return null;
            }
        }
        return $token;
    }

    private static function initModelWithData($data)
    {
        $model = new self();

        return $model->fill((array)$data);
    }

}