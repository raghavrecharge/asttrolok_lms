<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Log;
use Exception;

use App\Exports\NewslettersExport;
use App\Http\Controllers\Controller;
use App\Mail\SendNotifications;
use App\Models\Newsletter;
use App\Models\NewsletterHistory;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class NewslettersController extends Controller
{
    public function index()
    {
        try {
            $this->authorize('admin_newsletters_lists');

            $newsletters = Newsletter::orderBy('created_at', 'desc')
                ->paginate(10);

            $data = [
                'pageTitle' => trans('admin/main.newsletters'),
                'newsletters' => $newsletters
            ];

            return view('admin.newsletters.lists', $data);
        } catch (\Exception $e) {
            \Log::error('index error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function send()
    {
        try {
            $this->authorize('admin_newsletters_send');

            $data = [
                'pageTitle' => trans('update.send_newsletter')
            ];

            return view('admin.newsletters.send', $data);
        } catch (\Exception $e) {
            \Log::error('send error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function sendNewsletter(Request $request)
    {
        try {
            $this->authorize('admin_newsletters_send');

            $this->validate($request, [
                'title' => 'required|string',
                'description' => 'required|string',
                'send_method' => 'required|in:send_to_all,send_to_bcc,send_to_excel',
                'bcc_email' => 'required_if:send_method,send_to_bcc' . ($request->get('send_method') == 'send_to_bcc' ? '|email' : ''),
                'excel' => 'required_if:send_method,send_to_excel' . ($request->get('send_method') == 'send_to_excel' ? '|mimes:xlsx' : ''),
            ]);

            $data = $request->all();

            $title = $data['title'];
            $description = $data['description'];

            if ($data['send_method'] == 'send_to_bcc') {
                $send = $this->handleSendToCC($data);
            } elseif ($data['send_method'] == 'send_to_excel') {
                $send = $this->handleSentToExcelList($data);
            } else {
                $send = $this->handleSendToAllNewsletters($title, $description);
            }

            if ($send == false) {
                return back()->withInput($data);
            }

            NewsletterHistory::create([
                'title' => $title,
                'description' => $description,
                'send_method' => $data['send_method'],
                'bcc_email' => $data['bcc_email'] ?? null,
                'email_count' => $send ?? 0,
                'created_at' => time(),
            ]);

            return redirect(getAdminPanelUrl().'/newsletters/history');
        } catch (\Exception $e) {
            \Log::error('sendNewsletter error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    private function handleSendToAllNewsletters($title, $description)
    {
        $this->authorize('admin_newsletters_send');

        $newsletters = Newsletter::orderBy('created_at', 'desc')->get();

        if (!isProductionDomain()) {
            return count($newsletters);
        }

        try {
            foreach ($newsletters as $newsletter) {
                \Mail::to($newsletter->email)->send(new SendNotifications(['title' => $title, 'message' => $description]));
            }

            return count($newsletters);
        } catch (Exception $e) {
            session()->put('send_email_error', $e->getMessage());

            return false;
        }
    }

    private function handleSendToCC($data)
    {
        $this->authorize('admin_newsletters_send');

        $title = $data['title'];
        $description = $data['description'];
        $email = $data['bcc_email'];

        $ccEmails = Newsletter::orderBy('created_at', 'desc')->pluck('email')->toArray();

        if (!isProductionDomain()) {
            return count($ccEmails);
        }

        try {
            \Mail::to($email)->send(new SendNotifications(['title' => $title, 'message' => $description, 'cc' => $ccEmails]));

            return count($ccEmails);
        } catch (Exception $e) {
            session()->put('send_email_error', $e->getMessage());

            return false;
        }
    }

    private function handleSentToExcelList($data)
    {
        $this->authorize('admin_newsletters_send');

        $title = $data['title'];
        $description = $data['description'];
        $excel = $data['excel'];

        if (!isProductionDomain()) {
            return 0;
        }

        try {
            $rows = Excel::toArray(null, $excel);

            if (!empty($rows) and count($rows[0])) {
                foreach ($rows[0] as $row) {
                    if (!empty($row) and !empty($row[0])) {
                        $email = $row[0];

                        \Mail::to($email)->send(new SendNotifications(['title' => $title, 'message' => $description]));
                    }
                }
            }

            return count($rows[0]);
        } catch (Exception $e) {
            session()->put('send_email_error', $e->getMessage());

            return false;
        }
    }

    public function history()
    {
        try {
            $this->authorize('admin_newsletters_history');

            $newsletters = NewsletterHistory::orderBy('created_at','desc')
                ->paginate(10);

            $data = [
                'pageTitle' => trans('update.newsletters_history'),
                'newsletters' => $newsletters
            ];

            return view('admin.newsletters.history', $data);
        } catch (\Exception $e) {
            \Log::error('history error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function delete($id)
    {
        try {
            $this->authorize('admin_newsletters_delete');

            $item = Newsletter::findOrFail($id);

            $item->delete();

            return back();
        } catch (\Exception $e) {
            \Log::error('delete error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    public function exportExcel()
    {
        try {
            $this->authorize('admin_newsletters_export_excel');

            $newsletters = Newsletter::orderBy('created_at', 'desc')
                ->get();

            $newslettersExport = new NewslettersExport($newsletters);

            return Excel::download($newslettersExport, trans('admin/main.newsletters') . '.xlsx');
        } catch (\Exception $e) {
            \Log::error('exportExcel error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
}
