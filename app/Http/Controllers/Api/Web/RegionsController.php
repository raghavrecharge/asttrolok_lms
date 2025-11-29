<?php

namespace App\Http\Controllers\Api\Web;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Models\Region;
use Illuminate\Support\Facades\DB;

class RegionsController extends Controller
{

    public function countries()
    {
        try {
            $countries = Region::select(DB::raw('*, ST_AsText(geo_center) as geo_center'))
                ->where('type', Region::$country)
                ->get();
            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'),
                $countries
            );
        } catch (\Exception $e) {
            \Log::error('countries error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function provinces($id = null)
    {
        try {
            $region_id = $id;
            return $this->region(Region::$province, 'country_id', $region_id);
        } catch (\Exception $e) {
            \Log::error('provinces error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function cities($id = null)
    {
        try {
            $region_id = $id;
            return $this->region(Region::$city, 'province_id', $region_id);
        } catch (\Exception $e) {
            \Log::error('cities error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function districts($id = null)
    {
        try {
            $region_id = $id;
            return $this->region(Region::$district, 'city_id', $region_id);
        } catch (\Exception $e) {
            \Log::error('districts error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function region($type, $super_region_type, $super_region_id)
    {
        try {
            $user = apiAuth();
            $region_id = $super_region_id;
            $provinces = Region::select(DB::raw('*, ST_AsText(geo_center) as geo_center'))
                ->where('type', $type);

            if ($region_id) {

                $provinces = $provinces->where($super_region_type, $region_id);
            }
            $provinces = $provinces->get();

            foreach ($provinces as $province) {
                $province->geo_center = \Geo::get_geo_array($province->geo_center);
            }

            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'),
                $provinces
            );
        } catch (\Exception $e) {
            \Log::error('region error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

}
