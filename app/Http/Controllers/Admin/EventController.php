<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventPaymentLink;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::with(['paymentLink', 'payments'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('admin.events.index', compact('events'));
    }

    public function create()
    {
        return view('admin.events.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category' => 'nullable|string|max:100',
            'max_participants' => 'required|integer|min:1',
            'event_date' => 'required|date|after:now',
            'registration_deadline' => 'required|date|before_or_equal:event_date',
            'location' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $event = Event::create($request->except('image'));

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/events'), $imageName);
            $event->image = 'uploads/events/' . $imageName;
            $event->save();
        }

        // Create payment link
        $paymentLink = new EventPaymentLink([
            'event_id' => $event->id,
            'expires_at' => $event->registration_deadline,
            'status' => 'active'
        ]);
        $paymentLink->save();
        $paymentLink->generatePaymentLink();

        return redirect()
            ->route('admin.events.show', $event->id)
            ->with('success', 'Event created successfully! Payment link generated.');
    }

    public function show($id)
    {
        $event = Event::with(['paymentLink', 'payments', 'registrations'])
            ->findOrFail($id);
        
        return view('admin.events.show', compact('event'));
    }

    public function edit($id)
    {
        $event = Event::findOrFail($id);
        return view('admin.events.edit', compact('event'));
    }

    public function update(Request $request, $id)
    {
        $event = Event::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category' => 'nullable|string|max:100',
            'max_participants' => 'required|integer|min:1',
            'event_date' => 'required|date|after:now',
            'registration_deadline' => 'required|date|before_or_equal:event_date',
            'location' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $event->update($request->except('image'));

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($event->image && file_exists(public_path($event->image))) {
                unlink(public_path($event->image));
            }
            
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads/events'), $imageName);
            $event->image = 'uploads/events/' . $imageName;
            $event->save();
        }

        // Update payment link expiry
        if ($event->paymentLink) {
            $event->paymentLink->expires_at = $event->registration_deadline;
            $event->paymentLink->save();
        }

        return redirect()
            ->route('admin.events.show', $event->id)
            ->with('success', 'Event updated successfully!');
    }

    public function destroy($id)
    {
        $event = Event::findOrFail($id);
        
        // Delete image
        if ($event->image && file_exists(public_path($event->image))) {
            unlink(public_path($event->image));
        }
        
        $event->delete();

        return redirect()
            ->route('admin.events.index')
            ->with('success', 'Event deleted successfully!');
    }

    public function toggleStatus($id)
    {
        $event = Event::findOrFail($id);
        
        if ($event->status === 'active') {
            $event->status = 'closed';
            if ($event->paymentLink) {
                $event->paymentLink->status = 'disabled';
                $event->paymentLink->save();
            }
        } elseif ($event->status === 'closed') {
            $event->status = 'active';
            if ($event->paymentLink && !$event->paymentLink->isExpired()) {
                $event->paymentLink->status = 'active';
                $event->paymentLink->save();
            }
        }
        
        $event->save();

        return redirect()
            ->route('admin.events.show', $event->id)
            ->with('success', 'Event status updated successfully!');
    }

    public function regenerateLink($id)
    {
        $event = Event::findOrFail($id);
        
        if ($event->paymentLink) {
            $event->paymentLink->generatePaymentLink();
        } else {
            $paymentLink = new EventPaymentLink([
                'event_id' => $event->id,
                'expires_at' => $event->registration_deadline,
                'status' => 'active'
            ]);
            $paymentLink->save();
            $paymentLink->generatePaymentLink();
        }

        return redirect()
            ->route('admin.events.show', $event->id)
            ->with('success', 'Payment link regenerated successfully!');
    }
}
