<?php

namespace App\Helpers;

class LocationHelper
{
    public static function getLocationNames($countryId, $stateId, $cityId)
    {
        $path = public_path('json/countries_states_cities.json');
        $json = json_decode(file_get_contents($path), true);

        $countryName = $stateName = $cityName = null;

        foreach ($json as $country) {
            if ($country['id'] == $countryId) {
                $countryName = $country['name'];

                foreach ($country['states'] as $state) {
                    if ($state['id'] == $stateId) {
                        $stateName = $state['name'];

                        foreach ($state['cities'] as $city) {
                            if ($city['id'] == $cityId) {
                                $cityName = $city['name'];
                                break;
                            }
                        }
                    }
                }
            }
        }

        return [
            'country' => $countryName,
            'state'   => $stateName,
            'city'    => $cityName,
        ];
    }
}
