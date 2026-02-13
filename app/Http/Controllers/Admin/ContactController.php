<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Http\Controllers\Controller;
use App\Mail\sendContactReply;
use App\Mail\SendNotifications;
use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index()
    {
        try {
            $this->authorize('admin_contacts_lists');

            $contacts = Contact::orderBy('status', 'asc')
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            $data = [
                'pageTitle' => trans('admin/pages/users.contacts_lists'),
                'contacts' => $contacts
            ];

            return view('admin.contacts.lists', $data);
        } catch (\Exception $e) {
            \Log::error('index error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function reply($id)
    {
        try {
            $this->authorize('admin_contacts_reply');

            $contact = Contact::findOrFail($id);

            $data = [
                'pageTitle' => trans('admin/main.reply'),
                'contact' => $contact
            ];

            return view('admin.contacts.reply', $data);
        } catch (\Exception $e) {
            \Log::error('reply error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function storeReply(Request $request, $id)
    {
        $this->authorize('admin_contacts_reply');

        $this->validate($request, [
            'reply' => 'required'
        ]);

        $reply = $request->get('reply');

        $contact = Contact::findOrFail($id);
        $contact->reply = $reply;
        $contact->status = 'replied';
        $contact->save();

        if (!empty($contact->email)) {
            try {
            \Mail::to($contact->email)->send(new sendContactReply($contact));
            } catch (\Exception $e) {

}
        }

        return redirect(getAdminPanelUrl().'/contacts');
    }

    public function delete($id)
    {
        try {
            $this->authorize('admin_contacts_delete');

            $contact = Contact::findOrFail($id);

            $contact->delete();

            return redirect(getAdminPanelUrl().'/contacts');
        } catch (\Exception $e) {
            \Log::error('delete error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
