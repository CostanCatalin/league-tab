<?php
namespace LeagueTab;

use Dotenv\Dotenv;
use GuzzleHttp;

class APIController {
    static private $key;
    private $server;
    private $data;
    private $client;

    
    function __construct() {
        $dotenv = new Dotenv(__DIR__ . '/..');
        $dotenv->load();
        $this->data = new \stdClass();
        self::$key = getenv('API_KEY');
    }

    public function getData($username, $server) {
        if (!$this->getUserDataFromForm($username, $server)) {
            // Redirect to 404 page, try again
            echo "Username not found";
        }

        if (!$this->getLastMatches()) {
            echo "Could not retrieve matches";
        }

        if (!$this->getChampionSkin()) {
            echo "Could not retrieve skins";
        }

        //http://ddragon.leagueoflegends.com/cdn/6.24.1/img/profileicon/{id}

        $version = $this->getLatestVersion();
        if (!$version) {
            echo "Could not retrieve version";
        } else {
            $this->data->profileIconId = "http://ddragon.leagueoflegends.com/cdn/$version/img/profileicon/" . $this->data->profileIconId . ".png";
        }

        return $this->data;
    }

    /**
     * //https://{server}.api.riotgames.com/lol/summoner/v3/summoners/by-name/{username}
     *
     * @param $username
     * @param $server
     * @return bool|mixed
     */
    public function getUserDataFromForm($username, $server) {
        $this->server = $server;

        $this->client = new GuzzleHttp\Client();

        try {
            $res = $this->client->get('https://' . rawurlencode($this->server) .
                '.api.riotgames.com/lol/summoner/v3/summoners/by-name/' . rawurlencode($username),
                ['headers' => ['X-Riot-Token' => self::$key]]);

            $body = GuzzleHttp\json_decode($res->getBody());

            $this->data->name          = $body->name;
            $this->data->accountId     = $body->accountId;
            $this->data->profileIconId = $body->profileIconId;
            $this->data->summonerLevel = $body->summonerLevel;

            return true;
        }catch (\Exception $e) {
            return false;
        }
    }

    /**
     * //https://{server}.api.riotgames.com/lol/match/v3/matchlists/by-account/{accountId}/recent
     *
     */
    public function getLastMatches() {
        try {
            $res = $this->client->get('https://' . rawurlencode($this->server) .
                '.api.riotgames.com/lol/match/v3/matchlists/by-account/' . $this->data->accountId . '/recent/',
                ['headers' => ['X-Riot-Token' => self::$key]]);

            $body = GuzzleHttp\json_decode($res->getBody());

            $matches = array();
            $champions = [];

            foreach ($body->matches as $match) {
                if (count($matches) < 3) {
                    array_push($matches, $match->gameId);
                }

                if (!isset($champions[$match->champion])) {
                    $champions[$match->champion] = 1;
                } else {
                    $champions[$match->champion]++;
                }
            }

            //Getting most played champion, and if there are two with the same number of games, the most recent
            $this->data->mostPlayedChamp = array_keys($champions, max($champions))[0];

            $this->data->matches = $matches;
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Storing all the skins such that I can randomize between them each refresh.
     *
     * https://{server}.api.riotgames.com/lol/static-data/v3/champions/{championId}?tags=skins
     *
     */
    public function getChampionSkin() {
        try {
            $res = $this->client->get('https://' . rawurlencode($this->server) .
                '.api.riotgames.com/lol/static-data/v3/champions/' . urlencode($this->data->mostPlayedChamp) . '?tags=skins',
                ['headers' => ['X-Riot-Token' => self::$key]]);

            $body = GuzzleHttp\json_decode($res->getBody());

            $skins = array();

            foreach ($body->skins as $skin) {
                array_push($skins, $skin->id);
            }

            $this->data->championName = $body->name ;
            $this->data->championKey = $body->key;
            $this->data->skins = $skins;

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     *
     * https://{server}.api.riotgames.com/lol/static-data/v3/versions
     */
    private function getLatestVersion() {
        try {
            $res = $this->client->get('https://' . rawurlencode($this->server) .
                '.api.riotgames.com/lol/static-data/v3/versions',
                ['headers' => ['X-Riot-Token' => self::$key]]);

            $body = GuzzleHttp\json_decode($res->getBody());

            return $body[0];
        } catch(\Exception $e) {
            return false;
        }

    }
}