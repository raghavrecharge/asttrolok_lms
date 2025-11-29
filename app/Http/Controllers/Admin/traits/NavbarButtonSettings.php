<?php

namespace App\Http\Controllers\Admin\traits;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Models\NavbarButton;
use App\Models\Role;
use App\Models\Translation\NavbarButtonTranslation;
use Illuminate\Http\Request;

trait NavbarButtonSettings
{
    protected $settingName = 'navbar_button';

    public function navbarButtonSettings(Request $request)
    {
        try {
            removeContentLocale();

            $this->authorize('admin_settings_personalization');

            $navbarButtons = NavbarButton::query()
                ->with([
                    'role'
                ])->get();

            $defaultLocal = getDefaultLocale();
            $locale = $request->get('locale', mb_strtolower($defaultLocal));

            $data = [
                'pageTitle' => trans('admin/main.settings'),
                'navbarButtons' => $navbarButtons,
                'name' => $this->settingName,
                'selectedLocale' => $locale,
                'roles' => Role::all()
            ];

            return view('admin.settings.personalization', $data);
        } catch (\Exception $e) {
            \Log::error('navbarButtonSettings error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function storeNavbarButtonSettings(Request $request)
    {
        try {
            $this->authorize('admin_settings_personalization');

            $itemId = $request->get('item_id');

            $this->validate($request, [
                'role_id' => 'required|unique:navbar_buttons' . (!empty($itemId) ? (',role_id,' . $itemId) : ''),
                'title' => 'required',
                'url' => 'required',
            ]);

            $data = $request->all();

            $roleId = (!empty($data['role_id']) and $data['role_id'] != 'for_guest') ? $data['role_id'] : null;
            $forGuest = (!empty($data['role_id']) and $data['role_id'] == 'for_guest');

            $navbarButton = NavbarButton::where('role_id', $roleId)
                ->where('for_guest', $forGuest)
                ->first();

            if (!empty($navbarButton) and $navbarButton->id != $itemId) {
                return back()->withErrors([
                    'role_id' => trans('validation.unique', ['attribute' => trans('admin/main.role')])
                ]);
            }

            if (empty($navbarButton)) {
                $navbarButton = NavbarButton::create([
                    'role_id' => (!empty($data['role_id']) and $data['role_id'] != 'for_guest') ? $data['role_id'] : null,
                    'for_guest' => (!empty($data['role_id']) and $data['role_id'] == 'for_guest'),
                ]);
            }

            NavbarButtonTranslation::updateOrCreate([
                'navbar_button_id' => $navbarButton->id,
                'locale' => mb_strtolower($data['locale']),
            ], [
                'title' => $data['title'],
                'url' => $data['url'],
            ]);

            return redirect(getAdminPanelUrl().'/settings/personalization/navbar_button');
        } catch (\Exception $e) {
            \Log::error('storeNavbarButtonSettings error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function navbarButtonSettingsEdit(Request $request, $id)
    {
        try {
            $this->authorize('admin_settings_personalization');

            $navbarButton = NavbarButton::findOrFail($id);

            $defaultLocal = getDefaultLocale();
            $locale = $request->get('locale', mb_strtolower($defaultLocal));
            storeContentLocale($locale, $navbarButton->getTable(), $navbarButton->id);

            $data = [
                'pageTitle' => trans('admin/main.settings'),
                'navbarButton' => $navbarButton,
                'roles' => Role::all(),
                'name' => $this->settingName,
                'selectedLocale' => $locale,
            ];

            return view('admin.settings.personalization', $data);
        } catch (\Exception $e) {
            \Log::error('navbarButtonSettingsEdit error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function navbarButtonSettingsDelete($id)
    {
        try {
            $this->authorize('admin_settings_personalization');

            $navbarButton = NavbarButton::findOrFail($id);

            $navbarButton->delete();

            return redirect(getAdminPanelUrl().'/settings/personalization/navbar_button');
        } catch (\Exception $e) {
            \Log::error('navbarButtonSettingsDelete error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
