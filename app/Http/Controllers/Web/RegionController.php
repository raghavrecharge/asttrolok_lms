<?php

namespace App\Http\Controllers\Web;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegionController extends Controller
{
    public function provincesByCountry($countryId)
    {
        try {
            $provinces = Region::select(DB::raw('*, ST_AsText(geo_center) as geo_center'))
                ->where('type', Region::$province)
                ->where('country_id', $countryId)
                ->get();

            if (!empty($provinces)) {
                foreach ($provinces as $province) {
                    $province->geo_center = \Geo::get_geo_array($province->geo_center);
                }
            }

            return response()->json([
                'code' => 200,
                'provinces' => $provinces
            ]);
        } catch (\Exception $e) {
            \Log::error('provincesByCountry error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function citiesByProvince($provinceId)
    {
        try {
            $cities = Region::select(DB::raw('*, ST_AsText(geo_center) as geo_center'))
                ->where('type', Region::$city)
                ->where('province_id', $provinceId)
                ->get();

            if (!empty($cities)) {
                foreach ($cities as $city) {
                    $city->geo_center = \Geo::get_geo_array($city->geo_center);
                }
            }

            return response()->json([
                'code' => 200,
                'cities' => $cities
            ]);
        } catch (\Exception $e) {
            \Log::error('citiesByProvince error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function districtsByCity($cityId)
    {
        try {
            $districts = Region::select(DB::raw('*, ST_AsText(geo_center) as geo_center'))
                ->where('type', Region::$district)
                ->where('city_id', $cityId)
                ->get();

            if (!empty($districts)) {
                foreach ($districts as $district) {
                    $district->geo_center = \Geo::get_geo_array($district->geo_center);
                }
            }

            return response()->json([
                'code' => 200,
                'districts' => $districts
            ]);
        } catch (\Exception $e) {
            \Log::error('districtsByCity error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
